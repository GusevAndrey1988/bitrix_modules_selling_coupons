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
            // TODO: vvv
            /* if (empty($discountList))
				{
					$couponIterator = Internals\DiscountCouponTable::getList(array(
						'select' => array('ID', 'DISCOUNT_ID'),
						'filter' => array('@ID' => $listID)
					));
					while ($coupon = $couponIterator->fetch())
						$discountList[$coupon['DISCOUNT_ID']] = $coupon['DISCOUNT_ID'];
				}
				Internals\DiscountCouponTable::setDiscountCheckList($discountList);
				Internals\DiscountCouponTable::disableCheckCouponsUse();
				foreach ($listID as &$couponID)
				{
					$result = Internals\DiscountCouponTable::delete($couponID);
					if (!$result->isSuccess())
						$adminList->AddGroupError(implode('<br>', $result->getErrorMessages()), $couponID);
					unset($result);
				}
				unset($couponID);
				Internals\DiscountCouponTable::enableCheckCouponsUse();
				Internals\DiscountCouponTable::updateUseCoupons();
            } */

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