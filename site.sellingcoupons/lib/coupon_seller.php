<?php

/**
 * 
 * TODO: Переименовать класс в SoldCouoponManager
 * TODO: Переименовать deleteCoupons => deleteSoldCoupon
 * TODO: Переименовать sellCouopn => markCouponAsSold
 * 
 */
namespace Site\SellingCoupons;

use Bitrix\Main\Loader;
use \Bitrix\Sale\Internals;
use Site\SellingCoupons\DataMappers\SoldCouponsTable;

class CouponSeller
{
    /**
     * @return \Site\SellingCoupons\DataMappers\SoldCoupon[]
     */
    public function sellCoupons(int $discountId, int $count): array
    {
        if (!Loader::includeModule('sale'))
        {
            return [];
        }
        
        Internals\DiscountCouponTable::setDiscountCheckList([$discountId]);
        Internals\DiscountCouponTable::disableCheckCouponsUse();
        
        $connection = \Bitrix\Main\Application::getConnection();

        $connection->startTransaction();
        
        $couponClassName = Internals\DiscountCouponTable::getObjectClass();

        $success = true;
        $soldCouons = [];
        for ($counter = 0; $counter < $count; $counter++)
        {
            $couponCode = Internals\DiscountCouponTable::generateCoupon(true);

            $newCoupon = new $couponClassName();
            $newCoupon->setDiscountId($discountId);
            $newCoupon->setCoupon($couponCode);
            $newCoupon->setType(Internals\DiscountCouponTable::TYPE_ONE_ORDER);
            $result = $newCoupon->save();

            if (!$result->isSuccess())
            {
                $success = false;
                $connection->rollbackTransaction();
                break;
            }

            $newSoldCoupon = new \Site\SellingCoupons\DataMappers\SoldCoupon();
            $newSoldCoupon->setCoupon($newCoupon);
            $result = $newSoldCoupon->save();

            if (!$result->isSuccess())
            {
                $success = false;
                $connection->rollbackTransaction();
                break;
            }

            $soldCouons[] = $newSoldCoupon;
        }

        if ($success)
        {
            $connection->commitTransaction();
        }

        Internals\DiscountCouponTable::enableCheckCouponsUse();
        Internals\DiscountCouponTable::updateUseCoupons();
       
        return $success ? $soldCouons : [];
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