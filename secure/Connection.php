<?php

class Connection {

    private $host = null;
    private $username = null;
    private $password = null;
    private $name = null;

    var $conn = null;

    private $result = null;


    function __construct($dbhost, $dbusername, $dbpasswd, $dbname) {
        $this->host = $dbhost;
        $this->username = $dbusername;
        $this->password = $dbpasswd;
        $this->name = $dbname;
    }

    function connect() {
        $this->conn = new mysqli($this->host,$this->username,$this->password,
            $this->name);

        if (mysqli_connect_errno()) {
            echo "Impossibile connetersi al database: " . mysqli_connect_error() . "\n";
        }

        $this->conn->set_charset("utf8");
    }

    function disconnect() {
        if ($this->conn != null) {
            $this->conn->close();
        }
    }

    function registerStudent($nome, $cognome, $email, $password, $classe) {

        $idClasse = $this->getidClasseByString($classe);
        if ($idClasse == -1) {
            return 1;
        }

        require ("DBPasswordHelper.php");
        $passwdHelper = new DBPasswordHelper();
        $salt = $passwdHelper->createSalt();
        $token = $passwdHelper->createToken();

        $tempPasswd = $password;
        $passwordhashed = hash("sha512", $tempPasswd.$salt);
        $passwdCompleted = $passwdHelper->iniettaStringa($passwordhashed);


        $query = "INSERT INTO Studenti (nome, cognome, email, password, salt, idClasse, token) VALUES (?,?,?,?,?,?,?)";
        if ($stmt = $this->conn->prepare($query)) {
            $stmt->bind_param("sssssis", $nome, $cognome, $email, $passwdCompleted,
                $salt, $idClasse, $token);

            if (!$stmt->execute()) {
                return -1;
            }
        $stmt->close();

        }

        return 0;

    }

    public function getStudente($emailStudente) {
        $query = "SELECT * FROM Studenti WHERE email = ?";
        $stmt = $this->conn->prepare($query);

        $stmt->bind_param("s", $emailStudente);

        if (!$stmt->execute()) {
            return -1;
        }
        $user = null;

        $result = $stmt->get_result();

        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
            if (!empty($row)) {
                $user = $row;
            }

        }

        $stmt->close();

        $classeString = $this->getClassebyID($user["idClasse"]);

        if ($classeString == null) {
            return -2;
        }

        $user["classe"] = $classeString;


        return $user;

    }


    public function loginStudente($emailStudente, $passwordStudente) {
        $user = $this->getStudente($emailStudente);
        if ($user == null || $user == -1) {
            return -1;
        }

        $salt = $user["salt"];
        $rightPassword = $user["password"];

        $passwordTypedHashed = hash("sha512", $passwordStudente.$salt);

        require ("DBPasswordHelper.php");
        $passwdHelper = new DBPasswordHelper();
        $finalPasswdtyped = $passwdHelper->iniettaStringa($passwordTypedHashed);

        if ($finalPasswdtyped != $rightPassword) {
            return 1;
        }

        //$token = $user["token"];

        $classeString = $this->getClassebyID($user["idClasse"]);
        if ($classeString == null) {
            return -1;
        }

        $user["classe"] =  $classeString;

        return $user;
    }

    private function getidClasseByString($classeString) {

        $query = "SELECT idClasse FROM Classi WHERE classe = ?";
        $idClasse = null;
        if ($stmt = $this->conn->prepare($query)) {
            $stmt->bind_param("s", $classeString);
            $stmt->execute();

            $stmt->bind_result($idClasse);
            $stmt->fetch();
            $stmt->close();
        }
        else {
            return -1;
        }

        return $idClasse;
    }

    private function getClassebyID($idClasse) {
        $query = "SELECT classe FROM Classi WHERE idClasse = ?";
        $stmt = $this->conn->prepare($query);

        $stmt->bind_param("i", $idClasse);

        $stmt->execute();

        $classe = null;

        $stmt->bind_result($classe);
        $stmt->fetch();

        $stmt->close();

        return $classe;


    }

    public function createVerifica($materia, $classe, $titolo, $data, $token) {
        require ("Verifica.php");
        $verifica = new Verifica();

        $idMateriaClasse = $verifica->getIDMateria_Classe($materia, $classe);

        if ($idMateriaClasse == -1) {
            return -1;
        }

        $idFormatore = $verifica->getIDFormatoreByToken($token);
        if ($idFormatore == -1) {
            return -1;
        }


        $query = "INSERT INTO Verifiche (idMateriaClasse, Data, Titolo, idFormatore) VALUES (?,?,?,?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("issi", $idMateriaClasse, $data, $titolo, $idFormatore);

        if (!$stmt->execute()) {
            return -1;
        }

        return 0;

    }

    public function getVerificheByClasse($classeSelezionata) {

        $query = "SELECT idVerifica, Data, Svolgimento, Titolo, classe, Materia, Formatore FROM Verifiche
                  JOIN MateriePerClasse ON Verifiche.idMateriaClasse = MateriePerClasse.id
                  JOIN Classi ON MateriePerClasse.idClasse = Classi.idClasse
                  JOIN Materie ON MateriePerClasse.idMateria = Materie.idMateria
                  JOIN Formatori ON Verifiche.idFormatore = Formatori.idFormatore 
                  WHERE Classi.classe = ?";

        $stmt = $this->conn->prepare($query);

        $stmt->bind_param("s", $classeSelezionata);

        if (!$stmt->execute()) {
            return -1;
        }

        if (!$result = $stmt->get_result()) {
            return -1;
        }

        $verifiche = null;
        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
            if (!empty($row)) {
                $verifiche[] = array(
                    'idVerifica' => $row['idVerifica'],
                    'titolo' => $row['Titolo'],
                    'materia' => $row['Materia'],
                    'classe' => $row['classe'],
                    'data' => $row['Data'],
                    'formatore' => $row['Formatore'],
                    'svolgimento' => $row['Svolgimento'],
                );
            }
        }

        $stmt->close();

        return $verifiche;

    }

    public function insertVotoInDB($tokenStudent, $idVerificaSelezionato, $votoStudente) {
        $idStudente = $this->getIdStudenteByToken($tokenStudent);

        if ($idStudente == -1) {
            return -1;
        }

        $query = "INSERT INTO Voti (idStudente, idVerifica, Voto) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($query);

        $stmt->bind_param("iii", $idStudente, $idVerificaSelezionato, $votoStudente);

        if (!$stmt->execute()) {
            return -2;
        }



        $stmt->close();


    }

    private function getIdStudenteByToken($token) {
        $query = "SELECT id FROM Studenti WHERE token = ?";
        $stmt = $this->conn->prepare($query);

        $stmt->bind_param("s", $token);

        if (!$stmt->execute()) {
            return -1;
        }

        $idStudente = null;
        if (!$stmt->bind_result($idStudente)) {
            return -1;
        }

        $stmt->fetch();

        $stmt->close();
        return $idStudente;
    }


    public function getVotiVerificheStudente($token) {
        $idStudente = $this->getIdStudenteByToken($token);

        $query = "SELECT Voto, Titolo, Data, materia FROM Voti
                  JOIN Verifiche ON Voti.idVerifica = Verifiche.idVerifica
                  JOIN MateriePerClasse ON Verifiche.idMateriaClasse = MateriePerClasse.id
                  JOIN Materie ON MateriePerClasse.idMateria = Materie.idMateria
                  WHERE Voti.idStudente = ?";

        $stmt = $this->conn->prepare($query);

        $stmt->bind_param("i", $idStudente);

        if (!$stmt->execute()) {
            return -1;
        }

        if (!$result = $stmt->get_result()) {
            return -1;
        }

        $voti = null;
        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {

            if (!empty($row)) {
                $voti[] = array(
                    'Voto' => $row["Voto"],
                    'Titolo' => $row["Titolo"],
                    'Data' => $row["Data"],
                    'Materia' => $row["materia"],
                );
            }


        }
        $stmt->close();

        if ($voti == null) {
            return -2;
        }

        return $voti;


    }

    public function getVerificheFormatore($token) {

        require ("Verifica.php");
        $verificaHelper = new Verifica();
        $idFormatore = $verificaHelper->getIDFormatoreByToken($token);

        $query = "SELECT idVerifica, materia, Titolo, Data, classe FROM Verifiche
                  JOIN MateriePerClasse ON Verifiche.idMateriaClasse = MateriePerClasse.id
                  JOIN Classi ON MateriePerClasse.idClasse = Classi.idClasse
                  JOIN Materie ON MateriePerClasse.idMateria = Materie.idMateria
                  WHERE Verifiche.idFormatore = ? AND Svolgimento = 0";

        $stmt = $this->conn->prepare($query);

        $stmt->bind_param("i", $idFormatore);

        $stmt->execute();

        $result = $stmt->get_result();

        $verifiche = null;
        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
            if (!empty($row)) {
                $verifiche[] = array(
                   "idVerifica" => $row["idVerifica"],
                    "materia" => $row["materia"],
                    "titolo" => $row["Titolo"],
                    "data" => $row["Data"],
                    "classe" => $row["classe"],
                );
            }
        }

        $stmt->close();

        return $verifiche;

    }

    public function setVerificaCorretta($idVerifica, $token) {

        $isGenuine = $this->checkGenuineConnection($token);
        if ($isGenuine == -2) {
            return -2;
        }

        $query = "UPDATE Verifiche SET Svolgimento = 1 WHERE idVerifica = ?";
        $stmt = $this->conn->prepare($query);

        $stmt->bind_param("i", $idVerifica);
        if (!$stmt->execute()) {
            return -1;
        }
        $stmt->close();

        return 0;

    }

    private function checkGenuineConnection($token) {
        $query = "SELECT token FROM Formatori";

        $result = $this->conn->query($query);
        $tokensAvailable = $result->fetch_array(MYSQLI_NUM);

        foreach ($tokensAvailable as $value) {
            if ($value == $token) {
                return -2;
            }
        }

        $result->free();
        return 0;
    }


}



?>