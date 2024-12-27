<?php
session_start();
include("Config.php");
$dbh=new PDO($dbstr);

$a =[];

$stm=$dbh->prepare("SELECT wid,word FROM vocab ORDER BY wid");
$stm->execute();

while ($row=$stm->fetch()){
    $value=array("ID du mot"=>$row["wid"], "Mot"=>$row["word"]);
    $a[]=$value;
}

echo json_encode($a);