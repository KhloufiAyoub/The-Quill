<?php
session_start();
include("Config.php");
$dbh=new PDO($dbstr);

$a =[];


if (isset($_POST["gameInput"])){
    $gameInput = $_POST["gameInput"];
    if(!is_numeric($gameInput) && strlen($gameInput) >= 1) {
            switch ($gameInput){
                case "CMD":
                    $a[]="Veuillez saisir une commande";
                    $_SESSION["cmd"] = "CMD";
                    break;
                case "QUIT":
                    if($_SESSION["cmd"] == "CMD"){
                        $smsg=[12,30,31];
                        foreach ($smsg as $value){
                            $stm=$dbh->prepare("SELECT message FROM smsg WHERE smid=?");
                            $stm->execute(array($value));
                            $row=$stm->fetch();
                            $a[]=$row["message"];
                            $_SESSION["cmd"]= "QUIT";
                        }
                    }
                    break;
                case "YES":
                    if($_SESSION["cmd"] == "QUIT"){
                        $_SESSION["cmd"]= "YES";
                        $a[]="Game Over";
                    }
                    break;
                default:
                    $a[]="Commande inconnue";
                    $_SESSION["cmd"]= "";
                    break;
            }
        }
}

echo json_encode($a);