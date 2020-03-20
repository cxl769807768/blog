/*
 Navicat Premium Data Transfer

 Source Server         : localhost
 Source Server Type    : MySQL
 Source Server Version : 50721
 Source Host           : 192.168.33.10:3306
 Source Schema         : blog

 Target Server Type    : MySQL
 Target Server Version : 50721
 File Encoding         : 65001

 Date: 20/03/2020 16:43:02
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for ad
-- ----------------------------
DROP TABLE IF EXISTS `ad`;
CREATE TABLE `ad`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '广告ID',
  `link_url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '跳转类型为 2 时有效',
  `pic` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '图片的地址',
  `status` tinyint(1) NULL DEFAULT 1 COMMENT '广告状态（0：禁用，1：正常）',
  `sort` int(11) NULL DEFAULT 0 COMMENT '排序',
  `create_time` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime(0) NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 24 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '广告表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for ad_site
-- ----------------------------
DROP TABLE IF EXISTS `ad_site`;
CREATE TABLE `ad_site`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '广告位id',
  `alias` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '别名',
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '广告位名称',
  `describe` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '广告位描述',
  `create_time` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime(0) NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 12 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '广告位' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for auth_admin
-- ----------------------------
DROP TABLE IF EXISTS `auth_admin`;
CREATE TABLE `auth_admin`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(60) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户名',
  `password` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '登录密码',
  `phone` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户手机号',
  `email` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '登录邮箱',
  `avatar` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '/upload/thumbs/avatar/20190404/c57c729e7f2932e57fc3288edc8dc0fb.jpg' COMMENT '用户头像',
  `token` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户token',
  `exceed_time` int(11) NOT NULL DEFAULT 0 COMMENT '过期时间',
  `update_time` datetime(0) NULL DEFAULT NULL COMMENT '修改时间',
  `create_time` datetime(0) NULL DEFAULT NULL COMMENT '注册时间',
  `status` tinyint(11) NOT NULL DEFAULT 1 COMMENT '用户状态 0：未通过； 1：通过 ；2：未审核',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `user_login_key`(`username`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '管理员表' ROW_FORMAT = Compact;

-- ----------------------------
-- Records of auth_admin
-- ----------------------------
INSERT INTO `auth_admin` VALUES (1, 'admin', '527a55627a0da3a336701601de60fea3', '18725074649', 'cxl483064@163.com', '/upload/thumbs/avatar/20190404/c57c729e7f2932e57fc3288edc8dc0fb.jpg', 'f6d754cc0ee1575605061a28e6c9d06f', 1587278593, NULL, NULL, 1);
INSERT INTO `auth_admin` VALUES (2, 'editor', 'f6d754cc0ee1575605061a28e6c9d06f', '18725074649', 'cxl@163.com', '/upload/thumbs/avatar/20190404/c57c729e7f2932e57fc3288edc8dc0fb.jpg', 'f6d754cc0ee1575605061a28e6c9d06f', 1571278481, NULL, NULL, 1);
INSERT INTO `auth_admin` VALUES (3, 'test', '811441d3a138712b440ea470e8d5e638', '18725074648', 'cxl483064@163.com', '/upload/thumbs/avatar/20200103/ec641f74ffddbb908e90150674abdcc9.jpg', 'd3ca009c78c215c2f91d6440bf056e5e', 1580629264, '2020-01-03 15:36:46', '2020-01-03 10:09:10', 1);
INSERT INTO `auth_admin` VALUES (4, 'test2', '811441d3a138712b440ea470e8d5e638', '18725074647', 'cxl483064@163.com', '/upload/thumbs/avatar/20200103/689939fd2c159883dc209c0dfb08bf2b.jpg', '', 0, '2020-01-03 11:03:37', '2020-01-03 10:52:14', 1);

-- ----------------------------
-- Table structure for auth_role
-- ----------------------------
DROP TABLE IF EXISTS `auth_role`;
CREATE TABLE `auth_role`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `alias` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '别名',
  `name` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '商户名称',
  `remark` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '备注',
  `listorder` int(3) NOT NULL DEFAULT 0 COMMENT '排序，优先级，越小优先级越高',
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态 0：禁用； 1：正常 ；2：未验证',
  `create_time` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime(0) NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '角色表' ROW_FORMAT = Compact;

-- ----------------------------
-- Records of auth_role
-- ----------------------------
INSERT INTO `auth_role` VALUES (1, 'superman', '超级管理员', '', 0, 1, NULL, NULL);
INSERT INTO `auth_role` VALUES (2, 'editor', '编辑组', '编辑', 1, 1, NULL, NULL);
INSERT INTO `auth_role` VALUES (3, '', '测试1', '测试1', 999, 1, '2020-01-03 14:27:05', '2020-01-03 14:27:59');

-- ----------------------------
-- Table structure for auth_role_admin
-- ----------------------------
DROP TABLE IF EXISTS `auth_role_admin`;
CREATE TABLE `auth_role_admin`  (
  `role_id` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '角色 id',
  `admin_id` int(11) NULL DEFAULT 0 COMMENT '管理员id'
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '用户角色对应表' ROW_FORMAT = Compact;

-- ----------------------------
-- Records of auth_role_admin
-- ----------------------------
INSERT INTO `auth_role_admin` VALUES (1, 1);
INSERT INTO `auth_role_admin` VALUES (2, 4);
INSERT INTO `auth_role_admin` VALUES (2, 3);

-- ----------------------------
-- Table structure for auth_role_rule
-- ----------------------------
DROP TABLE IF EXISTS `auth_role_rule`;
CREATE TABLE `auth_role_rule`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) UNSIGNED NOT NULL COMMENT '角色',
  `rule_id` int(11) NOT NULL DEFAULT 0 COMMENT '权限id',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 12 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '权限授权表' ROW_FORMAT = Compact;

-- ----------------------------
-- Records of auth_role_rule
-- ----------------------------
INSERT INTO `auth_role_rule` VALUES (8, 3, 42);
INSERT INTO `auth_role_rule` VALUES (9, 3, 40);
INSERT INTO `auth_role_rule` VALUES (10, 2, 42);
INSERT INTO `auth_role_rule` VALUES (11, 2, 40);

-- ----------------------------
-- Table structure for auth_rule
-- ----------------------------
DROP TABLE IF EXISTS `auth_rule`;
CREATE TABLE `auth_rule`  (
  `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` char(80) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '规则唯一标识',
  `title` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '规则中文名称',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1 正常，0=禁用',
  `condition` char(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '规则表达式，为空表示存在就验证',
  `pid` mediumint(8) NOT NULL DEFAULT 0 COMMENT '上级菜单',
  `sorts` mediumint(8) NOT NULL DEFAULT 0 COMMENT '升序',
  `icon` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `path` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '路经',
  `component` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '组件',
  `hidden` tinyint(1) NOT NULL DEFAULT 0 COMMENT '左侧菜单 0==显示,1隐藏',
  `noCache` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1=不缓存，0=缓存',
  `alwaysShow` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1= 总显示,0=否 依据子菜单个数',
  `redirect` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `update_time` datetime(0) NULL DEFAULT NULL COMMENT '更新时间',
  `create_time` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `name`(`name`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 47 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '规则表' ROW_FORMAT = Compact;

-- ----------------------------
-- Records of auth_rule
-- ----------------------------
INSERT INTO `auth_rule` VALUES (1, 'auth', '权限管理', 1, '', 0, 0, 'component', '/auth', 'layout/Layout', 0, 0, 1, '', '2019-03-13 17:23:52', '2019-03-13 17:24:03');
INSERT INTO `auth_rule` VALUES (2, 'auth/admin', '管理员列表', 1, '', 1, 0, 'user', 'admin', 'admin/index', 0, 0, 0, '', '2019-03-13 17:23:54', '2019-03-13 17:24:06');
INSERT INTO `auth_rule` VALUES (7, 'auth/rule', '权限列表', 1, '', 1, 0, 'lock', 'rules', 'rule/index', 0, 0, 0, '', '2019-03-13 17:23:56', '2019-03-13 17:24:08');
INSERT INTO `auth_rule` VALUES (38, 'auth/role', '角色列表', 1, '', 1, 0, 'list', 'roles', 'role/index', 0, 1, 1, '', '2019-07-04 09:49:21', '2019-03-13 17:24:11');
INSERT INTO `auth_rule` VALUES (40, 'advert', '广告管理', 1, '', 0, 0, 'guide', '/advert', 'layout/Layout', 0, 1, 0, '', '2019-03-08 14:22:24', '2019-03-08 14:22:24');
INSERT INTO `auth_rule` VALUES (41, 'advert/site', '广告位管理', 1, '', 40, 0, 'list', 'site', 'advert/site', 0, 1, 0, '', '2019-03-08 14:25:46', '2019-03-08 14:25:46');
INSERT INTO `auth_rule` VALUES (42, 'advert/ad', '广告管理', 1, '', 40, 0, 'ad', 'ad', 'advert/ad', 0, 1, 0, '', '2019-03-08 14:27:10', '2019-03-13 17:24:21');
INSERT INTO `auth_rule` VALUES (43, 'product', '产品管理', 1, '', 0, 0, 'peoples', '/product', 'layout/Layout', 0, 1, 0, '', '2020-03-20 14:01:03', '2020-03-20 14:01:03');
INSERT INTO `auth_rule` VALUES (44, 'product/index', '产品列表', 1, '', 43, 0, 'list', 'index', 'product/index', 0, 1, 0, '', '2020-03-20 14:05:57', '2020-03-20 14:05:57');
INSERT INTO `auth_rule` VALUES (45, 'toppic', '栏目管理', 1, '', 0, 0, 'tab', '/toppic', 'layout/Layout', 0, 1, 0, '', '2020-03-20 14:17:53', '2020-03-20 14:17:53');
INSERT INTO `auth_rule` VALUES (46, 'toppic/index', '栏目列表', 1, '', 45, 0, 'list', 'index', 'toppic/index', 0, 1, 0, '', '2020-03-20 14:55:46', '2020-03-20 14:55:46');

-- ----------------------------
-- Table structure for buy_order
-- ----------------------------
DROP TABLE IF EXISTS `buy_order`;
CREATE TABLE `buy_order`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '购买订单表',
  `uid` int(11) NOT NULL DEFAULT 0 COMMENT '会员信息表主键ID',
  `xid` int(11) NOT NULL DEFAULT 0 COMMENT '模块商品ID',
  `cmod` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'course' COMMENT '功能模块',
  `state` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态（0待付款, 2已支付4待评价，5已评价,6退款7退款完成 9已取消）',
  `update_time` datetime(0) NOT NULL COMMENT '更新时间',
  `create_time` datetime(0) NOT NULL COMMENT '提交时间',
  `invoice_id` int(11) NOT NULL DEFAULT 0 COMMENT '发票id',
  `contact` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '联系人',
  `phone` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '联系电话',
  `order_no` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '订单号',
  `coupon_id` int(11) NOT NULL DEFAULT 0 COMMENT '客户的红包主键ID',
  `original_price` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '原价',
  `price` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '支付金额',
  `type` tinyint(1) NOT NULL DEFAULT 0 COMMENT '订单类型（0私人付款，1学校专供，2团体， 3亲子4特长培训5小记者报名）',
  `travel_detail` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '出行行程',
  `pay_type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '支付方式(1微信 2支付宝 3银联)',
  `source` tinyint(1) NOT NULL DEFAULT 0 COMMENT '来源（0 安卓,1苹果，2pc端, 3小程序）',
  `remark` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '备注',
  `travel_num` int(3) NOT NULL DEFAULT 0 COMMENT '出行人人数',
  `payid` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '支付商家返回的订单号',
  `paytime` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '支付时间',
  `ordertime` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '下单时间',
  `confirm_code` int(3) NULL DEFAULT 0 COMMENT '团体订单确认码',
  `sid` int(8) NULL DEFAULT 0 COMMENT '学生id',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '订单表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for img_manage
-- ----------------------------
DROP TABLE IF EXISTS `img_manage`;
CREATE TABLE `img_manage`  (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `mod` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '模块',
  `url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '图片地址',
  `create_time` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 8 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of img_manage
-- ----------------------------
INSERT INTO `img_manage` VALUES (2, 'avatar', '/upload/thumbs/avatar/20200103/ec641f74ffddbb908e90150674abdcc9.jpg', '2020-01-03 15:36:46');
INSERT INTO `img_manage` VALUES (3, 'product_cover', '/upload/thumbs/product_cover/20200320/e4669050d5c0aa608f6960828f8bd1ab.jpg', '2020-03-20 16:38:30');
INSERT INTO `img_manage` VALUES (4, 'product_slideshow', '/upload/thumbs/product_slideshow/20200320/50da939b331542bafdeec449cc0d221e.jpg', '2020-03-20 16:38:30');
INSERT INTO `img_manage` VALUES (5, 'product_slideshow', '/upload/thumbs/product_slideshow/20200320/ad2b62d9b3ca3a65ccabbd2272835382.jpg', '2020-03-20 16:38:30');
INSERT INTO `img_manage` VALUES (6, 'product_slideshow', '/upload/thumbs/product_slideshow/20200320/02132145ddbc54e45a79b5bc144dfa19.jpg', '2020-03-20 16:38:30');
INSERT INTO `img_manage` VALUES (7, 'product_introduce', '/upload/thumbs/product_introduce/20200320/18fa0d893388c1bfce6d409945bd2cc4.jpg', '2020-03-20 16:38:30');

-- ----------------------------
-- Table structure for money_log
-- ----------------------------
DROP TABLE IF EXISTS `money_log`;
CREATE TABLE `money_log`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NULL DEFAULT NULL,
  `role_id` int(11) NULL DEFAULT NULL COMMENT '关联的角色表id',
  `cmod` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '课件' COMMENT '模块',
  `xid` int(8) NULL DEFAULT 0 COMMENT '模块id',
  `order_no` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '订单号\n',
  `tip` tinyint(1) NULL DEFAULT NULL COMMENT '0充值1提现2购买3核销4退款5结算6退款产生的手续费损失',
  `outmoney` decimal(10, 2) NULL DEFAULT 0.00 COMMENT '出账',
  `inmoney` decimal(10, 2) NULL DEFAULT 0.00 COMMENT '进账',
  `create_time` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '资金流水表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for product
-- ----------------------------
DROP TABLE IF EXISTS `product`;
CREATE TABLE `product`  (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `name` varchar(15) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '名称',
  `cover` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '封面',
  `desc` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '描述',
  `slideshow` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '轮播图',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '0禁用1正常',
  `tid` int(3) NOT NULL COMMENT '栏目id',
  `phone` varchar(11) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '手机号',
  `introduce` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '介绍',
  `create_time` datetime(0) NULL DEFAULT NULL,
  `update_time` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of product
-- ----------------------------
INSERT INTO `product` VALUES (1, '罗茨萝卜', '/upload/thumbs/product_cover/20200320/e4669050d5c0aa608f6960828f8bd1ab.jpg', '好甜的萝卜', 'a:3:{i:0;s:78:\"/upload/thumbs/product_slideshow/20200320/50da939b331542bafdeec449cc0d221e.jpg\";i:1;s:78:\"/upload/thumbs/product_slideshow/20200320/ad2b62d9b3ca3a65ccabbd2272835382.jpg\";i:2;s:78:\"/upload/thumbs/product_slideshow/20200320/02132145ddbc54e45a79b5bc144dfa19.jpg\";}', 1, 1, '18725074649', '<p>好甜的萝卜</p><p><img src=\"http://www.blog.com/upload/thumbs/product_introduce/20200320/18fa0d893388c1bfce6d409945bd2cc4.jpg\" alt=\"图像\"></p>', '2020-03-20 16:37:02', '2020-03-20 16:37:02');
INSERT INTO `product` VALUES (2, '罗茨萝卜', '/upload/thumbs/product_cover/20200320/e4669050d5c0aa608f6960828f8bd1ab.jpg', '好甜的萝卜', 'a:3:{i:0;s:78:\"/upload/thumbs/product_slideshow/20200320/50da939b331542bafdeec449cc0d221e.jpg\";i:1;s:78:\"/upload/thumbs/product_slideshow/20200320/ad2b62d9b3ca3a65ccabbd2272835382.jpg\";i:2;s:78:\"/upload/thumbs/product_slideshow/20200320/02132145ddbc54e45a79b5bc144dfa19.jpg\";}', 1, 1, '18725074649', '<p>好甜的萝卜</p><p><img src=\"http://www.blog.com/upload/thumbs/product_introduce/20200320/18fa0d893388c1bfce6d409945bd2cc4.jpg\" alt=\"图像\"></p>', '2020-03-20 16:38:30', '2020-03-20 16:38:30');

-- ----------------------------
-- Table structure for purse
-- ----------------------------
DROP TABLE IF EXISTS `purse`;
CREATE TABLE `purse`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(8) NOT NULL,
  `money` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '余额',
  `role_id` int(8) NULL DEFAULT 0 COMMENT '关联的角色表id',
  `create_time` datetime(0) NULL DEFAULT NULL,
  `update_time` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '钱包表' ROW_FORMAT = Compact;

-- ----------------------------
-- Records of purse
-- ----------------------------
INSERT INTO `purse` VALUES (1, 1, 0.00, 0, '2020-03-20 16:34:48', '2020-03-20 16:34:48');

-- ----------------------------
-- Table structure for relation_site_ad
-- ----------------------------
DROP TABLE IF EXISTS `relation_site_ad`;
CREATE TABLE `relation_site_ad`  (
  `site_id` int(8) NOT NULL COMMENT '广告位id',
  `ad_id` int(8) NOT NULL COMMENT '广告id',
  PRIMARY KEY (`site_id`, `ad_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '广告位和广告的关系表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for toppic
-- ----------------------------
DROP TABLE IF EXISTS `toppic`;
CREATE TABLE `toppic`  (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `name` varchar(15) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '名称',
  `icon` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '图标',
  `isShow` tinyint(1) NOT NULL DEFAULT 1 COMMENT '0不显示1显示',
  `pid` int(8) NOT NULL COMMENT '父级id',
  `status` tinyint(1) NOT NULL COMMENT '0禁用1正常',
  `create_time` datetime(0) NULL DEFAULT NULL,
  `update_time` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of toppic
-- ----------------------------
INSERT INTO `toppic` VALUES (1, '农产品', 'tab', 1, 0, 1, '2020-03-20 14:59:47', '2020-03-20 14:59:47');
INSERT INTO `toppic` VALUES (2, '资讯', 'documentation', 1, 0, 1, '2020-03-20 15:01:44', '2020-03-20 15:01:44');

-- ----------------------------
-- Table structure for user
-- ----------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user`  (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `username` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '用户名',
  `phone` varchar(11) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '手机号',
  `password` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '密码',
  `token` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'token',
  `exceed_time` int(11) NULL DEFAULT NULL COMMENT 'token过期时间',
  `create_time` datetime(0) NULL DEFAULT NULL,
  `update_time` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of user
-- ----------------------------
INSERT INTO `user` VALUES (1, '18725074649', '18725074649', '811441d3a138712b440ea470e8d5e638', '33eafb87bbdab5bdf10d90733ddf9ff4', 1584779688, '2020-03-20 16:34:48', '2020-03-20 16:34:48');

-- ----------------------------
-- Table structure for vendor_type
-- ----------------------------
DROP TABLE IF EXISTS `vendor_type`;
CREATE TABLE `vendor_type`  (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `name` varchar(15) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '名称',
  `describe` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '描述',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '0禁用1正常',
  `create_time` datetime(0) NULL DEFAULT NULL,
  `update_time` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of vendor_type
-- ----------------------------
INSERT INTO `vendor_type` VALUES (1, '农产品', '农产品', 1, '2020-03-20 14:46:08', '2020-03-20 14:46:08');

SET FOREIGN_KEY_CHECKS = 1;
