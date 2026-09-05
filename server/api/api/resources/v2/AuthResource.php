<?php

require_once '../../config/database.php';
require_once '../../models/ApiUser.php';
require_once '../../models/ApiToken.php';

class AuthResource
{
    private $db;
    private $apiUser;
    private $apiToken;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->apiUser = new ApiUser($this->db);
        $this->apiToken = new ApiToken($this->db);
    }

    // Extrae el token del header "Authorization: Bearer xxxxx"
    // Regresa el string del token, o null si no viene / formato incorrecto
    private function getBearerToken()
    {
        $headers = null;

        if (function_exists('getallheaders')) {
            $headers = getallheaders();
        }

        // Fallback por si getallheaders() no está disponible en este entorno
        if (!$headers && isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers = ['Authorization' => $_SERVER['HTTP_AUTHORIZATION']];
        }

        if (!$headers) {
            return null;
        }

        // Normalizar nombre del header (puede venir como Authorization o authorization)
        $authHeader = null;
        foreach ($headers as $key => $value) {
            if (strtolower($key) === 'authorization') {
                $authHeader = $value;
                break;
            }
        }

        if (!$authHeader) {
            return null;
        }

        if (preg_match('/Bearer\s+(\S+)/i', $authHeader, $matches)) {
            return $matches[1];
        }

        return null;
    }

    // POST /login
    public function login()
    {
        header("Content-Type: application/json");

        $data = json_decode(file_get_contents("php://input"));

        if (empty($data->username) || empty($data->password)) {
            http_response_code(400);
            echo json_encode(array("message" => "Usuario y contraseña son requeridos"));
            return;
        }

        // Mensaje genérico: no revelamos si falló el usuario o la contraseña
        $invalidCredentialsResponse = function () {
            http_response_code(401);
            echo json_encode(array(
                "error" => "invalid_credentials",
                "message" => "Usuario o contraseña incorrectos"
            ));
        };

        if (!$this->apiUser->findActiveByUsername($data->username)) {
            $invalidCredentialsResponse();
            return;
        }

        if (!$this->apiUser->verifyPassword($data->password)) {
            $invalidCredentialsResponse();
            return;
        }

        if ($this->apiToken->createForUser($this->apiUser->id)) {
            http_response_code(200);
            echo json_encode(array(
                "access_token" => $this->apiToken->token,
                "token_type" => "Bearer",
                "expires_at" => $this->apiToken->expires_at
            ));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "No se pudo generar el token"));
        }
    }

    // POST /logout
    public function logout()
    {
        header("Content-Type: application/json");

        $token = $this->getBearerToken();

        if (!$token) {
            http_response_code(401);
            echo json_encode(array(
                "error" => "unauthorized",
                "message" => "Token inválido, expirado o no proporcionado"
            ));
            return;
        }

        $this->apiToken->revoke($token);

        http_response_code(200);
        echo json_encode(array("message" => "Sesión cerrada exitosamente"));
    }

    // GET /me
    public function me()
    {
        header("Content-Type: application/json");

        $token = $this->getBearerToken();

        if (!$token || !$this->apiToken->validate($token)) {
            http_response_code(401);
            echo json_encode(array(
                "error" => "unauthorized",
                "message" => "Token inválido, expirado o no proporcionado"
            ));
            return;
        }

        if (!$this->apiUser->findById($this->apiToken->user_id)) {
            http_response_code(404);
            echo json_encode(array("message" => "Usuario no encontrado"));
            return;
        }

        http_response_code(200);
        echo json_encode(array(
            "id" => $this->apiUser->id,
            "username" => $this->apiUser->username,
            "email" => $this->apiUser->email,
            "status" => $this->apiUser->status,
            "created_at" => $this->apiUser->created_at
        ));
        // Nota: password_hash nunca se carga ni se incluye aquí
    }
}
?>