<?php

$now = "[" . date( 'Y-m-d H:i:s' ) . "]";
file_put_contents( '/alidata1/script/log/flushDB.log', $now . "开始清理\n", FILE_APPEND );

include_once 'config.php';
//清理6379<1~127>库
for($i=1;$i<=$daily_client_dbnum;$i++){
	$redis->select($i);
	$redis->FLUSHDB();
}
//清理6380<0>库，在线客户端索引库
$redis80->FLUSHALL();
//清理6381<0>库，在线客户端详情库
$redis81->FLUSHALL();
//清理6382<0>库，在线客户端APPTOP10详情库
$redis82->FLUSHALL();
echo "[".date("Y-m-d H:i:s")."] 清理完成\n";

$now = "[" . date( 'Y-m-d H:i:s' ) . "]";
file_put_contents( '/alidata1/script/log/redis_statistics.log', $now . "清理完成\n=========================\n", FILE_APPEND );
