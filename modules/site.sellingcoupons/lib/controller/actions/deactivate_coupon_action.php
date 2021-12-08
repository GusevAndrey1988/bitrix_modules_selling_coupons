<?php

namespace Site\SellingCoupons\Controller\Actions;

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class DeactivateCouponAction extends ChangeActivityCouponActionBase
{
    public function run(int $couponId)
    {
        $this->changeActivity($couponId, false);
    }
}