<?php
session_start();
include("Config.php");
$dbh=new PDO($dbstr);

if (isset($_POST["maxSave"])){
    $maxSave = $_POST["maxSave"];
    if (is_numeric($maxSave)) {
        $stm=$dbh->prepare("UPDATE params SET value=? WHERE param='maxsave'");
        $stm->execute(array($maxSave));
    }
}