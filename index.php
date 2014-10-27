<?php

require_once ("include/config.php");
function fail(){
    global $db;
    global $pagetitle;
    header('WWW-Authenticate: Basic realm="'.$pagetitle.'"');
    header("HTTP/1.0 401 Unauthorized");
    include_once("include/401.html");
    if (isset($db)) $db->close();
    exit();
}

date_default_timezone_set($timezone_string);
$db = new mysqli($db_hostname,$db_user,$db_password,$db_table);
if (mysqli_connect_errno()){
    printf("Connect failed: %s\n",mysqli_connect_error());
    exit();
}
$db->set_charset("utf8");

require_once ("include/auth.php");

if (!auth()) {
    fail();
}

session_start();

if (empty($_POST) || !isset($_POST["action"])) 
    $action = "none";
else {
    if (isset($_POST["key"]) && $_POST["key"]==session_id())
        $action = $_POST["action"];
    else {
        $action = "none";
        $_SESSION["alarm"]="Page expired. Please try again.";
    }
}

//dispatcher
switch ($action){
case "add":
    $_SESSION["newline"]=1;
    session_regenerate_id();
    break;
case "edit":
case "remove":
    require_once("include/edit.php");
    editaction();
    break;
case "none":
    break;
default:
    fail();
}
require_once("include/main.php");

$db->close();
