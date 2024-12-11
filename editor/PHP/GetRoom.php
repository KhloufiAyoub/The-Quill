<?php
session_start();
include("Config.php");
$dbh=new PDO($dbstr);

$a =[];

$stm=$dbh->prepare("SELECT rid, roomdesc FROM location ORDER BY rid");
$stm->execute();

while ($row=$stm->fetch()){
    $value=array("rid"=>$row["rid"], "roomdesc"=>$row["roomdesc"]);
    $a[]=$value;
}

echo json_encode($a);