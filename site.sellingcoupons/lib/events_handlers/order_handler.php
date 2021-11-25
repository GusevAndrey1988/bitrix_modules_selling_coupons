<?php

namespace Site\SellingCoupons\EventsHandlers;

class OrderHandler
{
    private const MODULE_ID = 'site.sellingcoupons';

    private const OPTION_PROPERTY_CODE = 'property_code';
    private const OPTION_IBLOCK_ID = 'iblock_id';

    public static function onSaleOrderPaid(\Bitrix\Main\Event $event)
    {
        if (!\Bitrix\Main\Loader::includeModule(self::MODULE_ID))
        {
            return;
        }

        /** @var \Bitrix\Sale\Order $order */
        $order = $event->getParameter('ENTITY');

        /** @var \Bitrix\Sale\Basket $basket */
        $basket = $order->getBasket();

        $productList = [];
        if ($order->isPaid())
        {
           $productList = self::getProductsList($basket);
        }

        if (!$productList)
        {
            return;
        }

        $couponsList = self::getCouponsList(array_column($productList, 'ID'));

        $soldCouopnsList = self::createCoupons(
            $couponsList,
            $order->getId(),
            $productList
        );

        // TODO: отправка сообщения с кодами купонов
        // TODO: реализация отмены заказа
        // TODO: обработка ошибок
    }

    private static function getProductsList(\Bitrix\Sale\Basket $basket): array
    {
        $productList = [];

        /** @var \Bitrix\Sale\BasketItem $item */
        foreach ($basket as $item) 
        {
            $productId = $item->getField('PRODUCT_ID');
            $productList[$productId] = [
                'ID' => $productId,
                'QUANTITY' => intval($item->getField('QUANTITY')),
            ];
        }

        return $productList;
    }

    /**
     * Выбирает купоны из списка продуктов
     */
    private static function getCouponsList(array $productsIds): array
    {
        $propertyCode = \Bitrix\Main\Config\Option::get(self::MODULE_ID, self::OPTION_PROPERTY_CODE);
        $iblockId = \Bitrix\Main\Config\Option::get(self::MODULE_ID, self::OPTION_IBLOCK_ID);

        $iblockPropertyCode = 'PROPERTY_' . $propertyCode;

        $selectionResult = \CIBlockElement::GetList(
            [],
            [
                '=IBLOCK_ID' => $iblockId,
                '=ID' => $productsIds,
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

        return $couponsList;
    }

    private static function createCoupons(array $couponsList, int $orderId, array $productList): array
    {
        $iblockPropertyValue = 
            'PROPERTY_' . \Bitrix\Main\Config\Option::get(self::MODULE_ID, self::OPTION_PROPERTY_CODE) . '_VALUE';
        
        $soldCouponsManager = new \Site\SellingCoupons\SoldCouponManager();
        $soldCouopnsList = [];
        foreach ($couponsList as $coupon)
        {
            $soldCouopnsList += $soldCouponsManager->createAndMarkCoupons(
                $coupon[$iblockPropertyValue], $orderId, $productList[$coupon['ID']]['QUANTITY']);
        }

        return $soldCouopnsList;
    }
}