<?php
header( "Content-Type: text/html;charset=utf-8" );
include 'config.php';

for ( $index = 0; $index < $for; $index++ ) {
	
	$start = microtime( true );
	
	$batch = $group_size;
	
	//redis加载到php数组变量中，清除redis弹出数据
	$pop = $redis->LRANGE( $queue_name, 0, $batch - 1 );
	$redis->LTRIM( $queue_name, $batch, -1 );
	
	$phase_1 = microtime( true );
	
	//echo "取出", $batch, "条用时：", $phase_1 - $start, "|";
	

	$auth_type = $unique_client = $unique_client_mac = $del_key = array ();
	
	$db_online_client_key = $db_online_client_list = array ();
	
	$p80_gwid = $redis80->keys( '*' );
	$p80_timestamp = $redis80->mget( $redis80->keys( '*' ) );
	$i = $r = $c = 0;
	foreach ( $p80_gwid as $v ) {
		$p80[$v] = $p80_timestamp[$i];
		$i++;
	}
	
	$statistics = array ();
	
	foreach ( $pop as $k => $v ) {
		$r++;
		$package = json_decode( $v, TRUE );
		$S8_mongo_connection->batchInsert($package);
		
		foreach ( $package as $t ) {
			$c++;
			/*
			 统计单天唯一客户端prot:6379 db:127
			 mac是40:00开始的是二级路由
			 */
			if(substr( $t['mac'], 0, 5 ) === '40:00'){$t['mac']='00:00:00:00:00:00';}
			$t["_key"] = md5( $t['gwid'] . "|" . $t['mac'] . "|" . $t['ip'] );

			//统计单天唯一客户端mac
			$unique_client_mac[]=$t['mac'];
			
			//if ( in_array( $t['gwid'], $statistics_gwid ) ) {
			//	$statistics[$t['gwid']][] = $t["_key"];
			//}
			
			//$temp = json_decode($t['apptop'],true);
			//$temp['timestamp'] = $t['timestamp'];
			//$app[$t["_key"]][]=$temp;
			/*
			 统计认证数据
			 1用户密码2优惠券3固定密码4手机5腾讯QQ6微博7无密码8微信9支付宝10手机管家11免费试用
			 以认证方式数字为redis库名,一种认证方式存一个库
			 */
			if ( $t['auth_type'] > 0 && $t['auth_type'] < 15 ) {
				$auth_type[$t['auth_type']][] = $t["_key"];
				$daliy_gwid_auth[] = $t['gwid'] . ',' . $t['auth_type'] . ',' . $t['mac'] . ',' . $t['ip'];
			}
			
			//汇总单天客户端数
			$unique_client[] = $t["_key"];
			//单路由单天客端数
			$statistics[$t['gwid']][] = $t["_key"];
			
			/*
			 维护在线客户端表
			 如memcache5分钟没copy过数据则5号调用S12往S2的mysql<online_client表写>，最终用户在S2号库查询数据
			 */
			$gwid = $t['gwid'];
			$router_time = $t['timestamp'];
			$row_data = json_encode( $t );
			
			if ( isset( $p80[$gwid] ) ) {
				//db_online_client_key索引库中发现gwid数据,取出更新时间判断
				if ( intval( $p80[$gwid] ) < $router_time ) {
					//如果发现新一波数据,更新db_online_client_key索引库
					$old_key = $gwid . $p80[$gwid];
					$db_online_client_key[$gwid] = $router_time;
					
					//处理在线客户端数据db_online_client_list库
					$del_key[] = $old_key;
					$db_online_client_list[$gwid . $router_time][] = $row_data;
				} else if ( intval( $p80[$gwid] ) == $router_time ) {
					$db_online_client_list[$gwid . $router_time][] = $row_data;
				}
			} else {
				//db_online_client_key索引库中没发现gwid数据
				$db_online_client_key[$gwid] = $router_time;
				//插入在线客户端数据db_online_client_list库
				$new_key = $gwid . $router_time;
				$db_online_client_list[$new_key][] = $row_data;
			}
		}
	}
	
	$redis->select( $unique_statistics_db );
	
	$redis->pipeline();
	foreach ( $statistics as $k => $v ) {
		foreach ( $v as $value ) {
			$redis->SADD( $k, $value );
		}
	}
	$redis->exec();
	
	/*$redis82->pipeline();
	 foreach ( $app as $k => $v ) {
	 $redis82->RPUSH( $k, json_encode($v));
	 }
	 $redis82->exec();
	 */
	
	$redis80->pipeline();
	foreach ( $db_online_client_key as $k => $v ) {
		$redis80->set( $k, $v );
	}
	$redis80->exec();
	
	$redis81->pipeline();
	foreach ( $db_online_client_list as $k => $v ) {
		
		foreach ( $v as $value ) {
			$redis81->RPUSH( $k, $value );
		}
	}
	$redis81->exec();
	
	$redis81->pipeline();
	foreach ( $del_key as $v ) {
		$redis81->delete( $v );
	}
	$redis81->exec();
	
	foreach ( $auth_type as $db_number => $arr ) {
		$redis->select( $db_number );
		$redis->pipeline();
		foreach ( $arr as $key ) {
			$redis->set( $key, "1" );
		}
		$redis->exec();
	}

	//单天GWID,auth,mac,ip
	$redis->select( $daliy_gwid_auth_dbnum );
	$redis->pipeline();
	foreach ( $daliy_gwid_auth as $key ) {
		$redis->set( $key, "1" );
	}
	$redis->exec();

	
	
	$redis->select( $daily_client_dbnum );
	$redis->pipeline();
	foreach ( $unique_client as $key ) {
		$redis->set( $key, "1" );
	}
	$redis->exec();

	//统计单天唯一客户端mac
	$redis->select( $daily_client_mac );
        $redis->pipeline();
        foreach ( $unique_client_mac as $key ) {
                $redis->set( $key, "1" );
        }
        $redis->exec();
	

	//////////////
	//reset to DB0
	$redis->select( 0 );
	
	$run_time = microtime( true ) - $phase_1;
	if ( $run_time > $slow_time ) {
		echo "[ " . date( "Y-m-d H:i:s" ) . " ]处理router数据{$r}条,client数据{$c}条,迭代用时：", microtime( true ) - $phase_1, "\n\n";
	}
}
