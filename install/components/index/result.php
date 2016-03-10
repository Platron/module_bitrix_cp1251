<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
CModule::IncludeModule("sale");
CModule::IncludeModule("platron.pay");

/*
 * Configuration and parameters
 */
$strScriptName = PlatronSignature::getOurScriptName();

$arrRequest = PlatronIO::getRequest();

$objShop = CSalePaySystemAction::GetList('', array("PS_NAME"=>$arrRequest['PAYMENT_SYSTEM']));
$arrShop = $objShop->Fetch();
if(!empty($arrShop))
	$arrShopParams = unserialize($arrShop['PARAMS']);
else
{
	PlatronIO::makeResponse($strScriptName, '', 'error',
		'Please re-configure the module Platron.PAY in Bitrix CMS. The payment system should have a name '.$arrRequest['PAYMENT_SYSTEM']);
}

$strSecretKey = $arrShopParams['SHOP_SECRET_KEY']['VALUE'];

$strSalt = $arrRequest["pg_salt"];

$nOrderAmount = $arrRequest["pg_amount"];
$nOrderId = intval($arrRequest["pg_order_id"]);

$strStatusFailed = $arrRequest["STATUS_FAILED"];

/*
 * Signature
 */

if(!PlatronSignature::check($arrRequest['pg_sig'], $strScriptName, $arrRequest, $strSecretKey) )
	PlatronIO::makeResponse($strScriptName, $strSecretKey, 'error',
		'signature is not valid', $strSalt);

if(!($arrOrder = CSaleOrder::GetByID($nOrderId)))
	PlatronIO::makeResponse($strScriptName, $strSecretKey, 'error',
		'order not found', $strSalt);

if($nOrderAmount != $arrOrder['PRICE'])
	PlatronIO::makeResponse($strScriptName, $strSecretKey, 'error',
		'amount is not correct', $strSalt);

if($arrRequest["pg_result"] == 1){
	if($arrOrder['PAYED']=="Y")
		PlatronIO::makeResponse($strScriptName, $strSecretKey, "ok",
			"Order alredy payed", $strSalt);
		
	if($arrOrder['CANCELED']=="Y") {
		CSaleOrder::Update($nOrderId, array(
			'STATUS_ID' => $strStatusFailed,
			'PS_STATUS' => $strStatusFailed,
			'PS_STATUS_CODE' => "0",
			'PS_SUM' => $arrRequest['pg_amount'],
			'PS_CURRENCY' => $arrRequest['pg_currency'],
			'PS_RESPONSE_DATE' => Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", LANG))),
		));		
		
		PlatronIO::makeResponse($strScriptName, $strSecretKey, 'rejected',
			'Order canceled', $strSalt);
		
		return false;
	}
		
	if(!CSaleOrder::PayOrder($nOrderId, "Y"))
		PlatronIO::makeResponse($strScriptName, $strSecretKey, "error",
			"Order can\'t be payed", $strSalt);

	PlatronIO::makeResponse($strScriptName, $strSecretKey, "ok",
		"Order payed",$strSalt);
}
/*
 * Order cancel
 */
else{
	if($arrOrder['CANCELED']=="Y") {
		PlatronIO::makeResponse($strScriptName, $strSecretKey, 'ok',
			'Order alredy canceled', $strSalt);
	}
		
	if($arrOrder['PAYED']=="Y")
		PlatronIO::makeResponse($strScriptName, $strSecretKey, "error",
			"Order alredy paid", $strSalt);

	if(!CSaleOrder::CancelOrder($nOrderId, "Y", !empty($arrRequest['pg_failure_description'])? $arrRequest['pg_failure_description'] : ''))
		PlatronIO::makeResponse($strScriptName, $strSecretKey, "error",
			"Order can\'t be cancel", $strSalt);
	
	CSaleOrder::Update($nOrderId, array(
			'STATUS_ID' => $strStatusFailed,
			'PS_STATUS' => $strStatusFailed,
			'PS_STATUS_CODE' => "1",
			'PS_SUM' => $arrRequest['pg_amount'],
			'PS_CURRENCY' => $arrRequest['pg_currency'],
			'PS_RESPONSE_DATE' => Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", LANG))),
	));		

	PlatronIO::makeResponse($strScriptName, $strSecretKey, "ok",
			"Order cancel", $strSalt);
}
