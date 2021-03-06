<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */

class Vtiger_DashBoard_View extends Vtiger_Index_View
{

	/**
	 * Function to check permission
	 * @param \App\Request $request
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(\App\Request $request)
	{
		$currentUserPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPrivilegesModel->hasModulePermission($request->getModule())) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	public function preProcessAjax(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$sourceModule = $request->getByType('sourceModule', 1);
		if (empty($sourceModule)) {
			$sourceModule = $moduleName;
		}
		$currentDashboard = $this->getDashboardId($request);
		$dashBoardModel = Vtiger_DashBoard_Model::getInstance($moduleName);
		$dashBoardModel->set('dashboardId', $currentDashboard);
		//check profile permissions for Dashboards
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = $userPrivilegesModel->hasModulePermission($moduleModel->getId());
		if ($permission) {
			$dashBoardModel->verifyDashboard($moduleName);
			$widgets = $dashBoardModel->getDashboards('Header');
		} else {
			$widgets = [];
		}
		$modulesWithWidget = Vtiger_DashBoard_Model::getModulesWithWidgets($sourceModule, $currentDashboard);
		$viewer->assign('CURRENT_DASHBOARD', $currentDashboard);
		$viewer->assign('DASHBOARD_TYPES', Settings_WidgetsManagement_Module_Model::getDashboardTypes());
		$viewer->assign('MODULES_WITH_WIDGET', $modulesWithWidget);
		$viewer->assign('USER_PRIVILEGES_MODEL', $userPrivilegesModel);
		$viewer->assign('MODULE_PERMISSION', $permission);
		$viewer->assign('WIDGETS', $widgets);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('SRC_MODULE_NAME', $sourceModule);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->view('dashboards/DashBoardPreProcessAjax.tpl', $moduleName);
	}

	public function preProcess(\App\Request $request, $display = true)
	{
		parent::preProcess($request, false);
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$currentDashboard = $this->getDashboardId($request);
		$dashBoardModel = Vtiger_DashBoard_Model::getInstance($moduleName);
		$dashBoardModel->set('dashboardId', $currentDashboard);
		//check profile permissions for Dashboards
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = $userPrivilegesModel->hasModulePermission($moduleModel->getId());
		if ($permission) {
			$dashBoardModel->verifyDashboard($moduleName);
			$widgets = $dashBoardModel->getDashboards('Header');
		} else {
			$widgets = [];
		}

		$viewer->assign('CURRENT_DASHBOARD', $currentDashboard);
		$viewer->assign('DASHBOARD_TYPES', Settings_WidgetsManagement_Module_Model::getDashboardTypes());
		$viewer->assign('USER_PRIVILEGES_MODEL', $userPrivilegesModel);
		$viewer->assign('MODULE_PERMISSION', $permission);
		$viewer->assign('WIDGETS', $widgets);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		if ($display) {
			$this->preProcessDisplay($request);
		}
	}

	public function preProcessTplName(\App\Request $request)
	{
		return 'dashboards/DashBoardPreProcess.tpl';
	}

	public function process(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$currentDashboard = $this->getDashboardId($request);
		\App\Session::set($request->getModule() . 'LastDashBoardId', $currentDashboard);
		$dashBoardModel = Vtiger_DashBoard_Model::getInstance($moduleName);
		$dashBoardModel->set('dashboardId', $currentDashboard);
		//check profile permissions for Dashboards
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = $userPrivilegesModel->hasModulePermission($moduleName);
		if ($permission) {
			$widgets = $dashBoardModel->getDashboards();
		} else {
			return;
		}
		$viewer->assign('WIDGETS', $widgets);
		$viewer->view('dashboards/DashBoardContents.tpl', $moduleName);
	}

	public function postProcess(\App\Request $request)
	{
		parent::postProcess($request);
	}

	/**
	 * Get dashboard id
	 * @param \App\Request $request
	 * @return int
	 */
	public function getDashboardId(\App\Request $request)
	{
		$dashboardId = false;
		if (!$request->isEmpty('dashboardId', true)) {
			$dashboardId = $request->getInteger('dashboardId');
		} elseif (\App\Session::has($request->getModule() . 'LastDashBoardId')) {
			$dashboardId = \App\Session::get($request->getModule() . 'LastDashBoardId');
		}
		if (!$dashboardId) {
			$dashboardId = Settings_WidgetsManagement_Module_Model::getDefaultDashboard();
		}
		$request->set('dashboardId', $dashboardId);
		return $dashboardId;
	}

	/**
	 * Function to get the list of Script models to be included
	 * @param \App\Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	public function getFooterScripts(\App\Request $request)
	{
		$headerScriptInstances = parent::getFooterScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = array(
			'~libraries/jquery/gridster/jquery.gridster.js',
			'~libraries/jquery/flot/jquery.flot.js',
			'~libraries/jquery/flot/jquery.flot.pie.js',
			'~libraries/jquery/flot/jquery.flot.stack.js',
			'~libraries/jquery/jqplot/jquery.jqplot.js',
			'~libraries/jquery/jqplot/plugins/jqplot.canvasTextRenderer.js',
			'~libraries/jquery/jqplot/plugins/jqplot.canvasAxisTickRenderer.js',
			'~libraries/jquery/jqplot/plugins/jqplot.pieRenderer.js',
			'~libraries/jquery/jqplot/plugins/jqplot.barRenderer.js',
			'~libraries/jquery/jqplot/plugins/jqplot.categoryAxisRenderer.js',
			'~libraries/jquery/jqplot/plugins/jqplot.pointLabels.js',
			'~libraries/jquery/jqplot/plugins/jqplot.canvasAxisLabelRenderer.js',
			'~libraries/jquery/jqplot/plugins/jqplot.funnelRenderer.js',
			'~libraries/jquery/jqplot/plugins/jqplot.donutRenderer.js',
			'~libraries/jquery/jqplot/plugins/jqplot.barRenderer.js',
			'~libraries/jquery/jqplot/plugins/jqplot.logAxisRenderer.js',
			'~libraries/jquery/jqplot/plugins/jqplot.enhancedLegendRenderer.js',
			'~libraries/jquery/jqplot/plugins/jqplot.enhancedPieLegendRenderer.js',
			'modules.Vtiger.resources.DashBoard',
			'modules.' . $moduleName . '.resources.DashBoard',
			'modules.Vtiger.resources.dashboards.Widget',
			'~libraries/fullcalendar/fullcalendar.js'
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}

	/**
	 * Function to get the list of Css models to be included
	 * @param \App\Request $request
	 * @return <Array> - List of Vtiger_CssScript_Model instances
	 */
	public function getHeaderCss(\App\Request $request)
	{
		$parentHeaderCssScriptInstances = parent::getHeaderCss($request);

		$headerCss = array(
			'~libraries/jquery/gridster/jquery.gridster.css',
			'~libraries/jquery/jqplot/jquery.jqplot.css',
			'~libraries/fullcalendar/fullcalendar.css',
			'~libraries/fullcalendar/fullcalendarCRM.css'
		);
		$cssScripts = $this->checkAndConvertCssStyles($headerCss);
		$headerCssScriptInstances = array_merge($parentHeaderCssScriptInstances, $cssScripts);
		return $headerCssScriptInstances;
	}
}
