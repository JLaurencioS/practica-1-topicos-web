<?php
class ApiUser
{
    private $conn;
    private $table_name = "api_users";

    public $id;
    public $username;
    public $email;
    public $password_hash;
    public $status;
    public $created_at;
    public $updated_at;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Busca un usuario ACTIVO por username. Si existe, carga sus datos (incluido el hash).
    public function findActiveByUsername($username)
    {
        $query = "SELECT id, username, email, password_hash, status, created_at, updated_at 
                  FROM " . $this->table_name . " 
                  WHERE username = :username AND status = 'ACTIVE' 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->id = $row['id'];
            $this->username = $row['username'];
            $this->email = $row['email'];
            $this->password_hash = $row['password_hash'];
            $this->status = $row['status'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        return false;
    }

    // Verifica una contraseña en texto plano contra el hash cargado en $this->password_hash
    public function verifyPassword($plainPassword)
    {
        return password_verify($plainPassword, $this->password_hash);
    }

    // Busca un usuario por id (usado en /me, para obtener los datos del usuario autenticado)
    public function findById($id)
    {
        $query = "SELECT id, username, email, status, created_at, updated_at 
                  FROM " . $this->table_name . " 
                  WHERE id = :id 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->id = $row['id'];
            $this->username = $row['username'];
            $this->email = $row['email'];
            $this->status = $row['status'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        return false;
    }
}
?>