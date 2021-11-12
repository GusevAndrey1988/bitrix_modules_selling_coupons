<?php

\Bitrix\Main\Loader::registerAutoLoadClasses('site.selling.coupons', [
    '\\Site\\SellingCoupons\\CustomProperties\\DiscountProperty' => 'lib/custom_properties/discount_property.php',
    '\\Site\\SellingCoupons\\SoldCouponsTable' => 'lib/sold_coupons_table.php',
    '\\Site\\SellingCoupons\\SoldCoupon' => 'lib/sold_coupon.php',
    '\\Site\\SellingCoupons\\SoldCouponCollection' => 'lib/sold_coupon_collection.php',
]);