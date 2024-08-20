<?php
// Conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "inventario";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string($_POST['email']);

    // Verificar si el email existe
    $check_sql = "SELECT * FROM usuarios WHERE email = '$email'";
    $result = $conn->query($check_sql);

    if ($result->num_rows > 0) {
        // Generar un token de recuperación
        $token = bin2hex(random_bytes(50));

        // Guardar el token en la base de datos
        $sql = "UPDATE usuarios SET reset_token = '$token', reset_token_expiry = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE email = '$email'";
        $conn->query($sql);

        // Enviar el enlace por correo (para efectos de demostración solo imprimimos el enlace)
        $reset_link = "http://localhost/Proyecto/reset_password.php?token=$token";
        echo "Enlace de recuperación: <a href='$reset_link'>$reset_link</a>";

        // Aquí usarías una función de correo como mail() en PHP para enviar el enlace real
    } else {
        echo "No se encontró ninguna cuenta con ese correo.";
    }
}

$conn->close();
?>
