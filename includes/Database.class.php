<?php
/**
   * Account
   * 
   * @author  Tanvir Shaikat <ta.shaikat@gmail.com>
   */
class Database
{
	#DB CONNECT
	function connect($db){
		$id = @mysql_connect($db['host'] , $db['user'] , $db['pass']) or die("<h1>Error establishing a database connection</h1>");
		@mysql_select_db($db['name'] , $id) or die("<h1>Error establishing a database connection</h1>");
		return $id;	
	}
	#DB SELECT
    public function select($table, $rows = '*', $where = null, $order = null, $limit=null)
    {
        $q = 'SELECT '.$rows.' FROM '.$table;
        if($where != null) $q .= ' WHERE '.$where;
        if($order != null) $q .= ' ORDER BY '.$order;
		if($limit != null) $q .= ' LIMIT '.$limit;
        $query = @mysql_query($q);
        if($query) {
			if(mysql_num_rows($query)) {
				$this->numResults = mysql_num_rows($query);
				for($i = 0; $i < $this->numResults; $i++) {
					$r = mysql_fetch_array($query);
					$key = array_keys($r);
					for($x = 0; $x < count($key); $x++) 
						if(!is_int($key[$x])) {
							if(mysql_num_rows($query) > 1)
								$this->result[$i][$key[$x]] = $r[$key[$x]];
							else if(mysql_num_rows($query) < 1)
								$this->result = null;
							else 
								$this->result[1][$key[$x]] = $r[$key[$x]];
						}
				}
				return true;
			}
			else return false;
        }
        else return false;
    }
	public function ClearResult(){
		$this->result = null;
	}
	public function getResult(){
		return $this->result;
		ClearResult();
	}
	#DB INSERT
	function insert($table,$rows,$values) {
		$insert = 'INSERT INTO '.$table.' ('.$rows.')';
		for($i = 0; $i < count($values); $i++)
	   		if(is_string($values[$i]))
	        	$values[$i] = '"'.mysql_real_escape_string(str_replace("\n", " ",$values[$i])).'"';
	    $values = implode(',',$values);
	    $insert .= ' VALUES ('.$values.')';
	    if(@mysql_query($insert)) return true;
	    	else return false;
	}
	#DB UPDATE
	function update($table,$rows,$where=null,$limit = null) {
		$update = 'UPDATE '.$table.' SET ';
	  	$keys = array_keys($rows);
	   	for($i = 0; $i < count($rows); $i++) {
			if(is_string($rows[$keys[$i]]))
	    		$update .= $keys[$i].'="'.mysql_real_escape_string(str_replace("\n", " ",$rows[$keys[$i]])).'"';
	        else
				$update .= $keys[$i].'='.$rows[$keys[$i]];
	        if($i != count($rows)-1)
				$update .= ',';
		}
		if($where!=null)
	    	$update .= ' WHERE '.$where;
		if($limit!= null)
	    	$update .= ' LIMIT '.$limit;
	    if(@mysql_query($update)) return true;
	        else return false;
	}
	#DB DELETE
	function delete($table,$where = null,$limit = null) {
		$delete = 'DELETE FROM '.$table;
	    if($where!= null)
	    	$delete .= ' WHERE '.$where;
		if($limit!= null)
	    	$delete .= ' LIMIT '.$limit;
		if(@mysql_query($delete))
	    	return true;
	    else
	    	return false;
	}
}
?>