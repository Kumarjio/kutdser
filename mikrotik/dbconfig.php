<?php
session_start();

//error_reporting(E_ALL);
//ini_set('display_errors', 1);
date_default_timezone_set('America/New_York');

include "D:/Program/wamp/www/kutdser-master/ResellerPortal/tools/DBTools.php";
$dbTools = new DBTools();

$site_url="http://localhost/kutdser-master/mikrotik";
$api_url = "http://localhost/kutdser-master/api/";

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "router";

$conn_routers = new mysqli($servername, $username, $password, $dbname);

$admin_id = 0;
//Authentication
$page = basename($_SERVER['REQUEST_URI'], '?' . $_SERVER['QUERY_STRING']);
if ($page != "login.php") {
    $session_id = stripslashes($_SESSION["session_id"]);
    $admin_result = $conn_routers->query("select * from `admins` where `session_id`='" . $session_id . "' and `session_id`!=''");
    if(!$admin_row = $admin_result->fetch_assoc()){
        header('Location: '.$site_url.'/login.php');
        die();
    }
    $username = $admin_row["username"];
    $admin_id = $admin_row["admin_id"];
}
if (isset($_GET["do"])) {
    if ($_GET["do"] == "logout") {
        $_SESSION["session_id"] = null;
        header('Location: '.$site_url.'/login.php');
    }
}
?>