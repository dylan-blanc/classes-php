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
    public $login;
    public $password;
    public $email;
    public $firstname;
    public $lastname;


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
            return false;
        }

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

        if (!empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $mysqli->prepare("UPDATE utilisateurs SET login = ?, password = ?, email = ?, firstname = ?, lastname = ? WHERE id = ?");
            $stmt->bind_param("sssssi", $login, $hashedPassword, $email, $firstname, $lastname, $this->id);
            $result = $stmt->execute();
            $stmt->close();

            if ($result) {
                $this->login = $login;
                $this->password = $hashedPassword;
                $this->email = $email;
                $this->firstname = $firstname;
                $this->lastname = $lastname;
            }
            return $result;
        } else {
            $stmt = $mysqli->prepare("UPDATE utilisateurs SET login = ?, email = ?, firstname = ?, lastname = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $login, $email, $firstname, $lastname, $this->id);
            $result = $stmt->execute();
            $stmt->close();

            if ($result) {
                $this->login = $login;
                $this->email = $email;
                $this->firstname = $firstname;
                $this->lastname = $lastname;
            }
            return $result;
        }
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

