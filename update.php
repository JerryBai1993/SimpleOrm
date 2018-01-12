<?php
require_once "SimpleOrm.php";

$instance = SimpleMysqlModel::GetInstance("database","table_1","t1");

$sql = $instance->Update()
		->Set(array("col1"=>"val1","col2"=>"val2"))
		->ComposeSql()
		->Sql()
;
var_dump($sql);




