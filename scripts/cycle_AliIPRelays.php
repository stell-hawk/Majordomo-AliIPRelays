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

$tmp = SQLSelectOne("SELECT ID FROM AliIPRelays LIMIT 1");
if (!$tmp['ID'])
   exit; // no devices added -- no need to run this cycle
echo date("H:i:s") . " running " . basename(__FILE__) . PHP_EOL;
$latest_check=0;
$checkEvery=20; // poll every 5 seconds

//Инициализация Kincony;
global $lib;$sql=array();
foreach ($lib as $k => $v)if($v=='KinCony') $sql[]= "'".$k."'";$sql=implode($sql,",");
$sql="SELECT * FROM AliIPRelays where type IN (".$sql.")"	;
$tmp = SQLSelect($sql);
foreach ($tmp as $v)
{
 	$class=$lib[$v['type']]."_lib";
 	$e8r=new $class($v['IP'],$v['PORT']);
 	$e8r->activate();
}


while (1)
{
   setGlobal((str_replace('.php', '', basename(__FILE__))) . 'Run', time(), 1);
   if ((time()-$latest_check)>$checkEvery) {
    $latest_check=time();
    echo date('Y-m-d H:i:s').' Polling devices...'."\n";
    $AliIPRelays_module->processCycle();
   }
   if (file_exists('./reboot') || IsSet($_GET['onetime']))
   {
      $db->Disconnect();
      exit;
   }
   sleep(1);
}
DebMes("Unexpected close of cycle: " . basename(__FILE__));