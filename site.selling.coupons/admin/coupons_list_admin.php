<?php

use Bitrix\Main\Localization\Loc;

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');

$cuponModulePermissions = $APPLICATION->GetGroupRight('site.selling.coupons');
if ($cuponModulePermissions == 'D')
{
	$APPLICATION->AuthForm(Loc::getMessage('SITE_COUPON_ACCESS_DENIED'));
}

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');