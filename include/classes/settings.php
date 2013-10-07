<?php

/*---------------------------------------------------------------

settings.php  - php class that includes functions
on the settings mysql database

CREATE TABLE settings (
name VARCHAR(50) NOT NULL PRIMARY KEY,
played25 INT(5) NOT NULL DEFAULT 0,
played35 INT(5) NOT NULL DEFAULT 0,
played50 INT(5) NOT NULL DEFAULT 0,
played75 INT(5) NOT NULL DEFAULT 0,
numgames INT(5) NOT NULL DEFAULT 0,
numplayers INT(5) NOT NULL DEFAULT 0,
numlocations INT(5) NOT NULL DEFAULT 0,
numfactions INT(5) NOT NULL DEFAULT 0,
vsstaff INT(5) NOT NULL DEFAULT 0,
event1 INT(5) NOT NULL DEFAULT 0,
event2 INT(5) NOT NULL DEFAULT 0,
event3 INT(5) NOT NULL DEFAULT 0,
event4 INT(5) NOT NULL DEFAULT 0,
event5 INT(5) NOT NULL DEFAULT 0,
event6 INT(5) NOT NULL DEFAULT 0,
event7 INT(5) NOT NULL DEFAULT 0,
event8 INT(5) NOT NULL DEFAULT 0,
event9 INT(5) NOT NULL DEFAULT 0,
event10 INT(5) NOT NULL DEFAULT 0,
event1name VARCHAR(255),
event2name VARCHAR(255),
event3name VARCHAR(255),
event4name VARCHAR(255),
event5name VARCHAR(255),
event6name VARCHAR(255),
event7name VARCHAR(255),
event8name VARCHAR(255),
event9name VARCHAR(255),
event10name VARCHAR(255),
gametimelimit VARCHAR(20)
);


---------------------------------------------------------------*/

//----------- Class Declaration  ------------------

class Settings
{

	/*----------------------------------------
	*
	* properties (variables)
	*
	*---------------------------------------*/

	//mysql credentials
	private $mysql_user='ironarenauser';
	private $mysql_user_pass='iwantskullz';

	//boolean on status of connection to mysql
	public $connected=0;  


        /*---------------------------------
        *construct() - called when class is 
        *instantiated into an object
        *---------------------------------*/
	public function Settings(){
	}


        /*---------------------------------
        *destruct() - called when no references remain
	*to the object.
        *---------------------------------*/
	public function __destruct(){
	}


	public function getRawSettings() {
                $query = "SELECT * FROM settings";
                $s = $this->queryDB($query);

		return $s;
	}

	public function getSettings() {
		$query = "SELECT * FROM settings";
		$s = $this->queryDB($query);
		$s = $s[0];

	/*################################################################
		# re-work codes into keyed arrays:
		#
		# 5,1;10;2;15,3  should turn into
		#
		# array( [5]=>1, [10]=>2, [15]=>3 )
		#
		################################################################*/

		$coded_settings = array('numgames', 'numplayers', 'numlocations', 'numfactions');
		foreach($coded_settings as $cs){	
			$codes = explode(";", $s[$cs]);
			unset($s[$cs]);
			$s[$cs] = array();

			foreach($codes as $c){
				$temp = explode(",", $c);
				$s[$cs][$temp[0]] = $temp[1];
			}
		}

		if($s){
		
			return $s;
		}
		return false;
	}

	public function getName(){
		$query = "SELECT name FROM settings";
		$db_returned = $this->queryDB($query);

		if($db_returned){
			return $db_returned;
		}
		return false;
	}
	
	public function makeSettings($settings){
		$setting_fields=array('name', 'teamgame', 'newopponent', 'outofstate', 'fullypainted', 'scenariotable',
			'played25', 'played35', 'played50', 'played75', 'played100', 'playedall',
			'numgames', 'numplayers', 'numfactions', 'numlocations', 'numfactions', 'vsstaff',
			'event1', 'event2', 'event3', 'event4', 'event5', 'event6', 'event7', 'event8', 'event9', 'event10',
			'event1name', 'event2name', 'event3name', 'event4name', 'event5name', 'event6name', 'event7name', 
			'event8name', 'event9name', 'event10name', 'gametimelimit'); 
 
		foreach(array_keys($settings) as $key){
			if(in_array($key, $setting_fields)){
				//loop through settings and apply them
				$query = "UPDATE settings SET $key='$settings[$key]'";
var_dump($query);
				//attempt to insert into DB
				$success = $this->updateDB($query);
			} else {
				return "Invalid Key encountered: $key.";
			}
		}

		return true;
	}
		
	private function queryDB($query) {
		$settings_db=mysql_connect('localhost', $this->mysql_user, $this->mysql_user_pass) or die(mysql_error());
		
		if($settings_db){
                        mysql_select_db("ironarena") or die(mysql_error());
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
		$settings_db=mysql_connect('localhost', $this->mysql_user, $this->mysql_user_pass) or die(mysql_error());

                if($settings_db){
                        mysql_select_db("ironarena") or die(mysql_error());
                } else {
                        Die("Unable to connect to MYSQL!");
                }

                $db_result = mysql_query($query) or die(mysql_error());
		
		mysql_close($settings_db);

		return $db_result;
	}


}//end of class declaration

?>
