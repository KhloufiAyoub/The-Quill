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
        $tbl = $_SESSION["etat_partie"]["jeu"]["table_en_cours"];
        $tbl = $tbl =="action" ? 1 : 0;
        $stm = $dbh->prepare("SELECT wid1, wid2, pgm FROM action WHERE tbl =? ORDER BY aid LIMIT 1 OFFSET ?");
        $stm->execute(array($tbl,$_SESSION["etat_partie"]["jeu"]["regle"]));
        //si on ne traite pas d'instruction
        if($_SESSION["etat_partie"]["jeu"]["num_instruction"]==0) {
            //on essaye de lire la regle courante

            if ($row = $stm->fetch()) {
                //vérifie les conditions et les mots
                if (CheckCondition($row["wid1"], $row["wid2"], $row["pgm"])) {
                    if ($tbl == 1) {
                        $_SESSION["etat_partie"]["jeu"]["action_valide"]++;
                    }
                    //on traite la premiere instruction
                    CheckInstruction($row["pgm"]);
                } else {//si les conditions OU les mots ne sont pas valides
                    $_SESSION["etat_partie"]["jeu"]["num_instruction"]=0;
                    $_SESSION["etat_partie"]["jeu"]["regle"]++;
                    echo json_encode(array("action" => "NOP"));
                }
            }else {//elle n'existe pas
                if ($_SESSION["etat_partie"]["jeu"]["table_en_cours"] == "status") {
                    $_SESSION["etat_partie"]["jeu"]["etat_machine"] = "getCommandMessage";
                    echo json_encode(array("action" => "NOP"));
                }else{//on est dans la table action
                    //si aucune action n'a été validée
                    if ($_SESSION["etat_partie"]["jeu"]["action_valide"]==0){
                        if($_SESSION["etat_partie"]["jeu"]["entree_table"][0]<13){
                            $stm=$dbh->prepare("SELECT message FROM smsg WHERE smid=7");
                        }else{
                            $stm=$dbh->prepare("SELECT message FROM smsg WHERE smid=8");
                        }
                        $stm->execute();
                        $row=$stm->fetch();
                        $_SESSION["etat_partie"]["affichage"][]=$row["message"];
                        PrepareStatus();
                        echo json_encode(array("action"=>"TEXT", "str"=>$row["message"]));
                    }else{
                        $_SESSION["etat_partie"]["jeu"]["etat_machine"]="getCommandMessage";
                        echo json_encode(array("action"=>"NOP"));
                    }
                }
            }
        }else{// on traitait les instructions
            $row = $stm->fetch();
            CheckInstruction($row["pgm"]);
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
    case "QUIT":
        if(isset($_POST["command"])){
            $command = strtoupper($_POST["command"]);
            if($command=="YES"){
                $_SESSION["etat_partie"]["jeu"]["etat_machine"] = "tbl";
                $_SESSION["etat_partie"]["jeu"]["num_instruction"]++;
            }else{
                Done();
            }
            echo json_encode(array("action"=>"NOP"));
        }
        break;
    case "END":
        if(isset($_POST["command"])){
            $command = strtoupper($_POST["command"]);
            if($command=="YES"){
                $_SESSION["etat_partie"]["jeu"]["etat_machine"]="initialisation";
                echo json_encode(array("action"=>"NOP"));
            }else{
                echo json_encode(array("action"=>"LOGOUT"));
            }
        }
        break;
    case "LOAD":
        $uid=$_SESSION["uid"];
        $slot=$_SESSION["etat_partie"]["jeu"]["entree_table"][1];
        $slotsUsed = $_SESSION["etat_partie"]["slotsUsed"];
        if($slot>=1 && $slot<=5 && in_array($slot, $slotsUsed)){
            $stm=$dbh->prepare("SELECT savedata FROM savegame WHERE uid=? AND slot=?");
            $stm->execute(array($uid, $slot));
            $row=$stm->fetch();
            $_SESSION["etat_partie"]=unserialize($row["savedata"]);
            $_SESSION["etat_partie"]["jeu"]["etat_machine"]="clear";
            echo json_encode(array("action"=>"NOP"));
        }elseif ($slot == null){
            $stm=$dbh->prepare("SELECT autosave FROM usr WHERE uid=?");
            $stm->execute(array($uid));
            $row=$stm->fetch();
            if ($row["autosave"]!=null){
                $_SESSION["etat_partie"]=unserialize($row["autosave"]);
                $_SESSION["etat_partie"]["jeu"]["etat_machine"]="clear";
                echo json_encode(array("action"=>"NOP"));
            }else{
                Done();
                echo json_encode(array("action"=>"TEXT", "str"=>"No saved game ! :("));
            }
        }else{
            Done();
            echo json_encode(array("action"=>"TEXT", "str"=>"No saved game in slot ". $slot . " ! :("));
        }
        break;
    case "SAVE":
        $uid=$_SESSION["uid"];
        $slot=$_SESSION["etat_partie"]["jeu"]["entree_table"][1];

        $slots = $_SESSION["etat_partie"]["slotsUsed"];
        
        if( $slot>=1 && $slot<=5 && !in_array($slot, $slots)){
            $_SESSION["etat_partie"]["slotsUsed"][] = $slot;
            $_SESSION["etat_partie"]["affichage"][]="Game saved in slot ". $slot . " ! :)";
            $saveData=serialize($_SESSION["etat_partie"]);
            $stm=$dbh->prepare("UPDATE savegame SET savedata=? WHERE uid=? AND slot=?");
            $stm->execute(array($saveData, $uid,$slot));

            $_SESSION["etat_partie"]["jeu"]["etat_machine"]="clear";
            $saveData=serialize($_SESSION["etat_partie"]);

            $stm=$dbh->prepare("UPDATE usr SET autosave=? WHERE uid=?");
            $stm->execute(array($saveData, $uid));
            
            $slotsUnused = array_diff([1,2,3,4,5], $slots);

            unset($slotsUnused[array_search($slot, $slotsUnused)]);
            $slotsUnused = array_values($slotsUnused);

            Done();
            echo json_encode(array("action" => "SAVESLOT", "str" => "Game saved in slot ". $slot . " ! :)", "slots" => $slotsUnused));
        }elseif ($slot == null){
            $_SESSION["etat_partie"]["affichage"][]="Game saved ! :)";
            $saveData=serialize($_SESSION["etat_partie"]);
            $stm=$dbh->prepare("UPDATE usr SET autosave=? WHERE uid=?");
            $stm->execute(array($saveData, $uid));
            Done();
            echo json_encode(array("action" => "TEXT", "str" => "Game saved ! :)"));
        }else{
            echo json_encode(array("action"=>"NOP"));
        }
        break;
}

function description(): void{
    global $dbh;

    $_SESSION["etat_partie"]["flags"][2]--;
    if($_SESSION["etat_partie"]["flags"][2] < 0) $_SESSION["etat_partie"]["flags"][2] = 0;

    //il fait noir.
    if($_SESSION["etat_partie"]["flags"][0] != 0){
        $_SESSION["etat_partie"]["flags"][3]--;
        if($_SESSION["etat_partie"]["flags"][3] < 0) $_SESSION["etat_partie"]["flags"][3] = 0;

        //obj 0 est absent
        if($_SESSION["etat_partie"]["positionObj"][0] != -3 && $_SESSION["etat_partie"]["positionObj"][0] != $_SESSION["etat_partie"]["piece"]){
            $_SESSION["etat_partie"]["flags"][4]--;
            if($_SESSION["etat_partie"]["flags"][4] < 0) $_SESSION["etat_partie"]["flags"][4] = 0;

            $stm = $dbh->prepare("SELECT message FROM smsg WHERE smid=0 ");
            $stm->execute();
            $row=$stm->fetch();
            $_SESSION["etat_partie"]["affichage"][]=$row["message"];
            echo json_encode(array("action"=>"TEXT", "str"=>$row["message"]));
        }
    }else{
        $stm=$dbh->prepare("SELECT roomdesc FROM location WHERE rid=?");
        $stm->execute(array($_SESSION["etat_partie"]["piece"]));
        $row=$stm->fetch();
        $message = $row["roomdesc"];

        PrepareStatus();

        $objs=$_SESSION["etat_partie"]["positionObj"];
        $objNb=count($objs);
        $cpt=0;

        for ($i=0; $i < $objNb; $i++){
            if($objs[$i]["pos"] == $_SESSION["etat_partie"]["piece"]){
                if($cpt==0){
                    $stm = $dbh->prepare("SELECT message FROM smsg WHERE smid=1 ");
                    $stm->execute();
                    $row=$stm->fetch();
                    $message = $message . "\n\n" . $row["message"];
                    $cpt++;
                }
                $stm = $dbh->prepare("SELECT objdesc FROM obj WHERE objid=?");
                $stm->execute(array($objs[$i]["objid"]));
                $row=$stm->fetch();
                if ($cpt==1){
                    $message = $message . " " . $row["objdesc"];
                    $cpt++;
                }else{
                    $message = $message . "\n\t\t- ". $row["objdesc"];
                }

            }
        }
        $_SESSION["etat_partie"]["affichage"][]=$message;
        echo json_encode(array("action"=>"TEXT", "str"=>$message));
    }
}

function PrepareStatus(): void{
    $_SESSION["etat_partie"]["jeu"]["etat_machine"] = "tbl";
    $_SESSION["etat_partie"]["jeu"]["table_en_cours"] = "status";
    $_SESSION["etat_partie"]["jeu"]["action_valide"] = 0;
    $_SESSION["etat_partie"]["jeu"]["regle"] = 0;
    $_SESSION["etat_partie"]["jeu"]["num_instruction"] = 0;
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
            if ($row=$stm->fetch()){
                $words[]=$row["wid"];
                $count++;
            }elseif(is_numeric($command)){
                $words[]=$command;
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
            }else{// un mouvement n'a pas pu être fait
                //passe à l'état tbl en sélectionnant la table action
                $_SESSION["etat_partie"]["jeu"]["etat_machine"]="tbl";
                $_SESSION["etat_partie"]["jeu"]["regle"] = 0;
                $_SESSION["etat_partie"]["jeu"]["entree_table"] = $words;
                $_SESSION["etat_partie"]["jeu"]["table_en_cours"] = "action";
                $_SESSION["etat_partie"]["jeu"]["action_valide"]=0;
                $_SESSION["etat_partie"]["jeu"]["num_instruction"] = 0;

                if($_SESSION["etat_partie"]["flags"][5] != 0) $_SESSION["etat_partie"]["flags"][5]--;
                if($_SESSION["etat_partie"]["flags"][6] != 0) $_SESSION["etat_partie"]["flags"][6]--;
                if($_SESSION["etat_partie"]["flags"][7] != 0) $_SESSION["etat_partie"]["flags"][7]--;
                if($_SESSION["etat_partie"]["flags"][8] != 0) $_SESSION["etat_partie"]["flags"][8]--;
                if($_SESSION["etat_partie"]["flags"][9] != 0 && $_SESSION["etat_partie"]["flags"][0] != 0) $_SESSION["etat_partie"]["flags"][9]--;
                if($_SESSION["etat_partie"]["flags"][10] != 0 && $_SESSION["etat_partie"]["flags"][0] != 0 && $_SESSION["etat_partie"]["positionObj"][0] == -1) $_SESSION["etat_partie"]["flags"][10]--;
                //augmente le nombre de tours
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
        /*
         * -1 : non créé (not created)
         * -2 : porté (worn)
         * -3 : transporté (dans l'inventaire) (carried)
         */
        foreach ($pgm["condition"] as $condition){
            switch ($condition["nom"]) {
                case "TRUE":
                    break;
                case "AT":
                    if ($_SESSION["etat_partie"]["piece"] != $condition["param1"]) {
                        return false;
                    }
                    break;
                case "NOTAT":
                    if ($_SESSION["etat_partie"]["piece"] == $condition["param1"]) {
                        return false;
                    }
                    break;
                case "ATGT":
                    if ($_SESSION["etat_partie"]["piece"] != $condition["param1"]+1) {
                        return false;
                    }
                    break;
                case "ATLT":
                    if ($_SESSION["etat_partie"]["piece"] != $condition["param1"]-1) {
                        return false;
                    }
                    break;
                case "PRESENT":
                    $obj = (int)$condition["param1"];
                    $objs=$_SESSION["etat_partie"]["positionObj"];
                    $objNb=count($objs);
                    for ($i = 0; $i <$objNb; $i++) {
                        if ($objs[$i]["objid"] == $obj) {
                            $obj = $i;
                        }
                    }
                    if($objs[$obj]["pos"] != -2 &&
                        $objs[$obj]["pos"] != -3 &&
                        $objs[$obj]["pos"] != $_SESSION["etat_partie"]["piece"]){
                        return false;
                    }
                    break;
                case "ABSENT":
                    $obj = (int)$condition["param1"];
                    for ($i = 0; $i < count($_SESSION["etat_partie"]["positionObj"]); $i++) {
                        if ($_SESSION["etat_partie"]["positionObj"][$i]["objid"] == $obj) {
                            $obj = $i;
                        }
                    }
                    if($_SESSION["etat_partie"]["positionObj"][$obj]["pos"] == -2 ||
                        $_SESSION["etat_partie"]["positionObj"][$obj]["pos"] == -3 ||
                        $_SESSION["etat_partie"]["positionObj"][$obj]["pos"] == $_SESSION["etat_partie"]["piece"]){
                        return false;
                    }
                    break;
                case "WORN":
                    $obj = (int)$condition["param1"];
                    for ($i = 0; $i < count($_SESSION["etat_partie"]["positionObj"]); $i++) {
                        if ($_SESSION["etat_partie"]["positionObj"][$i]["objid"] == $obj) {
                            $obj = $i;
                        }
                    }
                    if($_SESSION["etat_partie"]["positionObj"][$obj]["pos"] != -2){
                        return false;
                    }
                    break;
                case "NOTWORN":
                    $obj = (int)$condition["param1"];
                    for ($i = 0; $i < count($_SESSION["etat_partie"]["positionObj"]); $i++) {
                        if ($_SESSION["etat_partie"]["positionObj"][$i]["objid"] == $obj) {
                            $obj = $i;
                        }
                    }
                    if($_SESSION["etat_partie"]["positionObj"][$obj]["pos"] == -2){
                        return false;
                    }
                    break;
                case "CARRIED":
                    $obj = (int)$condition["param1"];
                    for ($i = 0; $i < count($_SESSION["etat_partie"]["positionObj"]); $i++) {
                        if ($_SESSION["etat_partie"]["positionObj"][$i]["objid"] == $obj) {
                            $obj = $i;
                        }
                    }
                    if ($_SESSION["etat_partie"]["positionObj"][$obj]["pos"] != -3){
                        return false;
                    }
                    break;
                case "NOTCARR":
                    $obj = (int)$condition["param1"];
                    for ($i = 0; $i < count($_SESSION["etat_partie"]["positionObj"]); $i++) {
                        if ($_SESSION["etat_partie"]["positionObj"][$i]["objid"] == $obj) {
                            $obj = $i;
                        }
                    }
                    if ($_SESSION["etat_partie"]["positionObj"][$obj]["pos"] == -3){
                        return false;
                    }
                    break;
                case "CHANCE":
                    if (rand(1, 100) > $condition["param1"]) {
                        return false;
                    }
                    break;
                case "ZERO":
                    if ($_SESSION["etat_partie"]["flags"][(int)$condition["param1"]] != 0) {
                        return false;
                    }
                    break;
                case "NOTZERO":
                    if ($_SESSION["etat_partie"]["flags"][(int)$condition["param1"]] == 0) {
                        return false;
                    }
                    break;
                case "EQ":
                    if ($_SESSION["etat_partie"]["flags"][(int)$condition["param1"]] != $condition["param2"]) {
                        return false;
                    }
                    break;
                case "GT":
                    if ($_SESSION["etat_partie"]["flags"][(int)$condition["param1"]] <= $condition["param2"]) {
                        return false;
                    }
                    break;
                case "LT":
                    if ($_SESSION["etat_partie"]["flags"][(int)$condition["param1"]] >= $condition["param2"]) {
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
    // si on arrive à la dernière instruction
    if ($pgm["instruction"][$num_instruction] == null) {
        $_SESSION["etat_partie"]["jeu"]["num_instruction"]=0;
        $_SESSION["etat_partie"]["jeu"]["regle"]++;
        echo json_encode(array("action" => "NOP"));
        return;
    }
    $instruction = $pgm["instruction"][$num_instruction];
    switch ($instruction["nom"]) {
        case "NOP":
            echo json_encode(array("action" => "NOP"));
            break;
        case "INVENTORY":
            $message="";
            $stm=$dbh->prepare("SELECT message FROM smsg WHERE smid=9");
            $stm->execute();
            $row=$stm->fetch();
            $message.=$row["message"];
            $stm=$dbh->prepare("SELECT objid, objdesc FROM obj ");
            $stm->execute();
            $stm2=$dbh->prepare("SELECT message FROM smsg WHERE smid=10");
            $stm2->execute();
            $row2=$stm2->fetch();

            $cpt=0;
            while($row=$stm->fetch()) {
                if ($_SESSION["etat_partie"]["positionObj"][$row["objid"]]["pos"] == -2) {
                    if ($cpt == 0) {
                        $message .= " ". $row["objdesc"];
                        $cpt++;
                    } else {
                        $message .= "\n\t\t- ". $row["objdesc"];
                    }
                } elseif ($_SESSION["etat_partie"]["positionObj"][$row["objid"]]["pos"] == -3) {
                    if ($cpt == 0) {
                        $message .= " ". $row["objdesc"] . $row2["message"];
                        $cpt++;
                    } else {
                        $message .= "\n\t\t- ". $row["objdesc"] . $row2["message"];
                    }
                }
            }
            if ($cpt == 0) {
                $stm4=$dbh->prepare("SELECT message FROM smsg WHERE smid=11");
                $stm4->execute();
                $row4=$stm4->fetch();
                $_SESSION["etat_partie"]["affichage"][]=$row4["message"];
                echo json_encode(array("action" => "TEXT", "str" => $row4["message"]));
            }else{
                $_SESSION["etat_partie"]["affichage"][]=$message;
                echo json_encode(array("action" => "TEXT", "str" => $message));
            }
            break;
        case "DESCRIBE":
            $_SESSION["etat_partie"]["jeu"]["etat_machine"]="description";
            echo json_encode(array("action" => "NOP"));
            break;
        case "QUIT":
            $message="";
            $stm = $dbh->prepare("SELECT message FROM smsg WHERE smid=12");
            $stm->execute();
            $row = $stm->fetch();
            $message .= $row["message"]. " ";
            $stm = $dbh->prepare("SELECT message FROM smsg WHERE smid=30");
            $stm->execute();
            $row = $stm->fetch();
            $message .= $row["message"]." ";
            $stm = $dbh->prepare("SELECT message FROM smsg WHERE smid=31");
            $stm->execute();
            $row = $stm->fetch();
            $message .= $row["message"];
            $_SESSION["etat_partie"]["affichage"][]=$message;
            $_SESSION["etat_partie"]["jeu"]["etat_machine"]="QUIT";
            echo json_encode(array("action" => "YESNO", "str"=> $message));
            return;
        case "END":
            $message="";
            $stm = $dbh->prepare("SELECT message FROM smsg WHERE smid=13");
            $stm->execute();
            $row = $stm->fetch();
            $message .= $row["message"]. " ";
            $stm = $dbh->prepare("SELECT message FROM smsg WHERE smid=30");
            $stm->execute();
            $row = $stm->fetch();
            $message .= $row["message"]." ";
            $stm = $dbh->prepare("SELECT message FROM smsg WHERE smid=31");
            $stm->execute();
            $row = $stm->fetch();
            $message .= $row["message"];
            $_SESSION["etat_partie"]["affichage"][]=$message;
            $_SESSION["etat_partie"]["jeu"]["etat_machine"]="END";
            echo json_encode(array("action" => "YESNO", "str"=> $message));
            return;
        case "DONE":
            Done();
            echo json_encode(array("action" => "NOP"));
            return;
        case "OK":
            $stm=$dbh->prepare("SELECT message FROM smsg WHERE smid=15");
            $stm->execute();
            $row=$stm->fetch();
            $_SESSION["etat_partie"]["affichage"][]=$row["message"];
            Done();
            echo json_encode(array("action" => "TEXT", "str" => $row["message"]));
            return;
        case "ANYKEY":
            $stm=$dbh->prepare("SELECT message FROM smsg WHERE smid=16");
            $stm->execute();
            $row=$stm->fetch();
            $_SESSION["etat_partie"]["affichage"][]=$row["message"];
            echo json_encode(array("action" => "ANYKEY", "str"=>$row["message"]));
            break;
        case "SAVE":
            $_SESSION["etat_partie"]["jeu"]["etat_machine"]="SAVE";
            echo json_encode(array("action" => "NOP"));
            break;
        case "LOAD":
            $_SESSION["etat_partie"]["jeu"]["etat_machine"]="LOAD";
            echo json_encode(array("action" => "NOP"));
            break;
        case "TURNS":
            $message = "";
            $stm=$dbh->prepare("SELECT message FROM smsg WHERE smid=17");
            $stm->execute();
            $row = $stm->fetch();
            $message .= $row["message"];

            $message .= " " . $_SESSION["etat_partie"]["flags"][31] . " ";
            $stm=$dbh->prepare("SELECT message FROM smsg WHERE smid=18");
            $stm->execute();
            $row= $stm->fetch();
            $message .= $row["message"];

            if($_SESSION["etat_partie"]["flags"][31]>1){
                $stm=$dbh->prepare("SELECT message FROM smsg WHERE smid=19");
                $stm->execute();
                $row= $stm->fetch();
                $message .= $row["message"];
            }
            $stm=$dbh->prepare("SELECT message FROM smsg WHERE smid=20");
            $stm->execute();
            $row= $stm->fetch();
            $message .= $row["message"];
            $_SESSION["etat_partie"]["affichage"][]=$message;
            echo json_encode(array("action" => "TEXT", "str" => $message));
            break;
        case "SCORE":
            $stm=$dbh->prepare("SELECT message FROM smsg WHERE smid=21");
            $stm->execute();
            $stm2=$dbh->prepare("SELECT message FROM smsg WHERE smid=22");
            $stm2->execute();
            $row=$stm->fetch();
            $row2=$stm2->fetch();
            $message = $row["message"].$_SESSION["etat_partie"]["jeu"]["score"].$row2["message"];
            $_SESSION["etat_partie"]["affichage"][]=$message;
            echo json_encode(array("action" => "TEXT", "str" => $message));
            break;
        case "CLS":
            $_SESSION["etat_partie"]["affichage"] = [];
            echo json_encode(array("action" => "CLEAR"));
            break;
        case "DROPALL":
            $objPos = $_SESSION["etat_partie"]["positionObj"];
            $objSize = count($objPos);

            for($i=0;$i<$objSize; $i++){
                if ($objPos[$i]["pos"] == -2 || $objPos[$i]["pos"] == -3){
                    $_SESSION["etat_partie"]["positionObj"][$i]["pos"] = $_SESSION["etat_partie"]["piece"];
                }
            }
            $_SESSION["etat_partie"]["flags"][1]=0;
            echo json_encode(array("action" => "NOP"));
            break;
        case "GOTO":
            $_SESSION["etat_partie"]["piece"]=(int)$instruction["param1"];
            echo json_encode(array("action" => "NOP"));
            break;
        case "AUTOREM":
        case "REMOVE":
            if ($instruction["nom"] == "AUTOREM"){
                $obj = CheckValidObj();
                if ($obj==null){
                    return;
                }
            }else{
                $obj = $instruction["param1"];
            }
            $obj = GetObjPos($obj);
            if ($obj==null) return;

            $stm = $dbh->prepare("SELECT value FROM params WHERE param=?");
            $stm->execute(array("maxobj"));
            $row = $stm->fetch();
            $maxObj = $row["value"];
            if ($_SESSION["etat_partie"]["positionObj"][$obj]["pos"] != -2){
                $stm = $dbh->prepare("SELECT message FROM smsg WHERE smid=23");
                $stm->execute();
                $row = $stm->fetch();
                $_SESSION["etat_partie"]["affichage"][]=$row["message"];
                Done();
                echo json_encode(array("action" => "TEXT", "str" => $row["message"]));
                return;
            }
            elseif ($maxObj == $_SESSION["etat_partie"]["flags"][1]) {
                $stm = $dbh->prepare("SELECT message FROM smsg WHERE smid=24");
                $stm->execute();
                $row = $stm->fetch();
                Done();
                $_SESSION["etat_partie"]["affichage"][]=$row["message"];
                echo json_encode(array("action" => "TEXT", "str" => $row["message"]));
                return;
            }
            else{
                $_SESSION["etat_partie"]["positionObj"][$obj]["pos"] = -3;
                $_SESSION["etat_partie"]["flags"][1]++;
                if ($_SESSION["etat_partie"]["flags"][1] > 255) $_SESSION["etat_partie"]["flags"][1] = 0;
                echo json_encode(array("action" => "NOP"));
            }
            break;
        case "AUTOGET":
        case "GET":
            if ($instruction["nom"] == "AUTOGET"){
                $obj = CheckValidObj();
                if ($obj==null){
                    return;
                }
            }else{
                $obj = $instruction["param1"];
            }

            $obj = GetObjPos($obj);
            if ($obj==null) return;
            $stm = $dbh->prepare("SELECT value FROM params WHERE param=?");
            $stm->execute(array("maxobj"));
            $row = $stm->fetch();
            $maxObj = $row["value"];

            if ($_SESSION["etat_partie"]["positionObj"][$obj]["pos"] == -2 || $_SESSION["etat_partie"]["positionObj"][$obj]["pos"] == -3) {
                $stm = $dbh->prepare("SELECT message FROM smsg WHERE smid=25");
                $stm->execute();
                $row = $stm->fetch();
                Done();
                $_SESSION["etat_partie"]["affichage"][]=$row["message"];
                echo json_encode(array("action" => "TEXT", "str" => $row["message"]));
                return;
            }elseif ($_SESSION["etat_partie"]["positionObj"][$obj]["pos"] != $_SESSION["etat_partie"]["piece"]) {
                $stm = $dbh->prepare("SELECT message FROM smsg WHERE smid=26");
                $stm->execute();
                $row = $stm->fetch();
                Done();
                $_SESSION["etat_partie"]["affichage"][]=$row["message"];
                echo json_encode(array("action" => "TEXT", "str" => $row["message"]));
                return;
            }elseif ($_SESSION["etat_partie"]["flags"][1] == $maxObj) {
                $stm = $dbh->prepare("SELECT message FROM smsg WHERE smid=27");
                $stm->execute();
                $row = $stm->fetch();
                Done();
                $_SESSION["etat_partie"]["affichage"][]=$row["message"];
                echo json_encode(array("action" => "TEXT", "str" => $row["message"]));
                return;
            }else{
                $_SESSION["etat_partie"]["positionObj"][$obj]["pos"] = -3;
                $_SESSION["etat_partie"]["flags"][1]++;
                if ($_SESSION["etat_partie"]["flags"][1] > 255) $_SESSION["etat_partie"]["flags"][1] = 0;
                echo json_encode(array("action" => "NOP"));
            }
            break;
        case "AUTOWEAR":
        case "WEAR":
            if ($instruction["nom"] == "AUTOWEAR"){
                $obj = CheckValidObj();
                if ($obj==null){
                    return;
                }
            }else{
                $obj = $instruction["param1"];
            }
            $obj = GetObjPos($obj);
            if ($obj==null) return;
            if ($_SESSION["etat_partie"]["positionObj"][$obj]["pos"] == -2) {
                $stm = $dbh->prepare("SELECT message FROM smsg WHERE smid=29");
                $stm->execute();
                $row = $stm->fetch();
                Done();
                $_SESSION["etat_partie"]["affichage"][]=$row["message"];
                echo json_encode(array("action" => "TEXT", "str" => $row["message"]));
                return;
            }elseif ($_SESSION["etat_partie"]["positionObj"][$obj]["pos"] != -3){
                $stm = $dbh->prepare("SELECT message FROM smsg WHERE smid=28");
                $stm->execute();
                $row = $stm->fetch();
                Done();
                $_SESSION["etat_partie"]["affichage"][]=$row["message"];
                echo json_encode(array("action" => "TEXT", "str" => $row["message"]));
                return;
            }else{
                $_SESSION["etat_partie"]["positionObj"][$obj]["pos"] = -2;
                $_SESSION["etat_partie"]["flags"][1]++;
                if ($_SESSION["etat_partie"]["flags"][1] > 255) $_SESSION["etat_partie"]["flags"][1] = 0;
                echo json_encode(array("action" => "NOP"));
            }
            break;
        case "AUTODROP":
        case "DROP":
            if ($instruction["nom"] == "AUTODROP"){
                $obj = CheckValidObj();
                if ($obj==null){
                    return;
                }
            }else{
                $obj = $instruction["param1"];
            }
            $obj = GetObjPos($obj);
            if ($obj==null) return;
            if ($_SESSION["etat_partie"]["positionObj"][$obj]["pos"] == -2){
                $stm = $dbh->prepare("SELECT value FROM params WHERE param=?");
                $stm->execute(array("maxobj"));
                $row = $stm->fetch();
                $maxObj = $row["value"];
                if ($_SESSION["etat_partie"]["flags"][1] == $maxObj) {
                    $stm = $dbh->prepare("SELECT message FROM smsg WHERE smid=24");
                    $stm->execute();
                    $row = $stm->fetch();
                    Done();
                    $_SESSION["etat_partie"]["affichage"][]=$row["message"];
                    echo json_encode(array("action" => "TEXT", "str" => $row["message"]));
                    return;
                }else{
                    echo json_encode(array("action" => "NOP"));
                }
            }else{
                if($_SESSION["etat_partie"]["positionObj"][$obj]["pos"] != -3){
                    $stm = $dbh->prepare("SELECT message FROM smsg WHERE smid=28");
                    $stm->execute();
                    $row = $stm->fetch();
                    Done();
                    $_SESSION["etat_partie"]["affichage"][]=$row["message"];
                    echo json_encode(array("action" => "TEXT", "str" => $row["message"]));
                    return;
                }else{
                    $_SESSION["etat_partie"]["positionObj"][$obj]["pos"] = $_SESSION["etat_partie"]["piece"];
                    $_SESSION["etat_partie"]["flags"][1]--;
                    if ($_SESSION["etat_partie"]["flags"][1] < 0) $_SESSION["etat_partie"]["flags"][1] = 0;
                    echo json_encode(array("action" => "NOP"));
                }
            }
            break;
        case "CREATE":
            $obj = (int)$instruction["param1"];
            $obj = GetObjPos($obj);
            if ($obj==null) return;
            if ($_SESSION["etat_partie"]["positionObj"][$obj]["pos"] == -3) {
                $_SESSION["etat_partie"]["flags"][1]--;
                if ($_SESSION["etat_partie"]["flags"][1] < 0) $_SESSION["etat_partie"]["flags"][1] = 0;
            }
            $_SESSION["etat_partie"]["positionObj"][$obj]["pos"] = $_SESSION["etat_partie"]["piece"];
            echo json_encode(array("action" => "NOP"));
            break;
        case "DESTROY":
            $obj = (int)$instruction["param1"];
            $obj = GetObjPos($obj);
            if ($obj==null) return;
            if ($_SESSION["etat_partie"]["positionObj"][$obj]["pos"] == -3) {
                $_SESSION["etat_partie"]["flags"][1]--;
                if ($_SESSION["etat_partie"]["flags"][1] < 0) $_SESSION["etat_partie"]["flags"][1] = 0;
            }
            $_SESSION["etat_partie"]["positionObj"][$obj]["pos"] = -1;
            echo json_encode(array("action" => "NOP"));
            break;
        case "PLACE":
            $obj = $instruction["param1"];
            $obj = GetObjPos($obj);
            if ($obj==null) return;
            if ($_SESSION["etat_partie"]["positionObj"][$obj]["pos"] == -3) {
                $_SESSION["etat_partie"]["flags"][1]--;
                if ($_SESSION["etat_partie"]["flags"][1] < 0) $_SESSION["etat_partie"]["flags"][1] = 0;
            }
            $_SESSION["etat_partie"]["positionObj"][$obj]["pos"] = (int)$instruction["param2"];
            echo json_encode(array("action" => "NOP"));
            break;
        case "SWAP":
            $obj1 = $instruction["param1"];
            $obj2 = $instruction["param2"];
            $cpt = 0;
            for ($i = 0; $i < count($_SESSION["etat_partie"]["positionObj"]); $i++) {
                if ($_SESSION["etat_partie"]["positionObj"][$i]["objid"] == $obj1) {
                    $obj1 = $i;
                    $cpt++;
                }elseif ($_SESSION["etat_partie"]["positionObj"][$i]["objid"] == $obj2) {
                    $obj2 = $i;
                    $cpt++;
                }
                if ($cpt == 2) {
                    break;
                }
            }
            $tempLoc=$_SESSION["etat_partie"]["positionObj"][$obj1]["pos"];
            $_SESSION["etat_partie"]["positionObj"][$obj1]["pos"]=$_SESSION["etat_partie"]["positionObj"][$obj2]["pos"];
            $_SESSION["etat_partie"]["positionObj"][$obj2]["pos"]=$tempLoc;
            echo json_encode(array("action" => "NOP"));
            break;
        case "PAUSE":
            echo json_encode(array("action" => "PAUSE", "time" => $instruction["param1"]));
            break;
        case "MESSAGE":
            $stm = $dbh->prepare("SELECT message FROM msg WHERE mid=?");
            $stm->execute(array((int)$instruction["param1"]));
            $row = $stm->fetch();
            $_SESSION["etat_partie"]["affichage"][]=$row["message"];
            echo json_encode(array("action" => "TEXT", "str" => $row["message"]));
            break;
        case "SET":
            $_SESSION["etat_partie"]["flags"][(int)$instruction["param1"]] = 255;
            echo json_encode(array("action" => "NOP"));
            break;
        case "CLEAR":
            $_SESSION["etat_partie"]["flags"][(int)$instruction["param1"]] = 0;
            echo json_encode(array("action" => "NOP"));
            break;
        case "PLUS":
            $_SESSION["etat_partie"]["flags"][(int)$instruction["param1"]] += (int)$instruction["param2"];
            if ($_SESSION["etat_partie"]["flags"][(int)$instruction["param1"]] > 255) $_SESSION["etat_partie"]["flags"][(int)$instruction["param1"]] %= 255;
            echo json_encode(array("action" => "NOP"));
            break;
        case "MINUS":
            $_SESSION["etat_partie"]["flags"][(int)$instruction["param1"]] -= (int)$instruction["param2"];
            if ($_SESSION["etat_partie"]["flags"][(int)$instruction["param1"]] < 0) $_SESSION["etat_partie"]["flags"][(int)$instruction["param1"]] = 0;
            echo json_encode(array("action" => "NOP"));
            break;
        case "LET":
            $_SESSION["etat_partie"]["flags"][(int)$instruction["param1"]] = (int)$instruction["param2"];
            echo json_encode(array("action" => "NOP"));
            break;
    }
    $_SESSION["etat_partie"]["jeu"]["num_instruction"]++;
}

function Done(): void{
    $_SESSION["etat_partie"]["jeu"]["etat_machine"] = "tbl";
    $tblEnCours = $_SESSION["etat_partie"]["jeu"]["table_en_cours"];
    $_SESSION["etat_partie"]["jeu"]["table_en_cours"] = "status" == $tblEnCours ? "action" : "status";
    $_SESSION["etat_partie"]["jeu"]["regle"] = 0;
    $_SESSION["etat_partie"]["jeu"]["num_instruction"] = 0;
    $_SESSION["etat_partie"]["jeu"]["action_valide"] = 0;
}

function CheckValidObj(): ?int{
    global $dbh;
    $obj = $_SESSION["etat_partie"]["jeu"]["entree_table"][1];
    $stm=$dbh->prepare("SELECT objid  FROM obj WHERE wid=?");
    $stm->execute(array($obj));
    if($row = $stm->fetch()){
        return $row["objid"];
    }else{
        $stm=$dbh->prepare("SELECT message FROM smsg WHERE smid=8");
        $stm->execute();
        $row=$stm->fetch();
        $_SESSION["etat_partie"]["affichage"][]=$row["message"];
        Done();
        echo json_encode(array("action"=>"TEXT", "str"=>$row["message"]));
        return null;
    }
}

function GetObjPos($obj):?int{
    for ($i = 0; $i < count($_SESSION["etat_partie"]["positionObj"]); $i++) {
        if ($_SESSION["etat_partie"]["positionObj"][$i]["objid"] == $obj) {
            return $i;
        }
    }
    return null;
}