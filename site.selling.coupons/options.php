<?php

use Bitrix\Main\Config;
use Bitrix\Main\Loader;
    use Bitrix\Main\Localization\Loc;

    $module_id = 'site.selling.coupons';

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
        // TODO: Сохранение параметров
        Config\Option::set($module_id, 'iblock_id', $request->get('iblock'));
        Config\Option::set($module_id, 'property_code', $request->get('iblock-discount-property'));
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