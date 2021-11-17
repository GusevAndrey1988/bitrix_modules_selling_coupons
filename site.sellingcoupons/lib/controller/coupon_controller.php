<?php

namespace Site\SellingCoupons\Controller;

use Bitrix\Main\Engine\ActionFilter;

class CouponController extends \Bitrix\Main\Engine\Controller
{
    public function configureActions()
    {
        return [
            'deleteCoupon' => [
                'class' => Actions\DeleteCouponAction::class,
                'prefilters' => [
                    new ActionFilter\Authentication(),
                    new ActionFilter\Csrf(),
                ],
                'configure' => [
                ],
            ],
        ];
    }
}