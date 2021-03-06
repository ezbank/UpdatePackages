<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 *************************************************************************************/
/**
 * Vtiger Widget Model Class
 */
class Vtiger_Widget_Model extends Vtiger_Base_Model {

	public function getWidth() {
		$largerSizedWidgets = array('GroupedBySalesPerson', 'PipelinedAmountPerSalesPerson', 'GroupedBySalesStage', 'Funnel Amount','LeadsByIndustry');
		$title = $this->getName();
		if(in_array($title, $largerSizedWidgets)) {
			$this->set('width', '6');
		}

		$width = $this->get('width');
		if(empty($width)) {
			$this->set('width', '4');
		}
		return $this->get('width');
	}

	public function getHeight() {
		//Special case for History widget
		$title = $this->getTitle();
		if($title == 'History' || $title == 'Upcoming Activities' || $title == 'Overdue Activities') {
			$this->set('height', '2');
		}
		$height = $this->get('height');
		if(empty($height)) {
			$this->set('height', '1');
		}
		return $this->get('height');
	}

	public function getPositionCol($default=0) {
		$position = $this->get('position');
		if ($position) {
			$position = Zend_Json::decode(decode_html($position));
			return intval($position['col']);
		}
		return $default;
	}

	public function getPositionRow($default=0) {
		$position = $this->get('position');
		if ($position) {
			$position = Zend_Json::decode(decode_html($position));
			return intval($position['row']);
		}
		return $default;
	}

	/**
	 * Function to get the url of the widget
	 * @return <String>
	 */
	public function getUrl() {
		$url = decode_html($this->get('linkurl')).'&linkid='.$this->get('linkid');
		$widgetid = $this->has('widgetid')? $this->get('widgetid') : $this->get('id');
		$url .= '&widgetid=' . $widgetid .'&active='.$this->get('active');
		
		return $url;
	}

	/**
	 *  Function to get the Title of the widget
	 */
	public function getTitle() {
		$title = $this->get('title');
		if(!isset($title)) {
			$title = $this->get('linklabel');
		}
		return $title;
	}

	public function getName() {
		$widgetName = $this->get('name');
		if(empty($widgetName)){
			//since the html entitites will be encoded
			//TODO : See if you need to push decode_html to base model
			$linkUrl = decode_html($this->getUrl());
			preg_match('/name=[a-zA-Z]+/', $linkUrl, $matches);
			$matches = explode('=', $matches[0]);
			$widgetName = $matches[1];
			$this->set('name', $widgetName);
		}
		return $widgetName;
	}
	/**
	 * Function to get the instance of Vtiger Widget Model from the given array of key-value mapping
	 * @param <Array> $valueMap
	 * @return Vtiger_Widget_Model instance
	 */
	public static function getInstanceFromValues($valueMap) {
		$self = new self();
		$self->setData($valueMap);
		return $self;
	}

	public static function getInstance($linkId, $userId) {
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT * FROM vtiger_module_dashboard_widgets
			INNER JOIN vtiger_links ON vtiger_links.linkid = vtiger_module_dashboard_widgets.linkid
			WHERE linktype = ? AND vtiger_links.linkid = ? AND userid = ?', array('DASHBOARDWIDGET', $linkId, $userId));

		$self = new self();
		if($db->num_rows($result)) {
			$row = $db->query_result_rowdata($result, 0);
			$self->setData($row);
		}
		return $self;
	}

	public static function updateWidgetPosition($position, $linkId, $widgetId, $userId) {
		if (!$linkId && !$widgetId) return;

		$db = PearDatabase::getInstance();
		$sql = 'UPDATE vtiger_module_dashboard_widgets SET position=? WHERE userid=?';
		$params = array($position, $userId);
		if ($linkId) {
			$sql .= ' AND linkid = ?';
			$params[] = $linkId;
		} else if ($widgetId) {
			$sql .= ' AND id = ?';
			$params[] = $widgetId;
		}
		$db->pquery($sql, $params);
	}

	public static function getInstanceWithWidgetId($widgetId, $userId) {
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT * FROM vtiger_module_dashboard_widgets
			INNER JOIN vtiger_links ON vtiger_links.linkid = vtiger_module_dashboard_widgets.linkid
			WHERE linktype = ? AND vtiger_module_dashboard_widgets.id = ? AND userid = ?', array('DASHBOARDWIDGET', $widgetId, $userId));

		$self = new self();
		if($db->num_rows($result)) {
			$row = $db->query_result_rowdata($result, 0);
			if($row['linklabel'] == 'Mini List'){
				$minilistWidget = Vtiger_Widget_Model::getInstanceFromValues($row);
				$minilistWidgetModel = new Vtiger_MiniList_Model();
				$minilistWidgetModel->setWidgetModel($minilistWidget);
				$row['title'] = $minilistWidgetModel->getTitle();
			}
			$self->setData($row);
		}
		return $self;
	}

	/**
	 * Function to show a widget from the Users Dashboard
	 */
	public function show() {
		$db = PearDatabase::getInstance();
		if( 0 == $this->get('active') ){
			$query = 'UPDATE vtiger_module_dashboard_widgets SET `active` = ? WHERE id = ?';
			$params = array(1, $this->get('widgetid'));
			$db->pquery($query, $params);
		}
		$this->set('id', $this->get('widgetid'));
	}
	
	/**
	 * Function to remove the widget from the Users Dashboard
	 */
	public function remove($action = 'hide') {
		$db = PearDatabase::getInstance();
		if($action == 'delete')
			$db->pquery('DELETE FROM vtiger_module_dashboard_widgets WHERE id = ? AND blockid = ?',
				array($this->get('id'), $this->get('blockid')));
		else if($action == 'hide'){
			$query = 'UPDATE vtiger_module_dashboard_widgets SET `active` = ? WHERE id = ?';
			$params = array(0, $this->get('id'));
			$db->pquery($query, $params);
		}
	}

	/**
	 * Function returns URL that will remove a widget for a User
	 * @return <String>
	 */
	public function getDeleteUrl() {
		$url = 'index.php?module=Vtiger&action=RemoveWidget&linkid='. $this->get('linkid');
		$widgetid = $this->has('widgetid')? $this->get('widgetid') : $this->get('id');
		if ($widgetid) $url .= '&widgetid=' . $widgetid;
		return $url;
	}

	/**
	 * Function to check the Widget is Default widget or not
	 * @return <boolean> true/false
	 */
	public function isDefault() {
		if($this->get('isdefault') == 1) {
			return true;
		}
		return false;
	}
}