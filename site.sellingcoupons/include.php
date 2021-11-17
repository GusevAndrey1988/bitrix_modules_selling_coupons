<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

if (!Loader::includeModule('sale'))
{
    $APPLICATION->ThrowException(Loc::getMessage('SITE_COUPON_SALE_MODULE_ERROR'));
}

\Bitrix\Main\Loader::registerAutoLoadClasses('site.sellingcoupons', [
    '\\Site\\SellingCoupons\\CouponSeller' => 'lib/coupon_seller.php',

    '\\Site\\SellingCoupons\\CustomProperties\\DiscountProperty' => 'lib/custom_properties/discount_property.php',

    '\\Site\\SellingCoupons\\DataMappers\\SoldCouponsTable' => 'lib/data_mappers/sold_coupons_table.php',
    '\\Site\\SellingCoupons\\DataMappers\\SoldCoupon' => 'lib/data_mappers/sold_coupon.php',
    '\\Site\\SellingCoupons\\DataMappers\\SoldCouponCollection' => 'lib/data_mappers/sold_coupon_collection.php',

    '\\Site\\SellingCoupons\\EventsHandlers\\CouponHandler' => 'lib/events_handlers/coupon_handler.php',

    '\\Site\\SellingCoupons\\Controller\\CouponController' => 'lib/controller/coupon_controller.php',

    '\\Site\\SellingCoupons\\Controller\\Actions\\DeleteCouponAction' => 'lib/controller/actions/delete_coupon_action.php',
]);