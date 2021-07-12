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
$AliIPRelays_module->processIncomingMessage();

return(1);