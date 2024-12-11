<?php
session_start();
include("Config.php");
$dbh=new PDO($dbstr);

if (isset($_POST["id1"]) && isset($_POST["table"]) && isset($_POST["newValue"])){
    $id1 = $_POST["id1"];
    $table = $_POST["table"];
    $newValue = $_POST["newValue"];
    switch ($table){
        case "Message":
            $stm=$dbh->prepare("UPDATE msg SET message=? WHERE mid=?");
            $stm->execute(array($newValue, $id1));
            echo "message";
            break;
        case "Piece":
            $stm=$dbh->prepare("UPDATE location SET roomdesc=? WHERE rid=?");
            $stm->execute(array($newValue, $id1));
            echo "piece";
            break;
        case "Objet":
            $stm=$dbh->prepare("UPDATE obj SET objdesc=? WHERE objid=?");
            $stm->execute(array($newValue, $id1));
            echo "objet";
            break;
    }
}