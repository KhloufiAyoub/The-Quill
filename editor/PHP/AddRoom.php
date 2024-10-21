<?php
session_start();
include("Config.php");
$dbh=new PDO($dbstr);

if (isset($_POST["room"])){
    $room=$_POST["room"];
    if (!is_numeric($room) && strlen($room) >=1) {
        if (!preg_match("/<script/i",$room)) {
            $stm=$dbh->prepare("INSERT INTO location(roomdesc) VALUES (?)");
            $stm->execute(array($room));
        }
    }
}





