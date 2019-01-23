<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
CModule::IncludeModule("sale");
CModule::IncludeModule("platron.pay");

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

/*
 * Signature check
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

if($arrOrder['PAYED']=="Y")
	PlatronIO::makeResponse($strScriptName, $strSecretKey, "ok",
		"Order alredy payed", $strSalt);

if($arrOrder['CANCELED']=="Y")
	PlatronIO::makeResponse($strScriptName, $strSecretKey, 'error',
		'Order canceled', $strSalt);

PlatronIO::makeResponse($strScriptName, $strSecretKey, "ok",
	"",$strSalt);
