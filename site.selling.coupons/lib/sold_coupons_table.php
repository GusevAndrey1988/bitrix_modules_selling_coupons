<?php

namespace Site\SellingCoupons;

use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;

class SoldCouponsTable extends \Bitrix\Main\ORM\Data\DataManager
{
    public static function getTableName()
    {
        return 's_sold_coupons';    
    }

    public static function getObjectClass()
    {
        return SoldCoupon::class;
    }

    public static function getCollectionClass()
    {
        return SoldCouponCollection::class;
    }

    public static function getMap()
    {
        return [
            (new IntegerField('ID'))
                ->configurePrimary()
                ->configureAutocomplete(),

            (new IntegerField('COUPON_ID'))
                ->configureNullable(false)
                ->configureUnique(),

            (new Reference(
                'COUPON',
                \Bitrix\Sale\Internals\DiscountCouponTable::class,
                Join::on('this.COUPON_ID', 'ref.ID')
            )),
        ];
    }
}