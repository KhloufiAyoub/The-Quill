<?php
session_start();
include("Config.php");
$dbh=new PDO($dbstr);
$uid=$_SESSION["uid"];

$stm=$dbh->prepare("SELECT savegame FROM savegame WHERE uid=?");
$stm->execute(array($uid));
if ($row=$stm->fetch()) {
    $_SESSION["etat_partie"] = $row["savegame"];
    //TODO: check if the savegame is valid
    $_SESSION["etat_partie"]["jeu"]["etat_machine"]="description";
}else{
    $_SESSION["etat_partie"]["jeu"]["etat_machine"]="initialisation";
    $_SESSION["etat_partie"]["affichage"]=[];
}

