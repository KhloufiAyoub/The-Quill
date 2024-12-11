<?php
session_start();
include("Config.php");
$dbh=new PDO($dbstr);

$a =[];

$stm=$dbh->prepare("SELECT smid,message FROM smsg ORDER BY smid");
$stm->execute();

while ($row=$stm->fetch()){
    $value=array("smid"=>$row["smid"], "message"=>$row["message"]);
    $a[]=$value;
}



echo json_encode($a);