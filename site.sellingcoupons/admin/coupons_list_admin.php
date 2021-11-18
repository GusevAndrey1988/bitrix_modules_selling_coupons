<?php

/**
 * 
 * TODO: Групповые действия
 * 
 */

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');

IncludeModuleLangFile(__FILE__);

Loader::includeModule('site.sellingcoupons');

$cuponModulePermissions = $APPLICATION->GetGroupRight('site.sellingcoupons');
if ($cuponModulePermissions == 'D')
{
	$APPLICATION->AuthForm(Loc::getMessage('SITE_COUPON_ACCESS_DENIED'));
}

$tableId = 's_selling_coupons_table';

$adminSort = new \CAdminUiSorting($tableId, 'ID', 'ASC');
$adminList = new \CAdminUiList($tableId, $adminSort);

$filterFields = [
	[
		'id' => 'ID',
		'name' => 'ID',
		'filterable' => '',
		'default' => true,
	],
	[
		'id' => 'COUPON_ID',
		'name' => Loc::getMessage('SITE_COUPON_LIST_COUPON_ID'),
		'filterable' => '',
	],
	[
		'id' => 'COUPON_CODE',
		'name' => Loc::getMessage('SITE_COUPON_LIST_COUPON_CODE'),
		'filterable' => '',
	],
	[
		'id' => 'COUPON_ACTIVE',
		'name' => Loc::getMessage('SITE_COUPON_LIST_COUPON_ACTIVE'),
		'filterable' => '',
	],
	[
		'id' => 'USE_COUNT',
		'name' => Loc::getMessage('SITE_COUPON_LIST_COUPON_USE_COUNT'),
		'filterable' => '',
	]
];

$arFilter = [];
$adminList->AddFilter($filterFields, $arFilter);

global $by, $order;
if (!isset($by))
{
	$by = 'ID';
}

if (!isset($order))
{
	$order = 'ASC';
}

$couponsList = \Site\SellingCoupons\DataMappers\SoldCouponsTable::getList([
	'select' => [
		'*',
		'COUPON_CODE' => 'COUPON.COUPON',
		'COUPON_ACTIVE' => 'COUPON.ACTIVE',
		'COUPON_USE_COUNT' => 'COUPON.USE_COUNT',
	],
	'filter' => $arFilter,
	'limit' => \CAdminUiResult::GetNavSize($tableId),
	'order' => [
		$by => $order,
	]
]);

$resultList = new \CAdminUiResult($couponsList, $tableId);

/** @global \CAdminPage $adminPage */
$selfFolderUrl = $adminPage->getSelfFolderUrl();

$resultList->NavStart();
$adminList->SetNavigationParams($resultList, ['BASE_LINK' => $selfFolderUrl . 'coupons_list_admin.php']);

$headers = [
	[
		'id' => 'ID',
		'content' => 'ID',
		'sort' => 'ID',
		'default' => true,
	],
	[
		'id' => 'COUPON_ID',
		'content' => Loc::getMessage('SITE_COUPON_LIST_COUPON_ID'),
		'sort' => 'COUPON_ID',
		'default' => true,
	],
	[
		'id' => 'COUPON_CODE',
		'content' => Loc::getMessage('SITE_COUPON_LIST_COUPON_CODE'),
		'sort' => 'COUPON_CODE',
		'default' => true,
	],
	[
		'id' => 'COUPON_ACTIVE',
		'content' => Loc::getMessage('SITE_COUPON_LIST_COUPON_ACTIVE'),
		'sort' => 'COUPON_ACTIVE',
		'default' => true,
	],
	[
		'id' => 'COUPON_USE_COUNT',
		'content' => Loc::getMessage('SITE_COUPON_LIST_COUPON_USE_COUNT'),
		'sort' => 'COUPON_USE_COUNT',
		'default' => true,
	],
];

$adminList->AddHeaders($headers);

while ($coupon = $resultList->GetNext())
{
	$row = &$adminList->AddRow($coupon['ID'], $coupon);
	
	$actions = [
		[
			'ICON' => 'edit',
			'TEXT' => Loc::getMessage('SITE_COUPON_LIST_COUPON_EDIT'),
			'LINK' => 'sale_discount_coupon_edit.php?ID=' . $coupon['COUPON_ID'] . '&lang=' . LANGUAGE_ID,
			'DEFAULT' => true
		],
		[
			'ICON' => 'delete',
			'TEXT' => Loc::getMessage('SITE_COUPON_LIST_COUPON_DELETE'),
			'LINK' => 'javascript:if(confirm(\'' . Loc::getMessage('SITE_COUPON_LIST_COUPON_DELETE_CONFIRM') . '\')) 
				deleteCoupon(' . $coupon['COUPON_ID'] . ')',
		]
	];

	$row->AddActions($actions);
}

$adminList->CheckListMode();

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');

$adminList->DisplayFilter($filterFields);
$adminList->DisplayList();

?>
	<script>
		function deleteCoupon(id) {
			BX.ajax.runAction('site:sellingcoupons.Controller.CouponController.deleteCoupon', {
				data: {
					couponId: id,
				}
			}).then(function (response) {
				window.location = '<?=$APPLICATION->GetCurPage()?>';
			}, function (response) {
				let errorText = '<div class="main-grid-message main-grid-message-error">';
				response.errors.forEach(function(item) {
					errorText += item.message;
				});
				errorText += '</div>';

				let dialog = new BX.CDialog({
					title: '<?=Loc::getMessage('SITE_COUPON_LIST_COUPON_DELETE_ERR')?>',
					content: errorText,
					width: 300,
					height: 50,
					draggable: true,
					resizable: true,
					buttons: [
						BX.CDialog.prototype.btnClose,
					]
				});

				dialog.Show();
			});
		}
	</script>
<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');