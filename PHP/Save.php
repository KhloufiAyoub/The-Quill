<?php
session_start();
include("Config.php");
$dbh=new PDO($dbstr);
$uid=$_SESSION["uid"];

$_SESSION["etat_partie"]["jeu"]["etat_machine"]="getCommand";

$autoSave=serialize($_SESSION["etat_partie"]);

$stm=$dbh->prepare("UPDATE usr SET autosave=? WHERE uid=?");
$stm->execute(array($autoSave, $uid));

$_SESSION["uid"]=null;
session_destroy();