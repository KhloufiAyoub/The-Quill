<?php
session_start();
include "Config.php";
$dbh=new PDO($dbstr);

$flags = array_fill(0, 33, null);
$flags[0]=0;

$positionObj=[];
$stm=$dbh->prepare("SELECT objid, startloc FROM obj");
$stm->execute();
while ($row=$stm->fetch()) {
    $positionObj[]=array("objid"=>$row["objid"], "startloc"=>$row["startloc"]);
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
        "ligne_action" => ""
    )
);


