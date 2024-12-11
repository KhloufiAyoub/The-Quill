<?php
session_start();
include("Config.php");
$dbh=new PDO($dbstr);

$a =[];

$stm=$dbh->prepare("SELECT mid, message FROM msg ORDER BY mid");
$stm->execute();

while ($row=$stm->fetch()){
    $value=array("mid"=>$row["mid"], "message"=>$row["message"]);
    $a[]=$value;
}

echo json_encode($a);