<?php
session_start();
include "Config.php";
$dbh=new PDO($dbstr);


$_SESSION["flags"]=array_fill(0, 33, null);
$_SESSION["positionObj"]=[];
$_SESSION["piece"]=0;
$_SESSION["affichage"]="";
$_SESSION["jeu"]="";
$_SESSION["cmd"]="";