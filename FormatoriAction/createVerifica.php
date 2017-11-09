<?php

$materia = htmlentities($_REQUEST["materia"]);
$classe = htmlentities($_REQUEST["classe"]);
$data = htmlentities($_REQUEST["data"]);
$titolo = htmlentities($_REQUEST["titolo"]);

$token = htmlentities($_REQUEST["token"]);


if (!isset($materia) || !isset($classe) || !isset($data) || !isset($titolo) || !isset($token)) {
    $responseValue["code"] = "400";
    $responseValue["message"] = "Manca qualcosa nei dati che hai inviato";
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

$result = $connessione->createVerifica($materia, $classe, $titolo, $data, $token);

if ($result != 0) {
    $responseValue["code"] = "400";
    $responseValue["message"] = "C'è stato un errore durante la creazione della verifica";
}
else {
    $responseValue["code"] = "200";
    $responseValue["message"] = "Verifica creata con successo";
}

$connessione->disconnect();

echo json_encode($responseValue);


?>
