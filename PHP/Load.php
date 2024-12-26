<?php
session_start();
include("Config.php");
$dbh=new PDO($dbstr);
$uid=$_SESSION["uid"];

$stm=$dbh->prepare("SELECT autosave FROM usr WHERE uid=?");
$stm->execute(array($uid));
$row=$stm->fetch();
$autoSave = unserialize($row["autosave"]);

if ($autoSave == null) {
    $_SESSION["etat_partie"]["jeu"]["etat_machine"]="initialisation";
    $_SESSION["etat_partie"]["affichage"]=[];
}else{
    $_SESSION["etat_partie"] = $autoSave;
}
