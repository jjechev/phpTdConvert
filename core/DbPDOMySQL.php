<?php

class DbPDOMySQL
{

    private $_db_host;
    private $_db_user;
    private $_db_pass;
    private $_db_name;
    private $_db_enc;
    private $_db_link = null;
    private $_params;
    private $_sql;
    private $_stmt;
    private $_rows;
    private $_fields;

    const CLASSNAME = 'PDOMySQL';
    const MYSQLDEBUG = 'PDOMySQLDEBUG';

    public function __construct($host = false, $user = false, $pass = false, $name = false, $enc = "utf8")
    {
        $this->_db_host = $host ? $host : Settings::$dbHost;
        $this->_db_user = $user ? $user : Settings::$dbUser;
        $this->_db_pass = $pass ? $pass : Settings::$dbPass;
        $this->_db_name = $name ? $name : Settings::$dbName;
        $this->_db_enc = $enc;
        $this->_rows = -1;
        $this->_fields = -1;

        $this->db_connect($enc);
    }

    private function db_connect($enc)
    {
        $dsn = 'mysql:host=' . $this->_db_host . ';dbname=' . $this->_db_name;
        $options = array(
            PDO::ATTR_PERSISTENT => true,
//            PDO::MYSQL_ATTR_INIT_COMMAND=>set names $enc,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        );

        try
        {
            $this->_db_link = new PDO($dsn, $this->_db_user, $this->_db_pass, $options);
            Log::log(self::CLASSNAME, "Connection established. Current database: " . $this->_db_name);
        } catch (PDOException $e)
        {
            Log::log(self::CLASSNAME, "Can't connect to PDOMySQL : {$this->_db_host}<br />\n" . $e->getMessage());
        }
    }

    public function prepare($sql, $params = array(), $options = array())
    {
        $this->_stmt = $this->_db_link->prepare($sql);
        $this->_params = $params;
        $this->_sql = $sql;
        return $this;
    }

    public function execute($params = array())
    {
        if ($params)
            $this->_params = $params;
        $this->_stmt->execute($this->_params);
        return $this;
    }

    public function fetchAllAssoc()
    {
        return $this->_stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fetchRowAssoc()
    {
        return $this->_stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function fetchAllNum()
    {
        return $this->_stmt->fetchAll(PDO::FETCH_NUM);
    }

    public function fetchRowNum()
    {
        return $this->_stmt->fetch(PDO::FETCH_NUM);
    }

    public function fetchAllObj()
    {
        return $this->_stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function fetchRowObj()
    {
        return $this->_stmt->fetch(PDO::FETCH_OBJ);
    }

    public function fetchAllColumn($column)
    {
        return $this->_stmt->fetchAll(PDO::FETCH_COLUMN, $column);
    }

    public function fetchRowColumn($column)
    {
        return $this->_stmt->fetch(PDO::FETCH_COLUMN, $column);
    }

    public function fetchAllClass($class)
    {
        return $this->_stmt->fetchAll(PDO::FETCH_COLUMN, $class);
    }

    public function fetchRowClass($class)
    {
        return $this->_stmt->fetch(PDO::FETCH_COLUMN, $class);
    }

    public function getLastInsertId()
    {
        return $this->dblink->lastInsertId();
    }

    public function getAffectedRows()
    {
        return $this->_stmt->rowCount();
    }

    public function getSIMT()
    {
        return $this->simt;
    }

    public function db_query($sql, $params = array(), $resultType = false, $debug = false)
    {
        if ($this->_db_link === null)
            return;
        if (!is_array($params))
            $params = array();
        $startTime = microtime(true);
        $res = $this->prepare($sql, $params)->execute();
        if ($resultType)
            if ($resultType == 'assoc' || $resultType)
                $res = $res->fetchAllAssoc();
        $endTime = microtime(true);
        $time = number_format($endTime - $startTime, 5);
        $log = 'Execution time: ' . $time . 's' . '<br />Query: ' . $sql;
        Log::log(self::CLASSNAME, $log);
        if ($debug)
        {
            Log::log(self::MYSQLDEBUG, "Query:<pre>$sql \n"
                    . "Time: {$time}s\n"
                    . "Result:\n"
                    . print_r($res, true) . "\n</pre>"
            );
        }
        return $res;
    }

}
