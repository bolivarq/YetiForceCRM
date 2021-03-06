<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

class Vtiger_Phone_UIType extends Vtiger_Base_UIType
{

	/**
	 * Function to get the Template name for the current UI Type object
	 * @return string - Template Name
	 */
	public function getTemplateName()
	{
		return 'uitypes/Phone.tpl';
	}

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value
	 * @param mixed $value
	 * @param int|bool $record
	 * @param Vtiger_Record_Model|bool $recordInstance
	 * @param bool $rawText
	 * @return string
	 */
	public function getDisplayValue($value, $record = false, $recordInstance = false, $rawText = false)
	{
		$extra = '';
		if ($recordInstance) {
			$extra = $recordInstance->getDisplayValue($this->get('field')->getFieldName() . '_extra');
			if ($extra) {
				$extra = ' ' . $extra;
			}
		}
		$rfc3966 = $international = \App\Purifier::encodeHtml($value);
		if (AppConfig::main('phoneFieldAdvancedVerification', false)) {
			$phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
			try {
				$swissNumberProto = $phoneUtil->parse($value);
				$international = $phoneUtil->format($swissNumberProto, \libphonenumber\PhoneNumberFormat::INTERNATIONAL);
				$rfc3966 = $phoneUtil->format($swissNumberProto, \libphonenumber\PhoneNumberFormat::RFC3966);
			} catch (\libphonenumber\NumberParseException $e) {
				
			}
		}
		if ($rawText) {
			return $international . $extra;
		}
		if (!\App\Integrations\Pbx::isActive()) {
			return '<a href="' . $rfc3966 . '">' . $international . $extra . '</a>';
		}
		return '<a class="phoneField" onclick="Vtiger_Index_Js.performPhoneCall(\'' . preg_replace('/(?<!^)\+|[^\d+]+/', '', $international) . '\',' . $record . ')"><span class="glyphicon glyphicon-phone-alt" aria-hidden="true"></span> ' . $international . $extra . '</a>';
	}

	public function getListViewDisplayValue($value, $record = false, $recordInstance = false, $rawText = false)
	{
		$rfc3966 = $international = \App\Purifier::encodeHtml($value);
		if (AppConfig::main('phoneFieldAdvancedVerification', false)) {
			$phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
			try {
				$swissNumberProto = $phoneUtil->parse($value);
				$international = $phoneUtil->format($swissNumberProto, \libphonenumber\PhoneNumberFormat::INTERNATIONAL);
				$rfc3966 = $phoneUtil->format($swissNumberProto, \libphonenumber\PhoneNumberFormat::RFC3966);
			} catch (\libphonenumber\NumberParseException $e) {
				
			}
		}
		if ($rawText) {
			return $international;
		}
		if (!\App\Integrations\Pbx::isActive()) {
			return '<a href="' . $rfc3966 . '">' . $international . '</a>';
		}
		return '<a class="phoneField" onclick="Vtiger_Index_Js.performPhoneCall(\'' . preg_replace('/(?<!^)\+|[^\d+]+/', '', $international) . '\',' . $record . ')"><span class="glyphicon glyphicon-phone-alt" aria-hidden="true"></span> ' . $international . '</a>';
	}

	/**
	 * Function to get the db insert value
	 * @param mixed $value
	 * @param \Vtiger_Record_Model $recordModel
	 * @return mixed
	 */
	public function getDBValue($value, $recordModel = false)
	{
		if (empty($value)) {
			return '';
		}
		if (AppConfig::main('phoneFieldAdvancedVerification', false)) {
			$value = str_replace(' ', '', $value);
		}
		return \App\Purifier::decodeHtml($value);
	}
}
