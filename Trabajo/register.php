<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    $errors = [];

    // Validaciones
    if (empty($nombre) || strlen($nombre) < 2) {
        $errors[] = "El nombre debe tener al menos 2 caracteres";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "El email no es válido";
    }

    if (strlen($password) < 6) {
        $errors[] = "La contraseña debe tener al menos 6 caracteres";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Las contraseñas no coinciden";
    }

    // Verificar si el email ya existe
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $errors[] = "El email ya está registrado";
    }

    if (empty($errors)) {
        // Hash de la contraseña
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Insertar usuario
        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)");
        if ($stmt->execute([$nombre, $email, $password_hash])) {
            $_SESSION['success'] = "Registro exitoso. Ahora puedes iniciar sesión.";
            header("Location: index.php");
            exit();
        } else {
            $errors[] = "Error al registrar el usuario";
        }
    }

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        $_SESSION['form_data'] = $_POST;
        header("Location: index.php");
        exit();
    }
}
?>