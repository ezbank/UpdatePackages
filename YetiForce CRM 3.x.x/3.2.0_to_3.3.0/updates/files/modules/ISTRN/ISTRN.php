<?php
/**
 * ISTRN CRMEntity Class
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
include_once 'modules/Vtiger/CRMEntity.php';

class ISTRN extends Vtiger_CRMEntity
{

	var $table_name = 'u_yf_istrn';
	var $table_index = 'istrnid';
	protected $lockFields = ['istrn_status' => ['PLL_ACCEPTED']];

	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('u_yf_istrncf', 'istrnid');

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	var $tab_name = Array('vtiger_crmentity', 'u_yf_istrn', 'u_yf_istrncf');

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	var $tab_name_index = Array(
		'vtiger_crmentity' => 'crmid',
		'u_yf_istrn' => 'istrnid',
		'u_yf_istrncf' => 'istrnid');

	/**
	 * Mandatory for Listing (Related listview)
	 */
	var $list_fields = Array(
		/* Format: Field Label => Array(tablename, columnname) */
// tablename should not have prefix 'vtiger_'
		'subject' => Array('istrn', 'subject'),
		'Assigned To' => Array('crmentity', 'smownerid')
	);
	var $list_fields_name = Array(
		/* Format: Field Label => fieldname */
		'subject' => 'subject',
		'Assigned To' => 'assigned_user_id',
	);
// Make the field link to detail view
	var $list_link_field = 'subject';
// For Popup listview and UI type support
	var $search_fields = Array(
		/* Format: Field Label => Array(tablename, columnname) */
// tablename should not have prefix 'vtiger_'
		'subject' => Array('istrn', 'subject'),
		'Assigned To' => Array('vtiger_crmentity', 'assigned_user_id'),
	);
	var $search_fields_name = Array(
		/* Format: Field Label => fieldname */
		'subject' => 'subject',
		'Assigned To' => 'assigned_user_id',
	);
// For Popup window record selection
	var $popup_fields = Array('subject');
// For Alphabetical search
	var $def_basicsearch_col = 'subject';
// Column value to use on detail view record text display
	var $def_detailview_recname = 'subject';
// Used when enabling/disabling the mandatory fields for the module.
// Refers to vtiger_field.fieldname values.
	var $mandatory_fields = Array('subject', 'assigned_user_id');
	var $default_order_by = '';
	var $default_sort_order = 'ASC';

	/**
	 * Invoked when special actions are performed on the module.
	 * @param String Module name
	 * @param String Event Type
	 */
	public function vtlib_handler($moduleName, $eventType)
	{
		if ($eventType == 'module.postinstall') {
// TODO Handle actions after this module is installed.
		} else if ($eventType == 'module.disabled') {
// TODO Handle actions before this module is being uninstalled.
		} else if ($eventType == 'module.preuninstall') {
// TODO Handle actions when this module is about to be deleted.
		} else if ($eventType == 'module.preupdate') {
// TODO Handle actions before this module is updated.
		} else if ($eventType == 'module.postupdate') {
// TODO Handle actions after this module is updated.
		}
	}
}
