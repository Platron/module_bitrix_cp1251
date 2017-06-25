<?php
global $MESS;

$strPath2Lang = str_replace("\\", "/", __FILE__);
$strPath2Lang = substr($strPath2Lang, 0, strlen($strPath2Lang)-strlen("/install/index.php"));
include(GetLangFileName($strPath2Lang."/lang/", "/install/index.php"));

Class platron_pay extends CModule
{
	var $MODULE_ID = "platron.pay";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $MODULE_GROUP_RIGHTS = "Y";

	function platron_pay()
	{
		$arModuleVersion = array();

		$path = str_replace("\\", "/", __FILE__);
		$path = substr($path, 0, strlen($path) - strlen("/index.php"));
		include($path."/version.php");

		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];

		$this->PARTNER_NAME = "Platron";
		$this->PARTNER_URI = "http://www.platron.ru/";

		$this->MODULE_NAME = GetMessage("PLATRON_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("PLATRON_MODULE_DESC");
	}

	function InstallDB()
	{
		RegisterModule("platron.pay");
		return true;
	}

	function UnInstallDB()
	{
		UnRegisterModule("platron.pay");
		return true;
	}

	function InstallEvents()
	{
		return true;
	}

	function UnInstallEvents()
	{
		return true;
	}

	function InstallFiles()
	{
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/platron.pay/install/components/payment/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/payment/platron/", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/platron.pay/install/components/index/", $_SERVER["DOCUMENT_ROOT"]."/platron/",	true, true);
		return true;
	}
	
	function InstallPublic()
	{
	}

	function UnInstallFiles()
	{
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/platron.pay/install/components/payment/en/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/payment/platron/en/");
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/platron.pay/install/components/payment/ru/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/payment/platron/ru/");
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/platron.pay/install/components/payment/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/payment/platron/");
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/platron.pay/install/components/index/", $_SERVER["DOCUMENT_ROOT"]."/platron/");
		return true;
	}
	
	function DoInstall()
	{
		global $APPLICATION, $step;

		if (!IsModuleInstalled("platron.pay"))
		{
			$this->InstallFiles();
			$this->InstallDB(false);
			$this->InstallEvents();
			$this->InstallPublic();

			$APPLICATION->IncludeAdminFile(GetMessage("SCOM_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/platron.pay/install/step.php");
		}
	}

	function DoUninstall()
	{
		global $APPLICATION, $step;

		$this->UnInstallDB();
		$this->UnInstallFiles();
		$this->UnInstallEvents();
		$APPLICATION->IncludeAdminFile(GetMessage("SCOM_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/platron.pay/install/unstep.php");
	}
		 
}
?>
