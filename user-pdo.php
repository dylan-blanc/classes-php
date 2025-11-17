<?php

session_start();
$host = 'localhost';
$user = 'user';
$username = 'root';
$password = '';
$dbname = 'classes';

$conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);

class Userpdo
{
    private $id;
    private $login;
    private $password;
    private $email;
    private $firstname;
    private $lastname;
    public $error;

    public function __construct($id, $login, $password, $email, $firstname, $lastname)
    {

        $this->id = $id;
        $this->login = $login;
        $this->password = $password;
        $this->email = $email;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
    }

    public function register($pdo, $login, $password, $email, $firstname, $lastname)
    {
        if (empty($password)) {
            $this->error = "Mot de passe requis";
            return false;
        }
        if (empty($login) || empty($email) || empty($firstname) || empty($lastname)) {
            $this->error = "Tous les champs sont requis";
            return false;
        }
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare("INSERT INTO utilisateurs (login, password, email, firstname, lastname) VALUES (?,?,?,?,?)");
            if (!$stmt) {
                return false;
            }

            $result = $stmt->execute([$login, $hashedPassword, $email, $firstname, $lastname]);

            if ($result) {
                $this->id = $pdo->lastInsertId();
                $this->login = $login;
                $this->password = $hashedPassword;
                $this->email = $email;
                $this->firstname = $firstname;
                $this->lastname = $lastname;
            }

            $stmt->closeCursor();
            return $result;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function connect($pdo, $login, $password)
    {
        if ($this->login === $login && password_verify($password, $this->password)) {
            $_SESSION['user_id'] = $this->id;
            session_regenerate_id();
            return true;
        } else {
            $this->error = "Identifiants invalides";
            return false;
        }
    }

    public function disconnect()
    { {
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_destroy();
            }
        }
    }

    public function delete($pdo)
    {
        if (!$this->id) {
            return false;
        }

        try {
            $stmt = $pdo->prepare("DELETE FROM utilisateurs WHERE id = ?");
            $result = $stmt->execute([$this->id]);
            $stmt->closeCursor();

            if ($result) {
                $this->disconnect();
            }

            return $result;
        } catch (PDOException $e) {
            $this->error = "Erreur lors de la suppression : " . $e->getMessage();
            return false;
        }
    }

    public function update($pdo, $login, $password, $email, $firstname, $lastname)
    {
        if (!$this->id) {
            return false;
        }

        $fields = [];
        $params = [];
        $hashedPassword = null;

        if (!empty($login)) {
            $fields[] = 'login = ?';
            $params[] = $login;
        }
        if (!empty($password)) {
            $fields[] = 'password = ?';
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $params[] = $hashedPassword;
        }
        if (!empty($email)) {
            $fields[] = 'email = ?';
            $params[] = $email;
        }
        if (!empty($firstname)) {
            $fields[] = 'firstname = ?';
            $params[] = $firstname;
        }
        if (!empty($lastname)) {
            $fields[] = 'lastname = ?';
            $params[] = $lastname;
        }

        if (empty($fields)) {
            //  si aucun champ rempli :
            return false;
        }

        // verifie si login ou l'email existe deja
        try {
            $checkParts = [];
            $checkParams = [];

            if (!empty($login)) {
                $checkParts[] = 'login = ?';
                $checkParams[] = $login;
            }
            if (!empty($email)) {
                $checkParts[] = 'email = ?';
                $checkParams[] = $email;
            }

            if (!empty($checkParts)) {
                $checkSql = 'SELECT id FROM utilisateurs WHERE (' . implode(' OR ', $checkParts) . ') AND id != ?';
                $checkParams[] = $this->id;

                $checkStmt = $pdo->prepare($checkSql);
                $checkStmt->execute($checkParams);
                if ($checkStmt->fetch()) {
                    $checkStmt->closeCursor();

                    return false;
                }
                $checkStmt->closeCursor();
            }

            $sql = 'UPDATE utilisateurs SET ' . implode(', ', $fields) . ' WHERE id = ?';
            $params[] = $this->id;

            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute($params);
            $stmt->closeCursor();

            if ($result) {
                if (!empty($login)) $this->login = $login;
                if (!empty($password) && $hashedPassword) $this->password = $hashedPassword;
                if (!empty($email)) $this->email = $email;
                if (!empty($firstname)) $this->firstname = $firstname;
                if (!empty($lastname)) $this->lastname = $lastname;
            }

            return $result;
        } catch (PDOException $e) {
            echo 'Erreur lors de la mise à jour : ' . $e->getMessage();
            return false;
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
