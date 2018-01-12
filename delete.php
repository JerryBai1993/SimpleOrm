<?php
require_once "SimpleOrm.php";

$instance = SimpleMysqlModel::GetInstance("database","table_1","t1");

$sql = $instance->Delete()
		->Where(array(
				array("col"=>"col1","symbol"=>"IN","val"=>array(1,2,3,"a",'b','c'),"relation"=>"and"),
				array("col"=>"col2","symbol"=>"BETWEEN","val"=>array(110,119),"relation"=>"and"),
				array("col"=>"col3","symbol"=>"LIKE","val"=>'_adadadasd_',"relation"=>"and"),))
		->ComposeSql()
		->Sql()
;
var_dump($sql);




