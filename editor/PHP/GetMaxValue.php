<?php
session_start();
include("Config.php");
$dbh=new PDO($dbstr);

$result="";

$stm=$dbh->prepare("SELECT value FROM params order by param");
$stm->execute();

while($row=$stm->fetch()){
    $result = $result . $row["value"] . ",";
}
echo $result;