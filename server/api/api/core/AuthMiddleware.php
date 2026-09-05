<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/ApiToken.php';

class AuthMiddleware
{
    // Verifica el token del request actual.
    // Si es válido, guarda el user_id autenticado en $_REQUEST['auth_user_id']
    // y regresa true. Si no, regresa false (el Router decide qué responder).
    public static function check()
    {
        $token = self::getBearerToken();

        if (!$token) {
            return false;
        }

        $database = new Database();
        $db = $database->getConnection();
        $apiToken = new ApiToken($db);

        if (!$apiToken->validate($token)) {
            return false;
        }

        // Dejamos el user_id autenticado disponible globalmente para el resource
        $GLOBALS['auth_user_id'] = $apiToken->user_id;

        return true;
    }

    // Misma lógica de extracción que en AuthResource — ver nota abajo
    private static function getBearerToken()
    {
        $headers = null;

        if (function_exists('getallheaders')) {
            $headers = getallheaders();
        }

        if (!$headers && isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers = ['Authorization' => $_SERVER['HTTP_AUTHORIZATION']];
        }

        if (!$headers) {
            return null;
        }

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
}
?>