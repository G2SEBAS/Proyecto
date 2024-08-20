<?php
session_start();

// Incluir archivos de PHPMailer
require 'C:/laragon/www/Proyecto/PHPMailer/Exception.php';
require 'C:/laragon/www/Proyecto/PHPMailer/PHPMailer.php';
require 'C:/laragon/www/Proyecto/PHPMailer/SMTP.php';

// Configuración del servidor SMTP
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Conectar a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "inventario";

$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Mensajes de alerta
$alert_message = "";
$alert_class = "";

// Procesar el formulario de recuperación de contraseña
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['request_reset'])) {
    $usuario = $conn->real_escape_string($_POST['usuario']);

    // Consultar el usuario
    $sql = "SELECT id, email FROM usuarios WHERE usuario = '$usuario'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $id = $row['id'];
        $email = $row['email'];

        // Generar un token único
        $token = bin2hex(random_bytes(16));
        $token_expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Insertar el token en la base de datos
        $sql = "INSERT INTO password_resets (user_id, token, token_expiry) VALUES ('$id', '$token', '$token_expiry')";
        if ($conn->query($sql) === TRUE) {
            // Enviar el enlace de recuperación por correo
            $reset_link = "http://localhost/Proyecto/reset_password.php?token=$token";
            $mail = new PHPMailer(true);

            try {
                // Configuración del servidor SMTP
                $mail->isSMTP();
                $mail->Host = 'SMTP.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'jumboariel4@gmail.com'; // Cambia esto por tu dirección de correo
                $mail->Password = 'gpfr soyj ioeu qbpm'; // Cambia esto por tu clave de aplicación
                $mail->SMTPSecure = 'ssl'; // O usa 'ssl' si prefieres
                $mail->Port = 465; // O usa 465 si usas 'ssl'

                // Remitente y destinatario
                $mail->setFrom('no-reply@miempresa.com', 'Soporte de Mi Empresa');
                $mail->addAddress($email);

                // Contenido del correo
                $mail->isHTML(true);
                $mail->Subject = 'Reseteo de Clave';
                $mail->Body    = "Haga clic en el siguiente enlace para restablecer su contraseña: <a href='$reset_link'>$reset_link</a>";

                $mail->send();
                $alert_message = "Se ha enviado un enlace de recuperación a su correo electrónico.";
                $alert_class = "alert-success";
            } catch (Exception $e) {
                $alert_message = "No se pudo enviar el correo electrónico. Error: " . $mail->ErrorInfo;
                $alert_class = "alert-error";
            }
        } else {
            $alert_message = "Error al generar el token.";
            $alert_class = "alert-error";
        }
    } else {
        $alert_message = "Nombre de usuario no encontrado.";
        $alert_class = "alert-error";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recuperar Contraseña</title>
    <style>
     body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: linear-gradient(135deg, 
                #a2c2e2, /* Azul claro */
                #0072ff, /* Azul oscuro */
                #a3d9a5, /* Verde claro */
                #007e33 /* Verde oscuro */
            );
            background-size: 400% 400%;
            animation: gradientAnimation 15s ease infinite;
        }

        @keyframes gradientAnimation {
            0% { background-position: 0% 0%; }
            50% { background-position: 100% 100%; }
            100% { background-position: 0% 0%; }
        }

        .container {
            background-color: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 600px;
            text-align: center;
        }

        h1 {
            color: #3CBC19; /* Verde claro */
            margin-bottom: 20px;
        }

        a {
            text-decoration: none;
            color: #4b79a1;
            font-weight: bold;
            transition: color 0.3s;
            display: block;
            margin-bottom: 20px;
        }

        a:hover {
            color: #0c1b33;
        }

        form {
            margin-bottom: 20px;
        }

        .form-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .form-list li {
            margin-bottom: 15px;
        }

        label {
            font-weight: bold;
            color: #333;
            display: block;
            margin-top: 10px;
        }

        input {
            padding: 10px;
            margin: 5px;
            width: 100%;
            max-width: 300px;
        }

        .button-container {
            display: flex;
            justify-content: center;
            gap: 10px; /* Espacio entre los botones */
            margin-top: 20px;
        }

        button {
            padding: 10px;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
            border-radius: 4px;
            color: white;
        }

        .btn-recover {
            background-color: #3CBC19; /* Verde claro */
        }

        .btn-recover:hover {
            background-color: #2a9d8f; /* Verde más oscuro */
        }

        .btn-back {
            background-color: #4b79a1; /* Azul */
        }

        .btn-back:hover {
            background-color: #0c1b33; /* Azul oscuro */
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
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
    </style>
</head>
<body>
    <div class="container">
        <h1>Recuperar Contraseña</h1>

        <?php
        // Mostrar alertas
        if ($alert_message) {
            echo "<div class='alert $alert_class'>$alert_message</div>";
        }
        ?>

        <form action="forgot_password.php" method="POST">
            <ul class="form-list">
                <li>
                    <label for="usuario">Nombre de Usuario:</label>
                    <input type="text" id="usuario" name="usuario" required>
                </li>
                <li class="button-container">
                    <button type="submit" name="request_reset" class="btn-recover">Enviar Enlace de Recuperación</button>
                    <a href="login.php"><button type="button" class="btn-back">Volver al Inicio de Sesión</button></a>
                </li>
            </ul>
        </form>
    </div>
</body>
</html>