<?php
session_start();
include("Config.php");
$dbh=new PDO($dbstr);

if (isset($_POST["wordSize"])){
    $wordSize = $_POST["wordSize"];
    if (is_numeric($wordSize)) {
        $stm=$dbh->prepare("UPDATE params SET value=? WHERE param=?");
        $stm->execute(array($wordSize, "wordsize"));
    }
}