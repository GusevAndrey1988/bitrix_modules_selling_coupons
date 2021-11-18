<?php

namespace Site\SellingCoupons\EventsHandlers;

use Bitrix\Main\Localization\Loc;

IncludeModuleLangFile(__FILE__);

class CouponHandler
{
    public function onBeforeDelete(\Bitrix\Main\ORM\Event $event)
    {
        $eventResult = new \Bitrix\Main\ORM\EventResult();

        if (\Bitrix\Main\Loader::includeModule('site.sellingcoupons'))
        {
            $couponId = $event->getParameter('primary')['ID'];
            $coupon = \Bitrix\Sale\Internals\DiscountCouponTable::wakeUpObject($couponId);
            $coupon->fill();

            $couponSeller = new \Site\SellingCoupons\CouponSeller();
            if ($couponSeller->couponSold($couponId))
            {
                $eventResult->addError(new \Bitrix\Main\ORM\EntityError(
                    Loc::getMessage('SITE_COUPON_EVENTS_COUPON_SOLD')
                ));
            }
    
            return $eventResult;
        }
    }
}