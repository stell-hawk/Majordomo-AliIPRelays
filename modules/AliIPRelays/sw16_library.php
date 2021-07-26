<?php

class sw16_lib
{
/*
https://github.com/rambkk/HLK-SW16
*/

    public $socket;
    public $host;
    public $port;
    public $connected=false;
    
function sw16_lib($host,$port)
{
$this->host=$host;
$this->port=$port;
$this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if (!$this->socket)echo 'Unable to create socket';
socket_set_option($this->socket,SOL_SOCKET, SO_RCVTIMEO, array("sec"=>AliConnectTimeout, "usec"=>0));
//echo 'created socket'.$this->socket.'\n';
$this->connected=@socket_connect($this->socket,$this->host,$this->port);
if (!$this->connected)echo 'Unable to connect to '.$this->host.":".$this->port;

}
function send($in)
{
    socket_send($this->socket,$in,strlen($in),NULL);
    usleep(500);
    return socket_read($this->socket,1024);
}
function activate()
{
    return true;
    
}
function need_activate()
{
return false;	
}


function Relay_status($num=1)
{
    $buf1=$this->get_data();
    return $buf1[$num];
}

function Relay_on($num=1,$time=false)
{
		/*case "$1" in
		0) SWITCHID="\x00" ;;
		15) SWITCHID="\x0f" ;;
		all)    if [ "$2" == "on" ]; then
		                SWITCHID="\x01"
		                PREFIX2="\x0a"
		        fi
		        if [ "$2" == "off" ]; then
		                SWITCHID="\x02"
		                PREFIX2="\x0b"
		        fi
		*/
		$PREFIX="\xaa";
		$PREFIX2="\x0f";
		$SUFFIX="\x01\x01\x01\x01\x01\x01\x01\x01\x01\x01\x01\x01\x01\x01\x01\xbb";
		$SWITCHID=chr($num-1);
		$SWITCHTO="\x01";//01 -on 02-off

    $buf=$PREFIX.$PREFIX2.$SWITCHID.$SWITCHTO.$SUFFIX;
    $buf1=bin2hex($this->send($buf));
		//echo $buf1."\n";	
    $buf1=$this->decode_status_by_number($buf1,$num);
    return $buf1;	
}

function Relay_off($num=1,$time=false)
{
		$PREFIX="\xaa";
		$PREFIX2="\x0f";
		$SUFFIX="\x01\x01\x01\x01\x01\x01\x01\x01\x01\x01\x01\x01\x01\x01\x01\xbb";
		$SWITCHID=chr($num-1);
		$SWITCHTO="\x02";//01 -on 02-off

    $buf=$PREFIX.$PREFIX2.$SWITCHID.$SWITCHTO.$SUFFIX;
    $buf1=bin2hex($this->send($buf));
    $buf1=$this->decode_status_by_number($buf1,$num);
    return $buf1;	
}
function get_outout()
{
		$PREFIX="\xaa";
		$PREFIX2="\x1e";
		$SWITCHID="\x01";
		$SWITCHTO="\x01";
		$SUFFIX="\x01\x01\x01\x01\x01\x01\x01\x01\x01\x01\x01\x01\x01\x01\x01\xbb";
    $buf=$PREFIX.$PREFIX2.$SWITCHID.$SWITCHTO.$SUFFIX;
    $return=bin2hex($this->send($buf));
    //echo $return."\n";
		if(strlen($return)==40)
		{
		$out=substr($return,4,32);
    return $out;
  	}
		else {
			echo "return error:[$return]\n";
			return false;
		}
}

function get_data()
{
 	$out=array();
	$getr=$this->get_outout();
	$out1=str_split($getr,2);
	for($i=0;$i<count($out1);$i++)
	{ if((int)$out1[$i]==1)$out[$i+1]=1;
		else $out[$i+1]=0;
	}
 	return $out;
}

function decode_status_by_number($buf1,$num=1)
{
	if(strlen($buf1)==40)
	{
	$out1=substr($buf1,4,32);
 	}
 	else
 	{
 		echo "return error:[$return]\n";
		return false;
 	}
	
	$out1=str_split($out1,2);
	for($i=0;$i<count($out1);$i++)
	{ if((int)$out1[$i]==1)$out[$i+1]=1;
		else $out[$i+1]=0;
	}
	return $out[$num];
}

}
/*
$e8r=new sw16_lib("192.168.220.26",8080);
print_r($e8r->get_data());
//for($i=1;$i<17;$i++)
$e8r->Relay_on(1);
print_r($e8r->get_data());
$e8r->Relay_off(1);
print_r($e8r->get_data());
*/
?>