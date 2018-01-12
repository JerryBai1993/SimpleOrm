<?php
require_once "SimpleOrm.php";

$instance = SimpleMysqlModel::GetInstance("database","table_1","t1");

$sql = $instance->Select("t1.*,t2.*")
		->From()
		->Where(array(
			   array("col"=>"col1","symbol"=>"IN","val"=>array(1,2,3,"a",'b','c'),"relation"=>"and"),
			   array("col"=>"col2","symbol"=>"BETWEEN","val"=>array(110,119),"relation"=>"and"),
			   array("col"=>"col3","symbol"=>"LIKE","val"=>'_adadadasd_',"relation"=>"and"),
			))
		->Where(array(
                           array("col"=>"col4","symbol"=>"IN","val"=>array(1,2,3,"a",'b','c'),"relation"=>"and"),
                           array("col"=>"col5","symbol"=>"BETWEEN","val"=>array(110,119),"relation"=>"and"),
                           array("col"=>"col6","symbol"=>"LIKE","val"=>'_adadadasd_',"relation"=>"and"),
                        ),"OR")
		->Join(array(array("table"=>"table_join_2","alias"=>"t2","conditions"=>"t1.col1 = t2.col2")))
		->Group(" t1.group1,t2.group2")
		->Order(" t1.order1,t2.order2")
		->ComposeSql()
		->Sql()
;
var_dump($sql);




