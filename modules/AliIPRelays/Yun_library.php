<?php

//$e8r=new eth8relay("192.168.220.166",1234);

//echo $e8r->send("dump");
//echo $e8r->Relay_switch(1);
class Yun_lib
{


    public $host;
    public $port;
    public $connected=true;

function Yun_lib($host,$port)
{
$this->host=$host;
$this->port=$port;
}


function activate()
{
return true;	
}
function need_activate()
{
return false;	
}

	

function Relay_on($pin='1')
{
	$mode="digital";
	$command=1;
	list($trash,$pin)=explode("(",$pin);
	$service_url = 'http://'.$this->host.'/arduino/'.$mode.'/' .(int)$pin.'/' . $command;		
	$buf1=$this->send_by_http($service_url);
  if($buf1)return true;
    else return false;
}

function Relay_off($pin='1')
{
	$mode="digital";
	$command=0;
	list($trash,$pin)=explode("(",$pin);
	$service_url = 'http://'.$this->host.'/arduino/'.$mode.'/' .(int)$pin.'/' . $command;		
	$buf1=$this->send_by_http($service_url);
  if($buf1)return true;
    else return false;
}

function send_by_http($service_url)
{
		//echo $service_url;
		$curl = curl_init($service_url);
  	// Send cURL to Yun board
		curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
  	curl_setopt($curl, CURLOPT_USERPWD, "root:dragino");
  	$curl_response = curl_exec($curl);
  	curl_close($curl);
  	return $curl_response;
	
}

function get_data()
{
		$curl_response=$this->send_by_http('http://'.$this->host.'/data/get');
		$all = json_decode($curl_response,TRUE);
		
		$all=$all["value"];
		$inPorts=explode(',',$all["inPorts"]);
		$outPorts=explode(',',$all["outPorts"]);
		//var_dump($outPorts);
		if(count($outPorts)>1)foreach ($outPorts as $i => $v)
		{
			$out[($i+1)."(".$v.")"]=$all['D'.$v];
		}
		if(count($inPorts)>1)foreach ($inPorts as $i => $v)
		{
			$out[($i+101)."(".$v.")"]=$all['D'.$v];
		}
		
		//var_dump($out);
		return $out;
}

}
?>