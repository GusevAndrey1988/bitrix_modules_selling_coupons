<?php

namespace Site\SellingCoupons\EventsHandlers;

use Bitrix\Main\Localization\Loc;

IncludeModuleLangFile(__FILE__);

class CouponHandler
{
    public static function onBeforeDelete(\Bitrix\Main\Event $event)
    {
        $eventResult = new \Bitrix\Main\ORM\EventResult();

        if (\Bitrix\Main\Loader::includeModule('site.sellingcoupons'))
        {
            $couponId = $event->getParameter('primary')['ID'];

            $soldCouponManager = new \Site\SellingCoupons\SoldCouponManager();
            if ($soldCouponManager->couponsSold([$couponId]))
            {
                $eventResult->addError(new \Bitrix\Main\ORM\EntityError(
                    Loc::getMessage('SITE_COUPON_EVENTS_COUPON_SOLD')
                ));
            }
    
            return $eventResult;
        }
    }
}