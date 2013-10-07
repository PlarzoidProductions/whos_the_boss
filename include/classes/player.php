<?php

require_once("payout.php");
require_once("game.php");

/*---------------------------------------------------------------

player.php  - php class that includes functions
on the player, and players mysql database


  7 CREATE TABLE players (
  8 id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  9 firstname VARCHAR(20) NOT NULL,
 10 lastname VARCHAR(20) NOT NULL,
 11 location VARCHAR(20) NOT NULL,
 12 forumname VARCHAR(20) NOT NULL,
 13 email VARCHAR(30) NOT NULL,
 14 creationdate TIMESTAMP(8) DEFAULT NOW(),
 15 lastgamedate TIMESTAMP(8),
 16 points INT(5) NOT NULL DEFAULT 0,
 17 staff BOOLEAN NOT NULL DEFAULT FALSE,
 18 played25 BOOLEAN NOT NULL DEFAULT FALSE,
 19 played35 BOOLEAN NOT NULL DEFAULT FALSE,
 20 played50 BOOLEAN NOT NULL DEFAULT FALSE,
 21 played75 BOOLEAN NOT NULL DEFAULT FALSE,
 22 played100 BOOLEAN NOT NULL DEFAULT FALSE,
 23 playedall BOOLEAN NOT NULL DEFAULT FALSE,
 24 numgames INT(5) NOT NULL DEFAULT 0,
 25 numplayers INT(5) NOT NULL DEFAULT 0,
 26 locationlist VARCHAR(255) NOT NULL DEFAULT "NONE",
 27 factionlist VARCHAR(50) DEFAULT "NONE",
 28 vsstaff BOOLEAN NOT NULL DEFAULT FALSE,
 29 event1 BOOLEAN NOT NULL DEFAULT FALSE,
 30 event2 BOOLEAN NOT NULL DEFAULT FALSE,
 31 event3 BOOLEAN NOT NULL DEFAULT FALSE,
 32 event4 BOOLEAN NOT NULL DEFAULT FALSE,
 33 event5 BOOLEAN NOT NULL DEFAULT FALSE,
 34 event6 BOOLEAN NOT NULL DEFAULT FALSE,
 35 event7 BOOLEAN NOT NULL DEFAULT FALSE,
 36 event8 BOOLEAN NOT NULL DEFAULT FALSE,
 37 event9 BOOLEAN NOT NULL DEFAULT FALSE,
 38 event10 BOOLEAN NOT NULL DEFAULT FALSE
 39 );

---------------------------------------------------------------*/

//----------- Class Declaration  ------------------

class Player
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
	public function Player(){
	}


        /*---------------------------------
        *destruct() - called when no references remain
	*to the object.
        *---------------------------------*/
	public function __destruct(){
	}


	public function findPlayerByID($id) {
		$query = "SELECT * FROM players WHERE id=$id";
		$db_returned = $this->queryDB($query);
	
		if($db_returned){
		
			return $db_returned;
		}
		return false;
	}
	
	public function getAllPlayers(){
		$query = "SELECT * FROM players ORDER BY lastname";
		$db_returned = $this->queryDB($query);
		
		if($db_returned){
			return $db_returned;
		}
	
		return false;
	}
			
	public function getOpponents($list, $id){
		$ret = array();

		$list = explode("|", $list);		

		foreach($list as $p){
			if($p!=$id){
				$opponent = $this->findPlayerByID($p);

				$ret[] = $opponent[0][lastname].", ".$opponent[0][firstname];
			}
		}

		return $ret;
	}


	public function playerExists($firstname, $lastname){
		$query = "Select * FROM players WHERE firstname='$firstname' AND lastname='$lastname'";
		$result = $this->queryDB($query);

		if($result){
			return true;
		}

		return false;
	}
        

	public function createNewPlayer($firstname, $lastname, $location, $forumname, $email, $code, $staff){
		//build insert query

		//build majority of insert query
		$query = "INSERT INTO players (firstname, lastname, location, forumname, email, code, staff)
        				VALUES('$firstname', '$lastname', '$location', '$forumname', '$email', $code, ";

		//add admin flag
		if($staff){
			$query .= "TRUE);";
		} else{ 
			$query .= "FALSE);";
		}
//var_dump($query);		
		//attempt to insert into DB
		$success = $this->updateDB($query);
		
		return $success;
	}
	
	public function getPlayerLocationByPlayerID($id){
		$query = "Select location FROM players WHERE id=$id";
		$result = $this->queryDB($query);

                return $result[0][0];
        }

        public function updatePlayers($player_list, $size_list, $faction_list, $newplayer, $outofstate){

		$n=0;

		/*########################################################
                # handle vsstaff flag for opponents if current player is staff
                ########################################################*/

		foreach(array_keys($player_list) as $k){
			$pinfo = $this->findPlayerByID($player_list[$k]);
			$pinfo = $pinfo[0];
 
			if($pinfo['staff']){//if current player is staff
                                $opponent_list = $player_list;//make a copy of all players at the table
                                unset($opponent_list[$key]);//toss out current player

                                foreach($opponent_list as $o_id){
                                        $this->updateDB("UPDATE players SET vsstaff=1 WHERE id=".$o_id);
                                }
                                unset($opponent_list);
                        }
		}
			
		/*###############################################################################
		#loop through all the players in the game, updating flags and calculating points from scratch
		###############################################################################*/
		foreach(array_keys($player_list) as $key){

			//get the personal info for the player
			$playerinfo = $this->findPlayerByID($player_list[$key]);
			$playerinfo = $playerinfo[0]; //strip off array wrapper
			
			/*#########################################
			#  Add opponent factions to faction list
			#########################################*/
			//get the list of current user's previously fought against factions
			if($playerinfo[factionlist]=="NONE"){	//if none, set everything to null / 0
				$previous_factions = NULL;
				$prev_fact_count = 0;
			} else {		//else, explode the psv array and flip it, so factions are keys
				$previous_factions = array_flip(explode("|", $playerinfo[factionlist]));
				$prev_fact_count = count($previous_factions);  //get a count of previously played factions
			}

			//store the factions from the current game for easier use
			$game_factions = $faction_list;
			unset($game_factions[$key]);  //toss out the current player's faction, since it doesn't count

			//loop through the remaining factions, and add them as keys to the array
			foreach($game_factions as $faction){
				$previous_factions[$faction]=1;
			}

			$new_fact_count = count($previous_factions);
			
			$new_faction_list = implode(array_flip($previous_factions), "|");

			/*###########################################
			# Add opponent locations to location list
			###########################################*/
			//build list of locations of all players in game
			foreach(array_keys($player_list) as $loc_key){
                	        $location_list[$loc_key] = $this->getPlayerLocationByPlayerID($player_list[$loc_key]);
                	}

			//get the list of current user's previous opponent locations
                        if($playerinfo[locationlist]=="NONE"){   //if none, set everything to null / 0
                                $previous_locations = NULL;
                                $prev_loc_count = 0;
                        } else {                //else, explode the psv array and flip it, so locations are keys
                                $previous_locations = array_flip(explode("|", $playerinfo[locationlist]));
                                $prev_loc_count = count($previous_locations);  //get a count of previously played factions
                        }
			
			//toss out the current player's location, since it doesn't count
			unset($location_list[$key]);

                        //loop through the remaining factions, and add them as keys to the array
                        foreach($location_list as $l){
                                $previous_locations[$l]=1;
                        }

                        $new_loc_count = count($previous_locations);

                        $new_location_list = implode(array_flip($previous_locations), "|");
			//trash the variable so it's not messing up the rest of the players
			unset($location_list);
		
			/*########################################################
			// begin sql query to update player database
			########################################################*/
			$sql = "UPDATE players SET played".$size_list[$key]."=1, lastgamedate=NOW(), numgames=numgames+1";

			if($newplayer){$sql.=", numplayers=numplayers+1";}

			if($new_loc_count != $prev_loc_count){$sql.=", locationlist='$new_location_list'";}

			if($new_fact_count != $prev_fact_count){$sql.=", factionlist='$new_faction_list'";}

			$sql.=" WHERE id=".$player_list[$key][id];
			//var_dump($sql);
			
			//update database
			$success = $this->updateDB($sql);

			//keep count for any errors
			if($success){
				$points = $this->getPointsByPlayerID($player_list[$key][id]);
				$sql2 = "UPDATE players SET points=$points WHERE id=".$player_list[$key][id];
				$success2 = $this->updateDB($sql2);
				
				if($sql2){
					$n = $n + 1;
				}
			}

			//destroy temporary variables
			unset($sql);
			unset($playerinfo);
			unset($points);
			
		}
                return $n;
        }

	public function updatePoints($pid){

		$pts = $this->getPointsByPlayerID($pid);

		$query = "UPDATE players SET points=$pts WHERE id=".$pid;

                return $this->updateDB($query);
	}

	public function getPointsByPlayerID($id){

		//pull settings from database
		$s_db = new Settings();
		$s = $s_db->getSettings();//does not have an array wrapper

		//pull player info
		$p = $this->findPlayerByID($id);
		$p = $p[0];  //strip off array wrapper
		$p['numlocations'] = count(explode("|", $p['locationlist']));
		$p['numfactions'] = count(explode("|", $p['factionlist']));


		//variable to hold points total
		$points = 0;

		/*#####################################################################
		# First, check for each game level
		#####################################################################*/
		$sizes = array('played25', 'played35', 'played50', 'played75', 'played100', 'playedall');

		foreach($sizes as $size){
			if($p[$size]){$points += ($s[$size]+0);}
		}

		/*#####################################################################
                # Next, run through each of the coded sections
                #####################################################################*/
                $codes = array('numgames', 'numplayers', 'numlocations', 'numfactions');

                foreach($codes as $code){
                        foreach($s[$code] as $level=>$award){
				if($p[$code] >= ($level+0)){
					$points += ($award+0);
				}
			}
                }

		//check for the vsstaff bonus
		if($p['vsstaff']){$points += ($s['vsstaff']+0);}

		/*######################################################################
		# check the rest of the one-time only bonuses, event1-event10 
		#####################################################################*/
		for($k=1; $k<10; $k++){
			if($p['event'.$k]){$points += ($s['event'.$k]+0);}
		}

		
		/*######################################################################
                # run through all the games for the player, and extract 
		# fully painted, scenario, new opponent and team game bonuses 
                #####################################################################*/
                $g_db = new Game();

		$games = $g_db->getGamesByPlayerID($id);
	
		//initialize some counters
		if(!empty($games)){
			foreach($games as $g){
                        	if($g['scenario']){$points += ($s['scenariotable']+0);}
				if($g['fullypainted']){$points += ($s['fullypainted']+0);}
				if($g['teamgame']){$points += ($s['teamgame']+0);}
				if($g['newlocation']){$points += ($s['outofstate']+0);}
				if($g['newplayer']){$points += ($s['newopponent']+0);}
           	     }
		}

		/*######################################################################
                # run through all the point redemptions for the player, and  
                # subtract those points from what they have accumulated 
                #####################################################################*/
                $payout_db = new Payout();

                $redemptions = $payout_db->getPayoutsByPlayerID($id);

                //run through the payouts
                if(!empty($redemptions)){
			foreach($redemptions as $r){
                        	$points -= $r['points'];
                	}
		}


		return $points;

	}

	public function setEvent($event, $pid, $flag){
		$query = "UPDATE players SET ".$event."=";
		
		if($flag){
			$query.=1;
		} else {
			$query.=0;
		}

		$query.=" WHERE id=".$pid;

		return $this->updateDB($query);
	}

        public function processPayout($id, $pts){
                $query = "UPDATE players SET points-=".$pts;
                
                return $this->updateDB($query);
	}        

	private function queryDB($query) {
		$players_db=mysql_connect('localhost', $this->mysql_user, $this->mysql_user_pass) or die(mysql_error());

		if($players_db){
                        mysql_select_db("ironarena") or die(mysql_error());
                } else {
                        Die("Unable to connect to MYSQL!");
                }

//var_dump($query);

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
