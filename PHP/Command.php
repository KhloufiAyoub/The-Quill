<?php
session_start();
include("Config.php");
$dbh=new PDO($dbstr);


switch ($_SESSION["etat_partie"]["jeu"]["etat_machine"]) {
    case "initialisation":
        initialisation();
        break;
    case "description":
        description();
        break;
    case "getCommand":
        getCommand();
        break;
    case "decodeCommand":
        decodeCommand();
        break;
}


function initialisation(): void{
    echo json_encode(array("action"=>"RESET"));
}

function description(): void{
    global $dbh;
    $stm=$dbh->prepare("SELECT roomdesc FROM location WHERE rid=?");
    $stm->execute(array($_SESSION["etat_partie"]["piece"]));
    $row=$stm->fetch();
    $_SESSION["etat_partie"]["jeu"]["etat_machine"]="getCommand";
    $_SESSION["etat_partie"]["affichage"][]=$row["roomdesc"];
    echo json_encode(array("action"=>"TEXT", "str"=>$row["roomdesc"], "Clear"=>0));
}

function getCommand(): void{
    $_SESSION["etat_partie"]["jeu"]["etat_machine"]="decodeCommand";
    echo json_encode(array("action"=>"CMD"));
}

function decodeCommand(): void{
    global $dbh;
    if(isset($_POST["command"])) {
        $commands = explode(" ", $_POST["command"]);
        $count=0;
        $words=[];
        foreach ($commands as $command){
            $command=strtoupper($command);
            $stm=$dbh->prepare("SELECT wid FROM vocab WHERE word=?");
            $stm->execute(array($command));
            if ($row=$stm->fetch()) {
                $words[]=$row["wid"];
                $count++;
            }
            if($count==2){
                break;
            }
        }

        if ($count==0){
            $stm=$dbh->prepare("SELECT smsg FROM smsg WHERE smid=6");
            $stm->execute();
            $row=$stm->fetch();
            $_SESSION["etat_partie"]["jeu"]["etat_machine"]="getCommand";
            $_SESSION["etat_partie"]["affichage"][]=$row["smsg"];
            echo json_encode(array("action"=>"TEXT", "str"=>$row["smsg"], "Clear"=>0));
        }else{
            $stm=$dbh->prepare("SELECT newroom FROM move WHERE rid=? AND wid=?");
            $stm->execute(array($_SESSION["etat_partie"]["piece"], $words[0]));
            if ($row=$stm->fetch()) {
                $_SESSION["etat_partie"]["piece"]=$row["newroom"];
                $_SESSION["etat_partie"]["jeu"]["etat_machine"]="description";
                echo json_encode(array("action"=>"NOP"));
            }else{
                if($words[0]<13){
                    $stm=$dbh->prepare("SELECT smsg FROM smsg WHERE smid=7");
                }else{
                    $stm=$dbh->prepare("SELECT smsg FROM smsg WHERE smid=8");
                }
                $stm->execute();
                $row=$stm->fetch();
                $_SESSION["etat_partie"]["jeu"]["etat_machine"]="getCommand";
                $_SESSION["etat_partie"]["affichage"][]=$row["smsg"];
                echo json_encode(array("action"=>"TEXT", "str"=>$row["smsg"], "Clear"=>0));
            }
        }
    }
}