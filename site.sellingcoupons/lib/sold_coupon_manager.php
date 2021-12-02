<?php

// TODO: обработка ошибок

namespace Site\SellingCoupons;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Internals;
use Site\SellingCoupons\DataMappers\SoldCouponsTable;

Loc::loadMessages(__FILE__);

/**
 * Менеджер для работы с проданными купонами
 */
class SoldCouponManager
{
    /**
     * Проверка продажи купонов
     * 
     * @param array $couponsIds Список идентификаторов купонов
     * 
     * @return bool true - если хотя бы один купон из списка продан, false - в противном случае
     * 
     * @throws \Bitrix\Main\ArgumentException
     */
    public function couponsSold(array $couponsIds): bool
    {
        $soldCoupon = SoldCouponsTable::getList([
            'filter' => [
                '=COUPON_ID' => $couponsIds,
            ],
        ])->fetchAll();

        if ($soldCoupon)
        {
            return true;
        }

        return false;
    }
    
    /**
     * Создаёт купоны
     * 
     * @param int $basketRuleId Id правила работы с корзиной
     * @param int $couponCount Количество купонов
     * 
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * 
     * @return object[] Список купонов
     */
    public function createCoupons(int $basketRuleId, int $couponCount = 1): array
    {
        $this->validateId($basketRuleId);
        $this->validateCount($couponCount);

        if ($couponCount === 0)
        {
            return [];
        }

        if (!Loader::includeModule('sale'))
        {
            throw new \Bitrix\Main\LoaderException(
                Loc::getMessage('SITE_COUPON_MANAGER_MODULE_SALE_EXCEPTION'));
        }

        Internals\DiscountCouponTable::setDiscountCheckList([$basketRuleId]);
        Internals\DiscountCouponTable::disableCheckCouponsUse();

        $couponsList = [];
        $couponCreationError = '';
        for ($counter = 0; $counter < $couponCount; $counter++)
        {
            $newCoupon = $this->createCouponObject($basketRuleId);
            $result = $newCoupon->save();
            if (!$result->isSuccess())
            {
                $couponCreationError = implode('\n', $result->getErrorMessages());
                break;
            }

            $couponsList[] = $newCoupon;
        }

        Internals\DiscountCouponTable::enableCheckCouponsUse();
        Internals\DiscountCouponTable::updateUseCoupons();

        if ($couponCreationError !== '')
        {
            throw new \Bitrix\Main\ObjectException(
                Loc::getMessage('SITE_COUPON_MANAGER_COUPON_CREATION_EXCEPTION')
                    . '\n'
                    . $couponCreationError
            );
        }

        return $couponsList;
    }

    /** 
     * Помечает купон как проданный
     * 
     * @param array $couponsList Список купонов
     * @param int $orderId Id заказа
     * 
     * @throws \Bitrix\Main\ArgumentException
     */
    public function markCouponsAsSold(array $couponsList, int $orderId): array
    {
        $this->validateId($orderId);

        $soldCouponsList = [];
        $soldCouponCreationErrors = '';
        foreach ($couponsList as $coupon)
        {
            $newSoldCoupon = $this->createSoldCouponObject($coupon, $orderId);
            $result = $newSoldCoupon->save();
            if (!$result->isSuccess())
            {
                $soldCouponCreationErrors = implode('\n', $result->getErrorMessages());
                break;
            }

            $soldCouponsList[] = $newSoldCoupon;
        }

        if ($soldCouponCreationErrors !== '')
        {
            throw new \Bitrix\Main\ObjectException(
                Loc::getMessage('SITE_COUPON_MANAGER_COUPON_CREATION_EXCEPTION')
                    . '\n'
                    . $soldCouponCreationErrors
            );
        }

        return $soldCouponsList;
    }

    /**
     * Создаёт купоны и помечает их как проданные
     * 
     * @param int $basketRuleId Id правило корзины
     * @param int $orderId Id Заказа
     * @param int $couponCount Количество купонов
     * 
     * @return \Site\SellingCoupons\DataMappers\SoldCoupon[] Массив купонов
     * 
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     */
    public function createAndMarkCoupons(int $basketRuleId, int $orderId, int $couponCount = 1): array
    {
        $connection = \Bitrix\Main\Application::getConnection();
        $soldCouponsList = [];

        /** @var \Bitrix\Main\DB\Connection $connection */
        $connection->startTransaction();

        try
        {
            $couponsList = $this->createCoupons($basketRuleId, $couponCount);
            $soldCouponsList = $this->markCouponsAsSold($couponsList, $orderId);
        }
        catch (\Exception $exception)
        {
            $connection->rollbackTransaction();
            throw $exception;
        }

        $connection->commitTransaction();

        return $soldCouponsList;
    }

    /**
     * Удаляет купоны
     * 
     * @param int[] $couponsIds Массив идентификаторов купонов
     * 
     * @return bool false если купон продан и не использован
     */
    public function deleteSoldCoupons(array $couponsIds): bool
    {
        $soldCouponCollection = SoldCouponsTable::getList([
            'select' => [
                'ID',
                'COUPON',
                'COUPON_ID'
            ],
            'filter' => [
                '=COUPON_ID' => $couponsIds,
            ],
        ])->fetchCollection();

        if (!$soldCouponCollection)
        {
            return true;
        }

        $couponsIds = $soldCouponCollection->getCouponIdList();
        $couponsCollection = \Bitrix\Sale\Internals\DiscountCouponTable::getList([
            'select' => [
                'ID',
                'ACTIVE',
                'USE_COUNT',
                'DISCOUNT_ID',
            ],
            'filter' => [
                '=ID' => $soldCouponCollection->getCouponIdList(),
            ],
        ])->fetchCollection();
    
        foreach ($couponsCollection as $coupon)
        {
            if ($coupon->getActive() && $coupon->getUseCount() == 0)
            {
                return false;
            }
        }
       
        Internals\DiscountCouponTable::setDiscountCheckList($couponsCollection->getDiscountIdList());
        Internals\DiscountCouponTable::disableCheckCouponsUse();
        
        /** @var \Bitrix\Main\DB\Connection $db */
        $db = \Bitrix\Main\Application::getConnection();
        $db->startTransaction();
        try 
        {
            // Порядок удаления имеет значения (из-за обработчика события)
            
            foreach ($soldCouponCollection as $soldCoupon)
            {
                $result = $soldCoupon->delete();
                if (!$result->isSuccess())
                {
                    $db->rollbackTransaction();
                    return false;
                }
            }
            
            foreach ($couponsCollection as $coupon)
            {
                $result = $coupon->delete();
                if (!$result->isSuccess())
                {
                    $db->rollbackTransaction();
                    return false;
                }
            }
        }
        catch (\Exception $exception)
        {
            $db->rollbackTransaction();
            return false;
        }

        $db->commitTransaction();

        Internals\DiscountCouponTable::enableCheckCouponsUse();
        Internals\DiscountCouponTable::updateUseCoupons();

        return true;
    }

    private function createSoldCouponObject(object $coupon, int $orderId)
        : \Site\SellingCoupons\DataMappers\SoldCoupon
    {
        $newSoldCoupon = new \Site\SellingCoupons\DataMappers\SoldCoupon();
        $newSoldCoupon->setCoupon($coupon);
        $newSoldCoupon->setOrderId($orderId);

        return $newSoldCoupon;
    }

    private function createCouponObject(int $basketRuleId): object
    {
        $couponClassName = Internals\DiscountCouponTable::getObjectClass();

        $newCoupon = new $couponClassName();
        $newCoupon->setDiscountId($basketRuleId);
        $newCoupon->setCoupon(Internals\DiscountCouponTable::generateCoupon(true));
        $newCoupon->setType(Internals\DiscountCouponTable::TYPE_ONE_ORDER);

        return $newCoupon;
    }

    /**
     * @throws \Bitrix\Main\ArgumentException
     */
    private function validateId(int $id)
    {
        if ($id <= 0)
        {
            throw new \Bitrix\Main\ArgumentException(
                Loc::getMessage(
                    'SITE_COUPON_MANAGER_ARGUMENT_EXCEPTION',
                    [
                        '#ID#' => $id,
                    ]
                )
            );
        }
    }

    /**
     * @throws \Bitrix\Main\ArgumentException
     */
    private function validateCount(int $count)
    {
        if ($count < 0) 
        {
            throw new \Bitrix\Main\ArgumentException(
                Loc::getMessage(
                    'SITE_COUPON_MANAGER_CNT_EXCEPTION',
                    [
                        '#CNT#' => $count,
                    ]
                )
            );
        }
    }
}