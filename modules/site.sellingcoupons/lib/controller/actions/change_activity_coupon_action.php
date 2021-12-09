<?php

namespace Site\SellingCoupons\Controller\Actions;

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

abstract class ChangeActivityCouponActionBase extends \Bitrix\Main\Engine\Action
{
    protected function changeActivity(int $couponId, bool $active)
    {
        /** @global \CMain $APPLICATION */
        global $APPLICATION;

        $permission = $APPLICATION->GetUserRight('site.sellingcoupons');

        if ($permission === 'W')
        {
            $couponManager = new \Site\SellingCoupons\SoldCouponManager();
    
            if ($errorList = $couponManager->changeActivity([$couponId], $active))
            {
                foreach ($errorList as $error)
                {
                    $this->addError(new \Bitrix\Main\Error($error));
                }
            }
        }
        else
        {
            $this->addError(
                new \Bitrix\Main\Error(
                    Loc::getMessage('SITE_COUPON_CONTROLLER_ACTION_ACTIVATE_PERM_ERR')
                )
            );
        }
    }
}