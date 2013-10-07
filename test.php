<?php

require_once("include/classes/page.php");

$page = new Page();

$page->register("spinner", "submit", array("value"=>"img_test", "style"=>"spinner", "use_post"=>1));
$page->setDisplayMode("form");
$page->startTemplate();

include("include/templates/PP_content.tpl");

$page->displayFooter();
?>
