3.03;
ALTER TABLE `%DB_PREFIX%supplier_location`
MODIFY COLUMN `ref_avg_price`  float(14,4) NOT NULL COMMENT '人均消费' AFTER `image_count`;

ALTER TABLE `%DB_PREFIX%supplier_location`
ADD COLUMN `adv_img_1`  text NOT NULL COMMENT '门店顶部广告位' AFTER `dp_count_5`,
ADD COLUMN `adv_img_2`  text NOT NULL COMMENT '门店侧边广告位' AFTER `adv_img_1`,
ADD COLUMN `location_qq`  varchar(20) NOT NULL COMMENT '门店客服QQ' AFTER `adv_img_2`;

update  %DB_PREFIX%conf set value_scope='1,2,3' where name='KUAIDI_TYPE';

INSERT INTO `%DB_PREFIX%conf` VALUES ('181', 'BIZ_REGISTER_SMS', '0', '5', '1', '0,1', '1', '1', '100');
UPDATE `%DB_PREFIX%conf` set `value` = '3.03' where name = 'DB_VERSION';