<?php

/*
 * Mysql Model 1.0
 */

class SimpleMysqlModel
{

    /*
     *  client array
     * */
    private static $connects = array();

    /*
     * __construct
     *
     * 生成数据库client可单独放在一个父类中
     * */
    public function __construct($db_name, $table, $alias = "")
    {

        /*$key = md5($db_name);
        if (isset(self::$connects[$key])) {
            $this->client = self::$connects[$key];
        } else {
            $conn = DB::getInstance($db_name);
            $this->client = $conn;
            self::$connects[$key] = $conn;
        }*/
        $this->tablePropertiesInit($table, $alias);
    }

    /*
     * GetInstance
     *
     * @params $db_name string
     * @params $table string
     * @params $alias string
     * @return $this
     * */
    public static function GetInstance($db_name, $table, $alias = "")
    {
        $instance = new SimpleMysqlModel($db_name, $table, $alias);
        return $instance;
    }

    /*
     * TablePropertiesInit("table_name","table_name_alias")
     *
     * @params $table string
     * @params $alias string
     * */
    public function TablePropertiesInit($table, $alias)
    {
        $this->table = $table;
        $this->alias = $alias;
        $this->select = "";
        $this->update = "";
        $this->delete = "";
        $this->from = "";
        $this->join = "";
        $this->where = " WHERE 1 ";
        $this->wheres = array();
        $this->group = "";
        $this->order = "";
        $this->limit = "";
        $this->type = "select";
        $this->set = "";
        $this->sql = "";
    }

    /*
     * Select("clo1 as clo1_alias,col2 as clo2_alias...")
     *
     * @params  $fields  string
     * @return $this
     * */
    public function Select($fields = "*")
    {
        $this->type = "select";
        $this->select = " SELECT " . $fields;
        return $this;
    }

    /*
     * From => from table_name as table_name_alias
     *
     * @return $this
     * */
    public function From()
    {
        $this->from = " FROM (" . $this->table . (empty($this->alias) ? ")" : ") AS " . $this->alias);
        return $this;
    }

    /*
     * Join([{"table":table_name,"alias":table_name_alias,"condition":"t1.col1 = t2.col1 and ..."}...],""["left"|"right"])
     *
     * @params $joins array
     * @params $type string
     * @return $this
     * */
    public function Join(array $joins, $type = "")
    {
        $this->join = $type . " JOIN ";
        foreach ($joins as $val) {
            $this->join .= " (".$val["table"] . ") AS {$val["alias"]} ON " . $val["conditions"];
        }
        return $this;
    }

    /*
     * Where({"col":col_name,"symbol":[IN|NOT IN|...],"val":value,"relation":[AND|OR]})
     *
     * @params $where array
     * @return $this
     * */
    public function Where(array $wheres,$relation = "AND")
    {
    	$where = "";
    	$value = "";
        foreach ($wheres as &$val) {
            switch ($val["symbol"]) {
                case "IN":
                case "NOT IN":
                    foreach ($val["val"] as $v) {
                        if (is_string($v)) {
                            $value .= "'{$v}',";
                        } else {
                            $value .= $v . ",";
                        }
                    }
                    $val["val"] = substr($value, 0, -1);
                    $val["val"] = "(" . $val["val"] . ")";
                    break;
                case "LIKE":
                case "NOT LIKE":
                    $val["val"] = "'%{$val["val"]}%'";
                    break;
                case "BETWEEN":
                    $val["val"] = (is_string($val["val"][0]) ? "'{$val["val"][0]}'" : $val["val"][0]) .
                        " AND " . (is_string($val["val"][1]) ? "'{$val["val"][1]}'" : $val["val"][1]);
                    break;
                default:
                    if (is_string($val["val"])) {
                        $val["val"] = "'{$val["val"]}'";
                    }
                    break;
            }
            $where .= $val["relation"] . " `{$val["col"]}` " . $val["symbol"] . " " . $val["val"] . " ";
        }
        $this->where .= " " . $relation . " (" . $where . ")";
        return $this;
    }

    /*
     * Group("col1,col2...")
     *
     * @params $group string
     * @return $this
     * */
    public function Group($group)
    {
        $this->group = " GROUP BY " . $group;
        return $this;
    }

    /*
     * Order("col1,col2...")
     *
     * $params $order string
     * $return $this
     * */
    public function Order($order)
    {
        $this->order = " ORDER BY " . $order;
        return $this;
    }

    /*
     * Limit(number) => Limit 100
     *
     * @params $limit string
     * @return $this
     * */
    public function Limit($limit)
    {
        $this->limit = " LIMIT " . $limit;
        return $this;
    }

    /*
     * insert([col1,col2,...],[[val2,val2...]....])
     *
     * @params $cols array
     * @params $values array
     * @return $this
     * */
    public function Insert(array $cols, array $values)
    {
        $this->type = "insert";
        $this->sql = " INSERT INTO " . $this->table . "(" . implode(",", $cols) . ") VALUES ";
        foreach ($values as $val) {
            $tmp = "(";
            foreach ($val as $v) {
                if (is_string($v)) {
                    $tmp .= "'{$v}',";
                } else {
                    $tmp .= $v . ",";
                }
            }
            $tmp = substr($tmp, 0, -1);
            $this->sql .= $tmp . "),";
        }
        $this->sql = substr($this->sql, 0, -1);
        return $this;
    }

    /*
     * Delete() => DELETE FROM table_name
     *
     * @return $this
     * */
    public function Delete()
    {
        $this->type = "delete";
        $this->delete = " DELETE FROM " . $this->table;
        return $this;
    }

    /*
     * Update() => UPDATE table_name
     *
     * @return $this
     * */
    public function Update()
    {
        $this->type = "update";
        $this->update = " UPDATE " . $this->table;
        return $this;
    }

    /*
     * Set({col1:val1,cal2:val2...})
     *
     * @params $set array
     * @return $this
     * */
    public function Set(array $set)
    {
        $this->set = " SET ";
        foreach ($set as $key => $val) {
            $this->set .= "`{$key}`" . " = " . (is_string($val) ? "'{$val}'" : $val) . ",";
        }
        $this->set = substr($this->set, 0, -1);
        return $this;
    }

    /*
     * ComposeSql() => generate complete sql
     *
     * @return $this
     * */
    public function ComposeSql()
    {
        switch ($this->type) {
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

    /*
     * Find(sql) => execute sql
     *
     * @rreturn result
     * */
    public function Find($sql)
    {
        $res = $this->client->query($sql);
        return $res;
    }

    /*
     * Sql() => get sql
     *
     * @return sql
     * */
    public function Sql()
    {
        return $this->sql;
    }
}