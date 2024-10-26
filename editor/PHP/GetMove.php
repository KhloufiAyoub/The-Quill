<?php
session_start();
include("Config.php");
$dbh=new PDO($dbstr);

$a =[];

$stm=$dbh->prepare("SELECT rid, wid, newroom FROM move");
$stm->execute();

while ($row=$stm->fetch()){

    $stm2=$dbh->prepare("SELECT word FROM vocab WHERE wid=?");
    $stm2->execute(array($row["wid"]));
    if($row2=$stm2->fetch()){
        $value=array("rid"=>$row["rid"], "word"=>$row2["word"], "newroom"=>$row["newroom"]);
        $a[]=$value;
    }
}

echo json_encode($a);