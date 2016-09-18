<?php
//检查明天的表存不存在，不存在就创建
include 'config.php';
$tablename = "ik_heart_clientN_".date('Ymd',strtotime("+1 day"));
$S12->query("
CREATE TABLE IF NOT EXISTS`{$tablename}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gwid` varchar(32) DEFAULT NULL,
  `ap_mac` varchar(18) DEFAULT NULL,
  `client_mac` varchar(18) DEFAULT NULL,
  `client_ip` varchar(15) DEFAULT NULL,
  `client_agent` varchar(128) DEFAULT NULL,
  `total_down_flow` varchar(20) DEFAULT NULL,
  `total_up_flow` varchar(20) DEFAULT NULL,
  `up_speed` int(10) DEFAULT NULL,
  `down_speed` int(10) DEFAULT NULL,
  `authentication` varchar(20) DEFAULT NULL,
  `action_data` varchar(90) DEFAULT NULL,
  `timestamp` int(10) DEFAULT NULL,
  `connect_count` int(10) DEFAULT NULL,
  `client_hostname` varchar(32) DEFAULT NULL,
  `login_time` int(10) DEFAULT NULL,
  `online_time` int(10) DEFAULT NULL,
  `client_type` varchar(20) DEFAULT NULL,
  `router_time` int(10) DEFAULT NULL,
  `old_mac` int(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `k_gwid` (`gwid`) USING BTREE,
  KEY `k_timestamp` (`timestamp`) USING BTREE
) ENGINE=MEMORY DEFAULT CHARSET=utf8;");
