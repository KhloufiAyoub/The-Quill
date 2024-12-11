<?php
session_start();
include("Config.php");
$dbh=new PDO($dbstr);

if (isset($_POST["rid"]) && isset($_POST["wid"]) && isset($_POST["newroom"])){
    $rid=$_POST["rid"];
    $wid=$_POST["wid"];
    $new_room=$_POST["newroom"];
    if (is_numeric($rid) && is_numeric($wid) && is_numeric($new_room)) {
        $stm=$dbh->prepare("INSERT INTO move(rid, wid, newroom) VALUES (?, ?, ?)");
        $stm->execute(array($rid, $wid, $new_room));
    }
}





