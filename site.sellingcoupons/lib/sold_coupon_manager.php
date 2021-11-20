<?php

namespace Site\SellingCoupons;

use Bitrix\Main\Loader;
use \Bitrix\Sale\Internals;
use Site\SellingCoupons\DataMappers\SoldCouponsTable;

class SoldCouponManager
{
    /**
     * @param int $couponId
     * 
     * @return bool
     */
    public function couponSold(int $couponId): bool
    {
        $soldCoupon = SoldCouponsTable::getList([
            'filter' => [
                '=COUPON_ID' => $couponId,
            ],
        ])->fetchObject();

        if ($soldCoupon)
        {
            return true;
        }

        return false;
    }
    
    /**
     * @param int $discountId
     * @param int $couponCount
     * 
     * @return array Список купонов
     * 
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     */
    public function createCoupons(int $discountId, int $couponCount = 1): array
    {
        if (!Loader::includeModule('sale'))
        {
            throw new \Bitrix\Main\LoaderException('load module error "sale"');
        }

        if ($discountId <= 0) 
        {
            throw new \Bitrix\Main\ArgumentException('$discountId <= 0');
        }

        if ($couponCount <= 0) 
        {
            throw new \Bitrix\Main\ArgumentException('$couponCount <= 0');
        }

        Internals\DiscountCouponTable::setDiscountCheckList([$discountId]);
        Internals\DiscountCouponTable::disableCheckCouponsUse();

        $couponClassName = Internals\DiscountCouponTable::getObjectClass();

        $couponsList = [];
        for ($counter = 0; $counter < $couponCount; $counter++)
        {
            $newCoupon = new $couponClassName();
            $newCoupon->setDiscountId($discountId);
            $newCoupon->setCoupon(Internals\DiscountCouponTable::generateCoupon(true));
            $newCoupon->setType(Internals\DiscountCouponTable::TYPE_ONE_ORDER);
            $result = $newCoupon->save();

            if (!$result->isSuccess())
            {
                // TODO: exception
                break;
            }

            $couponsList[] = $newCoupon;
        }

        Internals\DiscountCouponTable::enableCheckCouponsUse();
        Internals\DiscountCouponTable::updateUseCoupons();

        return $couponsList;
    }

    /** 
     * @param array Список купонов
     */
    public function markCouponsAsSold(array $couponsList): array
    {
        $soldCouponsList = [];
        foreach ($couponsList as $coupon)
        {
            $newSoldCoupon = new \Site\SellingCoupons\DataMappers\SoldCoupon();
            $newSoldCoupon->setCoupon($coupon);
            $result = $newSoldCoupon->save();

            if (!$result->isSuccess())
            {
                // TODO: exception
                break;
            }

            $soldCouponsList[] = $newSoldCoupon;
        }

        return $soldCouponsList;
    }

    /**
     * @return \Site\SellingCoupons\DataMappers\SoldCoupon[]
     * 
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     */
    public function createAndMarkCoupons(int $discountId, int $couponCount = 1): array
    {
        $connection = \Bitrix\Main\Application::getConnection();
        $soldCouponsList = [];

        /** @var \Bitrix\Main\DB\Connection $connection */
        $connection->startTransaction();

        try
        {
            $couponsList = $this->createCoupons($discountId, $couponCount);
            $soldCouponsList = $this->markCouponsAsSold($couponsList);
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
     * @param int $couponId
     * 
     * @return bool false если купон активен и ни разу не использован
     */
    public function deleteCoupon(int $couponId): bool
    {
        $soldCoupon = SoldCouponsTable::getList([
            'select' => [
                'ID',
                'COUPON',
            ],
            'filter' => [
                '=COUPON_ID' => $couponId,
            ],
        ])->fetchObject();

        if (!$soldCoupon)
        {
            return true;
        }

        $coupon = $soldCoupon->getCoupon();
        if ($coupon && $coupon->getActive() && $coupon->getUseCount() == 0)
        {
            return false;
        }

        /** @var \Bitrix\Main\DB\Connection $db */
        $db = \Bitrix\Main\Application::getConnection();
        $db->startTransaction();
        try 
        {
            // Порядок удаления имеет значения (из за обработчика события)

            $result = $soldCoupon->delete();
            if (!$result->isSuccess())
            {
                $db->rollbackTransaction();
                return false;
            }

            if ($coupon)
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
        }

        $db->commitTransaction();

        return true;
    }
}