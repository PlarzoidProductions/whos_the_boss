<?php

require_once("include/classes/randomizer_class.php");
require_once("include/classes/page.php");

$form_action="randomizer/listCasters.php";
$form_method="post";

$factions=array("KHA"=>"Khador",
		"CYG"=>"Cygnar",
		"CRX"=>"Cryx",
		"POM"=>"Protectorate",
		"RET"=>"Retribution",
		"CON"=>"Convergence",
		"MRC"=>"Mercenaries",
		"COO"=>"Circle",
		"TRL"=>"Trolls",
		"SKO"=>"Skorne",
		"LOE"=>"Legion",
		"MIN"=>"Minions",
		"PPS"=>"Surprise!"
	);

$systems=array("W"=>"Warmachine", "H"=>"Hordes");
$surprise_rule=array("A"=>"Always available", "R"=>"Available once per round", "S"=>"Available once for entire event");

$page = new Page();

$page->register("apply_changes", "submit", array("value"=>"Apply Changes", "use_post"=>1));
$page->register("clear_all", "submit", array("value"=>"Clear All", "use_post"=>1));

$rc = new Casters();

$casters = $rc->getAllCasters();

$surprises = $rc->getSurprises();

if($page->submitIsSet("clear_all")){
	foreach($casters as $c){
		$rc->setCasterFree($c[id]);
	}

	foreach($surprises as $s){
		if($s['system']!='S'){
			$rc->setCasterFree($s[id]);
		}
	}

	unset($casters);
	$page->unregisterAll();
}

$casters = $rc->getAllCasters();
$surprises = $rc->getSurprises();

if($page->submitIsSet("apply_changes")){
	if(is_array($casters)){
		foreach($casters as $c){
		        $page->register("checkbox".$c[id], "checkbox", array("on_text"=>"In Use", "off_text"=>"Free",
	                                                                "use_post"=>1));
		}
	}

	if(is_array($surprises)){
                foreach($surprises as $su){
                        $page->register("checkbox".$su[id], "checkbox", array("on_text"=>"In Use", "off_text"=>"Free",
                                                                        "use_post"=>1));
                }
        }

        foreach($casters as $c){
                if($page->isChecked("checkbox".$c[id])){
			$rc->setCasterInUse($c[id]);
		} else {
			$rc->setCasterFree($c[id]);
		}
        }

	foreach($surprises as $su){
                if($page->isChecked("checkbox".$su[id]) && !($su[system]=='A')){
                        $rc->setCasterInUse($su[id]);
                } else {
                        $rc->setCasterFree($su[id]);
                }
        }


} else {

	if(is_array($surprises)){
        	foreach($surprises as $su){
        	        $page->register("checkbox".$su[id], "checkbox", array("on_text"=>"In Use", "off_text"=>"Free",
                	                                                       "default_val"=>($su[used] ? 1 : 0), "use_post"=>1));
        	}
	}

	foreach($casters as $c){
        	$page->register("checkbox".$c[id], "checkbox", array("on_text"=>"In Use", "off_text"=>"Free", 
         	                                                       "default_val"=>$c[used], "use_post"=>1));
	}
}


$page->setDisplayMode("form");

$page->startTemplate();

if($page->submitIsSet("apply_changes") || $page->submitIsSet("clear_all")){
	include("include/templates/success.tpl");
} else {
	include("include/templates/list_header.tpl");

	include("include/templates/list_caster_subheader.tpl");

	foreach($casters as $c){
	
		include("include/templates/list_caster.tpl");
	}

	if(is_array($surprises)){
        	include("include/templates/list_surprises.tpl");
	}

	include("include/templates/list_caster_subfooter.tpl");
}

$page->displayFooter();

?>
