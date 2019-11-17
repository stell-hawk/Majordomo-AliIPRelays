<?php

class Kincony_lib
{
    public $socket;
    public $host;
    public $port;
		public $connected=false;
function Kincony_lib($host,$port)
{
$this->host=$host;
$this->port=$port;
$this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if (!$this->socket)echo 'Unable to create socket';
socket_set_option($this->socket,SOL_SOCKET, SO_RCVTIMEO, array("sec"=>AliConnectTimeout, "usec"=>0));
//echo 'created socket'.$this->socket.'\n';
$this->connected=socket_connect($this->socket,$this->host,$this->port);
if (!$this->connected)echo 'Unable to connect to '.$this->host.":".$this->port;



}
function send($in)
{
    socket_send($this->socket,$in,strlen($in),MSG_EOF);
    return socket_read($this->socket,2048);
}
function activate()
{
    $buf="RELAY-TEST-NOW";
    $buf1=$this->send($buf);
    $buf="RELAY-SET-0,32,1";
    $buf1=$this->send($buf);
    return true;
    
}

function need_activate($getr)
{
	//print_r($getr);
	if(is_array($getr)&&$getr[32]==0)
	{
		echo "need activate\n";
		return true;
	}
	return false;	
}


function status_decode($buf1,$type="on")
{
	
    if($type=="on")
    {
    if (strstr($buf1,"1,OK"))return 1;
    elseif(strstr($buf1,"0,OK"))return 0;
    else return false;
    }
    else
    {
    if (strstr($buf1,"0,OK"))return 0;
    elseif(strstr($buf1,"1,OK"))return 1;
    else return false;
    }
}
function Relay_status($num=1)
{
    $buf="RELAY-READ-0,".$num;
    $buf1=$this->send($buf);
    return $this->status_decode($buf1);
}

function Relay_on($num=1)
{
    $buf="RELAY-SET-0,".$num.",1";
    $buf1=$this->send($buf);
    return $this->status_decode($buf1);
}

function Relay_off($num=1)
{
    $buf="RELAY-SET-0,".$num.",0";
    //$buf="";
    $buf1=$this->send($buf);
    return $this->status_decode($buf1,"off");
}
function get_outout()
{
    $buf="RELAY-STATE-0";
    $buf1=$this->send($buf);
    //echo "\n\nbuf output:".$buf1."\n";
    return $buf1;
}
function get_input()
{
    $buf="RELAY-GET_INPUT-0";
    $buf1=$this->send($buf);
    //echo "\n\nbuf input:".$buf1."\n";
    return $buf1;
}

function get_data()
{
 	$out=array();
 	
	$getr=$this->get_outout();
	//echo $getr."\n";
 	list($str[0],$datas[3],$datas[2],$datas[1],$datas[0],$str[1])=explode(",",$getr);
 	if($str[0]=="RELAY-STATE-0")
 	{foreach($datas as $k => $data)
	{
		$data=$this->reverceBin($data);
	 	for($i=0;$i<strlen($data);$i++)
 		{
 	 	$out[$k*8+$i+1]=$data[$i];
 		}
	}
	}
	$getr=$this->get_input();
	//echo $getr."\n";
 	list($str[0],$data,$str[1])=explode(",",$getr);
 	$str=$this->reverceBin(255-$data);
 	if($str[0]=="RELAY-GET_INPUT-0")
 	for($i=0;$i<strlen($str);$i++)
 	{
 	 $out[$i+101]=$str[$i];
 	}
	//if(isset($out[32]))unset $out[32];
 return $out;
 	
}

function reverceBin($str)
{
	return strrev(str_pad(decbin($str),8,0,STR_PAD_LEFT));
}
	



}
?>