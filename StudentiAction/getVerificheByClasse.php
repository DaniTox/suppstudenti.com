<?php

$classe = htmlentities($_REQUEST["classe"]);

if (!isset($classe)) {
    $responseValue["code"] = "400";
    $responseValue["message"] = "Non hai scritto la classe";

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

$verifiche = $connessione->getVerificheByClasse($classe);

if ($verifiche == -1) {
    $responseValue["code"] = "400";
    $responseValue["message"] = "C'è stato un errore";

    $connessione->disconnect();
    echo json_encode($responseValue);
    exit(1);
}

echo json_encode($verifiche);

$connessione->disconnect();




?>
