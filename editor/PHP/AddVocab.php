<?php
session_start();
include("Config.php");
$dbh=new PDO($dbstr);

if (isset($_POST["wid"]) && isset($_POST["word"]))
{
    $wid=$_POST["wid"];
    $word=$_POST["word"];

    if (is_numeric($wid) && strlen($word) >=1){
        if (!preg_match("/<script/i",$word)){
            $word=strtoupper($word);
            $stm=$dbh->prepare("INSERT INTO vocab(wid,word) VALUES (?,?)");
            $stm->execute(array($wid,$word));
        }
    }
}

echo "vocab";