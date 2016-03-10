<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>
<?php
include(GetLangFileName(dirname(__FILE__)."/", "/payment.php"));

CModule::IncludeModule("sale");
$getList = CSaleStatus::GetList(array(), array("LID" => LANGUAGE_ID));
$arrStatusName = array();
while($arrStatus = $getList->Fetch()) {
	$arrStatusName[] = $arrStatus;
}

$arrStatusIdAndName = array();
foreach($arrStatusName as $key => $value){
	$k = $arrStatusName[$key]['ID'];
	$arrStatusIdAndName[$k] = array(
		'NAME' => $arrStatusName[$key]['NAME']
	);
}

$arPSCorrespondence = array(
		"SHOP_MERCHANT_ID" => array(
				"NAME" => GetMessage("SHOP_MERCHANT_ID"),
				"DESCR" => GetMessage("SHOP_MERCHANT_ID_DESCR"),
				"VALUE" => "",
				"TYPE" => ""
			),
		"SHOP_SECRET_KEY" => array(
				"NAME" => GetMessage("SHOP_SECRET_KEY"),
				"DESCR" => GetMessage("SHOP_SECRET_KEY_DESCR"),
				"VALUE" => "",
				"TYPE" => ""
			),
		"SHOP_TESTING_MODE" => array(
				"NAME" => GetMessage("SHOP_TESTING_MODE"),
				"DESCR" => GetMessage("SHOP_TESTING_MODE_DESCR"),
				"VALUE" => "",
				"TYPE" => ""
			),
		"ORDER_ID" => array(
				"NAME" => GetMessage("ORDER_ID"),
				"DESCR" => GetMessage("ORDER_ID_DESCR"),
				"VALUE" => "ID",
				"TYPE" => "ORDER"
			),
		"ORDER_LIVETIME" => array(
				"NAME" => GetMessage("ORDER_LIVETIME"),
				"DESCR" => GetMessage("ORDER_LIVETIME_DESCR"),
				"VALUE" => "",
				"TYPE" => ""
			),
		"SHOULD_PAY" => array(
				"NAME" => GetMessage("SHOULD_PAY"),
				"DESCR" => GetMessage("SHOULD_PAY_DESCR"),
				"VALUE" => "SHOULD_PAY",
				"TYPE" => "ORDER"
			),
		"SITE_URL" => array(
				"NAME" => GetMessage("SITE_URL"),
				"DESCR" => GetMessage("SITE_URL_DESCR"),
				"VALUE" => "http://".$_SERVER['HTTP_HOST'],
				"TYPE" => ""
			),
		 "CHECK_URL" => array(
				"NAME" => GetMessage("CHECK_URL"),
				"DESCR" => GetMessage("CHECK_URL"),
				"VALUE" => "http://".$_SERVER['HTTP_HOST']."/platron/check.php",
				"TYPE" => ""
			),
		"RESULT_URL" => array(
				"NAME" => GetMessage("RESULT_URL"),
				"DESCR" => GetMessage("RESULT_URL_DESCR"),
				"VALUE" => "http://".$_SERVER['HTTP_HOST']."/platron/result.php",
				"TYPE" => ""
			),
		"REFUND_URL" => array(
				"NAME" => GetMessage("REFUND_URL"),
				"DESCR" => GetMessage("REFUND_URL_DESCR"),
				"VALUE" => "http://".$_SERVER['HTTP_HOST']."/platron/refund.php",
				"TYPE" => ""
			),
		"REQUEST_METHOD" => array(
				"NAME" => GetMessage("REQUEST_METHOD"),
				"DESCR" => GetMessage("REQUEST_METHOD_DESCR"),
				"VALUE" => "POST",
				"TYPE" => ""
			),
        "SUCCESS_URL" => array(
				"NAME" => GetMessage("SUCCESS_URL"),
				"DESCR" => GetMessage("SUCCESS_URL_DESCR"),
				"VALUE" => "http://".$_SERVER['HTTP_HOST']."/platron/success.php",
				"TYPE" => ""
			),
		"SUCCESS_URL_METHOD" => array(
				"NAME" => GetMessage("SUCCESS_URL_METHOD"),
				"DESCR" => GetMessage("SUCCESS_URL_METHOD_DESCR"),
				"VALUE" => "AUTOPOST",
				"TYPE" => ""
			),
		"FAILURE_URL" => array(
				"NAME" => GetMessage("FAILURE_URL"),
				"DESCR" => GetMessage("FAILURE_URL_DESCR"),
				"VALUE" => "http://".$_SERVER['HTTP_HOST']."/platron/failure.php",
				"TYPE" => ""
			),
		"FAILURE_URL_METHOD" => array(
				"NAME" => GetMessage("FAILURE_URL_METHOD"),
				"DESCR" => GetMessage("FAILURE_URL_METHOD_DESCR"),
				"VALUE" => "POST",
				"TYPE" => ""
			),
		"STATUS_FAILED" => array(
				"NAME" => GetMessage("STATUS_FAILED"),
				"DESCR" => GetMessage("STATUS_FAILED_DESCR"),
				"VALUE" => $arrStatusIdAndName,
				"TYPE" => "SELECT"
			),
		"STATUS_REVOKED" => array(
				"NAME" => GetMessage("STATUS_REVOKED"),
				"DESCR" => GetMessage("STATUS_REVOKED_DESCR"),
				"VALUE" => $arrStatusIdAndName,
				"TYPE" => "SELECT"
			),
		"PAYMENT_SYSTEM" => array(
				"NAME" => GetMessage("PS_NAME"),
				"DESCR" => GetMessage("PS_NAME_DESCR"),
				"VALUE" => "",
				"TYPE" => ""
			),
	);
?>