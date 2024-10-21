<?php
session_start();

if (isset($_POST["gameInput"])){
    $gameInput = $_POST["gameInput"];
    if(strlen($gameInput) >=1){
        echo $gameInput;
    }else {
        echo "problem";
    }
}