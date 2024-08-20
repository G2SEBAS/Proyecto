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
    $alert_message = "Error al conectar con la base de datos.";
    $alert_class = "alert-error";
    include('login_form.php'); // Incluir el formulario de inicio de sesión
    exit();
}

// Mensajes de alerta
$alert_message = "";
$alert_class = "";
$show_register_button = false; // Variable para controlar la visualización del botón de registro
$show_forgot_password_button = false; // Variable para controlar la visualización del botón de olvido de contraseña

// Procesar el formulario de inicio de sesión
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $usuario = $conn->real_escape_string($_POST['usuario']);
    $clave = $conn->real_escape_string($_POST['clave']);

    // Consultar el usuario
    $sql = "SELECT id, clave FROM usuarios WHERE usuario = '$usuario'";
    $result = $conn->query($sql);

    if ($result === FALSE) {
        $alert_message = "Error al realizar la consulta.";
        $alert_class = "alert-error";
    } elseif ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $id = $row['id'];
        $hashed_password = $row['clave'];

        // Verificar la contraseña
        if (password_verify($clave, $hashed_password)) {
            $_SESSION['user_id'] = $id;
            header("Location: index.php");
            exit();
        } else {
            $alert_message = "Nombre de usuario o contraseña incorrectos.";
            $alert_class = "alert-error";
            $show_forgot_password_button = true; // Mostrar el botón de olvido de contraseña si la contraseña es incorrecta
        }
    } else {
        $alert_message = "Nombre de usuario no registrado.";
        $alert_class = "alert-error";
        $show_register_button = true; // Mostrar el botón de registro si el usuario no está registrado
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inicio de Sesión</title>
    <style>
        /* Aquí va el estilo CSS */
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

        .login-container {
            display: flex;
            background-color: #fff; /* Fondo blanco sólido */
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            max-width: 800px;
            width: 100%;
            position: relative;
            z-index: 1;
        }

        .form-container {
            width: 60%;
            padding-right: 20px;
        }

        .image-container {
            width: 40%;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .image-container img {
            max-width: 100%;
            border-radius: 8px;
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
            width: 150px;
            padding: 10px;
            font-size: 16px;
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

        a {
            text-decoration: none;
        }

        .link-button {
            display: block;
            text-align: center;
            background-color: #007bff;
            color: white;
            padding: 10px;
            border-radius: 4px;
            margin-top: 10px;
            transition: background-color 0.3s ease;
            width: 150px;
            margin: 10px auto;
        }

        .link-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="form-container">
            <h1>Inicio de Sesión</h1>

            <?php
            if ($alert_message) {
                echo "<div class='alert $alert_class'>$alert_message</div>";
            }
            ?>

            <form action="login.php" method="POST">
                <ul class="form-list">
                    <li>
                        <label for="usuario">Nombre de Usuario:</label>
                        <input type="text" id="usuario" name="usuario" required>
                    </li>
                    <li>
                        <label for="clave">Contraseña:</label>
                        <input type="password" id="clave" name="clave" required>
                    </li>
                    <li>
                        <button type="submit" name="login">Iniciar Sesión</button>
                    </li>
                    <?php if ($show_register_button) { ?>
                        <li>
                            <a href="register.php" class="link-button">Registrarse</a>
                        </li>
                    <?php } ?>
                    <?php if ($show_forgot_password_button) { ?>
                        <li>
                            <a href="forgot_password.php" class="link-button">Olvidé mi Contraseña</a>
                        </li>
                    <?php } ?>
                </ul>
            </form>
        </div>
        <div class="image-container">
            <img src="https://educacioncontinua.uhemisferios.edu.ec/wp-content/uploads/2022/05/Logo-ISTTEcuatoriano.png" alt="Imagen de Inicio de Sesión">
        </div>
    </div>
</body>
</html>







