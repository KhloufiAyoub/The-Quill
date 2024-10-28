<?php
session_start();
include("Config.php");
$dbh=new PDO($dbstr);

$a =[];

$stm=$dbh->prepare("SELECT wid,word FROM vocab");
$stm->execute();

while ($row=$stm->fetch()){
    $value=array("wid"=>$row["wid"], "word"=>$row["word"]);
    $a[]=$value;
}

echo json_encode($a);