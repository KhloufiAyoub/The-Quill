<?php
session_start();
include("Config.php");
$dbh=new PDO($dbstr);

if (isset($_POST["msg"])){
    $msg=$_POST["msg"];
    if (strlen($msg) >=1) {
        if (!preg_match("/<script/i",$msg)) {
            $stm=$dbh->prepare("INSERT INTO msg(message) VALUES (?)");
            $stm->execute(array($msg));
        }
    }
}

echo "message";