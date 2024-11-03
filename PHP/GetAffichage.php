<?php
session_start();

echo json_encode(array("affichage"=>$_SESSION["etat_partie"]["affichage"]));