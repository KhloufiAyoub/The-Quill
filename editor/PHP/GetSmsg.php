<?php
session_start();
include("Config.php");
$dbh=new PDO($dbstr);

$a =[];

$stm=$dbh->prepare("SELECT smid,message FROM smsg ORDER BY smid");
$stm->execute();

while ($row=$stm->fetch()){
    $value=array("ID du message système"=>$row["smid"], "Message système"=>$row["message"]);
    $a[]=$value;
}

echo json_encode($a);