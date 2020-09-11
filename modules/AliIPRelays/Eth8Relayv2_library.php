<?php

//$e8r=new eth8relay("192.168.220.166",1234);

//echo $e8r->send("dump");
//echo $e8r->Relay_switch(1);
class Eth8Relayv2_lib
{


    public $socket;
    public $host;
    public $port;
		public $connected=false;
function Eth8Relayv2_lib($host,$port)
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

	

function Relay_on($num=1)
{
    $buf="setr=xxxxxxxx";
    $buf[$num+4]="1";
    echo $buf."\n";
    $buf1=$this->send($buf);
    if($buf1)return true;
    else return false;
}

function Relay_off($num=1)
{
    $buf="setr=xxxxxxxx";
    $buf[$num+4]="0";
    echo $buf."\n";
    $buf1=$this->send($buf);
    if($buf1)return true;
    else return false;
}

function dump()
{
    $buf="state=?";
    $buf1=$this->send($buf);
    return json_decode($buf1);
}

function get_data()
{
	$out=array();
	$getr=$this->dump();
	if($getr->output)
	{
		$data=str_split($getr->output);
		for($i=0;$i<count($data);$i++)
		{
			$out[$i+1]=$data[$i];
		}
	}
	if($getr->input)
	{
		$data=str_split($getr->input);
		for($i=0;$i<count($data);$i++)
		{
			$out[$i+101]=$data[$i];
		}
	}

 	return $out;
}


}
?>