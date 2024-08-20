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

// Registro de usuario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $conn->real_escape_string($_POST['nombre']);
    $apellido = $conn->real_escape_string($_POST['apellido']);
    $usuario = $conn->real_escape_string($_POST['usuario']);
    $email = $conn->real_escape_string($_POST['email']);
    $clave = $conn->real_escape_string($_POST['clave']);
    $verificacion_clave = $conn->real_escape_string($_POST['verificacion_clave']);

    // Verificar que las contraseñas coincidan
    if ($clave !== $verificacion_clave) {
        $alert_message = "Las contraseñas no coinciden.";
        $alert_class = "alert-error";
    } else {
        // Verificar si el nombre de usuario ya existe
        $check_sql = "SELECT * FROM usuarios WHERE usuario = '$usuario' OR email = '$email'";
        $result = $conn->query($check_sql);

        if ($result->num_rows > 0) {
            $alert_message = "El nombre de usuario o el correo electrónico ya están en uso.";
            $alert_class = "alert-error";
        } else {
            // Insertar nuevo usuario
            $clave_hash = password_hash($clave, PASSWORD_DEFAULT); // Hash de la contraseña
            $sql = "INSERT INTO usuarios (nombre, apellido, usuario, email, clave) VALUES ('$nombre', '$apellido', '$usuario', '$email', '$clave_hash')";

            if ($conn->query($sql) === TRUE) {
                $alert_message = "Usuario registrado exitosamente.";
                $alert_class = "alert-success";
            } else {
                $alert_message = "Error: " . $sql . "<br>" . $conn->error;
                $alert_class = "alert-error";
            }
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Usuario</title>
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

        .registration-container {
            display: flex;
            background-color: #fff; /* Fondo blanco sólido */
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            max-width: 600px;
            width: 100%;
            position: relative;
            z-index: 1;
            flex-direction: column;
            align-items: center;
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

        input, button {
            padding: 10px;
            margin-top: 5px;
            width: 100%;
            border-radius: 4px;
            border: 1px solid #ccc;
            font-size: 16px;
            color: #333;
        }

        button {
            background-color: #3CBC19;
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
            width: 120px; /* Ajustado para ser más pequeño */
            padding: 10px;
            font-size: 14px; /* Ajustado para ser más pequeño */
            display: block;
            margin: 0 auto;
        }

        button:hover {
            background-color: #00c851;
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
            justify-content: space-between;
            width: 100%;
            gap: 10px; /* Espacio entre los botones */
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
            font-size: 14px; /* Ajustado para ser más pequeño */
        }

        .link-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="registration-container">
        <h1>Registro de Usuario</h1>

        <?php
        // Mostrar alertas
        if ($alert_message) {
            echo "<div class='alert $alert_class'>$alert_message</div>";
        }
        ?>

        <form action="register.php" method="POST">
            <ul class="form-list">
                <li>
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" required>
                </li>
                <li>
                    <label for="apellido">Apellido:</label>
                    <input type="text" id="apellido" name="apellido" required>
                </li>
                <li>
                    <label for="usuario">Nombre de Usuario:</label>
                    <input type="text" id="usuario" name="usuario" placeholder="Ejemplo: ctubon" required>
                </li>
                <li>
                    <label for="email">Correo Electrónico:</label>
                    <input type="email" id="email" name="email" required>
                </li>
                <li>
                    <label for="clave">Contraseña:</label>
                    <input type="password" id="clave" name="clave" required>
                </li>
                <li>
                    <label for="verificacion_clave">Verificar Contraseña:</label>
                    <input type="password" id="verificacion_clave" name="verificacion_clave" required>
                </li>
                <li>
                    <div class="button-group">
                        <button type="submit">Registrar</button>
                        <a href="login.php" class="link-button">Volver al Inicio de Sesión</a>
                    </div>
                </li>
            </ul>
        </form>
    </div>
</body>
</html>




