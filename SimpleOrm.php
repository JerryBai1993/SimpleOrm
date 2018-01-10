<?php
class SimpleOrm{

	private static $connects = array();

	public function __construct(array $conn_params,$table,$alias){
		$key = implode(",",$conn_params);
		$key = md5($key);
		if(isset(self::$connects($key))){
			$this->client = self::$connects($key);
		}else{
			require_once DbBase.class.php;
			$conn = new DbBase($conn_params);
			$this->client = $conn;
			self::$connects[$key] = $conn;
		}
		$this->tablePropertiesInit($table,$alias);
	}

	public function TablePropertiesInit($table,$alias){
		$this->table = $table;
		$this->alias = $alias;
		$this->select = "";
		$this->update = "";
		$this->delete = "";
		$this->from = "";
		$this->join = "";
		$this->where = " WHERE 1 ";
		$this->group = "";
		$this->order = "";
		$this->limit = "";
		$this->sql = "";
		$this->type = "select";
		$this->set = "";
	}

	public function Select($fields = "*"){
		$this->type = "select";
		$this->select = " SELECT ".$fields;
		return $this;
	}

	public function From(){
		$this->from = " FROM ".$this->table.(empty($this->alias) ? "" : " AS ".$this->alias);
		return $this;
	}

	public function Join(array $joins,$type){
		$this->join = $type." JOIN ";
		foreach($joins as $val){
			$this->join .= $val["table"]." AS {$val["alias"]} ON ".$val["conditions"];
		}
	}

	public function Where(array $where){
		foreach($where as &$val){
			switch($val["symbol"]){
				case "IN":
				case "NOT IN"
					$val["val"] = "(";
					foreach($val["val"] as $v){
						if(is_string($v)){
						$val["val"] .= "'{$v}',";
						}else{
							$val["val"] .= $v.",";
						}
					}
					$val["val"] = substr($val["val"],0,-1);
					$val["val"] .= ")";
					break;
				case "LIKE":
				case "NOT LIKE":
					if(is_string($val["val"])){
						$val["val"] = "'%{$val["val"]}%'";
					}else{
						$val["val"] = "%{$val["val"]}%";
					}
				case "BETWEEN":
					$val["val"] = (is_string($val["val"][0]) ? "'{$val["val"][0]}'" : $val["val"][0]).
			" AND ".(is_string($val["val"][1]) ? "'{$val["val"][1]}'" : $val["val"][1]);
				default:
					if(is_string($val["val"])){
					$val["val"] = "'{$val["val"]}'";
					}
					break;
			}
			$this->where .= $val["relation"]." `{$val["col"]}` ".$val["symbol"]." ".$val["val"];
		}
	}

	public function Group($group){
		$this->group = " GROUP BY ".$group;
		return $this;
	}

	public function Order($order){
		$this->order = " ORDER BY ".$order;
		return $this;
	}

	public function Limit($limit){
		$this->limit = " LIMIT ".$limit;
		return $this;
	}

	public function Insert(array $cols,array $values){
		$this->type = "insert";
		$this->sql = " INSERT INTO ".$this->table."(".implode(",",$col).") VALUES ";
		$v = "";
		foreach($values as $val){
			$tmp = "(";
			foreach($val as $v){
				if(is_string($v)){
					$tmp .= "'{$v}',";
				}else{
					$tmp .= $v.",";
				}
			}
			$tmp = substr($tmp,0,-1);
			$this->sql .= $tmp."),";
		}
		$this->sql = substr($this->sql,0,-1);
		return $this;
	}

	public function Delete(){
		$this->type = "delete";
		$this->delete = " DELETE FROM ".$this->table;
		return $this;
	}

	public function Update(){
		$this->type = "update";
		$this->update = " UPDATE ".$this->table;
		return $this;
	}

	public function Set(array $set){
		$this->set = " SET ";
		foreach($set as $key=>$val){
			$this->set .= "`{$key}`"." = ".(is_string($val) ? "'{$val}'" : $val).",";
		}
		$this->set = substr($this->set,0,-1);
		return $this;
	}

	public function ComposeSql(){
		switch($this->type){
			case "select":
				$this->sql = $this->select;
				$this->sql .= $this->from;
				$this->sql .= $this->join;
				$this->sql .= $this->where;
				$this->sql .= $this->group;
				$this->sql .= $this->order;
				$this->sql .= $this->limit;
				break;
			case "delete":
				$this->sql = $this->delete;
				$this->sql .= $this->where;
				break;
			case "update":
				$this->sql = $this->update;
				$this->sql .= $this->set;
				$this->sql .= $this->where;
				break;
			default:
				break;
		}
		return $this;
	}

	public function Find($sql){
		$res = $this->client->query($sql);
		return $res;
	}
}