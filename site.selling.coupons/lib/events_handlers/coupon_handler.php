<?php

namespace Site\SellingCoupons\EventsHandlers;

use Bitrix\Main\Localization\Loc;

IncludeModuleLangFile(__FILE__);

class CouponHandler
{
    public function onBeforeDelete(\Bitrix\Main\ORM\Event $event)
    {
        $eventResult = new \Bitrix\Main\ORM\EventResult();

        if (\Bitrix\Main\Loader::includeModule('site.selling.coupons'))
        {
            $couponId = $event->getParameter('primary')['ID'];
            $coupon = \Bitrix\Sale\Internals\DiscountCouponTable::wakeUpObject($couponId);
            $coupon->fillActive();

            $couponSeller = new \Site\SellingCoupons\CouponSeller();
            if ($couponSeller->couponSold($couponId) && $coupon->getActive())
            {
                $eventResult->addError(new \Bitrix\Main\ORM\EntityError(
                    Loc::getMessage('SITE_COUPON_EVENTS_COUPON_SOLD')
                ));
            }
    
            return $eventResult;
        }
    }
}