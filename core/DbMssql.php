<?php

/*
class dbMssql{
		protected  $db_host;
		protected  $db_user;
		protected  $db_pass;
		protected  $db_name;
		protected  $db_enc;
		protected  $db_link;
		protected  $rows;
		protected  $r;
		protected  $fields;
		protected  $f;
		protected  $ceche = 0; 
		
		const CLASSNAME		= 'MSSQL';
		const MSSQLDEBUG	= 'MSSQLDEBUG';
	   
       public function __construct($host=false, $user=false, $pass=false, $name=false, $enc="utf8", $auto=true)
	   {
	         $this->db_host = $host ? $host : Settings::$dbHost;
	         $this->db_user = $user ? $user : Settings::$dbUser;
	         $this->db_pass = $pass ? $pass : Settings::$dbPass;
	         $this->db_name = $name ? $name : Settings::$dbName;
	         $this->db_enc = $enc;
	         $this->rows=-1;
	         $this->fields=-1;
	         if ($auto){
		              $this->db_connect();
		              $this->db_selectdb();
		 }
       }
        
       public function db_connect()
	   {	$this->db_link=@mssql_connect($this->db_host, $this->db_user, $this->db_pass);
	        if (!$this->db_link)
			{	
				Log::log(self::CLASSNAME,"Can't connect to MSSQL on: {$this->db_host}<br />\n" );
			}
	        //if (! @mssql_query("SET NAMES {$this->db_enc}", $this->db_link)) 
			//{	Log::log(self::CLASSNAME,"Can't set charset<br />\n" . mssql_error());
////				die ();
			}
       }
    
       public function db_selectdb(){
	        if(! @!mssql_select_db($this->db_name, $this->db_link)) 
			{	Log::log(self::CLASSNAME,"Can't select database: ".$this->db_name.'<br />\n');
//				die ();
			}
			else
				Log::log(self::CLASSNAME,"Connection established. Current database: ".$this->db_name);
			
       }
    
       public function db_query($q, $result=1, $debug=0, $type="assoc")
	   {	
			if (!$this->db_link) return;
	        if (empty($q)){ echo "MSSQL query is empty"; exit(); }

			$startTime = microtime(true);
	        if (!$query=mssql_query($q, $this->db_link) )
			{	Log::log(self::CLASSNAME,"Invalid MSSQL query: $q<br />\n" );
				return false;
			}
			$endTime = microtime(true);
			$log = 'Execution time: '. number_format($endTime - $startTime , 5 ). 's'.'<br />Query: '.$q;
			Log::log(self::CLASSNAME,$log);
	         $ret=0;
	         if ($result == 1){
		              $this->rows=mssql_num_rows($query);
		              $this->fields=mssql_num_fields($query);
		     
		      $this->f = $this->fields;
		      $this->r = $this->rows;
			}

	         $j=0;
	         if ($result == 1){
		              $ret=array();
		           $j=0;
		              while ($res=mssql_fetch_row($query)){
				              for ($i=0; $i<count($res); $i++){
						                 $fn=mssql_field_name($query,$i);
						                 if ($type=='assoc') $ret[$j][$fn]=$res[$i];
						                 elseif ($type=='num') $ret[$j][$i]=$res[$i];
						           else {
							                echo "Invalid query type: $type";
							                exit();
							   }
					      }
				              $j++;
			      }
		 } elseif ($result == 2)$ret=$query;
	           else $ret=$this->rows;
	         if ($debug){
					Log::log(self::MSSQLDEBUG,	"Query:<pre>$q \n"
												."Result:\n" 
												.print_r($ret,true)."\n</pre>"
												."Rows: {$this->rows}<br />\n"
												."Fileds: {$this->fields}\n"
												
					);
		 }
		 @mssql_free_result($query);
		 return($ret);
       }


		//public function db_field_name($table, $debug = false)
		//{	$i=0;
			//$result = mysql_query("select * from $table LIMIT 1;",$this->db_link);
			//while ($i < mysql_num_fields($result)) 
			//{
				//$meta = mysql_fetch_field($result, $i);
				//if ($meta) 
				//{
					//$data[$i] = $meta->name;
////					$data[type][$i] = $meta->type;
					//$i++;
				//}
			//}
			
			//if ($debug){
				//Log::log(self::MYSQLDEBUG,	"Mysql Table Field Names: '$table'<pre>\n"
											//."Result:\n" 
											//.print_r($data,true)."\n</pre>"	
				//);
			//}
			//return $data;
		//}
		//public function db_field_type($table, $where ="")
		//{	$i=0;
			//$result = mysql_query("select * from $table LIMIT 1;",$this->db_link);
			//while ($i < mysql_num_fields($result)) 
			//{
				//$meta = mysql_fetch_field($result, $i);
				//if ($meta) 
				//{
					//$data[$i] = $meta->type;
					//$i++;
				//}
			//}
			
			//if ($debug){
				//Log::log(self::MYSQLDEBUG,	"Mysql Table Field Types: '$table'<pre>\n"
											//."Result:\n" 
											//.print_r($data,true)."\n</pre>"	
				//);
			//}
			//return $data;
		//}		
}
