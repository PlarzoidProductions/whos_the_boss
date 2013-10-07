<?php

/*---------------------------------------------------------------

game.php  - php class that includes functions
on the game mysql database


CREATE TABLE games (
 47 id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
 48 playerlist VARCHAR(255),
 49 sizelist VARCHAR(255),
 50 scenario BOOLEAN NOT NULL DEFAULT FALSE,
 51 teamgame BOOLEAN NOT NULL DEFAULT FALSE,
 52 newplayer BOOLEAN NOT NULL DEFAULT FALSE,
 53 newlocation BOOLEAN NOT NULL DEFAULT FALSE,
 54 fullypainted BOOLEAN NOT NULL DEFAULT FALSE
 55 );

---------------------------------------------------------------*/

//----------- Class Declaration  ------------------

class Game
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
	public function Game(){
	}


        /*---------------------------------
        *destruct() - called when no references remain
	*to the object.
        *---------------------------------*/
	public function __destruct(){
	}


	public function findGamesByID($id) {
		$query = "SELECT * FROM games WHERE id=$id";
		$db_returned = $this->queryDB($query);
	
		if($db_returned){
		
			return $db_returned;
		}
		return false;
	}

	public function getOpponentsByPlayerID($id){
	
		$query = "SELECT playerlist FROM games WHERE playerlist LIKE '%".$id."%'";
		$playerlist = $this->queryDB($query);
		//$playerlist = $playerlist[0];//strip off array wrapper

		if(!$playerlist){return array();}

		$opponent_list = array();

		foreach($playerlist as $pl){
			$players = explode("|", $pl['playerlist']);

			foreach($players as $p){
				if($p != $id){$opponent_list[$p] = $p;}
			}
		}
		
		return $opponent_list;
	}

	public function checkNewPlayerAward($player_list){

		$list_of_opponents = array();
		
		foreach($player_list as $player){
			$list_of_opponents[$player] = $this->getOpponentsByPlayerID($player);
		}


		foreach($player_list as $player){
			foreach($player_list as $player2){
				if($player != $player2){if(!in_array($player, array_keys($list_of_opponents[$player2]))){return "YES";}}
			}
		}
		return "NO";
	}
	
	public function getAllGames(){
		$query = "SELECT * FROM games";
		$db_returned = $this->queryDB($query);

	if($db_returned){
			return $db_returned;
		}
	
		return false;
	}
			

	public function createNewGame($playerlist, $sizelist, $scenario, $teamgame, $newplayer, $newlocation, $fullypainted){
		//build insert query

		$playerlist = implode($playerlist, '|');
		$sizelist = implode($sizelist, '|');

		if($scenario=="YES"){
			$scenario=1;
		} else {
			$scenario=0;
		}

		if($teamgame=="YES"){
                        $teamgame=1;
                } else {
                        $teamgame=0;
                }

		if($newplayer=="YES"){
                        $newplayer=1;
                } else {
                        $newplayer=0;
                }

		if($newlocation=="YES"){
                        $newlocation=1;
                } else {
                        $newlocation=0;
                }

		if($fullypainted=="YES"){
                        $fullypainted=1;
                } else {
                        $fullypainted=0;
                }

		//build majority of insert query
		$query = "INSERT INTO games (playerlist, sizelist, scenario, teamgame, newplayer, newlocation, fullypainted, gametime)
        				VALUES('$playerlist', '$sizelist', $scenario, $teamgame, $newplayer, $newlocation, $fullypainted, NOW())";

		//attempt to insert into DB
		$success = $this->updateDB($query);

		if($success){
			$confirm="SELECT id FROM games WHERE playerlist='$playerlist' AND sizelist='$sizelist' AND scenario='$scenario'AND
					teamgame='$teamgame'AND newplayer='$newplayer'AND newlocation='$newlocation'AND fullypainted='$fullypainted'";

			$gid = $this->queryDB($confirm);
			
		return $gid;
		}

		return false;
	}
	
	public function getGamesByPlayerID($id){

		$query = "SELECT * FROM games WHERE playerlist LIKE '%$id%'ORDER BY gametime";

		return $this->queryDB($query);
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

                $db_result = mysql_query($query) or die(mysql_error());;
		
		mysql_close($players_db);

		return $db_result;
	}


}//end of class declaration

?>
