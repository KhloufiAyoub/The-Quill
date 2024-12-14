<?php
session_start();
include("Config.php");
$dbh=new PDO($dbstr);

//$a =array("action"=>[], "obj"=>[], "room"=>[], "msg"=>[], "vocab"=>[]);
$a = [];

// ACTION
$stm = $dbh->prepare("SELECT tbl,wid1, wid2, pgm FROM action WHERE aid<>-1 ORDER BY wid1, wid2, aid"); //trier sur ordre de creation
$stm->execute();
while ($row=$stm->fetch()){
    $value=array("tbl"=>$row["tbl"], "wid1"=>$row["wid1"], "wid2"=>$row["wid2"], "pgm"=>$row["pgm"]);
    //$a["action"][]=$value;
    $a[]=$value;
}
/**
//OBJ
$stm=$dbh->prepare("SELECT objid, objdesc FROM obj ORDER BY objid");
$stm->execute();
while ($row=$stm->fetch()){
    $value=array("objid"=>$row["objid"], "objdesc"=>$row["objdesc"]);
    $a["obj"][]=$value;
}

//ROOM
$stm=$dbh->prepare("SELECT rid, roomdesc FROM location ORDER BY rid");
$stm->execute();
while ($row=$stm->fetch()){
    $value=array("rid"=>$row["rid"], "roomdesc"=>$row["roomdesc"]);
    $a["room"][]=$value;
}

//MSG
$stm=$dbh->prepare("SELECT mid, message FROM msg ORDER BY mid");
$stm->execute();
while ($row=$stm->fetch()){
    $value=array("mid"=>$row["mid"], "message"=>$row["message"]);
    $a["msg"][]=$value;
}

//VOCAB
$stm=$dbh->prepare("SELECT wid,word FROM vocab ORDER BY wid");
$stm->execute();
while ($row=$stm->fetch()){
    $value=array("wid"=>$row["wid"], "word"=>$row["word"]);
    $a["vocab"][]=$value;
}
**/
echo json_encode($a);