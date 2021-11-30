<?php

use Bitrix\Main\Config;
use Bitrix\Main\Loader;
    use Bitrix\Main\Localization\Loc;

    $module_id = 'site.sellingcoupons';

    $cuponModulePermissions = $APPLICATION->GetGroupRight($module_id);
    if ($cuponModulePermissions < 'R')
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
        ShowError(Loc::getMessage('SITE_COUPON_CONFIG_MODULE_IBLOCK_ERROR'));
    }

    $request = \Bitrix\Main\Context::getCurrent()->getRequest();

    if (
        $request->getRequestMethod() == 'POST' 
        && $cuponModulePermissions === 'W' 
        && $request->get('Update') !== '' 
        && check_bitrix_sessid()
    ) {
        $propertyCode = $request->get('iblock-discount-property');
        if ($propertyCode)
        {
            Config\Option::set($module_id, 'property_code', $propertyCode);
        }
        else
        {
            \CAdminMessage::ShowMessage(Loc::getMessage('SITE_COUPON_CONFIG_PROPERTY_CODE_NOTSET'));
        }

        $iblockId = $request->get('iblock');
        if ($iblockId === '0')
        {
            Config\Option::set($module_id, 'iblock_id', $iblockId);
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
                Config\Option::set($module_id, 'iblock_id', $iblockId);
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
            Config\Option::set($module_id, 'mail_event_name', $mailEventName);
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

    <?php
        $tabControl->BeginNextTab();
    ?>

    <table>
        <tbody>
            <tr>
                <td>
                    <label for="iblock"><?=Loc::getMessage('SITE_COUPON_CONFIG_IBLOCK_ID')?></label>
                </td>

                <td>
                    <select name="iblock" id="iblock">
                        <?php $selectedIblockId = Config\Option::get($module_id, 'iblock_id')?>
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
                    <label for="iblock-discount-property">
                        <?=Loc::getMessage('SITE_COUPON_CONFIG_IBLOCK_PROPERTY_CODE')?>
                    </label>   
                </td>
                <td>
                    <input type="text" name="iblock-discount-property" 
                        id="iblock-discount-property" value="<?=Config\Option::get($module_id, 'property_code')?>">       
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
                        id="mail-event-name" value="<?=Config\Option::get($module_id, 'mail_event_name')?>">       
                </td>
            </tr>
        </tbody>

        <?php
            $tabControl->BeginNextTab();
            require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/admin/group_rights.php');
            $tabControl->Buttons();
        ?>
        <input <?php if ($cuponModulePermissions < 'W') echo 'disabled'?> type="submit" class="adm-btn-green" name="Update" value="<?=Loc::getMessage('SITE_COUPON_CONFIG_SAVE')?>" />
        <input type="hidden" name="Update" value="Y" />
        <input type="reset" name="reset" value="<?=Loc::getMessage('SITE_COUPON_CONFIG_RESET')?>" />
    
        <?php
            $tabControl->End();
        ?>
    </table>
</form>