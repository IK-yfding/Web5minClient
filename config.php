<?php
/**
 * config.php
 * @author Looper
 * @date 2015年10月17日
 */
$serverIP = '10.51.66.66';
$passwod79 = $passwod80 = $passwod81 = $passwod82 = 'feichangyouyiyi1234';

//采集&认证&单天唯一客户端库
$redis = new Redis();
$redis->connect( $serverIP, 6379 );
$redis->auth( $passwod79 );
//单天客户端累计，算法md5( $t['gwid'] . "|" . $t['client_mac'] . "|" . $t['client_ip'] );
$daily_client_mac=126;
$daily_client_dbnum=127;
$daliy_gwid_auth_dbnum=100;
//需要单独统计日流的路由GWID
$unique_statistics_db = 110;
//在线客户端索引库
$redis80 = new Redis();
$redis80->connect( $serverIP, 6380 );
$redis80->auth( $passwod80 );

//在线客户端数据实体库
$redis81 = new Redis();
$redis81->connect( $serverIP, 6381 );
$redis81->auth( $passwod81 );

//终端APP天记录库
$redis82 = new Redis();
$redis82->connect( $serverIP, 6382 );
$redis82->auth( $passwod82 );

$queue_name = 'ik_client_queue';
//pop redis row for Thread
$group_size = 1000;
$for=10;
$slow_time = 5;

$S2 = new mysqli( "10.163.5.47", "script", 'ikuai8.com.@)!$', "ik_bm_stat" );
$S6 = new mysqli( "10.170.202.42", "yfding", 'yfding.@)!$mimayetaichangle', "ik_collection_backup" );
$S10 = new mysqli( "10.172.128.196", "script", "ikuai8.comAD!", "ik_ad" );
$S12 = new mysqli( "localhost", "root", "", "ik_collection_backup" );
$S15 = new mysqli( "10.46.183.117", "script", "ikuai8.comAD!", "ik_ad" );

include '/alidata1/script/run_mongo.php';
