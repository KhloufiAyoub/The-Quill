<?php
session_start();
include("Config.php");
$dbh=new PDO($dbstr);
$uid=$_SESSION["uid"];

$save=$_SESSION["etat_partie"];

$stmt=$dbh->prepare("SELECT savedata FROM savegame WHERE uid=?");
$stmt->execute(array($uid));
if ($row=$stmt->fetch()) {
    $stm1=$dbh->prepare("UPDATE savegame SET savedata=? WHERE uid=?");
    $stm1->execute(array(json_encode($save), $uid));
}else{
    $stm2=$dbh->prepare("INSERT INTO savegame(uid, slot, savedata) VALUES (?,0,?)");
    $stm2->execute(array($uid, json_encode($save)));
}
