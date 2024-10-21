<?php
session_start();
include("Config.php");
$dbh=new PDO($dbstr);

if (isset($_POST["username"]) || isset($_POST["psw"])) {
    $user = $_POST["username"];
    $pass = $_POST["psw"];

    $error=0;

    if(preg_match("/<script/i",$user || preg_match("/<script/i",$pass))) {
        $error++;
    }

    if (!preg_match("/^[a-zA-Z][a-zA-Z0-9_]{0,15}$/",$user) || !preg_match("/^[a-zA-Z0-9_]{6,16}$/",$pass)) {
        $error++;
    }

    if($error==0){
        $stm=$dbh->prepare("SELECT passwd FROM usr WHERE login=?");
        $stm->execute(array($user));

        if ($row=$stm->fetch()) {
            if ($row["passwd"]==md5($pass)) {
                echo "success";
            }
            else{
                echo "fail";
            }
        }else{
            $stm=$dbh->prepare("INSERT INTO usr(login,passwd) VALUES (?,?)");
            $stm->execute(array($user,md5($pass)));
            echo "success";
        }
    }
}

