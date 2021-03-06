# Описание

Модуль добавляет возможность продажи купонов через обычный каталог товаров.

## Схема работы

Покупатель добавляет купон в корзину, далее, после оформления и оплаты заказа, ему на почту приходит сообщение с кодами купонов.\
В момент оплаты заказа купоны создаются и помечаются как проданные.\
Список проданных купонов можно посмотреть в административном разделе: _Магазин > Список проданных купонов_.\
Проданные купоны невозможно удалить пока они активны. Деактивация купонов происходит после его использования или в ручную в административном разделе.

# Установка

Скопировать каталог _modules/site.sellingcoupons_ в каталог _path_to_site/local/modules_. Затем в административном разделе перейти в меню _Marketplace > Установленные_решения_ и установить модуль "Продажа купонов".

# Настройка

## Основная

1. На странице "Правила работы с корзиной" создать необходимые скидки;
1. Для каждой созданной скидки добавить хотя бы один купон (необходимо для того, что бы скидка не применялась без купона, пока не придумал, как обойти эту проблему);
1. В инфоблоке, через который будут продаваться купоны, добавить свойство типа "Привязка к правилу корзины";
1. Создать купоны (элементы инфоблока) и указать привязку к правилу корзины;
1. На странице настроек модуля "Продажа купонов" указать инфоблок с купонами, код свойства с привязкой к правилу корзины и название почтового события в котором будут перечислены коды купленных купонов;

## Доступные поля в почтовом событии:

* #USER_EMAIL# - email пользователя
* #USER_NAME# - полное имя пользователя
* #COUPONS# - список кодов купонов
* #DEFAULT_EMAIL_FROM# - E-Mail адрес по умолчанию (устанавливается в настройках)
* #SITE_NAME# - Название сайта (устанавливается в настройках)
* #SERVER_NAME# - URL сервера (устанавливается в настройках)
