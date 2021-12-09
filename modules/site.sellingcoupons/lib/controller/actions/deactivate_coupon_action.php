<?php

namespace Site\SellingCoupons\Controller\Actions;

class DeactivateCouponAction extends ChangeActivityCouponActionBase
{
    public function run(int $couponId)
    {
        $this->changeActivity($couponId, false);
    }
}