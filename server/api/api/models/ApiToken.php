<?php
class ApiToken
{
    private $conn;
    private $table_name = "api_tokens";

    // Duración del token en minutos (configurable)
    private $expirationMinutes = 60;

    public $id;
    public $user_id;
    public $token;
    public $expires_at;
    public $revoked;
    public $created_at;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Genera un token aleatorio, no predecible, codificado en hex
    private function generateSecureToken()
    {
        return bin2hex(random_bytes(32)); // 32 bytes = 64 caracteres hex
    }

    // Crea un nuevo token para el usuario, invalidando los anteriores
    public function createForUser($userId)
    {
        // 1. Revocar tokens anteriores de este usuario
        $this->revokeAllForUser($userId);

        // 2. Generar el nuevo token
        $this->token = $this->generateSecureToken();
        $this->user_id = $userId;
        $this->expires_at = date('Y-m-d H:i:s', strtotime("+{$this->expirationMinutes} minutes"));

        $query = "INSERT INTO " . $this->table_name . " 
                  SET user_id = :user_id, token = :token, expires_at = :expires_at";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":token", $this->token);
        $stmt->bindParam(":expires_at", $this->expires_at);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    // Revoca todos los tokens activos de un usuario (usado en login y logout)
    public function revokeAllForUser($userId)
    {
        $query = "UPDATE " . $this->table_name . " 
                  SET revoked = TRUE 
                  WHERE user_id = :user_id AND revoked = FALSE";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $userId);
        return $stmt->execute();
    }

    // Revoca un token específico (usado en logout)
    public function revoke($tokenValue)
    {
        $query = "UPDATE " . $this->table_name . " 
                  SET revoked = TRUE 
                  WHERE token = :token";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":token", $tokenValue);
        return $stmt->execute();
    }

    // Valida un token: existe, no revocado, no expirado.
    // Si es válido, carga los datos (incluido user_id) y los regresa.
    public function validate($tokenValue)
    {
        $query = "SELECT id, user_id, token, expires_at, revoked 
                  FROM " . $this->table_name . " 
                  WHERE token = :token 
                    AND revoked = FALSE 
                    AND expires_at > NOW() 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":token", $tokenValue);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->id = $row['id'];
            $this->user_id = $row['user_id'];
            $this->token = $row['token'];
            $this->expires_at = $row['expires_at'];
            $this->revoked = $row['revoked'];
            return true;
        }
        return false;
    }
}
?>