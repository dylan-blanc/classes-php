<?php


session_start();

$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'classes';

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die('Erreur de connexion (' . $conn->connect_errno . ') ' . $conn->connect_error);
}

class User
{
    private $id;
    private $login;
    private $password;
    private $email;
    private $firstname;
    private $lastname;
    public $error;

    public function __construct($id, $login, $email, $firstname, $lastname)
    {
        $this->id = $id;
        $this->login = $login;
        $this->email = $email;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
    }
    public function register($mysqli, $login, $password, $email, $firstname, $lastname)
    {
        if (empty($password)) {
            $this->error = "Mot de passe requis";
            return false;
        }
        if (empty($login) || empty($email) || empty($firstname) || empty($lastname)) {
            $this->error = "Tous les champs sont requis";
            return false;
        }

        // empeche d'enregistrer si doublon
        $stmt = $mysqli->prepare("SELECT id FROM utilisateurs WHERE login = ? OR email = ?");
        if (!$stmt) {
            $this->error = "Erreur lors de l'enregistrement";
            return false;
        }
        $stmt->bind_param("ss", $login, $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $this->error = "Login ou email déjà utilisé";
            $stmt->close();
            return false;
        }
        $stmt->close();


        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $mysqli->prepare("INSERT INTO utilisateurs (login, password, email, firstname, lastname) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("sssss", $login, $hashedPassword, $email, $firstname, $lastname);
        $result = $stmt->execute();

        if ($result && $mysqli->insert_id > 0) {
            $this->id = $mysqli->insert_id;
            $this->login = $login;
            $this->password = $hashedPassword;
            $this->email = $email;
            $this->firstname = $firstname;
            $this->lastname = $lastname;
        }

        $stmt->close();

        return $result;
    }

    public function connect($login, $password)
    {
        if ($this->login === $login && password_verify($password, $this->password)) {
            $_SESSION['user_id'] = $this->id;
            session_regenerate_id();
            return true;
        } else {
            return false;
        }
    }

    public function disconnect()
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    public function delete($mysqli)
    {
        if (!$this->id) {
            return false;
        }
        $stmt = $mysqli->prepare("DELETE FROM utilisateurs WHERE id = ?");
        $stmt->bind_param("i", $this->id);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            $this->disconnect();
        }
        return $result;
    }

    public function update($mysqli, $login, $password, $email, $firstname, $lastname)
    {
        if (!$this->id) {
            return false;
        }

        $fields = [];
        $params = [];
        $types = '';

        if (!empty($login)) {
            $fields[] = 'login = ?';
            $params[] = $login;
            $types .= 's';
        }
        if (!empty($password)) {
            $fields[] = 'password = ?';
            $params[] = password_hash($password, PASSWORD_DEFAULT);
            $types .= 's';
        }
        if (!empty($email)) {
            $fields[] = 'email = ?';
            $params[] = $email;
            $types .= 's';
        }
        if (!empty($firstname)) {
            $fields[] = 'firstname = ?';
            $params[] = $firstname;
            $types .= 's';
        }
        if (!empty($lastname)) {
            $fields[] = 'lastname = ?';
            $params[] = $lastname;
            $types .= 's';
        }

        if (empty($fields)) {
            // si aucun champs rempli alors :
            return false;
        }

        $sql = "UPDATE utilisateurs SET " . implode(', ', $fields) . " WHERE id = ?";
        $params[] = $this->id;
        $types .= 'i';

        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            if (!empty($login)) $this->login = $login;
            if (!empty($password)) $this->password = password_hash($password, PASSWORD_DEFAULT);
            if (!empty($email)) $this->email = $email;
            if (!empty($firstname)) $this->firstname = $firstname;
            if (!empty($lastname)) $this->lastname = $lastname;
        }
        return $result;
    }

    public function isConnected()
    {
        return isset($_SESSION['user_id']) && $_SESSION['user_id'] === $this->id;
    }

    public function getAllinfos()
    {
        return [
            'id' => $this->id,
            'login' => $this->login,
            'email' => $this->email,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname
        ];
    }

    public function getLogin()
    {
        return $this->login;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getFirstname()
    {
        return $this->firstname;
    }

    public function getLastname()
    {
        return $this->lastname;
    }
}
