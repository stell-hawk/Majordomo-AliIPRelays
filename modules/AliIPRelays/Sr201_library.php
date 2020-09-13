<?php

class sr201_lib
{
/*
обрый день.
Да порт 6722
ДА:
если послать команду
XY,
где Y -это номер выхода
а X -это либо 2-выключить , либо 1-- включить.
В ответ приходит:
00000000 или 01000000 - если включено второе реле реле
00 - опрос состояния всех реле , приходит также 00000000 или 01000000 - если включено второе реле
*/

    public $socket;
    public $host;
    public $port;
    public $connected=false;
    
function sr201_lib($host,$port)
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


function status_decode($buf1,$type="on")
{
    if($type=="on")
    {
    if ($buf1=="1")return 1;
    elseif($buf1=="0")return 0;
    else return false;
    }
    else
    {
    if ($buf1=="0")return 0;
    elseif($buf1=="1")return 1;
    else return false;
    }
}

function Relay_status($num=1)
{
    $buf="00";
    $buf1=$this->send($buf);
    $buf1=$this->decode_status_by_number($buf1,$num);
    return $this->status_decode($buf1);
}

function Relay_on($num=1,$time=false)
{
    $buf="1".$num;
    if($time)$buf.=":".$time;

    $buf1=$this->send($buf);
    $buf1=$this->decode_status_by_number($buf1,$num);
    return $this->status_decode($buf1);
}

function Relay_off($num=1,$time=false)
{
    $buf="2".$num;
    if($time)$buf.=":".$time;

    $buf1=$this->send($buf);
    $buf1=$this->decode_status_by_number($buf1,$num);
    return $this->status_decode($buf1,"off");
}
function get_outout()
{
    $buf="00";
    $buf1=$this->send($buf);
    return $buf1;
}

function get_data()
{
 	$out=array();
	$getr=$this->get_outout();
	//echo $getr;
	$out1=str_split($getr);
	for($i=0;$i<count($out1);$i++)$out[$i+1]=$out1[$i];
 return $out;
}

function decode_status_by_number($buf1,$num=1)
{
	$out1=str_split($buf1);
	for($i=0;$i<count($out1);$i++)$out[$i+1]=$out1[$i];
	//var_dump($out[$num]);
	return $out[$num];
}


	



}
?>