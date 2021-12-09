<?php

namespace Site\SellingCoupons\Controller\Actions;

class ActivateCouponAction extends ChangeActivityCouponActionBase
{
    public function run(int $couponId)
    {
        $this->changeActivity($couponId, true);
    }
}