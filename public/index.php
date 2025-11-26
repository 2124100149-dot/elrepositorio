<?php
require_once '../app/controllers/LoginController.php';

$controller = new LoginController();

// Determinar qué acción ejecutar
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'login':
            $controller->login();
            break;
        default:
            $controller->index();
    }
} else {
    $controller->index();
}
?>