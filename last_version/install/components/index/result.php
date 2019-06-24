<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
CModule::IncludeModule("sale");
CModule::IncludeModule("platron.pay");

/*
 * Configuration and parameters
 */
$strScriptName = PlatronSignature::getOurScriptName();

$arrRequest = PlatronIO::getRequest();

$nOrderId = intval(isset($arrRequest["pg_order_id"]) ? $arrRequest["pg_order_id"] : 0);
$arOrder = CSaleOrder::GetByID($nOrderId);
$objShop = CSalePaySystemAction::GetList(array(), array('PAY_SYSTEM_ID'=>$arOrder['PAY_SYSTEM_ID'], 'PERSON_TYPE_ID'=>$arOrder['PERSON_TYPE_ID']));

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

if($nOrderAmount != ($arrOrder['PRICE'] - $arrOrder['SUM_PAID']))
	PlatronIO::makeResponse($strScriptName, $strSecretKey, 'error',
		'amount is not correct', $strSalt);

CSaleOrder::Update($nOrderId, Array('PAY_VOUCHER_NUM' => (string) $arrRequest['pg_payment_id'], 'PAY_VOUCHER_DATE' => new \Bitrix\Main\Type\DateTime));

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

	$paidInfo = array(
		"PS_STATUS" 		=> "Y",
		"PS_STATUS_CODE"	=> 1,
		"PS_RESPONSE_DATE"	=> new \Bitrix\Main\Type\DateTime,
		"PS_SUM"			=> (double) $nOrderAmount,
		"PS_CURRENCY"		=> $arrRequest['pg_ps_currency'],
		'PS_STATUS_DESCRIPTION'	=> 'ok'
	);
		
	if(!CSaleOrder::PayOrder($nOrderId, "Y", true, true, 0, $paidInfo))
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
