<?php
session_start();
include("Config.php");
$dbh=new PDO($dbstr);

if (isset($_POST["aid"]) && isset($_POST["tbl"]) && isset($_POST["wid1"]) && isset($_POST["wid2"]) && isset($_POST["pgm"])) {
    $aid = $_POST["aid"];
    $tbl = $_POST["tbl"];
    $wid1 = $_POST["wid1"];
    $wid2 = $_POST["wid2"];
    $pgm = $_POST["pgm"];
    $pgm = json_decode($pgm, true);
    $pgm = serialize($pgm);

    if (is_numeric($aid) && ($tbl==1||$tbl==0) && is_numeric($wid1) && is_numeric($wid2)) {
        $stm = $dbh->prepare("SELECT * FROM action WHERE aid=?");
        $stm->execute(array($aid));
        if ($row = $stm->fetch()) {
            $stm1 = $dbh->prepare("UPDATE action SET tbl=?, wid1=?, wid2=?, pgm=? WHERE aid=?");
            $stm1->execute(array($tbl, $wid1, $wid2, $pgm, $aid));
        } else {
            $stm1 = $dbh->prepare("INSERT INTO action(tbl, wid1, wid2, pgm) VALUES (?,?,?,?)");
            $stm1->execute(array($tbl, $wid1, $wid2, $pgm));
        }
    }
}

echo "action";
