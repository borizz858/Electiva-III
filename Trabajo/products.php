<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Agregar nuevo producto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_producto'])) {
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];
    $usuario_id = $_SESSION['user_id'];
    
    $errors = [];

    // Validaciones
    if (empty($nombre)) {
        $errors[] = "El nombre del producto es requerido";
    }

    if (empty($precio) || $precio <= 0) {
        $errors[] = "El precio debe ser mayor a 0";
    }

    if (empty($stock) || $stock < 0) {
        $errors[] = "El stock no puede ser negativo";
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO productos (nombre, descripcion, precio, stock, usuario_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nombre, $descripcion, $precio, $stock, $usuario_id]);
            
            $_SESSION['success'] = "Producto agregado exitosamente";
        } catch (PDOException $e) {
            $errors[] = "Error al agregar el producto: " . $e->getMessage();
        }
    }

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
    }
    
    header("Location: index.php");
    exit();
}

// Eliminar producto
if (isset($_GET['eliminar'])) {
    $producto_id = $_GET['eliminar'];
    $usuario_id = $_SESSION['user_id'];
    
    try {
        // Verificar que el producto pertenece al usuario
        $stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ? AND usuario_id = ?");
        $stmt->execute([$producto_id, $usuario_id]);
        $producto = $stmt->fetch();
        
        if ($producto) {
            $stmt = $pdo->prepare("DELETE FROM productos WHERE id = ? AND usuario_id = ?");
            $stmt->execute([$producto_id, $usuario_id]);
            $_SESSION['success'] = "Producto eliminado exitosamente";
        } else {
            $_SESSION['errors'] = ["No tienes permisos para eliminar este producto"];
        }
    } catch (PDOException $e) {
        $_SESSION['errors'] = ["Error al eliminar el producto: " . $e->getMessage()];
    }
    
    header("Location: index.php");
    exit();
}
?>