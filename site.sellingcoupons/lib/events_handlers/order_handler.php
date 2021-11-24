<?php

namespace Site\SellingCoupons\EventsHandlers;

class OrderHandler
{
    public function onSaleOrderPaid(\Bitrix\Main\Event $event)
    {
        if (!\Bitrix\Main\Loader::includeModule('site.sellingcoupons'))
        {
            return;
        }

        /** @var \Bitrix\Sale\Order $order */
        $order = $event->getParameter('ENTITY');

        /** @var \Bitrix\Sale\Basket $basket */
        $basket = $order->getBasket();

        if ($order->isPaid())
        {
            $couponIdsList = [];
            $productQuantityList = [];

            /** @var \Bitrix\Sale\BasketItem $item */
            foreach ($basket as $item) 
            {
                if ($item->getField('SOLD_COUPON_CODE'))
                {
                    continue;
                }

                $productId = $item->getField('PRODUCT_ID');
                $couponIdsList[] = $productId;
                $productQuantityList[$productId] = intval($item->getFields()['QUANTITY']);
            }
        }

        if (!$couponIdsList)
        {
            return;
        }

        $propertyCode = \Bitrix\Main\Config\Option::get('site.sellingcoupons', 'property_code');
        $iblockId = \Bitrix\Main\Config\Option::get('site.sellingcoupons', 'iblock_id');

        $iblockPropertyCode = 'PROPERTY_' . $propertyCode;

        $selectionResult = \CIBlockElement::GetList(
            [],
            [
                '=IBLOCK_ID' => $iblockId,
                '=ID' => $couponIdsList,
                '!' . $iblockPropertyCode => false,
            ],
            false,
            false,
            [
                'IBLOCK_ID',
                'ID',
                $iblockPropertyCode,
            ]
        );

        $couponsList = [];
        while ($coupon = $selectionResult->GetNext())
        {
            $couponsList[$coupon['ID']] = $coupon;
        }

        $soldCouponsManager = new \Site\SellingCoupons\SoldCouponManager();
        $iblockPropertyValue = $iblockPropertyCode . '_VALUE';
        $soldCouopnsList = [];
        foreach ($couponsList as $coupon)
        {
            $soldCouopnsList += $soldCouponsManager->createAndMarkCoupons(
                $coupon[$iblockPropertyValue], $order->getId(), $productQuantityList[$coupon['ID']]);
        }

        // TODO: отправка сообщения с кодами купонов
        // TODO: реализация отмены заказа
        // TODO: рафакторинг
    }
}