<?php

//$e8r=new eth8relay("192.168.220.166",1234);

//echo $e8r->send("dump");
//echo $e8r->Relay_switch(1);
class Kincony_lib
{
    //L-включить
    //D-выключить
    //R- считать
    //DUMP
    //setr=000000X1 -выключить выходы 3-8,2 не менять,1-включить
    //getr -вернуть строку состояния
    //geti -вернуть строку состояния входов (работает некорректно выбрасывает нули)
    //I -считать вход
    //LA - включить все
    //DA -выключить все
    //P - сделать импульс длиnельностью 800 мс

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
//echo 'created socket'.$this->socket.'\n';
$this->connected=socket_connect($this->socket,$this->host,$this->port);
if (!$this->connected)echo 'Unable to connect';

}
function send($in)
{
    socket_send($this->socket,$in,strlen($in),MSG_EOF);
    return socket_read($this->socket,1024);
}
function activate()
{
    $buf="RELAY-TEST-NOW";
    $buf1=$this->send($buf);
    return true;
    
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
    echo $buf1=$this->send($buf);
    return $this->status_decode($buf1);
}

function Relay_off($num=1)
{
    $buf="RELAY-SET-0,".$num.",0";
    $buf1=$this->send($buf);
    return $this->status_decode($buf1,"off");
}
function get_outout()
{
    $buf="RELAY-STATE-0";
    $buf1=$this->send($buf);
    return $buf1;
}
function get_input()
{
    $buf="RELAY-GET_INPUT-0";
    $buf1=$this->send($buf);
    //if($buf1)$buf1=substr($buf1,2);
    return $buf1;
}

function get_data()
{
 	$out=array();
	$getr=$this->get_outout();
	//echo $getr;
 	list($str[0],$datas[3],$datas[2],$datas[1],$datas[0],$str[1])=explode(",",$getr);
	foreach($datas as $k => $data)
	{
		$data=$this->reverceBin($data);
	 	for($i=0;$i<strlen($data);$i++)
 		{
 	 	$out[$k*8+$i+1]=$data[$i];
 		}
	}
	$getr=$this->get_input();
 	list($str[0],$data,$str[1])=explode(",",$getr);
 	$str=$this->reverceBin(255-$data);
 	for($i=0;$i<strlen($str);$i++)
 	{
 	 $out[$i+101]=$str[$i];
 	}
 return $out;
 	
}

function reverceBin($str)
{
	return strrev(str_pad(decbin($str),8,0,STR_PAD_LEFT));
}
	



}
?>