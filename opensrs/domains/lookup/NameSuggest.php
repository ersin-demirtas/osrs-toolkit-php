<?php

namespace opensrs\domains\lookup;

use OpenSRS\Base;
use OpenSRS\Exception;

class NameSuggest extends Base {
	private $_domain = "";
	private $_tldSelect = array ();
	private $_tldAll = array ();
	private $_dataObject;
	private $_formatHolder = "";

	public $defaulttld_allnsdomains = array (".com",".net",".org",".info",".biz",".us",".mobi");
	public $defaulttld_alllkdomains = array (".com",".net",".ca",".us",".eu",".de",".co.uk");

	public $resultFullRaw;
	public $resultRaw;
	public $resultFullFormatted;
	public $resultFormatted;
	public $result;

	public function __construct($formatString, $dataObject) {
		parent::__construct();
		$this->_dataObject = $dataObject;
		$this->_formatHolder = $formatString;
		$this->_validateObject();
	}

	public function __destruct() {
		parent::__destruct();
	}

	// Validate the object
	private function _validateObject(){
		$domain = "";

		if (!isset($this->_dataObject->data->domain)) {
			throw new Exception("oSRS Error - Search domain strinng not defined.");
		}

		// Grab domain name
		$tdomain = $this->_dataObject->data->domain;
		$tdom = explode (".", $tdomain);
		$domain = $tdom[0];

		$serviceOverride = $this->getTlds();

		$resObject = $this->_domainTLD ($domain, $serviceOverride);
	}

	/**
	* Get tlds for domain call 
	* Will use (in order of preference)... 
	* 1. selected tlds 
	* 2. supplied default tlds 
	* 3. included default tlds
	* 
	* @return array tlds 
	*/
	public function getTlds()
	{
		$arransSelected = array();
		$arralkSelected = array();
		$arransAll = array();
		$arralkAll = array();
		$arransCall = array();
		$arralkCall = array();

		$selected = array();
		$suppliedDefaults = array();

		// Select non empty one

        // Name Suggestion Choice Check
		if (isset($this->_dataObject->data->nsselected) && $this->_dataObject->data->nsselected != "") $arransSelected = explode (";", $this->_dataObject->data->nsselected);

        // Lookup Choice Check
		if (isset($this->_dataObject->data->lkselected) && $this->_dataObject->data->lkselected != "") $arralkSelected = explode (";", $this->_dataObject->data->lkselected);

        // Get Default Name Suggestion Choices For No Form Submission
		if (isset($this->_dataObject->data->allnsdomains) && $this->_dataObject->data->allnsdomains != "") $arransAll = explode (";", $this->_dataObject->data->allnsdomains);

        // Get Default Lookup Choices For No Form Submission
		if (isset($this->_dataObject->data->alllkdomains) && $this->_dataObject->data->alllkdomains != "") $arralkAll = explode (";", $this->_dataObject->data->alllkdomains);

		if (count($arransSelected) == 0) {
			if (count($arransAll) == 0){
				$arransCall = $this->defaulttld_allnsdomains;
			} else {
				$arransCall = $arransAll;
			}
		} else {
			$arransCall = $arransSelected;
		}

        // If Lookup Choices Empty
		if (count($arralkSelected) == 0) {
			if (count($arralkAll) == 0){
				$arralkCall = $this->defaulttld_alllkdomains;
			} else {
				$arralkCall = $arralkAll;
			}
		} else {
			$arralkCall = $arralkSelected;
		}

		$serviceOverride = array(
			"lookup" => array(
				"tlds" => $arransCall
				),
			"suggestion" => array(
				"tlds" => $arralkCall
				)
			);

		return $serviceOverride;
	}

	// Selected / all TLD options
	private function _domainTLD($domain, $serviceOverride){
		$cmd = array(
			"protocol" => "XCP",
			"action" => "name_suggest",
			"object" => "domain",
			"attributes" => array(
				"searchstring" => $domain,
				"service_override" => $serviceOverride,
				"services" => array(
					"lookup","suggestion"
					)
				)
			);

		if(isset($this->_dataObject->data->maximum) && $this->_dataObject->data->maximum != ""){
			$cmd['attributes']['service_override']['lookup']['maximum'] = $this->_dataObject->data->maximum;
			$cmd['attributes']['service_override']['suggestion']['maximum'] = $this->_dataObject->data->maximum;
		}

		// Flip Array to XML
		$xmlCMD = $this->_opsHandler->encode($cmd);
		// Send XML
		$XMLresult = $this->send_cmd($xmlCMD);
		// FLip XML to Array
		$arrayResult = $this->_opsHandler->decode($XMLresult);

		// Results
		$this->resultFullRaw = $arrayResult;

		if (isset($arrayResult['attributes'])){
			$this->resultRaw = array ();

			if(isset($arrayResult['attributes']['lookup']['items'])){
				$this->resultRaw['lookup'] = $arrayResult['attributes']['lookup']['items'];
			}

			if(isset($arrayResult['attributes']['suggestion']['items'])){
				$this->resultRaw['suggestion'] = $arrayResult['attributes']['suggestion']['items'];
			}
		} else {
			$this->resultRaw = $arrayResult;
		}

		$this->resultFullFormatted = $this->convertArray2Formatted ($this->_formatHolder, $this->resultFullRaw);
		$this->resultFormatted = $this->convertArray2Formatted ($this->_formatHolder, $this->resultRaw);
	}
}