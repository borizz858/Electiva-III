<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $errors = [];

    // Validaciones
    if (empty($email) || empty($password)) {
        $errors[] = "Todos los campos son requeridos";
    }

    if (empty($errors)) {
        // Buscar usuario
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nombre'];
            $_SESSION['success'] = "Bienvenido " . $user['nombre'];
            header("Location: index.php");
            exit();
        } else {
            $errors[] = "Credenciales incorrectas";
        }
    }

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header("Location: index.php");
        exit();
    }
}
?>