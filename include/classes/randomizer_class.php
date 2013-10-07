<?php

/*---------------------------------------------------------------

randomizer_class.php  - php class that includes functions
on the caster mysql database

  5 CREATE TABLE casters (
  6 id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  7 name VARCHAR(40) NOT NULL,
  8 system ENUM('W', 'H') DEFAULT 'W',
  9 faction ENUM('CYG', 'KHA', 'POM', 'CRX', 'RET', 'MRC', 'TRL', 'LOE', 'SKO', 'COO', 'MIN'),
 10 used BOOLEAN NOT NULL DEFAULT 0
 11 );


---------------------------------------------------------------*/

//----------- Class Declaration  ------------------

class Casters
{

	/*----------------------------------------
	*
	* properties (variables)
	*
	*---------------------------------------*/

	//mysql credentials
	private $mysql_user='random_user';
	private $mysql_user_pass='eeneymeeneymineymo';
	private $mysql_host='localhost';//'rc.plarzoid.com';
	private $mysql_table='casters';
	private $mysql_database='randomizer';

	//boolean on status of connection to mysql
	public $connected=0;  


        /*---------------------------------
        *construct() - called when class is 
        *instantiated into an object
        *---------------------------------*/
	public function Casters(){
	}


        /*---------------------------------
        *destruct() - called when no references remain
	*to the object.
        *---------------------------------*/
	public function __destruct(){
	}


	public function getAllCasters() {
                $query = "SELECT * FROM ".$this->mysql_table." where system='W' or system='H'";
                $s = $this->queryDB($query);

		return $s;
	}

	public function getFreeCasters() {
                $query = "SELECT * FROM ".$this->mysql_table." WHERE used='N'";
                $s = $this->queryDB($query);
                
		return $s;
        }

	public function getSurprises(){

		$query = "SELECT * FROM ".$this->mysql_table." where not system='W' and not system='H' order by system";
                $s = $this->queryDB($query);

                return $s;

	}

	public function insertCaster($name, $faction, $system) {
		$query = "INSERT INTO ".$this->mysql_table." ( name, faction, system ) VALUES (".$name.", "."$faction".", "."$system".")";
		$result = $this->updateDB($query);

		return $result;
	}

	public function purgeSelf() {
		$query = "DELETE FROM ".$this->mysql_table;

		$result = $this->updateDB($query);

                return $result;
	}

	public function setCasterInUse($id) {
		$query = "UPDATE ".$this->mysql_table." SET used='Y' WHERE id=".$id;

		$result = $this->updateDB($query);

                return $result;			
	}

	public function setCasterFree($id) {
                $query = "UPDATE ".$this->mysql_table." SET used='N' WHERE id=".$id;

                $result = $this->updateDB($query);

                return $result;
        }
		
	private function queryDB($query) {
		$settings_db=mysql_connect($this->mysql_host, $this->mysql_user, $this->mysql_user_pass) or die(mysql_error());
		
		if($settings_db){
                        mysql_select_db($this->mysql_database) or die(mysql_error());
                } else {
                        Die("Unable to connect to MYSQL!");
                }
		$db_result = mysql_query($query) or die(mysql_error());
		mysql_close($settings_db);
		
		//if a boolean was returned, kick it back now
		if(is_bool($db_result)){ return $db_result; }

		//else assume we have something to get
		$row = mysql_fetch_array($db_result);

		//repeat getting results as necessary, pumping them into a single master array
		while ($row){
			$ret[] = $row;
			$row = mysql_fetch_array($db_result);
		}	

		//only return it if we have a valid array
		if(is_array($ret)){
			return $ret;
		}

		//if all else fails, return false
		return false;
	}

	private function updateDB($query) {
		$settings_db=mysql_connect($this->mysql_host, $this->mysql_user, $this->mysql_user_pass) or die(mysql_error());

                if($settings_db){
                        mysql_select_db($this->mysql_database) or die(mysql_error());
                } else {
                        Die("Unable to connect to MYSQL!");
                }

                $db_result = mysql_query($query) or die(mysql_error());
		
		mysql_close($settings_db);

		return $db_result;
	}


}//end of class declaration

?>
