CREATE TABLE IF NOT EXISTS `s_sold_coupons` (
    `ID` INT(11) AUTO_INCREMENT PRIMARY KEY,
    `COUPON_ID` INT(11) NOT NULL UNIQUE,
    `ORDER_ID` INT(11) NOT NULL
);