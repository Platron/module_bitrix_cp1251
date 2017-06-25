<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
CModule::IncludeModule("sale");
CModule::IncludeModule("platron.pay");
$APPLICATION->SetTitle("Успешная оплата");

$arrRequest = PlatronIO::getRequest();
$strScriptName = PlatronSignature::getOurScriptName();

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

$bPay = isset($_GET['pay'])?$_GET['pay']:'n';
COption::SetOptionString("platron.pay","pay",$bPay);
unset($_GET['pay']);


/*
 * Signature check
 */

if(!PlatronSignature::check($arrRequest['pg_sig'], $strScriptName, $arrRequest, $strSecretKey))
    print("Signature is not valid.");
else
	if ($nOrderId != 0){
		print("Ваш заказ успешно оплачен");
		$APPLICATION->IncludeComponent(
			"bitrix:sale.personal.order.detail",
			"",
			Array(
				"PATH_TO_LIST" => "", // path to list
				"PATH_TO_CANCEL" => "", // path to cancel
				"PATH_TO_PAYMENT" => "payment.php", // path to payment
				"ID" => $nOrderId,
				"SET_TITLE" => "Y"
				)
			);
	}
	else
		die("Invalid params.");
  

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
