<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

if (!Loader::includeModule('sale'))
{
    $APPLICATION->ThrowException(Loc::getMessage('SITE_COUPON_SALE_MODULE_ERROR'));
}

\Bitrix\Main\Loader::registerAutoLoadClasses('site.selling.coupons', [
    '\\Site\\SellingCoupons\\CustomProperties\\DiscountProperty' => 'lib/custom_properties/discount_property.php',

    '\\Site\\SellingCoupons\\SoldCouponsTable' => 'lib/sold_coupons_table.php',
    '\\Site\\SellingCoupons\\SoldCoupon' => 'lib/sold_coupon.php',
    '\\Site\\SellingCoupons\\SoldCouponCollection' => 'lib/sold_coupon_collection.php',

    '\\Site\\SellingCoupons\\EventsHandlers\\CouponHandler' => 'lib/events_handlers/coupon_handler.php',
]);