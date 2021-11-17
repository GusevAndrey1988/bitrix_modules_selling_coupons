<?php

namespace Site\SellingCoupons\CustomProperties;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

IncludeModuleLangFile(__FILE__);

class DiscountProperty
{

    public function GetUserTypeDescription()
    {
        return [
              'PROPERTY_TYPE' => 'N',
              'USER_TYPE' => 'SITE_DISCOUNT',
              'DESCRIPTION' => Loc::getMessage('SITE_COUPON_PROPERTY_DISCOUNT_LINK'),
              'GetPropertyFieldHtml' => [DiscountProperty::class, 'GetPropertyFieldHtml'],
          ];
    }

    public function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
    {
        Loader::includeModule('sale');

        $discountsList = \Bitrix\Sale\Internals\DiscountTable::getList([
            'select' => [
                'ID',
                'NAME',
            ],
            'filter' => [
                '=ACTIVE' => 'Y',
            ]
        ])->fetchAll();

        $html = '';
        $html .= '<select name="' . $strHTMLControlName['VALUE'] . '">';
        
        foreach ($discountsList as $discount)
        {
            $html .= '
                <option value="' . $discount['ID'] . '" ' . ($discount['ID'] == $value['VALUE'] ? 'selected' : '') . '>
                    ' . $discount['NAME'] . '
                </option>
            ';
        }

        $html .= '</select>';

        return $html;
    }
}