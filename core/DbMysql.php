<?php

class DbMysql
{

    protected $db_host;
    protected $db_user;
    protected $db_pass;
    protected $db_name;
    protected $db_enc;
    protected $db_link;
    protected $rows;
    protected $r;
    protected $fields;
    protected $f;
    protected $ceche = 0;

    const CLASSNAME = 'MySQL';
    const MYSQLDEBUG = 'MySQLDEBUG';

    public function __construct($host = false, $user = false, $pass = false, $name = false, $enc = "utf8", $auto = true)
    {
        $this->db_host = $host ? $host : Settings::$dbHost;
        $this->db_user = $user ? $user : Settings::$dbUser;
        $this->db_pass = $pass ? $pass : Settings::$dbPass;
        $this->db_name = $name ? $name : Settings::$dbName;
        $this->db_enc = $enc;
        $this->rows = -1;
        $this->fields = -1;
        if ($auto)
        {
            $this->db_connect();
            $this->db_selectdb();
        }
    }

    public function db_connect()
    {
        $this->db_link = @mysql_connect($this->db_host, $this->db_user, $this->db_pass);
        if (!$this->db_link)
        {
            Log::log(self::CLASSNAME, "Can't connect to MySQL on: {$this->db_host}<br />\n" . mysql_error());
        }
        if (!@mysql_query("SET NAMES {$this->db_enc}", $this->db_link))
        {
            Log::log(self::CLASSNAME, "Can't set charset<br />\n" . mysql_error());
//				die ();
        }
    }

    public function db_selectdb()
    {
        if (!@mysql_select_db($this->db_name, $this->db_link))
        {
            Log::log(self::CLASSNAME, "Can't select database: " . $this->db_name . '<br />\n' . mysql_error());
//				die ();
        }
        else
            Log::log(self::CLASSNAME, "Connection established. Current database: " . $this->db_name);
    }

    public function db_query($q, $result = 1, $debug = 0, $type = "assoc")
    {
        if (!$this->db_link)
            return;
        if (empty($q))
        {
            echo "MySQL query is empty";
            exit();
        }

        $startTime = microtime(true);
        if (!$query = mysql_query($q, $this->db_link))
        {
            Log::log(self::CLASSNAME, "Invalid MySQL query: $q<br />\n" . mysql_error());
            return false;
        }
        $endTime = microtime(true);
        $log = 'Execution time: ' . number_format($endTime - $startTime, 5) . 's' . '<br />Query: ' . $q;
        Log::log(self::CLASSNAME, $log);
        $ret = 0;
        if ($result == 1)
        {
            $this->rows = mysql_num_rows($query);
            $this->fields = mysql_num_fields($query);

            $this->f = $this->fields;
            $this->r = $this->rows;
        }

        $j = 0;
        if ($result == 1)
        {
            $ret = array();
            $j = 0;
            while ($res = mysql_fetch_row($query))
            {
                for ($i = 0; $i < count($res); $i++)
                {
                    $fn = mysql_field_name($query, $i);
                    if ($type == 'assoc')
                        $ret[$j][$fn] = $res[$i];
                    elseif ($type == 'num')
                        $ret[$j][$i] = $res[$i];
                    else
                    {
                        echo "Invalid query type: $type";
                        exit();
                    }
                }
                $j++;
            }
        }
        elseif ($result == 2)
            $ret = $query;
        else
            $ret = $this->rows;
        if ($debug)
        {
            Log::log(self::MYSQLDEBUG, "Query:<pre>$q \n"
                    . "Result:\n"
                    . print_r($ret, true) . "\n</pre>"
                    . "Rows: {$this->rows}<br />\n"
                    . "Fileds: {$this->fields}\n"
            );
        }
        @mysql_free_result($query);
        return($ret);
    }

    public function checkConnection()
    {
        
    }

    public function db_fields_name($table, $debug = false)
    {
        $i = 0;
        $result = mysql_query("select * from $table LIMIT 1;", $this->db_link);
        while ($i < mysql_num_fields($result))
        {
            $meta = mysql_fetch_field($result, $i);
            if ($meta)
            {
                $data[$i] = $meta->name;
//					$data[type][$i] = $meta->type;
                $i++;
            }
        }

        if ($debug)
        {
            Log::log(self::MYSQLDEBUG, "Mysql Table Field Names: '$table'<pre>\n"
                    . "Result:\n"
                    . print_r($data, true) . "\n</pre>"
            );
        }
        return $data;
    }

    public function db_fields_type($table, $where = "")
    {
        $i = 0;
        $result = mysql_query("select * from $table LIMIT 1;", $this->db_link);
        while ($i < mysql_num_fields($result))
        {
            $meta = mysql_fetch_field($result, $i);
            if ($meta)
            {
                $data[$i] = $meta->type;
                $i++;
            }
        }

        if ($debug)
        {
            Log::log(self::MYSQLDEBUG, "Mysql Table Field Types: '$table'<pre>\n"
                    . "Result:\n"
                    . print_r($data, true) . "\n</pre>"
            );
        }
        return $data;
    }

    public function db_updateData($table, $where, $data, $debud = false)
    {
        if (!$table OR ! $data OR ! $where)
        {
            Log::log(self::CLASSNAME, 'UpdateData: "table" or "where" or "data" is empty');
            return -1;
        }

        if (!is_array($data) OR ! is_array($where))
        {
            Log::log(self::CLASSNAME, 'UpdateData: "where" or "data" is not array');
            return -1;
        }

        $qWhere = $qData = "";

        foreach ($where as $k => $val)
            $qWhere .= "`$table`.`$k` = '$val' AND";
        $qWhere = substr($qWhere, 0, -4);

        foreach ($data as $k => $val)
            $qData .= "`$table`.`$k` = '$val' ,";

        $qData = substr($qData, 0, -1);

        $query = "UPDATE $table SET " . $qData . " WHERE $qWhere";

        $this->db_query($query, 0, $debud);
    }

    public function db_setData($table, $data, $debud = false)
    {
        if (!$table OR ! $data)
        {
            Log::log(self::CLASSNAME, 'SetData: "table" or "data" is empty');
            return -1;
        }

        if (!is_array($data))
        {
            Log::log(self::CLASSNAME, 'SetData: "data" is not array');
            return -1;
        }

        $qFields = $qValues = "";

        foreach ($data as $k => $val)
        {
            $qFields .= $k . ",";
            $qValues .= "'" . $val . "',";
        }

        $qFields = substr($qFields, 0, -1);
        $qValues = substr($qValues, 0, -1);

        $query = "INSERT INTO $table ($qFields) VALUES ($qValues);";

        $this->db_query($query, 0, $debud);
    }

    public function db_deleteData($table, $where, $debud = false)
    {
        if (!$table OR ! $where)
        {
            Log::log(self::CLASSNAME, 'DeleteData: "table" or "where" is empty');
            return -1;
        }

        if (!is_array($where))
        {
            Log::log(self::CLASSNAME, 'DeleteData: "where" is not array');
            return -1;
        }

        $qWhere = "";

        foreach ($where as $k => $val)
            $qWhere .= "`$table`.`$k` = '$val' AND";
        $qWhere = substr($qWhere, 0, -4);

        $query = "DELETE FROM $table WHERE $qWhere;";

        $this->db_query($query, 0, $debud);
    }

    public function db_getData($table, $where = array(), $field = false, $debud = false)
    {
        if (!$table OR ! $where)
        {
            Log::log(self::CLASSNAME, 'getData: "table" or "where" is empty');
            return -1;
        }

        if (!is_array($where))
        {
            Log::log(self::CLASSNAME, 'getData: "where" is not array');
            return -1;
        }

        if (!$field)
            $field = array("*");

        $qWhere = $qFields = "";

        foreach ($where as $k => $val)
            $qWhere .= "`$table`.`$k` = '$val' AND";
        $qWhere = substr($qWhere, 0, -4);

        foreach ($field as $k => $val)
            $qFields .= "$val,";
        $qFields = substr($qFields, 0, -1);

        $query = "SELeCT $qFields FROM $table WHERE $qWhere;";

        return $this->db_query($query, 1, $debud);
    }

}
