<?php

$token = htmlentities($_REQUEST["token"]);
$idVerifica = htmlentities($_REQUEST["idVerifica"]);
$voto = htmlentities($_REQUEST["voto"]);

if (!isset($token) || !isset($idVerifica) || !isset($voto)) {
    $responseValue["code"] = "400";
    $responseValue["message"] = "Manca qualcosa nei dati che hai inserito";

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

$result = $connessione->insertVotoInDB($token, $idVerifica, $voto);

if ($result == -2) {
    $responseValue["code"] = "400";
    $responseValue["message"] = "C'è stato un errore durante l'aggiunta del voto";

    $connessione->disconnect();
    echo json_encode($responseValue);
    exit(1);
}

if ($result == -1) {
    $responseValue["code"] = "400";
    $responseValue["message"] = "Impossibile trovare l'idStudente dal token";

    $connessione->disconnect();
    echo json_encode($responseValue);
    exit(1);
}

$responseValue["code"] = "200";
$responseValue["message"] = "Voto aggiunto con successo";

$connessione->disconnect();

echo json_encode($responseValue);

?>
