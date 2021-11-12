<?php

/** @global \CMain $APPLICATION */

use Bitrix\Main\Localization\Loc;

IncludeModuleLangFile(__FILE__);

class site_selling_coupons extends \CModule
{
    public $MODULE_ID = 'site.selling.coupons';
	public $MODULE_VERSION;
	public $MODULE_VERSION_DATE;
	public $MODULE_NAME;
	public $MODULE_DESRIPTION;
	public $MODULE_GROUP_RIGHTS = 'Y';

    public function __construct()
    {
        include __DIR__ . '/version.php';

        if (isset($arModuleVersion) && key_exists('VERSION', $arModuleVersion))
        {
            $this->MODULE_VERSION = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        }

        $this->MODULE_NAME = Loc::getMessage('SITE_COUPON_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('SITE_COUPON_MODULE_DESCRIPTION');
    }

    public function InstallEvents()
    {
        $eventManager = \Bitrix\Main\EventManager::getInstance();

        $eventManager->registerEventHandler(
            'iblock',
            'OnIBlockPropertyBuildList',
            $this->MODULE_ID,
            \Site\SellingCoupons\CustomProperties\DiscountProperty::class,
            'GetUserTypeDescription'
        );
    }

    public function UnInstallEvents()
    {
        $eventManager = \Bitrix\Main\EventManager::getInstance();

        $eventManager->unRegisterEventHandler(
            'iblock',
            'OnIBlockPropertyBuildList',
            $this->MODULE_ID,
            \Site\SellingCoupons\CustomProperties\DiscountProperty::class,
            'GetUserTypeDescription'
        );
    }

    public function DoInstall()
    {
        $this->InstallEvents();

        RegisterModule('site.selling.coupons');
    }

    public function DoUninstall()
    {
        $this->UnInstallEvents();
        
        UnRegisterModule('site.selling.coupons');
    }
}