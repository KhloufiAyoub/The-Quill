<?php
session_start();
include("Config.php");
$dbh=new PDO($dbstr);

$a =[];

$stm=$dbh->prepare("SELECT rid, wid, newroom FROM move ORDER BY rid, wid");
$stm->execute();

while ($row=$stm->fetch()){
    $stm2=$dbh->prepare("SELECT word FROM vocab WHERE wid=?");
    $stm2->execute(array($row["wid"]));
    if($row2=$stm2->fetch()){
        $value=array("ID du déplacement"=>$row["rid"], "Mot"=>$row2["word"], "Nouvelle pièce"=>$row["newroom"]);
        $a[]=$value;
    }
}

echo json_encode($a);