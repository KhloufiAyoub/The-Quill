<?php
session_start();
include "Config.php";
$dbh=new PDO($dbstr);

$a=[];

$stm=$dbh->prepare("SELECT objid, objdesc, startloc, wid FROM obj ");
$stm->execute();

while ($row=$stm->fetch()){
    $value=array("objid"=>$row["objid"], "objdesc"=>$row["objdesc"], "startloc"=>$row["startloc"]);
    $a[]=$value;
}

echo json_encode($a);