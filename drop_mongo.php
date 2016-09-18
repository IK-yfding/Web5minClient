<?php
/**
 * mongo_mongo.php
 * @author Looper
 * @date 2016年1月22日
 */
$S8_mongo = new Mongo( "mongodb://10.171.60.79:27017" );

$need_to_drop = 'client_' . date( 'Y-m-d', time() - 86400 * 2 );

$res = $S8_mongo->ik_client->$need_to_drop->drop();

file_put_contents( '/alidata1/script/log/drop_mongo.log', '[' . date( 'Y-m-d H:i:s', time() ) . "]\n" . var_export( $res, true ) . "\n\n", FILE_APPEND );
