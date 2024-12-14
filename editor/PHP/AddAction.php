<?php
session_start();
include("Config.php");
$dbh=new PDO($dbstr);

if (isset($_POST["aid"]) && isset($_POST["tbl"]) && isset($_POST["wid1"]) && isset($_POST["wid2"]) && isset($_POST["pgm"])){
    $aid=$_POST["aid"];
    $tbl=$_POST["tbl"];
    $wid1=$_POST["wid1"];
    $wid2=$_POST["wid2"];
    $pgm=$_POST["pgm"];

    $stm=$dbh->prepare("SELECT * FROM action WHERE aid=?");
    $stm->execute(array($aid));
    if (is_numeric($aid) &&  !is_numeric($tbl) && strlen($tbl) >=1 && is_numeric($wid1) && is_numeric($wid2) && !is_numeric($pgm) && strlen($pgm) >=1) {
        if (!preg_match("/<script/i",$tbl) && !preg_match("/<script/i",$pgm)) {
            if ($row = $stm->fetch()) {
                $stm1 = $dbh->prepare("UPDATE action SET tbl=?, wid1=?, wid2=?, pgm=? WHERE aid=?");
                $stm1->execute(array($tbl, $wid1, $wid2, $pgm, $aid));
            } else {

                $stm = $dbh->prepare("INSERT INTO action(aid, tbl, wid1, wid2, pgm) VALUES (?,?,?,?,?)");
                $stm->execute(array($aid, $tbl, $wid1, $wid2, $pgm));
            }
        }
    }
}