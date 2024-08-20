<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "inventario";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Mensajes de alerta
$alert_message = "";
$alert_class = "";
$show_login_button = false; // Variable para controlar la visualización del botón de inicio de sesión

// Verificar el token
$token = $_GET['token'] ?? '';
if ($token) {
    $sql = "SELECT * FROM password_resets WHERE token = '$token' AND token_expiry > NOW()";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            if ($password === $confirm_password) {
                // Obtener el ID del usuario
                $row = $result->fetch_assoc();
                $user_id = $row['user_id'];

                // Actualizar la contraseña
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $sql = "UPDATE usuarios SET clave = '$password_hash' WHERE id = '$user_id'";

                if ($conn->query($sql) === TRUE) {
                    $alert_message = "Contraseña actualizada exitosamente.";
                    $alert_class = "alert-success";
                    $show_login_button = true; // Mostrar el botón de inicio de sesión
                } else {
                    $alert_message = "Error al actualizar la contraseña.";
                    $alert_class = "alert-error";
                }
            } else {
                $alert_message = "Las contraseñas no coinciden.";
                $alert_class = "alert-error";
            }
        }
    } else {
        $alert_message = "Token inválido o expirado.";
        $alert_class = "alert-error";
    }
} else {
    $alert_message = "Token no proporcionado.";
    $alert_class = "alert-error";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Restablecer Contraseña</title>
    <style>
    body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            overflow: hidden;
            background: linear-gradient(135deg, 
                #a2c2e2, /* Azul claro */
                #0072ff, /* Azul oscuro */
                #a3d9a5, /* Verde claro */
                #007e33 /* Verde oscuro */
            );
            background-size: 400% 400%;
            animation: gradientAnimation 20s ease infinite; /* Velocidad de animación más suave */
        }

        @keyframes gradientAnimation {
            0% { background-position: 0% 0%; }
            50% { background-position: 100% 100%; }
            100% { background-position: 0% 0%; }
        }

        .reset-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            background-color: #fff; /* Fondo blanco sólido */
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            max-width: 400px;
            width: 100%;
            position: relative;
            z-index: 1;
        }

        h1 {
            text-align: center;
            color: #3CBC19;
            font-size: 24px;
            margin-bottom: 20px;
        }

        .form-list {
            list-style: none;
            padding: 0;
            width: 100%;
        }

        .form-list li {
            margin-bottom: 15px;
        }

        label {
            font-size: 16px;
            color: #333;
        }

        input {
            padding: 10px;
            margin-top: 5px;
            width: 100%;
            border-radius: 4px;
            border: 1px solid #ccc;
            font-size: 16px;
            color: #333;
        }

        button {
            background-color: #007bff; /* Color azul */
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
            padding: 12px 20px; /* Mayor altura */
            font-size: 16px;
            border-radius: 4px;
        }

        button:hover {
            background-color: #0056b3; /* Azul más oscuro al pasar el ratón */
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            text-align: center;
            background-color: rgba(255, 255, 255, 0.8);
            color: #721c24;
        }

        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }

        .alert-error {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }

        .button-group {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px; /* Espacio entre los botones */
            width: 100%;
        }

        .link-button {
            display: block;
            text-align: center;
            background-color: #007bff;
            color: white;
            padding: 10px;
            border-radius: 4px;
            transition: background-color 0.3s ease;
            text-decoration: none;
            font-size: 14px; /* Tamaño de fuente pequeño */
        }

        .link-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <h1>Restablecer Contraseña</h1>

        <?php
        // Mostrar alertas
        if ($alert_message) {
            echo "<div class='alert $alert_class'>$alert_message</div>";
        }
        ?>

        <?php if (!$show_login_button) { ?>
            <form action="reset_password.php?token=<?php echo htmlspecialchars($token); ?>" method="POST">
                <ul class="form-list">
                    <li>
                        <label for="password">Nueva Contraseña:</label>
                        <input type="password" id="password" name="password" required>
                    </li>
                    <li>
                        <label for="confirm_password">Confirmar Contraseña:</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </li>
                </ul>
                <div class="button-group">
                    <button type="submit">Actualizar Contraseña</button>
                </div>
            </form>
        <?php } else { ?>
            <a href="login.php" class="link-button">Ir al Inicio de Sesión</a>
        <?php } ?>
    </div>
</body>
</html>



