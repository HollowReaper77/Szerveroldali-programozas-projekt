<?php
class Felhasznalo {
    private $conn;
    private $table = "felhasznalo";

    // Mezők
    public $felhasznalo_id;
    public $felhasznalonev;
    public $email;
    public $jelszo;
    public $profilkep_url;
    public $jogosultsag; // 'user', 'moderator', 'admin'
    public $regisztracio_ideje;
    public $aktiv;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Összes felhasználó lekérése (admin funkció)
    public function read() {
        $query = "SELECT felhasznalo_id, felhasznalonev, email, profilkep_url, jogosultsag, regisztracio_ideje, aktiv 
                  FROM " . $this->table . " 
                  WHERE aktiv = 1
                  ORDER BY regisztracio_ideje DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Egy felhasználó lekérése ID alapján
    public function read_single() {
        $query = "SELECT felhasznalo_id, felhasznalonev, email, profilkep_url, jogosultsag, regisztracio_ideje, aktiv 
                  FROM " . $this->table . " 
                  WHERE felhasznalo_id = :id 
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->felhasznalo_id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->felhasznalonev = $row['felhasznalonev'];
            $this->email = $row['email'];
            $this->profilkep_url = $row['profilkep_url'];
            $this->jogosultsag = $row['jogosultsag'];
            $this->regisztracio_ideje = $row['regisztracio_ideje'];
            $this->aktiv = $row['aktiv'];
            return true;
        }
        
        return false;
    }

    // Felhasználó lekérése email alapján (login-hoz)
    public function findByEmail() {
        $query = "SELECT felhasznalo_id, felhasznalonev, email, jelszo, profilkep_url, jogosultsag, regisztracio_ideje, aktiv 
                  FROM " . $this->table . " 
                  WHERE email = :email 
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $this->email);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->felhasznalo_id = $row['felhasznalo_id'];
            $this->felhasznalonev = $row['felhasznalonev'];
            $this->email = $row['email'];
            $this->jelszo = $row['jelszo'];
            $this->profilkep_url = $row['profilkep_url'];
            $this->jogosultsag = $row['jogosultsag'];
            $this->regisztracio_ideje = $row['regisztracio_ideje'];
            $this->aktiv = $row['aktiv'];
            return true;
        }
        
        return false;
    }

    // Új felhasználó létrehozása (regisztráció)
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  (felhasznalonev, email, jelszo, profilkep_url, jogosultsag) 
                  VALUES (:felhasznalonev, :email, :jelszo, :profilkep_url, :jogosultsag)";
        
        $stmt = $this->conn->prepare($query);
        
        // Input tisztítás
        $this->felhasznalonev = htmlspecialchars(strip_tags($this->felhasznalonev));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->profilkep_url = htmlspecialchars(strip_tags($this->profilkep_url));
        
        // Alapértelmezett jogosultság: user
        if (empty($this->jogosultsag)) {
            $this->jogosultsag = 'user';
        }
        
        // Jelszó hash
        $hashed_password = password_hash($this->jelszo, PASSWORD_DEFAULT);
        
        // Bind paraméterek
        $stmt->bindParam(':felhasznalonev', $this->felhasznalonev);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':jelszo', $hashed_password);
        $stmt->bindParam(':profilkep_url', $this->profilkep_url);
        $stmt->bindParam(':jogosultsag', $this->jogosultsag);
        
        if ($stmt->execute()) {
            $this->felhasznalo_id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }

    // Felhasználó adatok frissítése
    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET felhasznalonev = :felhasznalonev, 
                      email = :email,
                      profilkep_url = :profilkep_url
                  WHERE felhasznalo_id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Input tisztítás
        $this->felhasznalonev = htmlspecialchars(strip_tags($this->felhasznalonev));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->profilkep_url = htmlspecialchars(strip_tags($this->profilkep_url));
        $this->felhasznalo_id = htmlspecialchars(strip_tags($this->felhasznalo_id));
        
        // Bind paraméterek
        $stmt->bindParam(':felhasznalonev', $this->felhasznalonev);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':profilkep_url', $this->profilkep_url);
        $stmt->bindParam(':id', $this->felhasznalo_id);
        
        return $stmt->execute();
    }

    // Jogosultság frissítése (csak admin)
    public function updateRole() {
        $query = "UPDATE " . $this->table . " 
                  SET jogosultsag = :jogosultsag 
                  WHERE felhasznalo_id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':jogosultsag', $this->jogosultsag);
        $stmt->bindParam(':id', $this->felhasznalo_id);
        
        return $stmt->execute();
    }

    // Jelszó frissítése
    public function updatePassword() {
        $query = "UPDATE " . $this->table . " 
                  SET jelszo = :jelszo 
                  WHERE felhasznalo_id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Jelszó hash
        $hashed_password = password_hash($this->jelszo, PASSWORD_DEFAULT);
        
        $stmt->bindParam(':jelszo', $hashed_password);
        $stmt->bindParam(':id', $this->felhasznalo_id);
        
        return $stmt->execute();
    }

    // Felhasználó törlése (soft delete)
    public function delete() {
        $query = "UPDATE " . $this->table . " 
                  SET aktiv = 0 
                  WHERE felhasznalo_id = :id";
        
        $stmt = $this->conn->prepare($query);
        $this->felhasznalo_id = htmlspecialchars(strip_tags($this->felhasznalo_id));
        $stmt->bindParam(':id', $this->felhasznalo_id);
        
        return $stmt->execute();
    }

    // Email ellenőrzése (létezik-e már)
    public function emailExists() {
        $query = "SELECT felhasznalo_id FROM " . $this->table . " 
                  WHERE email = :email 
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $this->email);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }

    // Felhasználónév ellenőrzése
    public function usernameExists() {
        $query = "SELECT felhasznalo_id FROM " . $this->table . " 
                  WHERE felhasznalonev = :felhasznalonev 
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':felhasznalonev', $this->felhasznalonev);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }

    // Jelszó ellenőrzése
    public function verifyPassword($password) {
        return password_verify($password, $this->jelszo);
    }

    // Jogosultság ellenőrzése
    public function hasRole($role) {
        return $this->jogosultsag === $role;
    }

    // Admin jogosultság ellenőrzése
    public function isAdmin() {
        return $this->jogosultsag === 'admin';
    }

    // Moderátor vagy magasabb jogosultság
    public function isModerator() {
        return in_array($this->jogosultsag, ['moderator', 'admin']);
    }
}
?>
