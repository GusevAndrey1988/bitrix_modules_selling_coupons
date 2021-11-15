<?php

namespace Site\SellingCoupons\EventsHandlers;

class CouponHandler
{
    public function onBeforeDelete(\Bitrix\Main\ORM\Event $event)
    {
        $eventResult = new \Bitrix\Main\ORM\EventResult();
        $data = $event->getParameter('fields');
        $eventResult->addError(new \Bitrix\Main\ORM\EntityError('error'));
        var_dump($data);
        
        return $eventResult;
    }
}