<?php
/**
 * unique_statistics.php
 * @author Looper
 * @date 2015年12月11日
 */
require 'config.php';

/*
$daily_client_mac=126;
$daily_client_dbnum=127;
*/
$redis->select( $daily_client_mac );

$s=microtime(true);
$arrAllKeys = $redis->keys( '*' );
echo microtime(true)-$s."\n";
$str = '';
$file = fopen('/alidata1/script/log/unique_client_mac/unique_client_mac_'.date("Y_m_d").'.log',"a");
foreach ( $arrAllKeys as $key ) {
	fwrite($file,$key."\n");
}
fclose($file);
echo microtime(true)-$s;
