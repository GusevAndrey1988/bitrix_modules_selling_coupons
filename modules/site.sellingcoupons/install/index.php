<?php

/** @global \CMain $APPLICATION */

use Bitrix\Main\Localization\Loc;

IncludeModuleLangFile(__FILE__);

class site_sellingcoupons extends \CModule
{
    public $MODULE_ID = 'site.sellingcoupons';
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

        $eventManager->registerEventHandler(
            'sale',
            '\Bitrix\Sale\Internals\DiscountCoupon::onBeforeDelete',
            $this->MODULE_ID,
            \Site\SellingCoupons\EventsHandlers\CouponHandler::class,
            'onBeforeDelete'
        );

        $eventManager->registerEventHandler(
            'sale',
            'OnSaleOrderPaid',
            $this->MODULE_ID,
            \Site\SellingCoupons\EventsHandlers\OrderHandler::class,
            'onSaleOrderPaid'
        );
    }

    public function UnInstallEvents()
    {
        $eventManager = \Bitrix\Main\EventManager::getInstance();

        $eventManager->unRegisterEventHandler(
            'sale',
            'OnSaleOrderPaid',
            $this->MODULE_ID,
            \Site\SellingCoupons\EventsHandlers\OrderHandler::class,
            'onSaleOrderPaid'
        );

        $eventManager->unRegisterEventHandler(
            'sale',
            '\Bitrix\Sale\Internals\DiscountCoupon::onBeforeDelete',
            $this->MODULE_ID,
            \Site\SellingCoupons\EventsHandlers\CouponHandler::class,
            'onBeforeDelete'
        );

        $eventManager->unRegisterEventHandler(
            'iblock',
            'OnIBlockPropertyBuildList',
            $this->MODULE_ID,
            \Site\SellingCoupons\CustomProperties\DiscountProperty::class,
            'GetUserTypeDescription'
        );
    }

    public function InstallDB()
    {
        /** @global \CDatabase $DB 
         *  @global \CMain $APPLICATION
         */
        global $DB, $APPLICATION;

        if (!$DB->RunSQLBatch(__DIR__ . '/db/mysql/install.sql'))
        {
            $APPLICATION->ThrowException(Loc::getMessage('SITE_COUPON_MODULE_CREATE_TABLE_ERROR'));
        }
    }

    public function UnInstallDB()
    {
        /** @global \CDatabase $DB */
        global $DB;

        $DB->RunSQLBatch(__DIR__ . '/db/mysql/uninstall.sql');
    }

    public function InstallFiles()
    {
        $documentRoot = \Bitrix\Main\Application::getDocumentRoot();

        CopyDirFiles(__DIR__ . '/admin', $documentRoot . '/bitrix/admin');
    }

    public function UnInstallFiles()
    {
        $documentRoot = \Bitrix\Main\Application::getDocumentRoot();

        DeleteDirFiles(__DIR__ . '/admin', $documentRoot . '/bitrix/admin');
    }

    public function DoInstall()
    {
        $this->InstallDB();
        $this->InstallEvents();
        $this->InstallFiles();

        RegisterModule('site.sellingcoupons');
    }

    public function DoUninstall()
    {
        $this->UnInstallFiles();
        $this->UnInstallEvents();

        $request = \Bitrix\Main\Context::getCurrent()->getRequest();
        if ($request->get('unistall_db'))
        {
            $this->UnInstallDB();
        }
        
        UnRegisterModule('site.sellingcoupons');
    }
}