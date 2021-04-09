<?php

DEFINE("AliConnectTimeout",1);
include_once('../Yun_library.php');
$e8r=new Yun_lib("192.168.220.45",80);

echo "!test get_data:\n";
print_r( $e8r->get_data());
echo "!test Relay_on 7:\n";
var_dump($e8r->Relay_on('21(30)'));


exit(0);
$str=hex2bin("018cb4412f02fa");
$echo=$e8r->send($str);

var_dump(bin2hex($echo));

//echo "!test Relay_on 1 ->0:\n";
//var_dump($e8r->Relay_on(1));

//echo "!test Relay_on 2 ->1:\n";
//print_r( $e8r->Relay_on(2));
//echo "!test Relay_off 2 ->0:\n";
//print_r( $e8r->Relay_off(1));

//echo "!test Relay_off 1 ->1:\n";
//print_r( $e8r->Relay_off(1));


?>