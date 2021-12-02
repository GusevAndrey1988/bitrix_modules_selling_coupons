<?php

/**
 * Настройки модуля
 */

use Bitrix\Main\Config;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

$module_id = 'site.sellingcoupons';

$cuponModulePermission = $APPLICATION->GetGroupRight($module_id);
if ($cuponModulePermission < 'R')
{
    $APPLICATION->AuthForm(Loc::getMessage('COUPON_ACCESS_DENIED'));
}

Loader::includeModule($module_id);

if (Loader::includeModule('iblock'))
{
    $iblockList = \Bitrix\Iblock\IblockTable::getList([
        'select' => [
            'ID',
            'NAME',
        ],
        'filter' => [
            '=ACTIVE' => 'Y',
        ]
    ])->fetchAll();
}
else
{
    \CAdminMessage::ShowMessage(Loc::getMessage('SITE_COUPON_CONFIG_MODULE_IBLOCK_ERROR'));
}

$request = \Bitrix\Main\Context::getCurrent()->getRequest();

$MODULE_OPTION_IBLOCK_ID = 'iblock_id';
$MODULE_OPTION_PROPERTY_CODE = 'property_code';
$MODULE_OPTION_MAIL_EVENT = 'mail_event_name';

if (
    $request->getRequestMethod() == 'POST' 
    && $cuponModulePermission === 'W' 
    && $request->get('Update') !== '' 
    && check_bitrix_sessid()
) {
    $propertyCode = $request->get('iblock-rule-link-property-code');
    if ($propertyCode)
    {
        Config\Option::set($module_id, $MODULE_OPTION_PROPERTY_CODE, $propertyCode);
    }
    else
    {
        \CAdminMessage::ShowMessage(Loc::getMessage('SITE_COUPON_CONFIG_PROPERTY_CODE_NOTSET'));
    }

    $iblockId = $request->get('iblock-id');
    if ($iblockId === '0')
    {
        Config\Option::set($module_id, $MODULE_OPTION_IBLOCK_ID, $iblockId);
    }
    else if ($iblockId)
    {
        $iblockId = intval($iblockId);
        $iblockPropertiesList = \Bitrix\Iblock\PropertyTable::getList([
            'filter' => [
                '=IBLOCK_ID' => $iblockId,
            ],
        ])->fetchAll();

        $propertyContained = false;
        foreach ($iblockPropertiesList as $iblockProperty)
        {
            if ($iblockProperty['CODE'] === $propertyCode)
            {
                $propertyContained = true;
                break;
            }
        }

        if ($propertyContained)
        {
            Config\Option::set($module_id, $MODULE_OPTION_IBLOCK_ID, $iblockId);
        }
        else
        {
            \CAdminMessage::ShowMessage(Loc::getMessage('SITE_COUPON_CONFIG_PROPERTY_NOT_CONTAINED'));
        }
    }
    else
    {
        \CAdminMessage::ShowMessage(Loc::getMessage('SITE_COUPON_CONFIG_IBLOCK_NOTSET'));
    }

    $mailEventName = $request->get('mail-event-name');
    if ($mailEventName)
    {
        Config\Option::set($module_id, $MODULE_OPTION_MAIL_EVENT, $mailEventName);
    }
    else
    {
        \CAdminMessage::ShowMessage(Loc::getMessage('SITE_COUPON_CONFIG_MAIL_EVENT_NOTSET'));
    }
}

$tabs = [
    ['DIV' => 'edit1', 'TAB' => Loc::getMessage('MAIN_TAB_SET'), 'ICON' => 'vote_settings', 'TITLE' => Loc::getMessage('MAIN_TAB_TITLE_SET')],
    ['DIV' => 'edit2', 'TAB' => Loc::getMessage('MAIN_TAB_RIGHTS'), 'ICON' => 'vote_settings', 'TITLE' => Loc::getMessage('MAIN_TAB_TITLE_RIGHTS')],
];

$tabControl = new \CAdminTabControl('tabControl', $tabs);

$tabControl->Begin();
?>
<form method="POST" action="<?=$APPLICATION->GetCurPage()?>?mid=<?=htmlspecialcharsbx($module_id)?>&lang=<?=LANGUAGE_ID?>" id="FORMACTION">
    <?=bitrix_sessid_post()?>

    <?php $tabControl->BeginNextTab();?>

    <table>
        <tbody>
            <tr>
                <td>
                    <label for="iblock-id"><?=Loc::getMessage('SITE_COUPON_CONFIG_IBLOCK_ID')?></label>
                </td>

                <td>
                    <select name="iblock-id" id="iblock-id">
                        <?php $selectedIblockId = Config\Option::get($module_id, $MODULE_OPTION_IBLOCK_ID);?>

                        <option value="0" <?php if ($selectedIblockId == 0) echo 'selected'?>><?=Loc::getMessage('SITE_COUPON_CONFIG_NOT_SELECTED')?></option>
                        <?php foreach ($iblockList as $iblock):?>
                            <option value="<?=$iblock['ID']?>" <?php if ($iblock['ID'] == $selectedIblockId) echo 'selected'?>>
                                <?=$iblock['NAME'] . '[' . $iblock['ID'] . ']'?>
                            </option>
                        <?php endforeach?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="iblock-rule-link-property-code">
                        <?=Loc::getMessage('SITE_COUPON_CONFIG_IBLOCK_PROPERTY_CODE')?>
                    </label>   
                </td>
                <td>
                    <input type="text" name="iblock-rule-link-property-code" 
                        id="iblock-rule-link-property-code" value="<?=Config\Option::get($module_id, $MODULE_OPTION_PROPERTY_CODE)?>">       
                </td>
            </tr>
            <tr>
                <td>
                    <label for="mail-event-name">
                        <?=Loc::getMessage('SITE_COUPON_CONFIG_MAIL_EVENT')?>
                    </label>   
                </td>
                <td>
                    <input type="text" name="mail-event-name" 
                        id="mail-event-name" value="<?=Config\Option::get($module_id, $MODULE_OPTION_MAIL_EVENT)?>">       
                </td>
            </tr>
        </tbody>

        <?php
            $tabControl->BeginNextTab();
            require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/admin/group_rights.php');

            $tabControl->Buttons();
        ?>
        <input <?php if ($cuponModulePermission < 'W') echo 'disabled'?> type="submit" class="adm-btn-green" name="Update" value="<?=Loc::getMessage('SITE_COUPON_CONFIG_SAVE')?>" />
        <input type="hidden" name="Update" value="Y" />
        <input type="reset" name="reset" value="<?=Loc::getMessage('SITE_COUPON_CONFIG_RESET')?>" />
    
        <?php $tabControl->End();?>
    </table>
</form>