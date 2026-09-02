<?php
class Database
{
    private $host = "localhost";
    private $db_name = "bd_22030236";
    private $username = "u22030236";
    private $password = "22030236";

    //private $host = 'db'; // o 'db' si usas el docker-compose que armamos
    //private $db_name = 'miapp_db';
    //private $username = 'miapp_user';
    //private $password = 'ClaveSegura123!'; // usa tu contraseña real


    public $conn;

    public function getConnection()
    {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
        } catch (PDOException $e) {
            echo "Error de conexión: " . $e->getMessage();
        }
        return $this->conn;
    }
}
?>