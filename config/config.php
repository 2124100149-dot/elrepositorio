<?php
// Configuración de rutas base
define('BASE_URL', 'http://localhost/Practicas_AW/healthnet-project');
define('ASSETS_PATH', __DIR__ . '/../assets');
define('CSS_URL', BASE_URL . '/assets/css');
define('JS_URL', BASE_URL . '/assets/js');
define('IMAGES_URL', BASE_URL . '/assets/imagenes');

// Para debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuración de la aplicación
define('APP_MODE', 'development');
?>