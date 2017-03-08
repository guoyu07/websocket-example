<?php
$server = new swoole_websocket_server("0.0.0.0", 9501);

$server->on('open', function (swoole_websocket_server $server, $request) {
    echo "server: handshake success with fd{$request->fd}\n";
    $list = $server->connection_list();
    $data = array(
    	'total' => count($list),
    	'message' =>array(
    		"info" => "用户{$request->fd}进入房间",
    		"speed" => 8
    	)
    );
    foreach ($list as $client) {
    	$server->push($client, json_encode($data));
    }
});

$server->on('message', function (swoole_websocket_server $server, $frame) {
    echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
    $list = $server->connection_list();
    $data = array(
    	'total' => count($list),
    	'message' => json_decode($frame->data)
    );
    foreach ($list as $client) {
    	if($client != $frame->fd) {
    		$server->push($client, json_encode($data));
    	} 
    }
});

$server->on('close', function ($ser, $fd) {
	$list = $ser->connection_list();
    $data = array(
    	'total' => count($list),
    	'message' =>array(
    		"info" => "用户{$fd}离开房间",
    		"speed" => 8
    	)
    );
    foreach ($list as $client) {
    	if($client != $fd) {
    		$ser->push($client, json_encode($data));
    	}
    }
    echo "client {$fd} closed\n";
});

$server->start();
