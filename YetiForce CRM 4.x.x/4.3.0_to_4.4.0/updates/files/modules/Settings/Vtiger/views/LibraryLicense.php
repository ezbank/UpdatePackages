<?php

/**
 * Library License View Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Koń <a.kon@yetiforce.com>
 */
class Settings_Vtiger_LibraryLicense_View extends Vtiger_BasicModal_View
{
	/**
	 * Checking permissions.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermittedForAdmin
	 */
	public function checkPermission(\App\Request $request)
	{
		if (!\App\User::getCurrentUserModel()->isAdmin()) {
			throw new \App\Exceptions\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
		}
	}

	/**
	 * Function get modal size.
	 *
	 * @param \App\Request $request
	 *
	 * @return string
	 */
	public function getSize(\App\Request $request)
	{
		return 'modal-lg';
	}

	/**
	 * Process function.
	 *
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$result = false;
		$fileContent = '';
		if ($request->isEmpty('license')) {
			$result = false;
		} else {
			$dir = ROOT_DIRECTORY . DIRECTORY_SEPARATOR . 'licenses' . DIRECTORY_SEPARATOR;
			$filePath = $dir . $request->getByType('license', 'Text') . '.txt';
			if (file_exists($filePath)) {
				$result = true;
				$fileContent = file_get_contents($filePath);
			} else {
				$result =false;
			}
		}

		$this->preProcess($request);
		$viewer = $this->getViewer($request);
		$qualifiedModuleName = $request->getModule(false);
		$viewer = $this->getViewer($request);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('FILE_EXIST', $result);
		$viewer->assign('FILE_CONTENT', $fileContent);
		$viewer->view('LibraryLicense.tpl', $qualifiedModuleName);
		$this->postProcess($request);
	}
}