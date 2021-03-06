<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Vtiger_SaveWidgetPositions_Action extends Vtiger_IndexAjax_View
{
	public function process(\App\Request $request)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$positionsMap = $request->get('position');
		$sizesMap = $request->get('size');

		if ($positionsMap) {
			foreach ($positionsMap as $id => $position) {
				list($linkid, $widgetid) = array_pad(explode('-', $id), 2, false);
				if ($widgetid) {
					Vtiger_Widget_Model::updateWidgetPosition($position, null, (int) $widgetid, $currentUser->getId());
				} else {
					Vtiger_Widget_Model::updateWidgetPosition($position, (int) $linkid, null, $currentUser->getId());
				}
			}
		}
		if ($sizesMap) {
			foreach ($sizesMap as $id => $size) {
				list($linkid, $widgetid) = array_pad(explode('-', $id), 2, false);
				if ($widgetid) {
					Vtiger_Widget_Model::updateWidgetSize($size, null, (int) $widgetid, $currentUser->getId());
				} else {
					Vtiger_Widget_Model::updateWidgetSize($size, (int) $linkid, null, $currentUser->getId());
				}
			}
		}

		$response = new Vtiger_Response();
		$response->setResult(['Save' => 'OK']);
		$response->emit();
	}
}
