<?php

//require_once("player.php"); //this class is included in player.php 

/*---------------------------------------------------------------

payout.php  - php class that includes functions
on the payouts mysql database


CREATE TABLE payouts (
108 id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
109 playerid INT(5) NOT NULL,
110 points INT(5) NOT NULL,
111 notes VARCHAR(255),
112 time TIMESTAMP(8) NOT NULL
113 );
---------------------------------------------------------------*/

//----------- Class Declaration  ------------------

class Payout
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
	public function Payout(){
	}


        /*---------------------------------
        *destruct() - called when no references remain
	*to the object.
        *---------------------------------*/
	public function __destruct(){
	}


	public function findPayoutByID($id) {
		$query = "SELECT * FROM payouts WHERE id=".$id;
		$db_returned = $this->queryDB($query);
	
		if($db_returned){
		
			return $db_returned;
		}
		return false;
	}

	public function getPayoutsByPlayerID($id){
	
		$query = "SELECT * FROM payouts WHERE playerid=".$id;
		$payouts = $this->queryDB($query);
		
		return $payouts;
	}


	public function makePayout($playerid, $points, $notes){
		//build insert query

		//build majority of insert query
		$query = "INSERT INTO payouts (playerid, points, notes, time) VALUES('$playerid', '$points', '$notes', NOW())";

		//attempt to insert into DB
		$success = $this->updateDB($query);

		if($success){
			$player = new Player();

			$success = $player->processPayout($playerid, $points);
		} 

		return $success;
				
	}
	
	
	private function queryDB($query) {
		$players_db=mysql_connect('localhost', $this->mysql_user, $this->mysql_user_pass) or die(mysql_error());
//var_dump(mysql_error());                
		if($players_db){
                        mysql_select_db("ironarena") or die(mysql_error());
                } else {
                        Die("Unable to connect to MYSQL!");
                }
		$db_result = mysql_query($query) or die(mysql_error());
		mysql_close($players_db);
		
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
		$players_db=mysql_connect('localhost', $this->mysql_user, $this->mysql_user_pass) or die(mysql_error());

                if($players_db){
                        mysql_select_db("ironarena") or die(mysql_error());
                } else {
                        Die("Unable to connect to MYSQL!");
                }

                $db_result = mysql_query($query) or die(mysql_error());
		
		mysql_close($players_db);

		return $db_result;
	}


}//end of class declaration

?>
