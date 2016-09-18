<?php
/**
 * run_mongo.php
 * @author Looper
 * @date 2016年1月13日
 */
$S8_mongo = new Mongo( "mongodb://10.171.60.79:27017" );

$today = 'client_' . date( 'Y-m-d' );

$S8_mongo_connection = $S8_mongo->ik_client->$today;

if ( !file_exists( '/alidata1/script/log/' . $today ) ) {
	
	exec("rm /alidata1/script/log/client_* -rf");
	file_put_contents( '/alidata1/script/log/' . $today, date( 'Y-m-d H:i:s' ) );
	
	$S8_mongo_connection->ensureIndex( array (
			'gwid' => 1) );
	/*$S8_mongo_connection->ensureIndex( array (
			'mac' => 1) );
	$S8_mongo_connection->ensureIndex( array (
			'timestamp' => 1) );*/
}
