<?php

namespace Site\SellingCoupons;

use Site\SellingCoupons\DataMappers\SoldCouponsTable;

class CouponSeller
{
    public function doSellCoupon()
    {
        throw new \Exception('TODO: incoplite method');
    }

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
     * @param int $couponId
     * 
     * @return bool false если купон активен
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
        if ($coupon && $coupon->getActive())
        {
            return false;
        }

        /** @var \Bitrix\Main\DB\Connection $db */
        $db = \Bitrix\Main\Application::getConnection();
        $db->startTransaction();
        try 
        {
            if ($coupon)
            {
                $result = $coupon->delete();

                if (!$result->isSuccess())
                {
                    $db->rollbackTransaction();
                    return false;
                }
            }

            $result = $soldCoupon->delete();
            if (!$result->isSuccess())
            {
                $db->rollbackTransaction();
                return false;
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