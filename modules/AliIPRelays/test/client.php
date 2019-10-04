<?php
include_once('../Sr201_library.php');
$e8r=new sr201_lib("178.16.182.181",6722);

echo "!test get_data:\n";
print_r( $e8r->get_data());


//echo "!test Relay_on 1 ->0:\n";
//var_dump($e8r->Relay_on(1));

//echo "!test Relay_on 2 ->1:\n";
//print_r( $e8r->Relay_on(2));
//echo "!test Relay_off 2 ->0:\n";
//print_r( $e8r->Relay_off(1));

//echo "!test Relay_off 1 ->1:\n";
//print_r( $e8r->Relay_off(1));


?>