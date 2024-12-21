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
    case "tbl":
        //si on traite pas d'instructions
            //on essaye de lire la regle courante
        if($_SESSION["etat_partie"]["jeu"]["ligne_action"]!=-1) {
            $stm = $dbh->prepare("SELECT wid1, wid2, pgm FROM action LIMIT 1 OFFSET ?");
            $stm->execute(array($_SESSION["etat_partie"]["jeu"]["ligne_action"]));
            if ($row = $stm->fetch()) {
                if (CheckCondition($row["wid1"], $row["wid2"], $row["pgm"])) {
                    CheckInstruction($row["pgm"]);
                    $_SESSION["etat_partie"]["jeu"]["ligne_action"]++;
                    echo json_encode(array("action" => "NOP"));
                } else {
                    $_SESSION["etat_partie"]["jeu"]["ligne_action"]++;
                    echo json_encode(array("action" => "NOP"));
                }
            }else {
                if ($_SESSION["etat_partie"]["jeu"]["table_en_cours"] === "status") {
                    $_SESSION["etat_partie"]["jeu"]["etat_machine"] = "getCommandMessage";
                }elseif($_SESSION["etat_partie"]["jeu"]["table_en_cours"] === "action"){
                    if($_SESSION["etat_partie"]["jeu"]["entree_table"][0]<13){
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
                echo json_encode(array("action" => "NOP"));
            }
        }
        break;
    case "getCommandMessage":
        $_SESSION["etat_partie"]["jeu"]["etat_machine"]="getCommand";
        $random=rand(2, 5);
        $stm=$dbh->prepare("SELECT message FROM smsg WHERE smid=?");
        $stm->execute(array($random));
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

    $_SESSION["etat_partie"]["flags"][2]--;
    if($_SESSION["etat_partie"]["flags"][2] < 0) $_SESSION["etat_partie"]["flags"][2] = 255;

    if($_SESSION["etat_partie"]["flags"][0] !== 0){
        //il fait noir
        $_SESSION["etat_partie"]["flags"][3]--;
        if($_SESSION["etat_partie"]["flags"][3] < 0) $_SESSION["etat_partie"]["flags"][3] = 255;
        if($_SESSION["etat_partie"]["positionObj"][0] === -1){
            //obj 0 est absent
            $_SESSION["etat_partie"]["flags"][4]--;
            if($_SESSION["etat_partie"]["flags"][4] < 0) $_SESSION["etat_partie"]["flags"][4] = 255;

            $stm = $dbh->prepare("SELECT message FROM smsg WHERE smid=0 ");
            $stm->execute();
            $row=$stm->fetch();
            echo json_encode(array("action"=>"TEXT", "str"=>$row["message"]));
        }
    }else{
        $stm=$dbh->prepare("SELECT roomdesc FROM location WHERE rid=?");
        $stm->execute(array($_SESSION["etat_partie"]["piece"]));
        $row=$stm->fetch();
        $_SESSION["etat_partie"]["jeu"]["etat_machine"]="getCommandMessage";
        $_SESSION["etat_partie"]["affichage"][]=$row["roomdesc"];
        $message = $row["roomdesc"];

        $objs=$_SESSION["etat_partie"]["positionObj"];
        $objNb=count($objs);
        $cpt=0;

        for ($i=0; $i < $objNb; $i++){
            if($objs[$i]["startloc"] === $_SESSION["etat_partie"]["piece"]){
                if($cpt==0){
                    $stm = $dbh->prepare("SELECT message FROM smsg WHERE smid=1 ");
                    $stm->execute();
                    $row=$stm->fetch();
                    $message = $message . $row["message"];
                    $cpt++;
                }
                $stm = $dbh->prepare("SELECT objdesc FROM obj WHERE objid=?");
                $stm->execute(array($objs[$i]["objid"]));
                $row=$stm->fetch();
                $message = $message . $row["objdesc"];
            }
        }
        echo json_encode(array("action"=>"TEXT", "str"=>$message));
    }
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
                $_SESSION["etat_partie"]["jeu"]["etat_machine"]="tbl";
                $_SESSION["etat_partie"]["jeu"]["ligne_action"] = 0;
                $_SESSION["etat_partie"]["jeu"]["entree_table"] = $words;
                $_SESSION["etat_partie"]["jeu"]["table_en_cours"] = "action";
                if($_SESSION["etat_partie"]["flags"][5] != 0) $_SESSION["etat_partie"]["flags"][5]--;
                if($_SESSION["etat_partie"]["flags"][6] != 0) $_SESSION["etat_partie"]["flags"][6]--;
                if($_SESSION["etat_partie"]["flags"][7] != 0) $_SESSION["etat_partie"]["flags"][7]--;
                if($_SESSION["etat_partie"]["flags"][8] != 0) $_SESSION["etat_partie"]["flags"][8]--;
                if($_SESSION["etat_partie"]["flags"][9] != 0 && $_SESSION["etat_partie"]["flags"][0] !== 0) $_SESSION["etat_partie"]["flags"][9]--;
                if($_SESSION["etat_partie"]["flags"][10] != 0 && $_SESSION["etat_partie"]["flags"][0] !== 0 && $_SESSION["etat_partie"]["positionObj"][0] === -1) $_SESSION["etat_partie"]["flags"][10]--;
                $_SESSION["etat_partie"]["flags"][31]++;

                echo json_encode(array("action"=>"NOP"));
            }
        }
    }
}

function CheckCondition($wid1, $wid2, $pgm): bool{
    global $dbh;
    $pgm = unserialize($pgm);
    if (($wid1 != $_SESSION["etat_partie"]["jeu"]["entree_table"][0] && $wid1 != -1) || ($wid2 != $_SESSION["etat_partie"]["jeu"]["entree_table"][1] && $wid2 != -1)) {
        return false;
    }else{
        switch ($pgm["condition"]) {
            case "TRUE":
                break;
            case "AT":
                break;
            case "NOTAT":
                break;
            case "ATGT":
                break;
            case "ATLT":
                break;
            case "PRESENT":
                break;
            case "ABSENT":
                break;
            case "WORN":
                break;
            case "NOTWORN":
                break;
            case "CARRIED":
                break;
            case "NOTCARR":
                break;
            case "CHANCE":
                break;
            case "ZERO":
                break;
            case "NOTZERO":
                break;
            case "EQ":
                break;
            case "GT":
                break;
            case "LT":
                break;
        }
        return true;
    }

}

function CheckInstruction($pgm): void{
    global $dbh;
    $pgm = unserialize($pgm);
    switch ($pgm["instruction"]) {
        case "NOP":
            break;
        case "INVENTORY":
            break;
        case "DESCRIBE":
            break;
        case "QUIT":
            break;
        case "END":
            break;
        case "DONE":
            break;
        case "OK":
            break;
        case "ANYKEY":
            break;
        case "SAVE":
            break;
        case "LOAD":
            break;
        case "TURNS":
            break;
        case "SCORE":
            break;
        case "CLS":
            break;
        case "DROPFALL":
            break;
        case "AUTOGET":
            break;
        case "AUTODROP":
            break;
        case "AUTOWEAR":
            break;
        case "AUTOREM":
            break;
        case "GOTO":
            break;
        case "REMOVE":
            break;
        case "GET":
            break;
        case "WEAR":
            break;
        case "DROP":
            break;
        case "CREATE":
            break;
        case "DESTROY":
            break;
        case "PLACE":
            break;
        case "SWAP":
            break;
        case "PAUSE":
            break;
        case "MESSAGE":
            break;
        case "SET":
            break;
        case "CLEAR":
            break;
        case "PLUS":
            break;
        case "MINUS":
            break;
        case "LET":
            break;
    }
}