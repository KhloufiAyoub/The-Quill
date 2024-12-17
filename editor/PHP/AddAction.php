<?php
session_start();
include("Config.php");
$dbh=new PDO($dbstr);

if (isset($_POST["tbl"]) && isset($_POST["wid1"]) && isset($_POST["wid2"]) && isset($_POST["pgm"])) {
    $tbl = $_POST["tbl"];
    $wid1 = $_POST["wid1"];
    $wid2 = $_POST["wid2"];
    if ($wid1==="null") $wid1 = null;
    if ($wid2==="null") $wid2 = null;
    $pgm = $_POST["pgm"];
    $pgm = json_decode($pgm, true);
    $pgm = serialize($pgm);

    if (($tbl==1||$tbl==0) && (is_numeric($wid1) || is_null($wid1)) && (is_numeric($wid2) || is_null($wid2))) {
        $stm = $dbh->prepare("SELECT aid FROM action WHERE tbl=? AND wid1=? AND wid2=?");
        $stm->execute(array($tbl, $wid1, $wid2));
        if ($row = $stm->fetch()) {
            $aid = $row["aid"];
            $stm1 = $dbh->prepare("UPDATE action SET tbl=?, wid1=?, wid2=?, pgm=? WHERE aid=?");
            $stm1->execute(array($tbl, $wid1, $wid2, $pgm, $aid));
        } else {
            $stm1 = $dbh->prepare("INSERT INTO action(tbl, wid1, wid2, pgm) VALUES (?,?,?,?)");
            $stm1->execute(array($tbl, $wid1, $wid2, $pgm));
        }
    }
}

echo "action";
