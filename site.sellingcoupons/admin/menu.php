<?php
/** @global \CMain $APPLICATION */

use Bitrix\Main\Localization\Loc;

IncludeModuleLangFile(__FILE__);

if ($APPLICATION->GetGroupRight('site.sellingcoupons') >= 'R')
{
    return [
        'parent_menu' => 'global_menu_store',
        'sort' => 1000,
        'text' => Loc::getMessage('SITE_COUPON_LIST_MENU_ITEM_TEXT'),
        'title' => Loc::getMessage('SITE_COUPON_LIST_MENU_ITEM_TITLE'),
        'items_id' => 'sold_coupons_list',
        'url' => 'coupons_list_admin.php?lang=' . LANGUAGE_ID,
        'items' => [],
    ];
}