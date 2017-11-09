<?php

$token = htmlentities($_REQUEST["token"]);

if (!isset($token)) {
    $responseValue["code"] = "400";
    $responseValue["message"] = "Non hai dato il tuo token";

    echo json_encode($responseValue);
    exit(1);
}

require ("../secure/Connection.php");
$file = file_get_contents("../credentials.json");
$json = json_decode($file);
$dbPasswd = $json["password"];
$dbUser = $json["user"];

$connessione = new Connection("localhost", $dbUser, $dbPasswd, "App");
$connessione->connect();

$verifiche = $connessione->getVerificheFormatore($token);

if ($verifiche != null) {
    echo json_encode($verifiche);
}


$connessione->disconnect();


?>
