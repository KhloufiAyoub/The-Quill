<?php
session_start();
include "Config.php";
$dbh=new PDO($dbstr);

$a=[];

$stm=$dbh->prepare("SELECT objid, objdesc FROM obj ");
$stm->execute();

while ($row=$stm->fetch()){
    $value=array("objid"=>$row["objid"], "objdesc"=>$row["objdesc"]);
    $a[]=$value;
}

echo json_encode($a);