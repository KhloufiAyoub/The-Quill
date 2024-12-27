<?php
session_start();
include("Config.php");
$dbh=new PDO($dbstr);

if (isset($_POST["username"]) && isset($_POST["psw"])) {
    $user = $_POST["username"];
    $pass = $_POST["psw"];

    $error=0;

    if(preg_match("/<script/i",$user) || preg_match("/<script/i",$pass)) {
        $error++;
    }

    if (!preg_match("/^[a-zA-Z][a-zA-Z0-9_]{0,15}$/",$user) || !preg_match("/^[a-zA-Z0-9_]{6,16}$/",$pass)) {
        $error++;
    }

    if($error==0){
        $stm=$dbh->prepare("SELECT uid, passwd FROM usr WHERE login=?");
        $stm->execute(array($user));

        if ($row=$stm->fetch()) {
            if ($row["passwd"]==hash('sha256', $pass)) {
                $_SESSION["uid"]=$row["uid"];
            }else{
                echo "error";
            }
        }else{
            $stm=$dbh->prepare("INSERT INTO usr(login,passwd) VALUES (?,?)");
            $stm->execute(array($user, hash('sha256', $pass)));
            $_SESSION["uid"]=$dbh->lastInsertId();
            $stm=$dbh->prepare("SELECT value FROM params WHERE  param='maxsave'");
            $stm->execute();
            $row=$stm->fetch();
            for ($i=1; $i<=$row["value"]; $i++) {
                $stm=$dbh->prepare("INSERT INTO savegame(uid, slot,savedata) VALUES (?,?,null)");
                $stm->execute(array($_SESSION["uid"], $i));
            }
            $_SESSION["etat_partie"]["jeu"]["etat_machine"]="initialisation";
        }
    }else{
        echo "error";
    }
}else{
    echo "error";
}