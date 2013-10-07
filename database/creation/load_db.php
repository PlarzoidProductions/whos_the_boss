<?php

require_once("../../include/classes/randomizer_class.php");

//**********************************
//
// load db with values
//
//**********************************

$fptr = fopen("caster_master_list.csv", 'r');

$casters = array();

$line = fgets($fptr);

while($line){
  $line = trim($line);
  $casters[]=preg_split("/\|/", $line);
  $line = fgets($fptr);
}

fclose($fptr);

$rc = new Casters();

$rc->purgeSelf();

foreach($casters as $c){

  $rc->insertCaster($c[0], $c[1], $c[2]);

}


?>
