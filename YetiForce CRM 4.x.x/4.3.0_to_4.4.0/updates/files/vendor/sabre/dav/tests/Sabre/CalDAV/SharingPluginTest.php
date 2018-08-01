<?php

namespace Sabre\CalDAV;

use Sabre\DAV;
use Sabre\DAV\Xml\Element\Sharee;
use Sabre\HTTP;

class SharingPluginTest extends \Sabre\DAVServerTest
{
	protected $setupCalDAV = true;
	protected $setupCalDAVSharing = true;
	protected $setupACL = true;
	protected $autoLogin = 'user1';

	public function setUp()
	{
		$this->caldavCalendars = [
			[
				'principaluri' => 'principals/user1',
				'id'           => 1,
				'uri'          => 'cal1',
			],
			[
				'principaluri' => 'principals/user1',
				'id'           => 2,
				'uri'          => 'cal2',
				'share-access' => \Sabre\DAV\Sharing\Plugin::ACCESS_READWRITE,
			],
			[
				'principaluri' => 'principals/user1',
				'id'           => 3,
				'uri'          => 'cal3',
			],
		];

		parent::setUp();

		// Making the logged in user an admin, for full access:
		$this->aclPlugin->adminPrincipals[] = 'principals/user2';
	}

	public function testSimple()
	{
		$this->assertInstanceOf('Sabre\\CalDAV\\SharingPlugin', $this->server->getPlugin('caldav-sharing'));
		$this->assertSame(
			'caldav-sharing',
			$this->caldavSharingPlugin->getPluginInfo()['name']
		);
	}

	/**
	 * @expectedException \LogicException
	 */
	public function testSetupWithoutCoreSharingPlugin()
	{
		$server = new DAV\Server();
		$server->addPlugin(
			new SharingPlugin()
		);
	}

	public function testGetFeatures()
	{
		$this->assertSame(['calendarserver-sharing'], $this->caldavSharingPlugin->getFeatures());
	}

	public function testBeforeGetShareableCalendar()
	{
		// Forcing the server to authenticate:
		$this->authPlugin->beforeMethod(new HTTP\Request(), new HTTP\Response());
		$props = $this->server->getProperties('calendars/user1/cal1', [
			'{' . Plugin::NS_CALENDARSERVER . '}invite',
			'{' . Plugin::NS_CALENDARSERVER . '}allowed-sharing-modes',
		]);

		$this->assertInstanceOf('Sabre\\CalDAV\\Xml\\Property\\Invite', $props['{' . Plugin::NS_CALENDARSERVER . '}invite']);
		$this->assertInstanceOf('Sabre\\CalDAV\\Xml\\Property\\AllowedSharingModes', $props['{' . Plugin::NS_CALENDARSERVER . '}allowed-sharing-modes']);
	}

	public function testBeforeGetSharedCalendar()
	{
		$props = $this->server->getProperties('calendars/user1/cal2', [
			'{' . Plugin::NS_CALENDARSERVER . '}shared-url',
			'{' . Plugin::NS_CALENDARSERVER . '}invite',
		]);

		$this->assertInstanceOf('Sabre\\CalDAV\\Xml\\Property\\Invite', $props['{' . Plugin::NS_CALENDARSERVER . '}invite']);
		//$this->assertInstanceOf('Sabre\\DAV\\Xml\\Property\\Href', $props['{' . Plugin::NS_CALENDARSERVER . '}shared-url']);
	}

	public function testUpdateResourceType()
	{
		$this->caldavBackend->updateInvites(1,
			[
				new Sharee([
					'href' => 'mailto:joe@example.org',
				])
			]
		);
		$result = $this->server->updateProperties('calendars/user1/cal1', [
			'{DAV:}resourcetype' => new DAV\Xml\Property\ResourceType(['{DAV:}collection'])
		]);

		$this->assertSame([
			'{DAV:}resourcetype' => 200
		], $result);

		$this->assertSame(0, count($this->caldavBackend->getInvites(1)));
	}

	public function testUpdatePropertiesPassThru()
	{
		$result = $this->server->updateProperties('calendars/user1/cal3', [
			'{DAV:}foo' => 'bar',
		]);

		$this->assertSame([
			'{DAV:}foo' => 200,
		], $result);
	}

	public function testUnknownMethodNoPOST()
	{
		$request = HTTP\Sapi::createFromServerArray([
			'REQUEST_METHOD' => 'PATCH',
			'REQUEST_URI'    => '/',
		]);

		$response = $this->request($request);

		$this->assertSame(501, $response->status, $response->body);
	}

	public function testUnknownMethodNoXML()
	{
		$request = HTTP\Sapi::createFromServerArray([
			'REQUEST_METHOD' => 'POST',
			'REQUEST_URI'    => '/',
			'CONTENT_TYPE'   => 'text/plain',
		]);

		$response = $this->request($request);

		$this->assertSame(501, $response->status, $response->body);
	}

	public function testUnknownMethodNoNode()
	{
		$request = HTTP\Sapi::createFromServerArray([
			'REQUEST_METHOD' => 'POST',
			'REQUEST_URI'    => '/foo',
			'CONTENT_TYPE'   => 'text/xml',
		]);

		$response = $this->request($request);

		$this->assertSame(501, $response->status, $response->body);
	}

	public function testShareRequest()
	{
		$request = new HTTP\Request('POST', '/calendars/user1/cal1', ['Content-Type' => 'text/xml']);

		$xml = <<<RRR
<?xml version="1.0"?>
<cs:share xmlns:cs="http://calendarserver.org/ns/" xmlns:d="DAV:">
    <cs:set>
        <d:href>mailto:joe@example.org</d:href>
        <cs:common-name>Joe Shmoe</cs:common-name>
        <cs:read-write />
    </cs:set>
    <cs:remove>
        <d:href>mailto:nancy@example.org</d:href>
    </cs:remove>
</cs:share>
RRR;

		$request->setBody($xml);

		$response = $this->request($request, 200);

		$this->assertSame(
			[
				new Sharee([
					'href'       => 'mailto:joe@example.org',
					'properties' => [
						'{DAV:}displayname' => 'Joe Shmoe',
					],
					'access'       => \Sabre\DAV\Sharing\Plugin::ACCESS_READWRITE,
					'inviteStatus' => \Sabre\DAV\Sharing\Plugin::INVITE_NORESPONSE,
					'comment'      => '',
				]),
			],
			$this->caldavBackend->getInvites(1)
		);

		// Wiping out tree cache
		$this->server->tree->markDirty('');

		// Verifying that the calendar is now marked shared.
		$props = $this->server->getProperties('calendars/user1/cal1', ['{DAV:}resourcetype']);
		$this->assertTrue(
			$props['{DAV:}resourcetype']->is('{http://calendarserver.org/ns/}shared-owner')
		);
	}

	public function testShareRequestNoShareableCalendar()
	{
		$request = new HTTP\Request(
			'POST',
			'/calendars/user1/cal2',
			['Content-Type' => 'text/xml']
		);

		$xml = '<?xml version="1.0"?>
<cs:share xmlns:cs="' . Plugin::NS_CALENDARSERVER . '" xmlns:d="DAV:">
    <cs:set>
        <d:href>mailto:joe@example.org</d:href>
        <cs:common-name>Joe Shmoe</cs:common-name>
        <cs:read-write />
    </cs:set>
    <cs:remove>
        <d:href>mailto:nancy@example.org</d:href>
    </cs:remove>
</cs:share>
';

		$request->setBody($xml);

		$response = $this->request($request, 403);
	}

	public function testInviteReply()
	{
		$request = HTTP\Sapi::createFromServerArray([
			'REQUEST_METHOD' => 'POST',
			'REQUEST_URI'    => '/calendars/user1',
			'CONTENT_TYPE'   => 'text/xml',
		]);

		$xml = '<?xml version="1.0"?>
<cs:invite-reply xmlns:cs="' . Plugin::NS_CALENDARSERVER . '" xmlns:d="DAV:">
    <cs:hosturl><d:href>/principals/owner</d:href></cs:hosturl>
    <cs:invite-accepted />
</cs:invite-reply>
';

		$request->setBody($xml);
		$response = $this->request($request);
		$this->assertSame(200, $response->status, $response->body);
	}

	public function testInviteBadXML()
	{
		$request = HTTP\Sapi::createFromServerArray([
			'REQUEST_METHOD' => 'POST',
			'REQUEST_URI'    => '/calendars/user1',
			'CONTENT_TYPE'   => 'text/xml',
		]);

		$xml = '<?xml version="1.0"?>
<cs:invite-reply xmlns:cs="' . Plugin::NS_CALENDARSERVER . '" xmlns:d="DAV:">
</cs:invite-reply>
';
		$request->setBody($xml);
		$response = $this->request($request);
		$this->assertSame(400, $response->status, $response->body);
	}

	public function testInviteWrongUrl()
	{
		$request = HTTP\Sapi::createFromServerArray([
			'REQUEST_METHOD' => 'POST',
			'REQUEST_URI'    => '/calendars/user1/cal1',
			'CONTENT_TYPE'   => 'text/xml',
		]);

		$xml = '<?xml version="1.0"?>
<cs:invite-reply xmlns:cs="' . Plugin::NS_CALENDARSERVER . '" xmlns:d="DAV:">
    <cs:hosturl><d:href>/principals/owner</d:href></cs:hosturl>
</cs:invite-reply>
';
		$request->setBody($xml);
		$response = $this->request($request);
		$this->assertSame(501, $response->status, $response->body);

		// If the plugin did not handle this request, it must ensure that the
		// body is still accessible by other plugins.
		$this->assertSame($xml, $request->getBody(true));
	}

	public function testPublish()
	{
		$request = new HTTP\Request('POST', '/calendars/user1/cal1', ['Content-Type' => 'text/xml']);

		$xml = '<?xml version="1.0"?>
<cs:publish-calendar xmlns:cs="' . Plugin::NS_CALENDARSERVER . '" xmlns:d="DAV:" />
';

		$request->setBody($xml);

		$response = $this->request($request);
		$this->assertSame(202, $response->status, $response->body);
	}

	public function testUnpublish()
	{
		$request = new HTTP\Request(
			'POST',
			'/calendars/user1/cal1',
			['Content-Type' => 'text/xml']
		);

		$xml = '<?xml version="1.0"?>
<cs:unpublish-calendar xmlns:cs="' . Plugin::NS_CALENDARSERVER . '" xmlns:d="DAV:" />
';

		$request->setBody($xml);

		$response = $this->request($request);
		$this->assertSame(200, $response->status, $response->body);
	}

	public function testPublishWrongUrl()
	{
		$request = new HTTP\Request(
			'POST',
			'/calendars/user1',
			['Content-Type' => 'text/xml']
		);

		$xml = '<?xml version="1.0"?>
<cs:publish-calendar xmlns:cs="' . Plugin::NS_CALENDARSERVER . '" xmlns:d="DAV:" />
';

		$request->setBody($xml);
		$this->request($request, 501);
	}

	public function testUnpublishWrongUrl()
	{
		$request = new HTTP\Request(
			'POST',
			'/calendars/user1',
			['Content-Type' => 'text/xml']
		);
		$xml = '<?xml version="1.0"?>
<cs:unpublish-calendar xmlns:cs="' . Plugin::NS_CALENDARSERVER . '" xmlns:d="DAV:" />
';

		$request->setBody($xml);

		$this->request($request, 501);
	}

	public function testUnknownXmlDoc()
	{
		$request = new HTTP\Request(
			'POST',
			'/calendars/user1/cal2',
			['Content-Type' => 'text/xml']
		);

		$xml = '<?xml version="1.0"?>
<cs:foo-bar xmlns:cs="' . Plugin::NS_CALENDARSERVER . '" xmlns:d="DAV:" />';

		$request->setBody($xml);

		$response = $this->request($request);
		$this->assertSame(501, $response->status, $response->body);
	}
}