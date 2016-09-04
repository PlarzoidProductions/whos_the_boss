<?php

require_once("session.php");
require_once("check.php");
require_once("choices.php");

class Page {

    var $sysClass;
    var $authLevel;
    var $vars;
    var $root=null;

    var $version = "v1.0.2";

    public function Page($authentication_level="Public", $pageid=false, $title=false) {
        Session::init();

        //set the webroot
        $this->servername = $_SERVER["HTTP_HOST"];

        if (preg_match("/\/~(\w+)\//", $_SERVER["PHP_SELF"], $matches)) {
            $this->root = "/~" . $matches["1"] . "/";
        } else {
            //$this->root = "/";
        }

        //set the vars array to empty
        $vars = array();

        //set internal authority level
        switch((string)$authentication_level) {
            case "Public"    : 
            case "public"    : $this->authLevel="PUBLIC";
                break;
            case "Admin"    :
            case "admin"    : $this->authLevel="ADMIN";
                break;
            default: $this->authLevel=$authentication_level;
                break;
        }//switch($authentication_level) {

        //authentication checking
        if($this->isNotAuthorized()){
            $this->startTemplate();
            include("include/templates/authError.html");    
            $this->displayFooter();
            exit;
        }
    }

    function isNotAuthorized(){
        //returns true if user is NOT authorized
        //returns false if they are

        //if, a public page, always authorized
        if(!strcmp($this->authLevel, "PUBLIC")){return false;}//strcmp returns 0 on a match

        //for the rest of the tests, a user must be logged in, so stop now if we're not logged in
        if(!Session::isLoggedIn()){return true;}
    
        //if page is admin page, check for admin logged in
        if(!strcmp($this->authLevel,"ADMIN")){
            if(Session::isNotAdmin()){
                //echo "You must be an administrator to view this page!";
                return true;
            }
        }


        //at this point, it's not a public or admin page,
        //so check user's auth_level vs page's authLevel
        if(!Session::isAuthorized($this->authLevel)){
            //echo "You are not authorized to view this page!";
            return true;
        }

        //user fell through all the traps above, so is authorized.
        return false;
    }

    function getWebRoot(){
        return $this->root;
    }

    function getVersion(){
        return $this->version;
    }

    function printView() {
        if ($this->print == "Y") return true;
        else return false;
    }

    function startTemplate($meta=NULL) {

        include("include/templates/default_header.tpl");
    }


    function doTabs(){

        $tabs = array(
            "Register Player"=>"register_player",
            "View Player Profile"=>"view_player",
            "Report Game"=>"report_game",
            "Redeem Skulls"=>"redeem",
            "Event Achievements"=>"batch_processing",
            "Software Feedback"=>"feedback"
            );


        if(Session::isAdmin()){
            $admin_tabs = array(
                "Bulk Registration"=>"bulk_registration",
				"Leaderboard"=>"leaderboard",
                "Manage Users"=>"manage_users",
                "General Configuration"=>"general_config",
                "Achievement Configuration"=>"achievement_config",
                "Export & Reset Database"=>"export_reset"
            );

        }

        $view = $_REQUEST[view];

        include("templates/default_aside.html");
    }


    function close($noheader=false) {
        $this->displayFooter($noheader);
        $this->closeDatabase();
    }
    
    function displayFooter($noheader=false) {

        include("include/templates/default_footer.tpl");

    }
    
    function closeDatabase() {
    }
    
    function pageName() {
        return $_SERVER["PHP_SELF"];
    }

    //HTML FORM Functions
    function register($varname, $type, $attributes=array()) {

        //first, first, add the damn use_post
        $attributes["use_post"]=1;

        //first, check that $type and $attributes are set up correctly
        //optional attr args: check_func (can be "none")
        //              check_func_args (additional args passed to ceck_func)
        //              error_message (required if there is a check_func)
        //              setget (part of set and get functions after "set" or "get"
        //              on_text, off_text (used only for "text view of checkbos)
        //              value, used for submit buttons
        //              filedir (used only for file type)

        switch($type) {
            case "file":     
                if(!Check::arrayKeysFormat(array("filedir", "filedir_webpath"), $attributes)) return false;
                if(!preg_match("/\/$/", $attributes["filedir"])) $attributes["filedir"] .= "/";
				break;
            case "reset":
            case "submit":    
                if(!Check::arrayKeysFormat(array("value"), $attributes)) return false;
                break;
            case "checkbox":
                if(!Check::arrayKeysFormat(array("on_text", "off_text"), $attributes)) return false;
                $attributes["value"]=1;
                break;
            case "checkbox_array";
            case "radio":    
                if(!Check::arrayKeysFormat(array("get_choices_array_func"), $attributes)) return false;
                break;
            case "textbox":
            case "textarea":
            case "hidden":
            case "password":
            case "select":
                break;

            //New HTML5 input types

            case "tel":
                break;
            case "number":
            case "range":
                if(!Check::arrayKeysFormat(array("min", "max", "step"), $attributes)) return false;
                break;
            case "date":
            case "time":
            case "week":
            case "month":
            case "datetime":
            case "color":
            case "email":
            case "search":
            case "url":
                break;
            default:
                return false;
                break;
        }
       
        //Override the registered default value with the returned one, if it's there
        if(array_key_exists("use_post", $attributes) && $attributes["use_post"]){
            if(in_array($varname, array_keys($_POST)) && !empty($_POST[$varname])){
                $attributes["default_val"] = $_POST[$varname];
            }
        } else {
            if(in_array($varname, array_keys($_REQUEST)) && !empty($_REQUEST[$varname])){
                $attributes["default_val"] = $_REQUEST[$varname];
            }
        }

        //Handle select function names and inputs
        if($type == "select" || $type == "radio") { //check_func is always validSelect
            $attributes["check_func"] = "validSelect";
            $attributes["check_func_args"] = array($attributes["get_choices_array_func"], 
                                                 $attributes["get_choices_array_func_args"]);
        }

        //put form var into global scope
        global $$varname;

        //Snag POST values
	if(array_key_exists("use_post", $attributes) 
            && $attributes["use_post"]
            && in_array($varname, array_keys($_POST))){
	        
            $$varname = trim($_POST[$varname]);

        //Snag REQUEST values
	} else if(in_array($varname, array_keys($_REQUEST))){
	    $$varname = trim($_REQUEST[$varname]);
	
        //else, set to null
        } else {
            $$varname = null;
        }

        //Store type into the attributes array for use later
        if(empty($attributes)){
            $attributes = array("type"=>$type);
        } else {
            if(!array_key_exists("type", $attributes)){
                $attributes["type"] = $type;
            }
        }

        //Add the form var to the stored list of form vars
        $this->vars[$varname] = $attributes;

        return true;
    }

    function unregister($varname) {
        global $$varname;
        unset($$varname);
        unset($this->vars[$varname]);
    }

    function submitIsSet($submitvar_name) {
        global $$submitvar_name;
        if (array_key_exists($submitvar_name, $this->vars) && 
           ($$submitvar_name == $this->vars["$submitvar_name"]["value"])) 
            return true;
        
        return false;
    }

    function setDisplayMode($mode) {
        $this->disp_mode = $mode;
    }

        
    //set disp_type to either "form" or "success"
    function displayVar($varname, $disp_type = false, $args = array()) {

        if ($disp_type == false) {
            if (!$this->disp_mode) {
                $disp_type = "form";
            } else {
                $disp_type = $this->disp_mode;
            }
        }

        //extract the type from the attributes array
        $type = $this->vars[$varname]["type"];
        unset($this->vars[$varname]["type"]);
        
        switch ($type) {
            //Special cases
            case "hidden": 
                $this->printHidden($varname, $this->vars[$varname], $disp_type);
                break;
            case "submit": 
                $this->printSubmit($varname, $this->vars[$varname], $disp_type);
                break;
            case "select": 
                $this->printSelect($varname, $this->vars[$varname], $disp_type);
                break;
            case "checkbox_array": 
                $this->printCheckboxArray($varname, $this->vars[$varname], $disp_type);
                break;
            case "radio": 
                $this->printRadio($varname, $this->vars[$varname], $disp_type);
                break;
            case "reset":
                $this->printReset($varname, $this->vars[$varname], $disp_type);
                break;
            case "textarea":
                $this->printTextarea($varname, $this->vars[$varname], $disp_type);
                break;
            //Everything else
            default: 
                $this->printGenericInput($varname, $type, $this->vars[$varname], $disp_type);
                break;
        }
    }


    function getVar($v) {
        global $$v;
       
        /* 
        if(Check::isNull($_REQUEST[$v])){
            $_REQUEST[$v] = $$v;
        } else {
            $$v = $_REQUEST[$v];
        }
        */

        return stripslashes($$v);
    }


    function getChoices() {
        foreach ($this->vars as $v=>$attr) {
            if($attr["type"]=="select" || $attr["type"]=="checkbox_array" || $attr["type"]=="radio"){

                if(strlen($attr["choices_array_var"]) > 0){
                    $cname = $attr["choices_array_var"];
                } else {
                    $cname = $v . "_choices";
                }
                if(isset($$cname)){//variable already exists, let's clear it??
                    continue;
                }

                $cfunc = $attr["get_choices_array_func"];
                $ch = new Choices();
                global $$cname;
                $a = $attr["get_choices_array_func_args"];
        
                if(!is_array($a)) $a = array();
                switch(count($a)) {
                    case 0: $$cname = $ch->$cfunc(); break;
                    case 1: $$cname = $ch->$cfunc($a["0"]); break;
                    case 2: $$cname = $ch->$cfunc($a["0"], $a["1"]); break;
                    case 3: $$cname = $ch->$cfunc($a["0"], $a["1"], $a["2"]); break;
                    case 4: $$cname = $ch->$cfunc($a["0"], $a["1"], $a["2"], $a["3"]); break;
                    default: $$cname = $ch->$cfunc();
                }
            }//if
        }//foreach
    }//function


    function printGenericInput($v, $type, $attrs, $disp_type = "form"){
        //Pull out the requested variable's registered data from teh global variable space
        global $$v;

        //Set the REQUEST variable to the global array
        //$_REQUEST[$v] = $$v; //-- I don't know what this does, actually...

        //Create the simple form, for printing
        $lvar = stripslashes($$v);

        //Check for variable existence...
        if(($lvar===null) || (empty($lvar) && !is_numeric($lvar))){  //empty(0) == true, but we may want the number 0
            
            //If it's not there, set it to the default
	    if(in_array("default_val", array_keys($attrs))){
            	$lvar = $attrs["default_val"];
	    } else {
		$lvar = null;
	    }
        }

        //if we're just showing data, do it and quit now
        if(strcmp($disp_type, "form")){ //returns 0 on true
            echo $lvar;
            return;
        }

        //else, generate the input form:

        //Use or make up a label for the input
        if(in_array("label", array_keys($attrs))){
            $label = $attrs["label"];
        } else {
            $label = $this->generateLabel($v);
        }

        //detect units
        $units="";
        if(in_array("units", array_keys($attrs))){
            $units = $attrs["units"];
        }
        
        //generate the input header
        $str = '<input type="'.$type.'" name="'.$v.'" ';
       
        if(!strcmp($type, "checkbox")){
            $str.= "value=\"1\" ";
        }

        //Add the attributes
        foreach($attrs as $attr=>$value){
            switch($attr){

                //Skip these
                case "use_post":
                case "label":
                case "units":
                case "on_text":
                case "off_text":
                case "check_func":
                case "check_func_args";
                case "get_choices_array_func":
                case "get_choices_array_func_args":
                case "hidden":
                case "divname":
                    break;

                //Boolean attributes
                case "disabled":
                case "required":
                case "multiple":
                case "autofocus":
                case "novalidate":
                case "formnovalidate":
                    $str.= "$attr ";
                    break;

                case "default_val":
                    if(!(($value===null) || (empty($value) && !(is_numeric($value))))){
                        if(!strcmp($type, "checkbox")){
                            if(!strcmp($value, "1")){
                                $str.= "CHECKED ";
                            }
                        } else {
                            $str.= "value=\"$value\" ";
                        }
                    }
                    break;
                
                //Everything else
                default:
                    $str.= "$attr=\"$value\" ";
                    break;
            }
        }

        //Close the input
        $str.="> $units";

	//Detect if this is one of the hidden inputs
	$is_hidden = false;
	if(in_array("hidden", array_keys($attrs))){
		$is_hidden = $attrs["hidden"];
	}

        //Finally, echo the HTML
        $this->printComplexInput($v, $label, $str, $is_hidden);
    }

    function printComplexInput($name, $label, $input, $hidden=null){
        if($hidden==1){
            $class = "hidden_input_container";
        } else {
            $class = "input_container";
        }

        $str = "<div class=\"$class\" name=\"$name\">";
        $str.=     "<div class=\"label\"><label for=\"$name\">$label:</label></div>";
        $str.=     "<div class=\"input\">$input</div>";
        $str.= "</div>";

        echo $str;
    }

    function generateLabel($v){
        $label = "";
        $name_parts = preg_split("~_~", $v);
        foreach($name_parts as $part){
            $label .= ucfirst(strtolower($part));
            if(strcmp($part, end($name_parts))){
                $label.= " ";
            }
        }
        return $label;
    }

    function printSimpleInput($input){
        echo "<div class=\"input_container\"><div class=\"simple\">$input</div></div>";
    }

    function printHidden($v, $attr, $disp_type = "form"){
        if($disp_type == "form"){
            if(in_array("value", array_keys($attr))){
                echo "<input type=\"hidden\" name=\"$v\" value=\"".$attr["value"]."\">";
            } else {
                if(in_array("default_val", array_keys($attr))){
                    echo "<input type=\"hidden\" name=\"$v\" value=\"".$attr["default_val"]."\">";
                }
            }
        }
    }

    function printData($label, $data){

        $str = "<div class=\"input_container\">";
        $str.=     "<div class=\"label\"><strong>$label:</strong></div>";
        $str.=     "<div class=\"input\">$data</div>";
        $str.= "</div>";

        echo $str;


    }

    function printSubmit($v, $attr, $disp_type = "form") {
        
        global $$v;

		//if($attr["id"]) $id = "id=\"".$attr["id"]."\" ";

		if($attr["confirm"]){ $confirm = "onclick=\"return confirm('".$attr["confirm"]."');\""; }

        if($disp_type == "form") {
    
            $str = "<input type=\"submit\" name=\"$v\" value=\"".$attr["value"]."\" $confirm>";
            
            $this->printSimpleInput($str);
        }
    }

    function printReset($v, $attr, $disp_type = "form") {
        global $$v;
        $_REQUEST[$v] = $$v;
        if($disp_type == "form") {
            $str = "<input type=\"reset\" value=\"".$attr["value"]."\">";
            $this->printSimpleInput($str);
        }
    }

    function printSelect($v, $attr, $disp_type = "form") {
        global $$v;
        $_REQUEST[$v] = $$v;
        if(strlen($attr["choices_array_var"]) > 0) {
            $vchoices = $attr["choices_array_var"];
        } else {
            $vchoices = $v . "_choices";
        }

        global $$vchoices;
        $choices = $$vchoices;

        if($attr["label"]){
            $label = $attr["label"];
        } else {
            $label = $this->generateLabel($v);
        }

        if($disp_type == "form"){

            //Build the open select tag
            $reloading = "";
            
            if($attr["reloading"]){
                $reloading.= " onChange=\"this.form.submit()\"";
            }
            
            if($attr["multiple"]){
                $reloading.=" multiple";
            }

            if($attr["class"]){
                $reloading.=" class=\"".$attr["class"]."\"";
            }

            $str = "<select name=\"$v\"$reloading>";

            if($_REQUEST[$v]){
                $selected_option = $_REQUEST[$v];
            } else {
                $selected_option = $attr["default_val"];
            }

            //Toss in the choices
            foreach($choices as $c) {

                $selected = "";
                if($selected_option == $c["value"]) $selected = " SELECTED";
            
                $str.= "<option value=\"".$c["value"]."\"$selected>".$c["text"]."</option>";
            }

            //Close the select tag
            $str.= "</select>";
           
            $this->printComplexInput($v, $label, $str);

        } else {
            foreach($choices as $c) {
                if($_REQUEST[$v] == $c["value"]) {
                    if($args["lowercase"] == true) $text = strtolower($c["text"]);
                    else $text = $c["text"];

                    $this->printComplexInput($v, $label, $text);
                }
            }            
        }
    }        

    function printTextarea($v, $attr, $disp_type = "form"){
        global $$v;
        $_REQUEST[$v] = $$v;
        
        if($_REQUEST[$v]){
            $lvar = $_REQUEST[$v];
        } else {
            $lvar = $attr["default_val"];
        }

        if($disp_type == "form"){

            //Build the input String
            $str = "<textarea name=\"$v\"";

            if($attr["rows"]){
                $str.= " rows=\"".$attr["rows"]."\"";
            }

            if($attr["cols"]){
                $str.= " cols=\"".$attr["cols"]."\"";
            }

            if($attr["placeholder"]){
                $str.= " placeholder=\"".$attr["placeholder"]."\"";
            }

            $str.= ">";

            if($attr["default_val"]){
                $value = $attr["default_val"];
                
                if(!(($value===null) || (empty($value) && !(is_numeric($value))))){
                    $str.= $value;
                }
            }

            $str.= "</textarea>";

            //create the Label
            if($attr["label"]){
                $label = $attr["label"];
            } else {
                $label = $this->generateLabel($v);
            }

            //Print it

            $this->printComplexInput($v, $label, $str);
        } else {
            echo $lvar;
        }
    }


    function printRadio($v, $attr, $disp_type = "form") {
        global $$v;
        $_REQUEST[$v] = $$v;
        if(strlen($attr["choices_array_var"]) > 0) {
            $vchoices = $attr["choices_array_var"];
        } else {
            $vchoices = $v . "_choices";
        }

        global $$vchoices;
        $choices = $$vchoices;

        if(!$_REQUEST[$v]){$_REQUEST[$v]=$choices["0"]["value"];}

        if($disp_type == "form"){
            $str = "";
            foreach($choices as $c) {
                $reloading = "";
                if($attr["reloading"]) $reloading = " onClick=\"this.form.submit()\"";

                $checked = "";
                if($_REQUEST[$v] == $c["value"]) $checked = " CHECKED";
                
                $str.= "<input type=\"radio\" name=\"$v\" value=\"".$c["value"]."\"$reloading$checked>".$c["text"];
            }

            if($attr["label"]){
                $label = $attr["label"];
            } else {
                $label = $this->generateLabel($v);
            }

            $this->printComplexInput($v, $label, $str);
        } else {
            foreach($choices as $c) {
                if($_REQUEST[$v] == $c["value"]) {
                    echo $c["text"];
                }
            }
        }
    }

}

?>
