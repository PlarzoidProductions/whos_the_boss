<?php

require_once("include/classes/randomizer_class.php");
require_once("include/classes/page.php");

$faction_logos=array("KHA"=>"khador",
                "CYG"=>"cygnar",
                "CRX"=>"cryx",
                "POM"=>"the-protectorate-of-menoth",
                "RET"=>"the-retribution-of-scyrah",
                "MRC"=>"mercenaries",
				"CON"=>"convergence",
                "COO"=>"circle-orboros",
                "TRL"=>"trollbloods",
                "SKO"=>"skorne",
                "LOE"=>"legion-of-everblight",
                "MIN"=>"minions",
		"PPS"=>"privateer-logo"
        );

$systems=array("W"=>"Warmachine", "H"=>"Hordes", "A"=>"Surprise!", "S"=>"Surprise!", "R"=>"Surprise!");

$page = new Page();

$page->register("keep", "submit", array("value"=>"Keep This Caster", "use_post"=>1));
$page->register("new", "submit", array("value"=>"Spin Again", "use_post"=>1));
$page->register("spinner", "submit", array("value"=>"    ", "use_post"=>1, "style"=>"spinner"));
$page->register("autospin", "hidden", array("use_post"=>1));

$rc = new Casters();

$page->register("caster_id", "hidden", array("use_post"=>1));

if($page->submitIsSet("keep")){
	$rc->setCasterInUse($page->getVar("caster_id")+0); 
} 

if($page->submitIsSet("spinner") || ($page->getVar("autospin") == "true")){
	$casters = $rc->getFreeCasters();
	if(is_array($casters)){
		$chosen=array_rand($casters);
	} else {
		$chosen=NULL;
	}
	$template="include/templates/announce.tpl";
} else {
	$template="include/templates/spinner.tpl";
}

if($page->submitIsSet("new")){
	$reload=true;
} else {
	$reload=false;
}

$page->setDisplayMode("form");

$page->startTemplate();
include($template);
$page->displayFooter();

?>
