<?php

namespace Site\SellingCoupons\EventsHandlers;

use Bitrix\Main\Config;

class OrderHandler
{
    private const MODULE_ID = 'site.sellingcoupons';

    private const OPTION_PROPERTY_CODE = 'property_code';
    private const OPTION_IBLOCK_ID = 'iblock_id';
    private const OPTION_MAIL_EVENT_NAME = 'mail_event_name';

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

        $productList = self::getProductsList($basket);

        if (!$productList)
        {
            return;
        }

        $couponsList = self::getCouponsList(array_column($productList, 'ID'));

        if ($order->isPaid())
        {
            $soldCouopnsList = self::createCoupons(
                $couponsList,
                $order->getId(),
                $productList
            );
    
            $user = \CUser::GetById($order->getUserId())->Fetch();
    
            self::sendMessage($soldCouopnsList,
                $order->getSiteId(),
                $user['LAST_NAME'] . ' ' . $user['NAME'],
                $user['EMAIL']
            );
        }
        else
        {
            $soldCouponsList = \Site\SellingCoupons\DataMappers\SoldCouponsTable::getList([
                'select' => [
                    'COUPON_ID',
                ],

                'filter' => [
                    '=ORDER_ID' => $order->getId(),
                ],
            ])->fetchAll();

            \Bitrix\Sale\Internals\DiscountCouponTable::disableCheckCouponsUse();
            foreach ($soldCouponsList as $coupon)
            {
                \Bitrix\Sale\Internals\DiscountCouponTable::update(
                    $coupon['COUPON_ID'],
                    [
                        'ACTIVE' => 'N',
                    ]
                );
            }
            \Bitrix\Sale\Internals\DiscountCouponTable::enableCheckCouponsUse();

            $soldCouponManger = new \Site\SellingCoupons\SoldCouponManager();
            $soldCouponManger->deleteSoldCoupons(array_column($soldCouponsList, 'COUPON_ID'));
        }
        
        // TODO: обработка ошибок
    }

    /** 
     * Отправляет сообщение с приобретёнными купонами
     */
    public static function sendMessage(array $soldCouopnsList, string $siteId, string $fullName, string $email)
    {
        $codesList = [];
        foreach ($soldCouopnsList as $coupon)
        {
            $codesList[] = $coupon->getCoupon()->getCoupon();
        }

        \Bitrix\Main\Mail\Event::sendImmediate([
            'EVENT_NAME' => Config\Option::get(self::MODULE_ID, self::OPTION_MAIL_EVENT_NAME),
            'LID' => $siteId,
            'C_FIELDS' => [
                'USER_EMAIL' => $email,
                'USER_NAME' => $fullName,
                'COUPONS' => implode(', ', $codesList),
            ]
        ]);
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