<?php
session_start();
require_once 'config.php';


if (isset($_SESSION['errors'])) {
    $errors = $_SESSION['errors'];
    unset($_SESSION['errors']);
}


if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}


$productos = [];
try {
    $stmt = $pdo->query("
        SELECT p.*, u.nombre as usuario_nombre 
        FROM productos p 
        JOIN usuarios u ON p.usuario_id = u.id 
        ORDER BY p.fecha_creacion DESC
    ");
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errors[] = "Error al cargar productos: " . $e->getMessage();
}


$mis_productos_count = 0;
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM productos WHERE usuario_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $result = $stmt->fetch();
        $mis_productos_count = $result['total'];
    } catch (PDOException $e) {
        
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Productos</title>
    <style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

:root {
    
    --color-bg-dark: #1e1e2f;          
    --color-primary: #a37a4c;        
    --color-secondary: #4b4b69;      
    --color-text-light: #f0f0f5;     
    --color-text-muted: #c3c3e0;     
    --color-success: #4caf50;        
    --color-danger: #e91e63;         
    --color-warning: #ffc107;        
 
    --shadow-elegant: 0 8px 16px rgba(0, 0, 0, 0.4);
    --transition-fast: all 0.2s ease-in-out;
    --transition-medium: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
    background-color: var(--color-bg-dark);
    min-height: 100vh;
    color: var(--color-text-light);
    line-height: 1.6;
    padding: 20px 0; 
}


.loading-page {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: var(--color-bg-dark);
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    opacity: 1; 
    transition: opacity 1s ease-out, visibility 1s; 
    visibility: visible;
}

.loading-page.hidden {
    opacity: 0;
    visibility: hidden;
}


.loader {
    width: 60px;
    height: 60px;
    border: 5px solid var(--color-secondary);
    border-top: 5px solid var(--color-primary); 
    border-radius: 50%;
    animation: spin 1.2s cubic-bezier(0.5, 0.2, 0.5, 0.8) infinite; 
    margin-bottom: 20px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}


.container {
    max-width: 1280px;
    margin: 0 auto;
    padding: 0 30px;
}

.header, .auth-section, .stat-card {
    background-color: #2a2a3f; 
    padding: 25px;
    border-radius: 12px;
    margin-bottom: 30px;
    box-shadow: var(--shadow-elegant);
    border: 1px solid var(--color-secondary); 
    transition: var(--transition-medium);
}


.welcome-message h1 {
    color: var(--color-primary);
    font-size: 3em;
    margin-bottom: 15px;
    text-shadow: 0 0 10px rgba(163, 122, 76, 0.5); 
}

.welcome-message p {
    color: var(--color-text-muted);
    font-size: 1.3em;
}


.auth-buttons, .user-actions, .product-actions {
    display: flex;
    gap: 20px;
    justify-content: center;
    margin-top: 20px;
    flex-wrap: wrap;
}

.btn {
    padding: 12px 30px;
    border: none;
    border-radius: 8px; 
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    text-align: center;
    font-size: 17px;
    transition: var(--transition-medium);
    font-weight: 600; 
    letter-spacing: 0.5px;
    text-transform: uppercase;
}

.btn:hover {
    transform: translateY(-3px) scale(1.02);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
}

.btn-primary {
    background-color: var(--color-primary);
    color: var(--color-bg-dark); 
    border: 1px solid var(--color-primary);
}

.btn-primary:hover {
    background-color: #b78e5f; 
    box-shadow: 0 0 15px var(--color-primary);
}

.btn-success {
    background-color: var(--color-success);
    color: white;
}

.btn-danger {
    background-color: var(--color-danger);
    color: white;
}

.btn-warning {
    background-color: var(--color-warning);
    color: var(--color-bg-dark);
}

.btn-small {
    padding: 10px 20px;
    font-size: 15px;
    border-radius: 6px;
}


.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.8); 
    z-index: 1000;
    backdrop-filter: blur(5px); 
}

.modal-content {
    background-color: #2a2a3f;
    margin: 5vh auto; 
    padding: 40px;
    border-radius: 12px;
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: var(--shadow-elegant);
    animation: fadeInScale 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
}

@keyframes fadeInScale {
    from { opacity: 0; transform: scale(0.9); }
    to { opacity: 1; transform: scale(1); }
}

.form-group label {
    color: var(--color-text-light);
    margin-bottom: 10px;
}

.form-group input, .form-group textarea {
    background-color: #1e1e2f;
    color: var(--color-text-light);
    border: 2px solid var(--color-secondary);
    padding: 15px 12px;
    font-size: 16px;
    border-radius: 8px;
    transition: var(--transition-fast);
}

.form-group input:focus, .form-group textarea:focus {
    border-color: var(--color-primary);
    box-shadow: 0 0 8px rgba(163, 122, 76, 0.5);
    outline: none;
}

/* Mensajes de estado */
.error-message, .success {
    padding: 18px;
    border-radius: 8px;
    margin-bottom: 25px;
    font-weight: 600;
}

.error-message {
    background-color: rgba(233, 30, 99, 0.15);
    color: var(--color-danger);
    border: 1px solid var(--color-danger);
}

.success {
    background-color: rgba(76, 175, 80, 0.15);
    color: var(--color-success);
    border: 1px solid var(--color-success);
}


.catalog {
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); 
    gap: 30px;
}

.product-card {
    background-color: #2a2a3f;
    color: var(--color-text-light);
    border-radius: 12px;
    padding: 30px;
    box-shadow: var(--shadow-elegant);
    transition: var(--transition-medium);
    border: 1px solid var(--color-secondary);
}

.product-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.5);
}

.product-card h3 {
    color: var(--color-primary);
    font-size: 1.6em;
    border-bottom: 1px solid var(--color-secondary);
    padding-bottom: 10px;
    margin-bottom: 15px;
}

.product-card .description {
    color: var(--color-text-muted);
    font-size: 15px;
}

.product-card .price {
    font-size: 28px;
    font-weight: bold;
    color: var(--color-success);
    margin-top: 15px;
    margin-bottom: 5px;
}

.product-card .stock {
    color: var(--color-text-muted);
    font-size: 14px;
}

.product-card .owner {
    color: var(--color-primary);
    font-size: 13px;
    font-style: normal;
    font-weight: 500;
}

.product-badge {
    top: 20px;
    right: 20px;
    background-color: var(--color-primary);
    color: var(--color-bg-dark);
    padding: 6px 12px;
    border-radius: 50px; 
    font-size: 13px;
    text-transform: uppercase;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}


.stat-card {
    padding: 30px;
    flex: 1;
    min-width: 200px;
    transition: var(--transition-medium);
}

.stat-card:hover {
    background-color: #383850;
    transform: scale(1.05);
}

.stat-number {
    font-size: 3em;
    font-weight: 700;
    color: var(--color-primary);
    margin-bottom: 5px;
    letter-spacing: -1px;
}

.stat-label {
    color: var(--color-text-muted);
    font-size: 15px;
    text-transform: uppercase;
}
</style>
</head>
<body>
    
    <div class="loading-page" id="loadingPage">
        <div class="loader"></div>
        <h2 style="color: white;">Cargando Catálogo...</h2>
    </div>

    <div class="container" id="mainContent" style="display: none;">
        
        <div class="welcome-message">
            <h1> Catálogo de Productos</h1>
            <p>Gestiona y explora nuestro catálogo de productos</p>
        </div>

        <!-- AUTENTICACIÓN -->
        <div class="auth-section">
            <?php if (isset($_SESSION['user_id'])): ?>
                <!-- logueado -->
                <div class="success">
                    <h3> ¡Hola, <?php echo $_SESSION['user_name']; ?>!</h3>
                    <p>Puedes agregar nuevos productos al catálogo y gestionar tus productos.</p>
                    
                    <div class="stats">
                        <div class="stat-card">
                            <div class="stat-number"><?php echo count($productos); ?></div>
                            <div class="stat-label">Productos Totales</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number"><?php echo $mis_productos_count; ?></div>
                            <div class="stat-label">Mis Productos</div>
                        </div>
                    </div>

                    <div class="user-actions">
                        <button class="btn btn-success" onclick="openModal('agregarProductoModal')">
                             Agregar Producto
                        </button>
                        <a href="logout.php" class="btn btn-danger">Cerrar Sesión</a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Usuario NO logueado -->
                <h2> Acceso al Catálogo</h2>
                <p>Para agregar y gestionar productos, inicia sesión o regístrate</p>
                <div class="auth-buttons">
                    <button class="btn btn-primary" onclick="openModal('loginModal')">
                         Iniciar Sesión
                    </button>
                    <button class="btn btn-success" onclick="openModal('registerModal')">
                        Registrarse
                    </button>
                </div>
                <div style="margin-top: 15px; color: #666; font-size: 14px;">
                    <p> <strong>¿Primera vez aquí?</strong> Regístrate para comenzar a agregar productos</p>
                </div>
            <?php endif; ?>

            <?php if (isset($errors)): ?>
                <div class="error-message">
                    <?php foreach ($errors as $error): ?>
                        <div> <?php echo $error; ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($success)): ?>
                <div class="success"> <?php echo $success; ?></div>
            <?php endif; ?>
        </div>

        <!-- PRODUCTOS -->
        <h2 style="color: white; margin-bottom: 20px; text-align: center;">
            Todos los Productos (<?php echo count($productos); ?>)
        </h2>
        <div class="catalog">
            <?php if (empty($productos)): ?>
                <div style="background: white; padding: 40px; border-radius: 10px; text-align: center; grid-column: 1 / -1;">
                    <h3> No hay productos en el catálogo</h3>
                    <p>Sé el primero en agregar un producto al catálogo</p>
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <button class="btn btn-success" onclick="openModal('loginModal')" style="margin-top: 15px;">
                             Iniciar Sesión para Agregar
                        </button>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <?php foreach ($productos as $producto): ?>
                    <div class="product-card">
                        <?php if ($producto['usuario_id'] == ($_SESSION['user_id'] ?? 0)): ?>
                            <span class="product-badge">Mi Producto</span>
                        <?php endif; ?>
                        
                        <h3><?php echo htmlspecialchars($producto['nombre']); ?></h3>
                        <p class="description"><?php echo htmlspecialchars($producto['descripcion']); ?></p>
                        <div class="price">$<?php echo number_format($producto['precio'], 2); ?></div>
                        <div class="stock">Stock: <?php echo $producto['stock']; ?> unidades</div>
                        <div class="owner">
                            Agregado por: <?php echo htmlspecialchars($producto['usuario_nombre']); ?>
                        </div>
                        
                        <div class="product-actions">
                            <?php if (isset($_SESSION['user_id']) && $producto['usuario_id'] == $_SESSION['user_id']): ?>
                                <a href="products.php?eliminar=<?php echo $producto['id']; ?>" 
                                   class="btn btn-danger btn-small"
                                   onclick="return confirm('¿Estás seguro de que quieres eliminar este producto?')">
                                     Eliminar
                                </a>
                            <?php elseif (!isset($_SESSION['user_id'])): ?>
                                <button class="btn btn-primary btn-small" onclick="openModal('loginModal')">
                                     Iniciar Sesión
                                </button>
                            <?php else: ?>
                                <span style="color: #666; font-size: 14px;">Producto de otro usuario</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Registro -->
    <div id="registerModal" class="modal">
        <div class="modal-content">
            <h2> Registro</h2>
            <form id="registerForm" action="register.php" method="POST">
                <div class="form-group">
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" required>
                    <div class="error" id="nombreError"></div>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                    <div class="error" id="emailError"></div>
                </div>
                <div class="form-group">
                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" required>
                    <div class="error" id="passwordError"></div>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirmar Contraseña:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                    <div class="error" id="confirmPasswordError"></div>
                </div>
                <button type="submit" class="btn btn-success">Registrarse</button>
                <button type="button" class="btn btn-primary" onclick="closeModal('registerModal')">Cancelar</button>
            </form>
        </div>
    </div>

    <!--  Login -->
    <div id="loginModal" class="modal">
        <div class="modal-content">
            <h2> Iniciar Sesión</h2>
            <form id="loginForm" action="login.php" method="POST">
                <div class="form-group">
                    <label for="login_email">Email:</label>
                    <input type="email" id="login_email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="login_password">Contraseña:</label>
                    <input type="password" id="login_password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
                <button type="button" class="btn btn-primary" onclick="closeModal('loginModal')">Cancelar</button>
            </form>
        </div>
    </div>

    <!-- Modal para Agregar Producto -->
    <div id="agregarProductoModal" class="modal">
        <div class="modal-content">
            <h2> Agregar Nuevo Producto</h2>
            <form action="products.php" method="POST">
                <div class="form-group">
                    <label for="producto_nombre">Nombre del Producto:</label>
                    <input type="text" id="producto_nombre" name="nombre" required>
                </div>
                <div class="form-group">
                    <label for="producto_descripcion">Descripción:</label>
                    <textarea id="producto_descripcion" name="descripcion" required></textarea>
                </div>
                <div class="form-group">
                    <label for="producto_precio">Precio ($):</label>
                    <input type="number" id="producto_precio" name="precio" step="0.01" min="0" required>
                </div>
                <div class="form-group">
                    <label for="producto_stock">Stock:</label>
                    <input type="number" id="producto_stock" name="stock" min="0" required>
                </div>
                <button type="submit" name="agregar_producto" class="btn btn-success">Agregar Producto</button>
                <button type="button" class="btn btn-primary" onclick="closeModal('agregarProductoModal')">Cancelar</button>
            </form>
        </div>
    </div>

    <script>
        // Simular carga de página
        window.addEventListener('load', function() {
            setTimeout(function() {
                document.getElementById('loadingPage').style.opacity = '0';
                setTimeout(function() {
                    document.getElementById('loadingPage').style.display = 'none';
                    document.getElementById('mainContent').style.display = 'block';
                }, 500);
            }, 2000);
        });

        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // validacion
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            let isValid = true;
            const nombre = document.getElementById('nombre').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

        
            document.getElementById('nombreError').textContent = '';
            document.getElementById('emailError').textContent = '';
            document.getElementById('passwordError').textContent = '';
            document.getElementById('confirmPasswordError').textContent = '';

            
            if (nombre.length < 2) {
                document.getElementById('nombreError').textContent = 'El nombre debe tener al menos 2 caracteres';
                isValid = false;
            }

            
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                document.getElementById('emailError').textContent = 'Ingrese un email válido';
                isValid = false;
            }

            
            if (password.length < 6) {
                document.getElementById('passwordError').textContent = 'La contraseña debe tener al menos 6 caracteres';
                isValid = false;
            }

            
            if (password !== confirmPassword) {
                document.getElementById('confirmPasswordError').textContent = 'Las contraseñas no coinciden';
                isValid = false;
            }

            if (isValid) {
                this.submit();
            }
        });

        //  Esto me sirve para cerrar modal al hacer clic fuera
        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = 'none';
            }
        }

        // Esto me sirve para mostrar modales automáticamente si hay errores
        <?php if (isset($_SESSION['form_data'])): ?>
            document.addEventListener('DOMContentLoaded', function() {
                openModal('registerModal');
            });
            <?php unset($_SESSION['form_data']); ?>
        <?php endif; ?>
    </script>
</body>

</html>

