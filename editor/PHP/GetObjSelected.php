<?php
session_start();
include("Config.php");
$dbh=new PDO($dbstr);

if (isset($_POST["id"])){
    $id = $_POST["id"];
    $stm=$dbh->prepare("SELECT startloc,wid FROM obj WHERE objid=?");
    $stm->execute(array($id));
    $row=$stm->fetch();
    $value=array("Mot associé"=>$row["wid"], "Position de départ"=>$row["startloc"]);
    echo json_encode($value);
}