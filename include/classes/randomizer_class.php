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

require_once("query.php");

class Casters
{

	/*----------------------------------------
	*
	* properties (variables)
	*
	*---------------------------------------*/

	//mysql credentials
	private $mysql_table='casters';

	private $db = null;

        /*---------------------------------
        *construct() - called when class is 
        *instantiated into an object
        *---------------------------------*/
	public function Casters(){
		$this->db = Query::getINstance();
	}


        /*---------------------------------
        *destruct() - called when no references remain
	*to the object.
        *---------------------------------*/
	public function __destruct(){
	}


	public function getAllCasters() {
                $query = "SELECT * FROM ".$this->mysql_table." where system='W' or system='H'";
                $s = $this->db->query($query, array());

		return $s;
	}

	public function getFreeCasters() {
                $query = "SELECT * FROM ".$this->mysql_table." WHERE used='N'";
                $s = $this->db->query($query, array());
                
		return $s;
        }

	public function getSurprises(){

		$query = "SELECT * FROM ".$this->mysql_table." where not system='W' and not system='H' order by system";
                $s = $this->db->query($query, array());

                return $s;

	}

	public function insertCaster($name, $faction, $system) {
		$query = "INSERT INTO ".$this->mysql_table." ( name, faction, system ) VALUES (:name, :faction, :system)";
		$values = array(":name"=>$name, ":faction"=>$faction, ":system"=>$system);
		
		var_dump($values);
		$result = $this->db->insert($query, $values);

		return $result;
	}

	public function purgeSelf() {
		$query = "DELETE FROM ".$this->mysql_table;

		$result = $this->db->delete($query, array());

        return $result;
	}

	public function setCasterInUse($id) {
		$query = "UPDATE ".$this->mysql_table." SET used='Y' WHERE id=:id";

		$result = $this->db->update($query, array(":id"=>$id));

        return $result;			
	}

	public function setCasterFree($id) {
         $query = "UPDATE ".$this->mysql_table." SET used='N' WHERE id=:id";

         $result = $this->db->update($query, array(":id"=>$id));

         return $result;
    }



}//end of class declaration

?>
