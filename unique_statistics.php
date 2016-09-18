<?php
/**
 * unique_statistics.php
 * @author Looper
 * @date 2015年12月11日
 */
$now = "[" . date( 'Y-m-d H:i:s' ) . "]";
file_put_contents( '/alidata1/script/log/unique_statistics.log', $now . "开始unique_statistics.php\n", FILE_APPEND );

require 'config.php';

$redis->select( $unique_statistics_db );

$arrAllKeys = $redis->keys( '*' );

$arrResult = array ();

foreach ( $arrAllKeys as $key ) {
	
	$arrResult[$key] = $redis->scard( $key );
}

arsort( $arrResult );

$result = '';
foreach ( $arrResult as $k => $v ) {
	
	$result .= $k . "\t" .  $v . "\n";
}


//单天路由认证详情
$redis->select( $daliy_gwid_auth_dbnum );
$all_gwid_auth=$redis->keys( '*' );
foreach ( $all_gwid_auth as $v ) {
        $_a = explode(',',$v);
        @$auth_detail[$_a[0]][$_a[1]] ++;
}

//取出路由与用户的对应关系
$user_routers=array();
$rs=$S2->query("select user_id,gwid from ik_user_router");
while ( $r = $rs->fetch_array( MYSQLI_ASSOC ) ) {
        $user_routers[$r['gwid']]=$r['user_id'];
}

$tb_name="ik_daily_client_".date("Ymd");
$TryTableSql="CREATE TABLE IF NOT EXISTS {$tb_name} (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `gwid` varchar(32) NOT NULL DEFAULT '',
  `dailyClient` int(10) NOT NULL DEFAULT '0',
  `timestamp` int(10) NOT NULL DEFAULT '0',
  `k` varchar(32) NOT NULL DEFAULT '',
  `user_id` int(10) NOT NULL DEFAULT '0',
  `auth` varchar(256) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `k` (`k`) USING BTREE,
  KEY `k_gwid` (`gwid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
//$S10->query($TryTableSql);
$S15->query($TryTableSql);
$Hsql = "replace into {$tb_name}(gwid,dailyClient,timestamp,k,user_id,auth) values ";
$Bsql = "";
foreach( $arrResult as $k=>$v ){
	if($Bsql){$Bsql .= ",";}
	$user_id=@$user_routers[$k]?$user_routers[$k]:0;
	if(isset($auth_detail[$k])){
		foreach($auth_detail[$k] as $x){@$auth_detail[$k]['total'] += $x;}
		$auth=json_encode($auth_detail[$k]);
	}else{
		$auth='';
	}
	$Bsql.="('{$k}','{$v}',".time().",'".md5($k.date('Y-m-d'))."',$user_id,'$auth')";
        }

//$S10->query($Hsql.$Bsql);
$S15->query($Hsql.$Bsql);
//file_put_contents( '/alidata1/script/1111111111', $Hsql.$Bsql, FILE_APPEND );

$now = "[" . date( 'Y-m-d H:i:s' ) . "]";
file_put_contents( '/alidata1/script/log/unique_statistics.log', $now . "结束unique_statistics.php\n=========================\n", FILE_APPEND );
