<?php
session_start();
include "Config.php";
$dbh=new PDO($dbstr);

$flags = array_fill(0, 33, 0);

$positionObj=[];
$stm=$dbh->prepare("SELECT objid, startloc FROM obj ORDER BY objid");
$stm->execute();
while ($row=$stm->fetch()) {
    $positionObj[]=array("objid"=>$row["objid"], "pos"=>$row["startloc"]);
}

$stm=$dbh->prepare("SELECT COUNT(objid) FROM obj WHERE startloc=-3");
$stm->execute();
if($row=$stm->fetch()){
    $flags[1]=$row["COUNT(objid)"];
}


$_SESSION["etat_partie"] = array(
    "flags" => $flags,
    "positionObj" => $positionObj,
    "piece" => 0,
    "affichage" => [],
    "jeu" => array(
        "etat_machine" => "clear",
        "mots_entres" => [],
        "table_en_cours" => "",
        "entree_table" => "",
        "ligne_action" => -1,
        "num_instruction" => 0,
        "nbr_tours" => 0,
        "score" =>0
    )
);


