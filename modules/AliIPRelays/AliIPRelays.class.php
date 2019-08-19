<?php
/**
* Ali IP реле
* @package project
* @author Wizard <sergejey@gmail.com>
* @copyright http://majordomo.smartliving.ru/ (c)
* @version 0.1 (wizard, 11:08:54 [Aug 18, 2019])
*/
//
include_once(DIR_MODULES.'AliIPRelays/Eth8Relay_library.php');
include_once(DIR_MODULES.'AliIPRelays/KinCony_library.php');
$lib=array('Eth8Relay v5'=>'Eth8Relay','Eth8Relay v6'=>'Eth8Relay','kc868-h8'=>'KinCony','kc868-h16'=>'KinCony','kc868-h32'=>'KinCony');

//
class AliIPRelays extends module {
/**
* AliIPRelays
*
* Module class constructor
*
* @access private
*/
function __construct() {
  $this->name="AliIPRelays";
  $this->title="Ali IP Реле";
  $this->module_category="<#LANG_SECTION_DEVICES#>";
  $this->checkInstalled();
}
/**
* saveParams
*
* Saving module parameters
*
* @access public
*/
function saveParams($data=1) {
 $p=array();
 if (IsSet($this->id)) {
  $p["id"]=$this->id;
 }
 if (IsSet($this->view_mode)) {
  $p["view_mode"]=$this->view_mode;
 }
 if (IsSet($this->edit_mode)) {
  $p["edit_mode"]=$this->edit_mode;
 }
 if (IsSet($this->data_source)) {
  $p["data_source"]=$this->data_source;
 }
 if (IsSet($this->tab)) {
  $p["tab"]=$this->tab;
 }
 return parent::saveParams($p);
}
/**
* getParams
*
* Getting module parameters from query string
*
* @access public
*/
function getParams() {
  global $id;
  global $mode;
  global $view_mode;
  global $edit_mode;
  global $data_source;
  global $tab;
  if (isset($id)) {
   $this->id=$id;
  }
  if (isset($mode)) {
   $this->mode=$mode;
  }
  if (isset($view_mode)) {
   $this->view_mode=$view_mode;
  }
  if (isset($edit_mode)) {
   $this->edit_mode=$edit_mode;
  }
  if (isset($data_source)) {
   $this->data_source=$data_source;
  }
  if (isset($tab)) {
   $this->tab=$tab;
  }
}
/**
* Run
*
* Description
*
* @access public
*/
function run() {
 global $session;
  $out=array();
  if ($this->action=='admin') {
   $this->admin($out);
  } else {
   $this->usual($out);
  }
  if (IsSet($this->owner->action)) {
   $out['PARENT_ACTION']=$this->owner->action;
  }
  if (IsSet($this->owner->name)) {
   $out['PARENT_NAME']=$this->owner->name;
  }
  $out['VIEW_MODE']=$this->view_mode;
  $out['EDIT_MODE']=$this->edit_mode;
  $out['MODE']=$this->mode;
  $out['ACTION']=$this->action;
  $out['DATA_SOURCE']=$this->data_source;
  $out['TAB']=$this->tab;
  $this->data=$out;
  $p=new parser(DIR_TEMPLATES.$this->name."/".$this->name.".html", $this->data, $this);
  $this->result=$p->result;
}
/**
* BackEnd
*
* Module backend
*
* @access public
*/
function admin(&$out) {
 if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
  $out['SET_DATASOURCE']=1;
 }
 if ($this->data_source=='AliIPRelays' || $this->data_source=='') {
  if ($this->view_mode=='' || $this->view_mode=='search_AliIPRelays') {
   $this->search_AliIPRelays($out);
  }
  if ($this->view_mode=='edit_AliIPRelays') {
   $this->edit_AliIPRelays($out, $this->id);
  }
  if ($this->view_mode=='delete_AliIPRelays') {
   $this->delete_AliIPRelays($this->id);
   $this->redirect("?data_source=AliIPRelays");
  }
 }
 if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
  $out['SET_DATASOURCE']=1;
 }
 if ($this->data_source=='AliIPRelay') {
  if ($this->view_mode=='' || $this->view_mode=='search_AliIPRelay') {
   $this->search_AliIPRelay($out);
  }
  if ($this->view_mode=='edit_AliIPRelay') {
   $this->edit_AliIPRelay($out, $this->id);
  }
 }
}
/**
* FrontEnd
*
* Module frontend
*
* @access public
*/
function usual(&$out) {
 $this->admin($out);
}
/**
* AliIPRelays search
*
* @access public
*/
 function search_AliIPRelays(&$out) {
  require(DIR_MODULES.$this->name.'/AliIPRelays_search.inc.php');
 }
/**
* AliIPRelays edit/add
*
* @access public
*/
 function edit_AliIPRelays(&$out, $id) {
  require(DIR_MODULES.$this->name.'/AliIPRelays_edit.inc.php');
 }
/**
* AliIPRelays delete record
*
* @access public
*/
 function delete_AliIPRelays($id) {
  $rec=SQLSelectOne("SELECT * FROM AliIPRelays WHERE ID='$id'");
  // some action for related tables
  SQLExec("DELETE FROM AliIPRelays WHERE ID='".$rec['ID']."'");
 }
/**
* AliIPRelay search
*
* @access public
*/
 function search_AliIPRelay(&$out) {
  require(DIR_MODULES.$this->name.'/AliIPRelay_search.inc.php');
 }
/**
* AliIPRelay edit/add
*
* @access public
*/
 function edit_AliIPRelay(&$out, $id) {
  require(DIR_MODULES.$this->name.'/AliIPRelay_edit.inc.php');
 }
 function propertySetHandle($object, $property, $value) {
 	 global $lib;
   $table='AliIPRelay';
   $properties=SQLSelect("SELECT * FROM $table WHERE LINKED_OBJECT LIKE '".DBSafe($object)."' AND LINKED_PROPERTY LIKE '".DBSafe($property)."'");
   $total=count($properties);
   if ($total) {
    for($i=0;$i<$total;$i++) {
     //to-do
     $v=SQLSelectOne("SELECT * FROM AliIPRelays where id=".$properties[$i]['relay_id']);
 		 $class=$lib[$v['type']]."_lib";
 	   $e8r=new $class($v['IP'],$v['PORT']);
		 if($e8r->connected)
 			{
 				if($value)$e8r->Relay_on($properties[$i]['ch_num']);
 				else $e8r->Relay_off($properties[$i]['ch_num']);
    	}
    }
   }
 }
 
function processCycle() {
 global $lib;
 $this->getConfig();
 $res=SQLSelect("SELECT * FROM AliIPRelays");
 foreach ($res as $v)
 {
 	//echo "connect to ".$v['IP'].":".$v['PORT']."\n";
 	$class=$lib[$v['type']]."_lib";
 	$e8r=new $class($v['IP'],$v['PORT']);
 	if($e8r->connected)
 	{
 	  $getr=$e8r->get_data();
 	  print_r($getr);
 	  $data=array();
 	  foreach ($getr as $key => $val)
 	  {
 	  	$data['VALUE']=$val;
 	  	$data['TITLE']="ch ".$key;
 	  	$data['ch_num']=$key;
 	  	$data['relay_id']=$v['ID'];
	  	SQLExec(
 	  	 "INSERT INTO `AliIPRelay` (VALUE, ch_num,relay_id,TITLE) 
 	  	 VALUES ('".$data['VALUE']."','".$data['ch_num']."','".$data['relay_id']."','".$data['TITLE']."')
 	  	 ON DUPLICATE KEY UPDATE VALUE='".$data['VALUE']."'");
   		$table='AliIPRelay';   		
   		$sql="SELECT * FROM $table WHERE ch_num='".DBSafe($data['ch_num'])."' AND relay_id='".DBSafe($data['relay_id'])."' and LINKED_OBJECT<>''";
   		//echo $sql."\n";
   		$properties=SQLSelectOne($sql);
   		if($properties)
   		{
   			if (gg($properties['LINKED_OBJECT'].".".$properties['LINKED_PROPERTY'])<>$data['VALUE'])
   			{
   				echo "sg(".$properties['LINKED_OBJECT'].".".$properties['LINKED_PROPERTY'].",".$data['VALUE'].")\n";
   				sg($properties['LINKED_OBJECT'].".".$properties['LINKED_PROPERTY'],$data['VALUE']);
   			}
   		}
 	  }
	  
 	}
 	
 }
	
}

function processTEST() {
 global $lib;
 $this->getConfig();
 $res=SQLSelect("SELECT * FROM AliIPRelays where id=2");
 foreach ($res as $v)
 {
 	//echo "connect to ".$v['IP'].":".$v['PORT']."\n";
 	$class=$lib[$v['type']]."_lib";
 	$e8r=new $class($v['IP'],$v['PORT']);
 	if($e8r->connected)
 	{
 	  //$getr=$e8r->get_data();
 	  //print_r($getr);
 	  $e8r->Relay_on(2);
 	  $e8r->Relay_on(3);
 	  sleep(2);
 	  $e8r->Relay_off(3);
 	  $e8r->Relay_off(2);
	}
 //echo "\n end";
}
}
 
/**
* Install
*
* Module installation routine
*
* @access private
*/
 function install($data='') {
  parent::install();
 }
/**
* Uninstall
*
* Module uninstall routine
*
* @access public
*/
 function uninstall() {
  SQLExec('DROP TABLE IF EXISTS AliIPRelays');
  SQLExec('DROP TABLE IF EXISTS AliIPRelay');
  SQLExec('DROP TABLE IF EXISTS AliIPRelays_queue');
  parent::uninstall();
 }
/**
* dbInstall
*
* Database installation routine
*
* @access private
*/
 function dbInstall($data) {
/*
AliIPRelays - 
AliIPRelay - 
AliIPRelays_queue - 
*/
  $data = <<<EOD
 AliIPRelays: ID int(10) unsigned NOT NULL auto_increment
 AliIPRelays: TITLE varchar(100) NOT NULL DEFAULT ''
 AliIPRelays: IP varchar(15) NOT NULL DEFAULT ''
 AliIPRelays: PORT varchar(10) NOT NULL DEFAULT ''
 AliIPRelays: STATUS TINYINT
 AliIPRelays: type ENUM('Eth8Relay v5','Eth8Relay v6','kc868-h8','kc868-h16','kc868-h32') NOT NULL
 AliIPRelays: tcp_mode ENUM('Немедленно','В очередь') DEFAULT 'Немедленно'
 AliIPRelays: period int(10) unsigned
 AliIPRelays: next_check datetime
 AliIPRelays: UPDATED TIMESTAMP on update CURRENT_TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
 AliIPRelays: INDEX type (type)
 
 AliIPRelay: ID int(10) unsigned NOT NULL auto_increment
 AliIPRelay: relay_id int(10) NOT NULL DEFAULT '0'
 AliIPRelay: TITLE varchar(100) NOT NULL DEFAULT ''
 AliIPRelay: VALUE varchar(255) NOT NULL DEFAULT ''
 AliIPRelay: ch_num varchar(10) NOT NULL DEFAULT ''
 AliIPRelay: LINKED_OBJECT varchar(100) NOT NULL DEFAULT ''
 AliIPRelay: LINKED_PROPERTY varchar(100) NOT NULL DEFAULT ''
 AliIPRelay: LINKED_METHOD varchar(100) NOT NULL DEFAULT ''
 AliIPRelay: UPDATED TIMESTAMP on update CURRENT_TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
 AliIPRelay: INDEX relay_id (relay_id)
 AliIPRelay: UNIQUE uniq (relay_id, ch_num) USING BTREE
 AliIPRelay: INDEX LINKED_OBJECT (LINKED_OBJECT)
 AliIPRelay: INDEX LINKED_PROPERTY (LINKED_PROPERTY)
  
 AliIPRelays_queue: ID int(10) unsigned NOT NULL auto_increment
 AliIPRelays_queue: relay_id int(10) NOT NULL DEFAULT '0'
 AliIPRelays_queue: queue varchar(100)
 AliIPRelays_queue: ADDED  TIMESTAMP on update CURRENT_TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
 
EOD;
  parent::dbInstall($data);
  //if(!is_array(SQLSelectOne("SELECT * FROM INFORMATION_SCHEMA.STATISTICS WHERE table_name = 'AliIPRelay' AND INDEX_NAME <> 'PRIMARY'")))
  //if(!is_array(SQLSelectOne("SELECT * FROM INFORMATION_SCHEMA.STATISTICS WHERE table_name = 'AliIPRelay' AND INDEX_NAME = 'relay_id'")))SQLExec("ALTER TABLE `AliIPRelay` ADD INDEX `relay_id` (`relay_id`);");
  if(!is_array(SQLSelectOne("SELECT * FROM INFORMATION_SCHEMA.STATISTICS WHERE table_name = 'AliIPRelay' AND INDEX_NAME = 'uniq'")))SQLExec("ALTER TABLE `AliIPRelay` ADD UNIQUE `uniq` (`relay_id`, `ch_num`) USING BTREE;");
  //if(!is_array(SQLSelectOne("SELECT * FROM INFORMATION_SCHEMA.STATISTICS WHERE table_name = 'AliIPRelay' AND INDEX_NAME = 'LINKED_OBJECT'")))SQLExec("ALTER TABLE `AliIPRelay` ADD INDEX `LINKED_OBJECT` (`LINKED_OBJECT`);");
  //if(!is_array(SQLSelectOne("SELECT * FROM INFORMATION_SCHEMA.STATISTICS WHERE table_name = 'AliIPRelay' AND INDEX_NAME = 'LINKED_PROPERTY'")))SQLExec("ALTER TABLE `AliIPRelay` ADD INDEX `LINKED_PROPERTY` (`LINKED_PROPERTY`);");
 }
// --------------------------------------------------------------------
}

/*
*
* TW9kdWxlIGNyZWF0ZWQgQXVnIDE4LCAyMDE5IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/