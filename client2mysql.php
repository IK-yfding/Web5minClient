<?php
include_once 'config.php';

if ( isset( $argv ) ) {
	$gwid = $argv[1];
} else {
	$gwid = $_REQUEST['gwid'];
}
if ( !$gwid ) {
	die( '缺少参数gwid' );
}

$key = $gwid . $redis80->get( $gwid );

$S2->query( "delete from ik_online_client where gwid='{$gwid}'" );
$S2->query("delete FROM `ik_online_client` where `timestamp`<UNIX_TIMESTAMP()-900");
$hsql = "insert into ik_online_client(gwid,ap_mac,client_host,client_ip,client_mac,up_speed,down_speed,login_time,online_time,timestamp,authentication,router_time,total_down_flow,total_up_flow) values ";
$bsql = "";

$data = $redis81->lrange( $key, 0, -1 );
foreach ( $data as $v ) {
	$r = json_decode( $v, true );
	$client_name = mysql_escape_string( $r['hostname'] );
	$bsql .= "('{$r['gwid']}','{$r['apmac']}','{$client_name}','{$r['ip']}','{$r['mac']}','{$r['upload']}','{$r['download']}','{$r['logtime']}','{$r['online_time']}','{$r['timestamp']}','{$r['auth_type']}','{$r['timestamp']}','{$r['total_down']}','{$r['total_up']}'),";
	$client_name = "";
}
$S2->query( $hsql . rtrim( $bsql, "," ) );
echo "Success!";
