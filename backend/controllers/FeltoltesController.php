<?php
class FeltoltesController {
    private $uploadDir;
    private $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    private $maxFileSize = 5242880; // 5MB

    public function __construct() {
        $this->uploadDir = dirname(__DIR__, 2) . '/uploads/';
        
        // Létrehozza az uploads mappát, ha nem létezik
        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    // POST /upload/image (kép feltöltés)
    public function uploadImage() {
        // Ellenőrzi, hogy be van-e jelentkezve
        if (!isset($_SESSION['felhasznalo_id'])) {
            http_response_code(401);
            echo json_encode(["message" => "Bejelentkezés szükséges."]);
            return;
        }

        // Ellenőrzi, hogy van-e fájl
        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode(["message" => "Nincs feltöltött fájl vagy hiba történt."]);
            return;
        }

        $file = $_FILES['image'];

        // Fájl típus ellenőrzése
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $this->allowedTypes)) {
            http_response_code(400);
            echo json_encode(["message" => "Csak képfájlok engedélyezettek (JPG, PNG, GIF, WebP)."]);
            return;
        }

        // Fájl méret ellenőrzése
        if ($file['size'] > $this->maxFileSize) {
            http_response_code(400);
            echo json_encode(["message" => "A fájl túl nagy. Maximum 5MB engedélyezett."]);
            return;
        }

        // Egyedi fájlnév generálása
        $extension = $this->getExtension($mimeType);
        $filename = uniqid('img_', true) . '.' . $extension;
        $filepath = $this->uploadDir . $filename;

        // Fájl mozgatása a célkönyvtárba
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            // URL generálása
            $baseUrl = $this->getBaseUrl();
            $imageUrl = $baseUrl . '/uploads/' . $filename;

            http_response_code(201);
            echo json_encode([
                "message" => "Kép sikeresen feltöltve.",
                "url" => $imageUrl,
                "filename" => $filename
            ]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Hiba történt a fájl mentése során."]);
        }
    }

    // DELETE /upload/image/{filename} (kép törlése)
    public function deleteImage($filename) {
        // Ellenőrzi, hogy be van-e jelentkezve
        if (!isset($_SESSION['felhasznalo_id'])) {
            http_response_code(401);
            echo json_encode(["message" => "Bejelentkezés szükséges."]);
            return;
        }

        // Ellenőrzi, hogy admin vagy moderátor
        if ($_SESSION['jogosultsag'] !== 'admin' && $_SESSION['jogosultsag'] !== 'moderator') {
            http_response_code(403);
            echo json_encode(["message" => "Nincs jogosultságod a kép törléséhez."]);
            return;
        }

        // Fájlnév sanitizálása
        $filename = basename($filename);
        $filepath = $this->uploadDir . $filename;

        // Fájl létezésének ellenőrzése
        if (!file_exists($filepath)) {
            http_response_code(404);
            echo json_encode(["message" => "A fájl nem található."]);
            return;
        }

        // Fájl törlése
        if (unlink($filepath)) {
            http_response_code(200);
            echo json_encode(["message" => "Kép sikeresen törölve."]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Hiba történt a fájl törlése során."]);
        }
    }

    // Fájl kiterjesztés meghatározása MIME type alapján
    private function getExtension($mimeType) {
        $extensions = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp'
        ];
        return $extensions[$mimeType] ?? 'jpg';
    }

    // Base URL meghatározása
    private function getBaseUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $scriptName = dirname(dirname($_SERVER['SCRIPT_NAME']));
        return $protocol . '://' . $host . $scriptName;
    }
}
?>
