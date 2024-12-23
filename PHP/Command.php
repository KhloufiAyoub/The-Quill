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
                } else {
                    $_SESSION["etat_partie"]["jeu"]["ligne_action"]++;
                    echo json_encode(array("action" => "NOP"));
                }
            }else {
                if ($_SESSION["etat_partie"]["jeu"]["table_en_cours"] === "status") {
                    $_SESSION["etat_partie"]["jeu"]["etat_machine"] = "getCommandMessage";
                    echo json_encode(array("action" => "NOP"));
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
                }else{
                    echo json_encode(array("action" => "NOP"));
                }
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
    if($_SESSION["etat_partie"]["flags"][2] < 0) $_SESSION["etat_partie"]["flags"][2] = 0;

    if($_SESSION["etat_partie"]["flags"][0] !== 0){
        //il fait noir
        $_SESSION["etat_partie"]["flags"][3]--;
        if($_SESSION["etat_partie"]["flags"][3] < 0) $_SESSION["etat_partie"]["flags"][3] = 255;
        if($_SESSION["etat_partie"]["positionObj"][0] === -1){
            //obj 0 est absent
            $_SESSION["etat_partie"]["flags"][4]--;
            if($_SESSION["etat_partie"]["flags"][4] < 0) $_SESSION["etat_partie"]["flags"][4] = 0;

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
            }
            echo json_encode(array("action"=>"NOP"));
        }
    }
}

function CheckCondition($wid1, $wid2, $pgm): bool{
    if (($wid1 != $_SESSION["etat_partie"]["jeu"]["entree_table"][0] && $wid1 != -1) || ($wid2 != $_SESSION["etat_partie"]["jeu"]["entree_table"][1] && $wid2 != -1)) {
        return false;
    }else{
        $pgm = unserialize($pgm);
        foreach ($pgm["condition"] as $condition) {
            switch ($condition["nom"]) {
                case "TRUE":
                    break;
                case "AT":
                    if ($_SESSION["etat_partie"]["piece"] !== $condition["param1"]) {
                        return false;
                    }
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
                    if ($_SESSION["etat_partie"]["positionObj"][$condition["param1"]] !== -3){
                        return false;
                    }
                    break;
                case "NOTCARR":
                    if ($_SESSION["etat_partie"]["positionObj"][$condition["param1"]] === -3){
                        return false;
                    }
                    break;
                case "CHANCE":
                    if (rand(1, 100) > $condition["param1"]) {
                        return false;
                    }
                    break;
                case "ZERO":
                    if ($_SESSION["etat_partie"]["flags"][$condition["param1"]] !== 0) {
                        return false;
                    }
                    break;
                case "NOTZERO":
                    if ($_SESSION["etat_partie"]["flags"][$condition["param1"]] === 0) {
                        return false;
                    }
                    break;
                case "EQ":
                    if ($_SESSION["etat_partie"]["flags"][$condition["param1"]] !== $condition["param2"]) {
                        return false;
                    }
                    break;
                case "GT":
                    if ($_SESSION["etat_partie"]["flags"][$condition["param1"]] <= $condition["param2"]) {
                        return false;
                    }
                    break;
                case "LT":
                    if ($_SESSION["etat_partie"]["flags"][$condition["param1"]] >= $condition["param2"]) {
                        return false;
                    }
                    break;
            }
        }
        return true;
    }
}

function CheckInstruction($pgm): void{
    global $dbh;
    $pgm = unserialize($pgm);
    $num_instruction = $_SESSION["etat_partie"]["jeu"]["num_instruction"];
    if ($pgm["instruction"][$num_instruction] === null) {
        $_SESSION["etat_partie"]["jeu"]["ligne_action"]++;
        echo json_encode(array("action" => "NOP"));
        return;
    }
    $instruction = $pgm["instruction"][$num_instruction];
    
    switch ($instruction["nom"]) {
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
        case "GOTO":
            $_SESSION["etat_partie"]["piece"]=$instruction["param1"];
            echo json_encode(array("action" => "NOP"));
            break;
        case "AUTOREM":
        case "REMOVE":
            if ($instruction["nom"] === "AUTOREM"){
                $obj = $_SESSION["etat_partie"]["jeu"]["entree_table"][1];
            }else{
                $obj = $instruction["param1"];
            }
            $stm = $dbh->prepare("SELECT value FROM params WHERE param=?");
            $stm->execute(array("maxobj"));
            $row = $stm->fetch();
            $maxObj = $row["value"];
            if ($_SESSION["etat_partie"]["positionObj"][$obj]["pos"] !== -2){
                $stm = $dbh->prepare("SELECT message FROM smsg WHERE smid=23");
                $stm->execute();
                $row = $stm->fetch();
                $_SESSION["etat_partie"]["jeu"]["etat_machine"] = "getCommand";
                echo json_encode(array("action" => "TEXT", "str" => $row["message"]));
            }elseif ($maxObj === $_SESSION["etat_partie"]["flags"][1]) {
                $stm = $dbh->prepare("SELECT message FROM smsg WHERE smid=24");
                $stm->execute();
                $row = $stm->fetch();
                $_SESSION["etat_partie"]["jeu"]["etat_machine"] = "getCommand";
                echo json_encode(array("action" => "TEXT", "str" => $row["message"]));
            }else{
                $_SESSION["etat_partie"]["positionObj"][$obj]["pos"] = -3;
                $_SESSION["etat_partie"]["flags"][1]++;
                if ($_SESSION["etat_partie"]["flags"][1] > 255) $_SESSION["etat_partie"]["flags"][1] = 255;
                echo json_encode(array("action" => "NOP"));
            }
            break;
        case "AUTOGET":
        case "GET":
            if ($instruction["nom"] === "AUTOGET"){
                $obj = $_SESSION["etat_partie"]["jeu"]["entree_table"][1];
            }else{
                $obj = $instruction["param1"];
            }
            if ($_SESSION["etat_partie"]["positionObj"][$obj]["pos"] === -2 || $_SESSION["etat_partie"]["positionObj"][$obj]["pos"] === -3) {
                $stm = $dbh->prepare("SELECT message FROM smsg WHERE smid=25");
                $stm->execute();
                $row = $stm->fetch();
                $_SESSION["etat_partie"]["jeu"]["etat_machine"] = "getCommand";
                echo json_encode(array("action" => "TEXT", "str" => $row["message"]));
            }elseif ($_SESSION["etat_partie"]["positionObj"][$obj]["pos"] !== $_SESSION["etat_partie"]["piece"]) {
                $stm = $dbh->prepare("SELECT message FROM smsg WHERE smid=26");
                $stm->execute();
                $row = $stm->fetch();
                $_SESSION["etat_partie"]["jeu"]["etat_machine"] = "getCommand";
                echo json_encode(array("action" => "TEXT", "str" => $row["message"]));
            }else{
                $_SESSION["etat_partie"]["positionObj"][$obj]["pos"] = -3;
                $_SESSION["etat_partie"]["flags"][1]++;
                if ($_SESSION["etat_partie"]["flags"][1] > 255) $_SESSION["etat_partie"]["flags"][1] = 255;
                echo json_encode(array("action" => "NOP"));
            }
            break;
        case "AUTOWEAR":
        case "WEAR":
            if ($instruction["nom"] === "AUTOWEAR"){
                $obj = $_SESSION["etat_partie"]["jeu"]["entree_table"][1];
            }else{
                $obj = $instruction["param1"];
            }
            if ($_SESSION["etat_partie"]["positionObj"][$obj]["pos"] === -2) {
                $stm = $dbh->prepare("SELECT message FROM smsg WHERE smid=29");
                $stm->execute();
                $row = $stm->fetch();
                $_SESSION["etat_partie"]["jeu"]["etat_machine"]="getCommand";
                echo json_encode(array("action" => "TEXT", "str" => $row["message"]));
            }elseif ($_SESSION["etat_partie"]["positionObj"][$obj]["pos"] !== -3){
                $stm = $dbh->prepare("SELECT message FROM smsg WHERE smid=28");
                $stm->execute();
                $row = $stm->fetch();
                $_SESSION["etat_partie"]["jeu"]["etat_machine"] = "getCommand";
                echo json_encode(array("action" => "TEXT", "str" => $row["message"]));
            }else{
                $_SESSION["etat_partie"]["positionObj"][$obj]["pos"] = -2;
                $_SESSION["etat_partie"]["flags"][1]++;
                if ($_SESSION["etat_partie"]["flags"][1] > 255) $_SESSION["etat_partie"]["flags"][1] = 255;
                echo json_encode(array("action" => "NOP"));
            }
            break;
        case "AUTODROP":
        case "DROP":
        if ($instruction["nom"] === "AUTODROP"){
            $obj = $_SESSION["etat_partie"]["jeu"]["entree_table"][1];
        }else{
            $obj = $instruction["param1"];
        }
            if ($_SESSION["etat_partie"]["positionObj"][$obj]["pos"] === -2){
                $stm = $dbh->prepare("SELECT value FROM params WHERE param=?");
                $stm->execute(array("maxobj"));
                $row = $stm->fetch();
                $maxObj = $row["value"];
                if ($_SESSION["etat_partie"]["flags"][1] === $maxObj) {
                    $stm = $dbh->prepare("SELECT message FROM smsg WHERE smid=24");
                    $stm->execute();
                    $row = $stm->fetch();
                    $_SESSION["etat_partie"]["jeu"]["etat_machine"]="getCommand";
                    echo json_encode(array("action" => "TEXT", "str" => $row["message"]));
                }else{
                    echo json_encode(array("action" => "NOP"));
                }
            }else{
                if($_SESSION["etat_partie"]["positionObj"][$obj]["pos"] !== -3){
                    $stm = $dbh->prepare("SELECT message FROM smsg WHERE smid=28");
                    $stm->execute();
                    $row = $stm->fetch();
                    $_SESSION["etat_partie"]["jeu"]["etat_machine"]="getCommand";
                    echo json_encode(array("action" => "TEXT", "str" => $row["message"]));
                }else{
                    $_SESSION["etat_partie"]["positionObj"][$obj]["pos"] = $_SESSION["etat_partie"]["piece"];
                    $_SESSION["etat_partie"]["flags"][1]--;
                    if ($_SESSION["etat_partie"]["flags"][1] < 0) $_SESSION["etat_partie"]["flags"][1] = 0;
                    echo json_encode(array("action" => "NOP"));
                }
            }
            break;
        case "CREATE":
            if ($_SESSION["etat_partie"]["positionObj"][$instruction["param1"]]["pos"] === -3) {
                $_SESSION["etat_partie"]["flags"][1]--;
                if ($_SESSION["etat_partie"]["flags"][1] < 0) $_SESSION["etat_partie"]["flags"][1] = 0;
            }
            $_SESSION["etat_partie"]["positionObj"][$instruction["param1"]]["pos"] = $_SESSION["etat_partie"]["piece"];
            echo json_encode(array("action" => "NOP"));
            break;
        case "DESTROY":
            if ($_SESSION["etat_partie"]["positionObj"][$instruction["param1"]]["pos"] === -3) {
                $_SESSION["etat_partie"]["flags"][1]--;
                if ($_SESSION["etat_partie"]["flags"][1] < 0) $_SESSION["etat_partie"]["flags"][1] = 0;
            }
            $_SESSION["etat_partie"]["positionObj"][$instruction["param1"]]["pos"] = -1;
            echo json_encode(array("action" => "NOP"));
            break;
        case "PLACE":
            if ($_SESSION["etat_partie"]["positionObj"][$instruction["param1"]]["pos"] === -3) {
                $_SESSION["etat_partie"]["flags"][1]--;
                if ($_SESSION["etat_partie"]["flags"][1] < 0) $_SESSION["etat_partie"]["flags"][1] = 0;
            }
            $_SESSION["etat_partie"]["positionObj"][$instruction["param1"]]["pos"] = $_SESSION["etat_partie"]["piece"];
            echo json_encode(array("action" => "NOP"));
            break;
        case "SWAP":
            $tempLoc=$_SESSION["etat_partie"]["positionObj"][$instruction["param1"]]["pos"];
            $_SESSION["etat_partie"]["positionObj"][$instruction["param1"]]["pos"]=$_SESSION["etat_partie"]["positionObj"][$instruction["param2"]]["pos"];
            $_SESSION["etat_partie"]["positionObj"][$instruction["param2"]]["pos"]=$tempLoc;
            echo json_encode(array("action" => "NOP"));
            break;
        case "PAUSE":
            echo json_encode(array("action" => "PAUSE", "time" => $instruction["param1"]));
            break;
        case "MESSAGE":
            echo json_encode(array("action" => "TEXT", "str" => $instruction["param1"]));
            break;
        case "SET":
            $_SESSION["etat_partie"]["flags"][$instruction["param1"]] = 255;
            echo json_encode(array("action" => "NOP"));
            break;
        case "CLEAR":
            $_SESSION["etat_partie"]["flags"][$instruction["param1"]] = 0;
            echo json_encode(array("action" => "NOP"));
            break;
        case "PLUS":
            $_SESSION["etat_partie"]["flags"][$instruction["param1"]] += $instruction["param2"];
            if ($_SESSION["etat_partie"]["flags"][$instruction["param1"]] > 255) $_SESSION["etat_partie"]["flags"][$instruction["param1"]] = 0;
            echo json_encode(array("action" => "NOP"));
            break;
        case "MINUS":
            $_SESSION["etat_partie"]["flags"][$instruction["param1"]] -= $instruction["param2"];
            if ($_SESSION["etat_partie"]["flags"][$instruction["param1"]] < 0) $_SESSION["etat_partie"]["flags"][$instruction["param1"]] = 0;
            echo json_encode(array("action" => "NOP"));
            break;
        case "LET":
            $_SESSION["etat_partie"]["flags"][$instruction["param1"]] = $instruction["param2"];
            echo json_encode(array("action" => "NOP"));
            break;
    }
    $_SESSION["etat_partie"]["jeu"]["num_instruction"]++;
}