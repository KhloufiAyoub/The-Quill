<?php
session_start();
include("Config.php");
$dbh=new PDO($dbstr);

if (isset($_POST["maxObj"])){
    $maxObj = $_POST["maxObj"];
    if (is_numeric($maxObj)) {
        $stm=$dbh->prepare("UPDATE params SET value=? WHERE param=?");
        $stm->execute(array($maxObj, "maxobj"));
    }
}
