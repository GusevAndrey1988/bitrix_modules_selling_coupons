<?php

namespace Site\SellingCoupons\Controller\Actions;

use Bitrix\Main\Localization\Loc;

IncludeModuleLangFile(__FILE__);

class DeleteCouponAction extends \Bitrix\Main\Engine\Action
{
    public function run(int $couponId)
    {
        /** @global \CMain $APPLICATION */
        global $APPLICATION;

        $permission = $APPLICATION->GetUserRight('site.sellingcoupons');

        if ($permission === 'W')
        {
            $couponManager = new \Site\SellingCoupons\SoldCouponManager();
    
            if (!$couponManager->deleteSoldCoupons([$couponId]))
            {
                $this->addError(
                    new \Bitrix\Main\Error(
                        Loc::getMessage('SITE_COUPON_CONTROLLER_ACTION_DELETE_ERR')
                    )
                );
            }
        }
        else
        {
            $this->addError(
                new \Bitrix\Main\Error(
                    Loc::getMessage('SITE_COUPON_CONTROLLER_ACTION_DELETE_PERM_ERR')
                )
            );
        }
    }
}