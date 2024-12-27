<?php
session_start();
include "Config.php";
$dbh=new PDO($dbstr);

$flags = array_fill(0, 33, 0);

$positionObj=[];
$stm=$dbh->prepare("SELECT objid, startloc, wid FROM obj ORDER BY objid");
$stm->execute();
while ($row=$stm->fetch()) {
    $positionObj[]=array("objid"=>$row["objid"], "pos"=>$row["startloc"], "wid"=>$row["wid"]);
}

$stm=$dbh->prepare("SELECT COUNT(objid) FROM obj WHERE startloc=-3");
$stm->execute();
if($row=$stm->fetch()){
    $flags[1]=$row["COUNT(objid)"];
}

$_SESSION["etat_partie"] = array(
    "flags" => $flags,
    "positionObj" => $positionObj,
    "piece" =>0,
    "affichage" => [],
    "slotsUsed"=>[],
    "jeu" => array(
        "etat_machine" => "clear",
        "table_en_cours" => "",
        "entree_table" => [],
        "regle" => 0,
        "num_instruction" => 0,
        "action_valide" => 0,
        "score" =>0
    )
);