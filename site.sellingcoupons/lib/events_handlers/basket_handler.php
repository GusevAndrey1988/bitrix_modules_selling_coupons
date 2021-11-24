<?php

// TODO: delete this

namespace Site\SellingCoupons\EventsHandlers;

class BasketHandler
{
    public function onBeforeBasketAdd(array &$arFields)
    {
        if (!\Bitrix\Main\Loader::includeModule('site.sellingcoupons'))
        {
            return;
        }

        var_dump($arFields);
        die();
    }
}