<?php
session_start();
include("Config.php");
$dbh=new PDO($dbstr);

if (isset($_POST["obj"])){
    $obj=$_POST["obj"];
    if (!is_numeric($obj) || strlen($obj) >=1) {
        if (!preg_match("/<script/i",$obj)) {
            $stm=$dbh->prepare("INSERT INTO obj(objdesc, startloc) VALUES (?,-1)");
            $stm->execute(array($obj));
        }
    }
}





