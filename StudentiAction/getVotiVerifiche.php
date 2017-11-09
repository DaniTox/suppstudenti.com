<?php

$token = htmlentities($_REQUEST["token"]);

if (!isset($token)) {
    $responseValue["code"] = "400";
    $responseValue["message"] = "Non hai dato il token";

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


$voti = $connessione->getVotiVerificheStudente($token);

if ($voti == -1) {
    $responseValue["code"] = "400";
    $responseValue["message"] = "C'è stato un errore a recuperare i voti";

}

else if ($voti == -2) {
    $responseValue["code"] = "201";
    $responseValue["message"] = "Non ci sono voti";
}

else {
    $responseValue["code"] = "200";
    $responseValue["message"] = "Voti ottenuti correttamente";
    $responseValue["voti"] = $voti;
}



$connessione->disconnect();
echo json_encode($responseValue);

?>
