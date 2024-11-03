<?php
session_start();
include("Config.php");
$dbh=new PDO($dbstr);
$uid=$_SESSION["uid"];

$_SESSION["etat_partie"]["jeu"]["etat_machine"]="getCommand";

$stmt=$dbh->prepare("SELECT savedata FROM savegame WHERE uid=?");
$stmt->execute(array($uid));
if ($row=$stmt->fetch()) {
    $stm1=$dbh->prepare("UPDATE savegame SET savedata=? WHERE uid=?");
    $stm1->execute(array(json_encode($_SESSION["etat_partie"]), $uid));
}else{
    $stm2=$dbh->prepare("INSERT INTO savegame(uid, slot, savedata) VALUES (?,0,?)");
    $stm2->execute(array($uid, json_encode($_SESSION["etat_partie"])));
}

$_SESSION["uid"]=null;
session_destroy();