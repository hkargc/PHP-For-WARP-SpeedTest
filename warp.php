<?php
/**
 * 
 */
if(php_sapi_name() !== 'cli'){
	die("Run in CLI mode.\n");
}
if(class_exists('Swoole\Coroutine\Scheduler') == false){
	die("Need Swoole Extension: https://pecl.php.net/package/swoole\n");
}

include(__DIR__ . '/vendor/autoload.php');

/**
 * 官方指定的IP及PORT在:
 * https://developers.cloudflare.com/cloudflare-one/connections/connect-devices/warp/deployment/firewall/
 */
$ipv4_cidrs = array(
	'162.159.193.0/24'
);
$ports = [500, 1701, 2408, 4500];

$list = array();
foreach($ipv4_cidrs as $cidr){
	list($ipAddress, $networkSize) = explode('/', $cidr);

	$sub = new IPv4\SubnetCalculator($ipAddress, $networkSize);
	[$startIp, $endIp] = $sub->getAddressableHostRange();

	$startIp = ip2long($startIp);
	$endIp = ip2long($endIp);

	for($i = 1, $j = 1; ($i <= 200) && ($j <= 1000); $j++){ //每个网段随机取200个
		$ip = rand($startIp, $endIp);
		if(isset($list[$ip])){ //rand结果可能重复,$j规避死循环
			continue;
		}
		$i += 1;
		$list[$ip] = long2ip($ip);
	}
}
$list = array_values($list);

//$list = ['162.159.193.193'];

/**
 * 用Wireshark抓warp.exe第一个数据包得到的
 */
$data = "048792cd9d8631f11f226c0df5225e23979601980529b028988f00a99bdb073737000000000000000000000000baba1a1346e1b2fe7cd524fa23163746";
$data = hex2bin($data);

$cidrs = array();
$scheduler = new Swoole\Coroutine\Scheduler();
foreach($list as $ip){ //利用swoole提供的协程使之"并行"执行
	
	$port = $ports[rand(0, count($ports) - 1)];
	
	$scheduler->add(function($ip, $port, $data) use(&$cidrs){
		$client = new Swoole\Coroutine\Client(SWOOLE_SOCK_UDP);
		$client->set(array(
			'timeout' => 1.0, //总超时，包括连接、发送、接收所有超时
			'connect_timeout' => 0.5, //连接超时，会覆盖第一个总的 timeout
			'write_timeout' => 0.5, //发送超时，会覆盖第一个总的 timeout
			'read_timeout' => 0.5 //接收超时，会覆盖第一个总的 timeout
		));

		$a = array(
			"dst" => "{$ip}:{$port}",
			"loss" => 100,
			"delay" => 1000
		);

		if($client->connect($ip, $port)){

			$ttl = 0; //收发包时间
			$cnt = 0; //收到包数量

			for($i = 0; $i < 10; $i++){ //收发10个包
				$start = hrtime(true); //PHP内置高精度时间(纳秒)
				if($client->send($data)){
					
				}
				if($client->recv()){
					$cnt += 1;
					$ttl += (hrtime(true) - $start);
				}
			}

			$client->close();

			$a["loss"] = (10 - $cnt) / 10 * 100; //丢包率
			$a["delay"] = $cnt ? ($ttl / $cnt / 1000000) : 1000; //收发包平均耗时
		}
		$cidrs[$a['dst']] = $a;
	}, $ip, $port, $data);
}
$scheduler->start();

$list = array_values($cidrs);
foreach($list as $key => $row){ //丢包率升序,延迟升序
	$loss[$key] = $row['loss'];
	$delay[$key] = $row['delay'];
}
array_multisort($loss, SORT_ASC, $delay, SORT_ASC, $list);
foreach($list as $a){
	if($a["loss"] >= 100){ //连不上
		continue;
	}
	if($a["delay"] >= 1000){ //收发包超过1秒
		continue;
	}
	echo implode("\t", $a)."\n";
}

$a = array_shift($list);

$s = "@echo off\n";
$s .= "\"C:\Program Files\Cloudflare\Cloudflare WARP\warp-cli\" tunnel endpoint reset\n";
$s .= "\"C:\Program Files\Cloudflare\Cloudflare WARP\warp-cli\" tunnel endpoint set {$a['dst']}\n";
file_put_contents(__DIR__ . '/warp.bat', $s);