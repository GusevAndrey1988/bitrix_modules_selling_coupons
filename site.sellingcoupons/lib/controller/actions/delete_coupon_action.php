<?php

namespace Site\SellingCoupons\Controller\Actions;

use Bitrix\Main\Engine\Response;
use Bitrix\Main\Localization\Loc;

IncludeModuleLangFile(__FILE__);

class DeleteCouponAction extends \Bitrix\Main\Engine\Action
{
    public function run(int $couponId)
    {
        $uiserId = $this->getCurrentUser()->getId();

        /** @global \CMain $APPLICATION */
        global $APPLICATION;
        $permission = $APPLICATION->GetUserRight('site.sellingcoupons');

        if ($permission === 'W')
        {
            $seller = new \Site\SellingCoupons\CouponSeller();
    
            if (!$seller->deleteCoupon($couponId))
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