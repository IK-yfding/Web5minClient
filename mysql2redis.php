<?php
header( "Content-Type: text/html;charset=utf-8" );
include 'config.php';
$i = 0;
$limit = 100000;
$new_pipeline = true;
$result = $S6->query( "select 
gwid,
ap_mac,
client_mac,
client_ip,
client_agent,
total_down_flow,
total_up_flow,
up_speed,
down_speed,
authentication,
action_data,
timestamp,
connect_count,
client_hostname,
login_time,
online_time,
client_type,
router_time,
old_mac 
from ik_heart_client_".date("Ymd",strtotime("-1 day"))." where authentication>0 limit $limit" );
 
while ( $row = $result->fetch_array( MYSQLI_ASSOC ) ) {
	$i++;
	if ( $new_pipeline ) {
		$redis->pipeline();
		$new_pipeline = false;
	}
	$value = json_encode( $row );
	try {
		$redis->RPUSH( $queue_name, $value ); // 入列，最后插入的数据在表尾
		

		if ( $i % $limit == 0 ) {
			
			$redis->exec();
			$new_pipeline = true;
		}
	} catch (Exception $e) {
		echo $e->getMessage() . "\n";
	}
}

//$redis->set("tutorial-name", "Redis tutorial");
// 获取存储的数据并输出
//echo "Stored string in redis:: " , $redis->get("tutorial-name");
