<?php

$idVerifica = htmlentities($_REQUEST["idVerifica"]);
$token = htmlentities($_REQUEST["token"]);

if (!isset($idVerifica) || !isset($token)) {
    $responseValue["code"] = "400";
    $responseValue["message"] = "Non hai dato tutti i valori necessari";

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

$result = $connessione->setVerificaCorretta($idVerifica, $token);

if ($result == -2) {
    $responseValue["code"] = "400";
    $responseValue["message"] = "Non sono riuscito a verificare la tua identità con il token";
    $responseValue["token"] = $token;

    $connessione->disconnect();
    echo json_encode($responseValue);
    exit(1);
}

if ($result == -1) {
    $responseValue["code"] = "400";
    $responseValue["message"] = "C'è stato un errore durante l'update della verifica";

    $connessione->disconnect();
    echo json_encode($responseValue);
    exit(1);
}


$connessione->disconnect();

$responseValue["code"] = "200";
$responseValue["message"] = "Verifica aggiornata con successo";

echo json_encode($responseValue);


?>
