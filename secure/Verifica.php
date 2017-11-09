<?php

class Verifica {

    public function getIDFormatoreByToken($tokenFormatore) {

        $idFormatore = null;

        require_once ("Connection.php");
        $file = file_get_contents("../credentials.json");
        $json = json_decode($file);
        $dbPasswd = $json["password"];
        $dbUser = $json["user"];

        $connessione = new Connection("localhost", $dbUser, $dbPasswd, "App");
        $connessione->connect();

        $query = "SELECT idFormatore FROM Formatori WHERE token = ?";
        $stmt = $connessione->conn->prepare($query);
        if (!$stmt->bind_param("s", $tokenFormatore)) {
            return -1;
        }

        if(!$stmt->execute()) {
            return -1;
        }

        $stmt->bind_result($idFormatore);
        $stmt->fetch();
        $stmt->close();

        return $idFormatore;

    }

    public function getIDMateria_Classe($materia, $classe) {

        require_once ("Connection.php");
        $file = file_get_contents("../credentials.json");
        $json = json_decode($file);
        $dbPasswd = $json["password"];
        $dbUser = $json["user"];

        $connessione = new Connection("localhost", $dbUser, $dbPasswd, "App");
        $connessione->connect();

        $idMateriaClasse = null;

        $query = "SELECT id FROM MateriePerClasse
                  JOIN Materie ON MateriePerClasse.idMateria = Materie.idMateria
                  JOIN Classi ON MateriePerClasse.idClasse = Classi.idClasse
                  WHERE Materie.materia = ? AND Classi.classe = ?";

        $stmt = $connessione->conn->prepare($query);

        $stmt->bind_param("ss", $materia, $classe);

        if (!$stmt->execute()) {
            return -1;
        }

        $stmt->bind_result($idMateriaClasse);

        if (!$stmt->fetch()) {
            return -1;
        }

        $stmt->close();

        return $idMateriaClasse;
    }






}


?>
