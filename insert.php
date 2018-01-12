<?php
require_once "SimpleOrm.php";

$instance = SimpleMysqlModel::GetInstance("database","table_1","t1");

$sql = $instance->Insert(array("col1","col2"),array(array("val1","val2"),array("val3","val4")))
		->Sql()
;
var_dump($sql);




