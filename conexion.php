<?php
function conectarDB() {
    $host = "yamabiko.proxy.rlwy.net";
$port = "39168";
$basedatos   = "healthnet"; // ¡OJO! Cambiamos 'railway' por tu base real 'healthnet'
$user = "root";
$pass = "JBExxPHraFCRUGRIvODbqiGvtJyhpOwl";
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;port=$port;dbname=$basedatos;charset=$charset";
    
    try {
        $db = new mysqli($host, $user, $pass, $basedatos, $port);
        $db->set_charset("utf8");
        return $db;
    } catch (Exception $e) {
        echo "Error en la conexión: " . $e->getMessage();
        exit;
    }
}
?>