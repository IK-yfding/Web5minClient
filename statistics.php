<?php
include_once 'config.php';
//每天23:59:45跑一次统计昨天的数据
//$run_time = date('Ymd',strtotime("-1 day",time()));
$today = date( 'Ymd' );

//单天认证次数
$people = 0;
$info = $redis->info();
$auth_arr = array (
		1 => '用户密码', 
		2 => '优惠券', 
		3 => '固定密码', 
		4 => '手机', 
		5 => '腾讯QQ', 
		6 => '微博', 
		7 => '无密码', 
		8 => '微信', 
		9 => '支付宝', 
		10 => '手机管家', 
		11 => '免费试用', 
		12 => '微信连WiFi', 
		13 => 'Radius对接', 
		$daily_client_mac => '唯一mac',
		$daily_client_dbnum => '单天客流');
for ( $i = 1; $i <= $daily_client_dbnum; $i++ ) {
	if ( isset( $info['db' . $i] ) ) {
		preg_match( '/keys=(\d+),/i', $info['db' . $i], $out );
		$auth[$auth_arr[$i]] = $out[1];
		if ( $i < 14 ) {
			//认证人数
			$people += $out[1];
		}
	}
}
arsort( $auth );
echo "[" . date( "Y-m-d H:i:s" ) . "] 开始天统计\n";
print_r( $auth );
//单天唯一客户端
$client_count = $auth["单天客流"];
unset( $auth["单天客流"] );

//'认证类型':'认证次数'  JSON字符串
$data = addslashes( json_encode( $auth, true ) );
/*
 //认证次数
 $times=$S6->query("select sum(a) s from (select max(auth) a from ik_heart_router_".$run_time." group by gwid) x");
 $times=$times->fetch_array(MYSQLI_ASSOC);
 $times=$times['s'];
 
 //今日认证开启数
 $auth_status=$S6->query("select count(*) c from (select auth_status from ik_heart_router_".$run_time." where auth_status=1 group by gwid) x");
 $auth_status=$auth_status->fetch_array(MYSQLI_ASSOC);
 $auth_status=$auth_status['c'];
 
 //今日AP使用场景数
 $router_aps=$S6->query("select count(*) c from (SELECT gwid FROM `ik_heart_ap_".$run_time."` group by gwid) x");
 $router_aps=$router_aps->fetch_array(MYSQLI_ASSOC);
 $router_aps = $router_aps['c'];
 */
//$S12->query("update ik_admin_auth set people={$people},data='{$data}',client_count={$client_count} where date='{$today}'");
$S2->query( "update ik_admin_auth set people={$people},data='{$data}',client_count={$client_count} where date='{$today}'" );

exec('/usr/local/php/bin/php /alidata1/script/unique_statistics.php' );
exec('/usr/local/php/bin/php /alidata1/script/unique_mac.php' );

//统计完成清理redis库
exec('/usr/local/php/bin/php /alidata1/script/flushAllDB.php' );
