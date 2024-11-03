<?php
session_start();
include("Config.php");
$dbh=new PDO($dbstr);
$uid=$_SESSION["uid"];

$stm=$dbh->prepare("SELECT savedata FROM savegame WHERE uid=?");
$stm->execute(array($uid));
if ($row=$stm->fetch()) {
    $_SESSION["etat_partie"] =json_decode($row["savedata"], true);
}else{
    $_SESSION["etat_partie"]["jeu"]["etat_machine"]="initialisation";
    $_SESSION["etat_partie"]["affichage"]=[];
}

