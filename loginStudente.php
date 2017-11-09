<?php

$email = htmlentities($_REQUEST["email"]);
$password = htmlentities($_REQUEST["password"]);

if ($email == null || $password == null) {
    $responsevalue["code"] = "400";
    $responsevalue["message"] = "Manca qualcosa nei valori richiesti";

    echo json_encode($responsevalue);
    exit(1);
}


require ("secure/Connection.php");

$file = file_get_contents("credentials.json");
$json = json_decode($file);
$dbPasswd = $json["password"];
$dbUser = $json["user"];

$connessione = new Connection("localhost", $dbUser, $dbPasswd, "App");
$connessione->connect();

$result = $connessione->loginStudente($email, $password);

if ($result == 1) {
    $responsevalue["code"] = "400";
    $responsevalue["message"] = "Password errata";

    echo json_encode($responsevalue);
    exit(1);
}
else if ($result == -1) {
    $responsevalue["code"] = "400";
    $responsevalue["message"] = "Non è stato trovato nessuno studente con quella mail";
    echo json_encode($responsevalue);
    exit(1);
}

else {
    $responsevalue["code"] = "200";
    $responsevalue["Message"] = "Password Corretta!";
    $responsevalue["id"] = (string) $result["id"];
    $responsevalue["nome"] = $result["nome"];
    $responsevalue["cognome"] = $result["cognome"];
    $responsevalue["email"] = $result["email"];
    $responsevalue["classe"] = $result["classe"];
    $responsevalue["token"] = $result["token"];

}


echo json_encode($responsevalue);

$connessione->disconnect();
?>
