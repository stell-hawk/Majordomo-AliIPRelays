<?php

//$e8r=new eth8relay("192.168.220.166",1234);

//echo $e8r->send("dump");
//echo $e8r->Relay_switch(1);
class eth8relay_lib
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
function eth8relay_lib($host,$port)
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

function Relay_switch($num=1)
{
$buf="R".$num;
$buf1=$this->send($buf);
if (strstr($buf1,"Relayon"))
{
    $buf="D".$num;
    $buf1=$this->send($buf);
}
elseif(strstr($buf1,"Relayoff"))
{
    $buf="L".$num;
    $buf1=$this->send($buf);

}
else $buf1=false;
return $buf1;
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
    if (strstr($buf1,"Relayon"))return 1;
    elseif(strstr($buf1,"Relayoff"))return 0;
    else return false;
    }
    else
    {
    if (strstr($buf1,"Relayon"))return 0;
    elseif(strstr($buf1,"Relayoff"))return 1;
    else return false;
    }
}
function Relay_status($num=1)
{
    $buf="R".$num;
    $buf1=$this->send($buf);
    return $this->status_decode($buf1);
}

function Relay_on($num=1)
{
    $buf="L".$num;
    $buf1=$this->send($buf);
    return $this->status_decode($buf1);
}

function Relay_off($num=1)
{
    $buf="D".$num;
    $buf1=$this->send($buf);
    return $this->status_decode($buf1,"off");
}
function getr()
{
    $buf="getr";
    $buf1=$this->send($buf);
    if($buf1)$buf1=substr($buf1,6);
    return $buf1;
}
function dump()
{
    $buf="dump";
    $buf1=$this->send($buf);
    if($buf1)$buf1=substr($buf1,2);
    return $buf1;
}

function get_data()
{
	$getr=$this->dump();
 	//echo "get values\n".$getr."\n";
 	$getr=explode("\n",$getr);
 	$out=array();
 	foreach($getr as $data)
 	{
 		list($data,$num)=explode(" ",$data);
 		
 	if(strstr($data,'relayon'))$out[($num+0)]=1;
 	elseif(strstr($data,'relayoff')){$out[($num+0)]="0";}
 	elseif(strstr($data,'IL'))$out[($num+100)]="0";
 	elseif(strstr($data,'IH'))$out[($num+100)]=1;
}
	//print_r($out);
 	return $out;
}


}
?>