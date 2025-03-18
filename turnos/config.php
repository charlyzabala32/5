<?php
session_start();

$db_host = 'localhost';
$db_user = 'u369746653_ibymeturnos';
$db_pass = 'Charly3269!';
$db_name = 'u369746653_ibymeturnos';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Definir la ruta base del proyecto
define('BASE_URL', '/turnos');

// Función para redirigir
function redirect($path) {
    header("Location: " . BASE_URL . $path);
    exit();
}
?>
