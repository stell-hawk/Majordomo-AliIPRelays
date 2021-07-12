<?php
chdir(dirname(__FILE__) . '/../');
include_once("./config.php");
include_once("./lib/loader.php");
include_once("./lib/threads.php");
set_time_limit(0);
// connecting to database
$db = new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME);
include_once("./load_settings.php");
include_once(DIR_MODULES . "control_modules/control_modules.class.php");
$ctl = new control_modules();
include_once(DIR_MODULES . 'AliIPRelays/AliIPRelays.class.php');

$AliIPRelays_module = new AliIPRelays();
$AliIPRelays_module->getConfig();

$tmp = SQLSelectOne("SELECT ID FROM AliIPRelays where period>0 LIMIT 1");
if (!$tmp['ID'])
   exit; // no devices added -- no need to run this cycle
echo date("H:i:s") . " running " . basename(__FILE__) . PHP_EOL;


function reload_timer_config($timers=array())
{
	GLOBAL $AliIPRelays_module;
	if(!isset($timers['cycle_check']['last']))$timers['cycle_check']['last']=0;
	if(!isset($timers['cycle_check']['config']))$timers['cycle_check']['config']=25;//25 секунд достаточно длч XRAY
	if(!isset($timers['config_reload']['last']))$timers['config_reload']['last']=0;
	if(!isset($timers['config_reload']['config']))$timers['config_reload']['config']=10*60;//10 минут
	if(!isset($timers['next_check']))$timers['next_check']=0;//10 минут
	if(!isset($timers['max_check_period']))$timers['max_check_period']=0;
	
	$timers['devices']=array();

	$tmp=$AliIPRelays_module->getData("Ali:AliIPRelays|period>0","SELECT ID,IP,PORT,TYPE,PERIOD FROM AliIPRelays where period>0");
	global $lib;

	foreach($tmp as $relay)
	{
		$timers['devices'][$relay['ID']]['period']=$relay['PERIOD'];
		$timers['devices'][$relay['ID']]['last']=0;
		//$timers['devices'][$relay['ID']]['next']=$timers['devices'][$relay['ID']]['last']+$timers['devices'][$relay['ID']]['period'];
		$timers['devices'][$relay['ID']]['IP']=$relay['IP'];
		$timers['devices'][$relay['ID']]['ID']=$relay['ID'];
		$timers['devices'][$relay['ID']]['PORT']=$relay['PORT'];
		$timers['devices'][$relay['ID']]['TYPE']=$relay['TYPE'];
		$timers['devices'][$relay['ID']]['CLASS']=$lib[$relay['TYPE']];
		if($timers['devices'][$relay['ID']]['period']>$timers['max_check_period'])$timers['max_check_period']=$timers['devices'][$relay['ID']]['period'];
		
		//Инициализация Kinkony
		if($timers['devices'][$relay['ID']]['CLASS'] =='KinCony')
		{
		 	$class=$lib[$relay['TYPE']]."_lib";
		 	var_dump($class);
 			$e8r=new $class($relay['IP'],$relay['PORT']);
 			//$e8r->activate();
 			}
		}
	return $timers;
}

$timers=reload_timer_config();


$cycleVarName='ThisComputer.'.str_replace('.php', '', basename(__FILE__)).'Run';
$table='AliIPRelay';
while (1)
{
	//ставим принудительно 25 секунд. потому что проверка идет на отсутсвие данных более 30 секунд.
		if(time()-$timers['cycle_check']['last']>$timers['cycle_check']['config'])
		{
			echo date('Y-m-d H:i:s').' Send xray stats...'."\n";
			setGlobal($cycleVarName, time(), 1);
			$timers['cycle_check']['last']=time();
		}
		//Перечитываем конфиг раз в 10 минут
		if(time()-$timers['config_reload']['last']>$timers['config_reload']['config'])
		{
			$timers=reload_timer_config();
			echo date('Y-m-d H:i:s').' Reload config...'."\n";
			$timers['config_reload']['last']=time();
		}
		if(time()> $timers['next_check'])
		{
			$timers['next_check']=time()+$timers['max_check_period'];
			
			foreach($timers['devices'] as $k => $relay)
			{
				if((time()-$timers['devices'][$k]['last'])>$timers['devices'][$k]['period'])
				{
				$timers['devices'][$k]['last']=time();
				if(($timers['devices'][$k]['last']+$timers['devices'][$k]['period'])<$timers['next_check'])
					$timers['next_check']=$timers['devices'][$k]['last']+$timers['devices'][$k]['period'];
				echo date('Y-m-d H:i:s').' Check '.$relay['IP'].':'.$relay['PORT'].'('.$relay['period'].'sec)'."\n";
				$v=$relay;
				//Process processCycle()
 				//echo "connect to ".$v['IP'].":".$v['PORT']."\n";
 				$class=$lib[$v['TYPE']]."_lib";
				$e8r=new $class($v['IP'],$v['PORT']);
				if($e8r->connected)
				{
				 	  $getr=$e8r->get_data();
				 	  //echo json_encode($getr);
				 	  
				 	  if($e8r->need_activate($getr))
				 	  {
				 	  	$e8r->activate();	
				 	  	$AliIPRelays_module->RelaySetFromLinkedProporties($v['ID'],$getr);
				 	  	$getr=$e8r->get_data();
				 	  	}
				 	   $AliIPRelays_module->lastsate[$v['IP'].":".$v['PORT']]=json_encode($getr);
				 	  
				 	  $data=array();
				 	  foreach ($getr as $key => $val)
				 	  {
				 	  	$data['VALUE']=$val;
				 	  	$data['TITLE']="ch ".$key;
				 	  	$data['ch_num']=$key;
				 	  	$data['relay_id']=$v['ID'];
				 	  	$sql="SELECT LINKED_OBJECT,LINKED_PROPERTY,LINKED_METHOD,VALUE FROM $table WHERE ch_num='".DBSafe($data['ch_num'])."' AND relay_id='".DBSafe($data['relay_id'])."'";
				 	  	$properties=$AliIPRelays_module->getData("Ali:AliIPRelay|ch_num".DBSafe($data['ch_num'])."|relay_id".DBSafe($data['relay_id']),$sql,true);
				 	  	//SQLSelectOne($sql);
				 	  	
				 	  	
				 	  	if(!$properties)
					  	{
				  		  //echo "need to add\n";
					  		SQLExec(
				 	  	 "INSERT INTO `AliIPRelay` (VALUE, ch_num,relay_id,TITLE) 
				 	  	 VALUES ('".$data['VALUE']."','".$data['ch_num']."','".$data['relay_id']."','".$data['TITLE']."')
				 	  	 ON DUPLICATE KEY UPDATE VALUE='".$data['VALUE']."'");
				 	  	 $AliIPRelays_module->clearCache("Ali:");
				 	  	}
				 	  	if($properties['VALUE']!=$data['VALUE'])
					  	{
					  		//echo "need to update (" . $properties['VALUE'] . "<>" . $data['VALUE'].")\n";
					  		SQLExec(
				 	  	 "INSERT INTO `AliIPRelay` (VALUE, ch_num,relay_id,TITLE) 
				 	  	 VALUES ('".$data['VALUE']."','".$data['ch_num']."','".$data['relay_id']."','".$data['TITLE']."')
				 	  	 ON DUPLICATE KEY UPDATE VALUE='".$data['VALUE']."'");
				 	  	 $AliIPRelays_module->clearCache("Ali:");
				 	  	}
				 	  	/*else 
				 	  	{echo "not need to update\n";	}*/
				 	  	
				   		if($properties&&$properties['LINKED_OBJECT']!='')
				   		{
				   			if (gg($properties['LINKED_OBJECT'].".".$properties['LINKED_PROPERTY'])<>$data['VALUE'])
				   			{
				   				echo "sg(".$properties['LINKED_OBJECT'].".".$properties['LINKED_PROPERTY'].",".$data['VALUE'].")\n";
				   				sg($properties['LINKED_OBJECT'].".".$properties['LINKED_PROPERTY'],$data['VALUE'],0,"processCycle");
				   			}
					        if ($properties['LINKED_METHOD']) {
				                    callMethod($properties['LINKED_OBJECT'].'.'.$properties['LINKED_METHOD'],$data);
				          }
				                               			
				   		}
				   		
				 	  }
					  
				 	}				
				
				}
			}
		}

   if (file_exists('./reboot') || IsSet($_GET['onetime']))
   {
      $db->Disconnect();
      exit;
   }
   sleep(1);
}
DebMes("Unexpected close of cycle: " . basename(__FILE__));