<?php
require_once '../app/controllers/RegisterController.php';

$controller = new RegisterController();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $controller->register();
} else {
    $controller->index();
}
?>