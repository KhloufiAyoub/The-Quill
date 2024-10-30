<?php
session_start();

if (isset($_POST["gameInput"])){
    $gameInput = $_POST["gameInput"];
    if(!is_numeric($gameInput) && strlen($gameInput) >= 1) {
        echo $gameInput;
    } else {
        echo "problem";
    }
}