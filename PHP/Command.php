<?php
session_start();
include("Config.php");
$dbh=new PDO($dbstr);


switch ($_SESSION["etat_partie"]["jeu"]["etat_machine"]) {
    case "initialisation":
        echo json_encode(array("action"=>"RESET"));
        break;
    case "clear":
        $_SESSION["etat_partie"]["affichage"]=[];
        $_SESSION["etat_partie"]["jeu"]["etat_machine"]="description";
        echo json_encode(array("action"=>"CLEAR"));
        break;
    case "description":
        description();
        break;
    case "getCommandMessage":
        $_SESSION["etat_partie"]["jeu"]["etat_machine"]="getCommand";
        $stm=$dbh->prepare("SELECT message FROM smsg WHERE smid=5");
        $stm->execute();
        $row=$stm->fetch();
        $_SESSION["etat_partie"]["affichage"][]=$row["message"];
        echo json_encode(array("action"=>"TEXT", "str"=>$row["message"]));
        break;
    case "getCommand":
        $_SESSION["etat_partie"]["jeu"]["etat_machine"]="decodeCommand";
        echo json_encode(array("action"=>"CMD"));
        break;
    case "decodeCommand":
        decodeCommand();
        break;
}


function description(): void{
    global $dbh;
    $stm=$dbh->prepare("SELECT roomdesc FROM location WHERE rid=?");
    $stm->execute(array($_SESSION["etat_partie"]["piece"]));
    $row=$stm->fetch();
    $_SESSION["etat_partie"]["jeu"]["etat_machine"]="getCommandMessage";
    $_SESSION["etat_partie"]["affichage"][]=$row["roomdesc"];
    echo json_encode(array("action"=>"TEXT", "str"=>$row["roomdesc"]));
}

function decodeCommand(): void{
    global $dbh;
    if(isset($_POST["command"])) {
        $commands = explode(" ", $_POST["command"]);
        $count=0;
        $words=[];
        $stm = $dbh->prepare("SELECT value FROM params WHERE param=?");
        $stm->execute(array("wordsize"));
        $row = $stm->fetch();
        $wordsize = $row["value"];
        foreach ($commands as $command){
            $command=strtoupper($command);
            $command=substr($command, 0, $wordsize);
            $stm=$dbh->prepare("SELECT wid FROM vocab WHERE SUBSTRING(word, 1, ?) = ?");
            $stm->execute(array($wordsize, $command));
            if ($row=$stm->fetch()) {
                $words[]=$row["wid"];
                $count++;
            }
            if($count==2){
                break;
            }
        }

        if ($count==0){
            $stm=$dbh->prepare("SELECT message FROM smsg WHERE smid=6");
            $stm->execute();
            $row=$stm->fetch();
            $_SESSION["etat_partie"]["jeu"]["etat_machine"]="getCommand";
            $_SESSION["etat_partie"]["affichage"][]=$row["message"];
            echo json_encode(array("action"=>"TEXT", "str"=>$row["message"]));
        }else{
            $stm=$dbh->prepare("SELECT newroom FROM move WHERE rid=? AND wid=?");
            $stm->execute(array($_SESSION["etat_partie"]["piece"], $words[0]));
            if ($row=$stm->fetch()) {
                $_SESSION["etat_partie"]["piece"]=$row["newroom"];
                $_SESSION["etat_partie"]["jeu"]["etat_machine"]="clear";
                echo json_encode(array("action"=>"NOP"));
            }else{
                if($words[0]<13){
                    $stm=$dbh->prepare("SELECT message FROM smsg WHERE smid=7");
                }else{
                    $stm=$dbh->prepare("SELECT message FROM smsg WHERE smid=8");
                }
                $stm->execute();
                $row=$stm->fetch();
                $_SESSION["etat_partie"]["jeu"]["etat_machine"]="getCommand";
                $_SESSION["etat_partie"]["affichage"][]=$row["message"];
                echo json_encode(array("action"=>"TEXT", "str"=>$row["message"]));
            }
        }
    }
}