<?php
class User {
    private $conn;
    private $table = "users";

    // Mezők
    public $id;
    public $nev;
    public $email;
    public $jelszo;
    public $profilkep_url;
    public $letrehozva;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Összes felhasználó lekérése (admin funkció)
    public function read() {
        $query = "SELECT id, nev, email, profilkep_url, letrehozva 
                  FROM " . $this->table . " 
                  ORDER BY letrehozva DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Egy felhasználó lekérése ID alapján
    public function read_single() {
        $query = "SELECT id, nev, email, profilkep_url, letrehozva 
                  FROM " . $this->table . " 
                  WHERE id = :id 
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->nev = $row['nev'];
            $this->email = $row['email'];
            $this->profilkep_url = $row['profilkep_url'];
            $this->letrehozva = $row['letrehozva'];
            return true;
        }
        
        return false;
    }

    // Felhasználó lekérése email alapján (login-hoz)
    public function findByEmail() {
        $query = "SELECT id, nev, email, jelszo, profilkep_url, letrehozva 
                  FROM " . $this->table . " 
                  WHERE email = :email 
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $this->email);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->id = $row['id'];
            $this->nev = $row['nev'];
            $this->email = $row['email'];
            $this->jelszo = $row['jelszo'];
            $this->profilkep_url = $row['profilkep_url'];
            $this->letrehozva = $row['letrehozva'];
            return true;
        }
        
        return false;
    }

    // Új felhasználó létrehozása (regisztráció)
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  (nev, email, jelszo, profilkep_url) 
                  VALUES (:nev, :email, :jelszo, :profilkep_url)";
        
        $stmt = $this->conn->prepare($query);
        
        // Input tisztítás
        $this->nev = htmlspecialchars(strip_tags($this->nev));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->profilkep_url = htmlspecialchars(strip_tags($this->profilkep_url));
        
        // Jelszó hash
        $hashed_password = password_hash($this->jelszo, PASSWORD_DEFAULT);
        
        // Bind paraméterek
        $stmt->bindParam(':nev', $this->nev);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':jelszo', $hashed_password);
        $stmt->bindParam(':profilkep_url', $this->profilkep_url);
        
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }

    // Felhasználó adatok frissítése
    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET nev = :nev, 
                      email = :email,
                      profilkep_url = :profilkep_url
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Input tisztítás
        $this->nev = htmlspecialchars(strip_tags($this->nev));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->profilkep_url = htmlspecialchars(strip_tags($this->profilkep_url));
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        // Bind paraméterek
        $stmt->bindParam(':nev', $this->nev);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':profilkep_url', $this->profilkep_url);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }

    // Jelszó frissítése
    public function updatePassword() {
        $query = "UPDATE " . $this->table . " 
                  SET jelszo = :jelszo 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Jelszó hash
        $hashed_password = password_hash($this->jelszo, PASSWORD_DEFAULT);
        
        $stmt->bindParam(':jelszo', $hashed_password);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }

    // Felhasználó törlése
    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }

    // Email ellenőrzése (létezik-e már)
    public function emailExists() {
        $query = "SELECT id FROM " . $this->table . " 
                  WHERE email = :email 
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $this->email);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }

    // Jelszó ellenőrzése
    public function verifyPassword($password) {
        return password_verify($password, $this->jelszo);
    }
}
?>
