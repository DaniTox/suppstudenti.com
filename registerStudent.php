<?php

$nome = htmlentities($_REQUEST["nome"]);
$cognome = htmlentities($_REQUEST["cognome"]);
$email = htmlentities($_REQUEST["email"]);
$password = htmlentities($_REQUEST["password"]);
$classe = htmlentities($_REQUEST["classe"]);

if ($nome == null || $cognome == null || $email == null || $password == null || $classe == null) {

    $responseValue["code"] = "400";
    $responseValue["message"] = "Manca qualcosa nei valori inviati";

    echo json_encode($responseValue);
    exit(1);
}


require ("secure/Connection.php");

$file = file_get_contents("credentials.json");
$json = json_decode($file, true);
$dbPasswd = $json["password"];
$dbUser = $json["user"];

$connessione = new Connection("localhost", $dbUser, $dbPasswd, "App");
$connessione->connect();

if ($connessione->registerStudent($nome, $cognome, $email, $password, $classe) != 0 ) {
    $responseValue["code"] = "400";
    $responseValue["message"] = "C'è stato un errore durante la registrazione";
}

$user = $connessione->getStudente($email);
if ($user == null || $user == -1) {
    $responseValue["code"] = "400";
    $responseValue["message"] = "Non riesco a recuperare lo studente";
}

else if ($user == -2) {
    $responseValue["code"] = "400";
    $responseValue["message"] = "Non sono riuscito a prendere la classeString dall'idClasse che hai";
}

else {
    $responseValue["code"] = "200";
    $responseValue["message"] = "Registrato con successo";

    $id = strval($responseValue["id"]);
    $responseValue["id"] = $id;
    $responseValue["nome"] = $user["nome"];
    $responseValue["cognome"] = $user["cognome"];
    $responseValue["email"] = $user["email"];
    $responseValue["token"] = $user["token"];
    $responseValue["classe"] = $user["classe"];
}

echo json_encode($responseValue);

$connessione->disconnect();

?>
