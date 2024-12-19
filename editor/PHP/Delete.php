<?php
session_start();
include("Config.php");
$dbh=new PDO($dbstr);

if (isset($_POST["id1"]) && isset($_POST["table"])){
    $id1 = $_POST["id1"];
    $table = $_POST["table"];
    switch ($table){
        case "Message":
            $stm=$dbh->prepare("DELETE FROM msg WHERE mid=?");
            $stm->execute(array($id1));
            echo "message";
            break;
        case "Piece":
            $stm=$dbh->prepare("DELETE FROM location WHERE rid=?");
            $stm->execute(array($id1));
            $stm=$dbh->prepare("DELETE FROM move WHERE rid=?");
            $stm->execute(array($id1));
            $stm=$dbh->prepare("UPDATE obj SET startloc=-1 WHERE startloc=?");
            $stm->execute(array($id1));
            echo "piece";
            break;
        case "Deplacement":
            if (isset($_POST["id2"])){
                $id2 = $_POST["id2"];
                $stm = $dbh->prepare("SELECT wid FROM vocab WHERE word=?");
                $stm->execute(array($id2));
                $res = $stm->fetch();
                $id2 = $res["wid"];

                $stm=$dbh->prepare("DELETE FROM move WHERE rid=? AND wid=?");
                $stm->execute(array($id1,$id2));
            }
            echo "deplacement";
            break;
        case "Vocab":
            if (isset($_POST["id2"])) {
                $id2 = $_POST["id2"];
                $stm = $dbh->prepare("DELETE FROM vocab WHERE wid=? AND word=?");
                $stm->execute(array($id1,$id2));
            }
            echo "vocab";
            break;
        case "Objet":
            $stm=$dbh->prepare("DELETE FROM obj WHERE objid=?");
            $stm->execute(array($id1));
            echo "objet";
            break;
        case "Action":
            $stm = $dbh->prepare("DELETE FROM action WHERE aid=?");
            $stm->execute(array($id1));
            echo "action";
            break;
    }
}