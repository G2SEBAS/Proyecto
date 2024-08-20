<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Manejar la acción de cerrar sesión
if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit();
}

// Conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "inventario";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Mensajes de alerta
$alert_message = "";
$alert_class = "";

// Insertar nuevo producto
if (isset($_POST['action']) && $_POST['action'] == 'insert') {
    $nombre = $_POST['nombre'];
    $cantidad = intval($_POST['cantidad']);
    $precio = $_POST['precio'];
    $descripcion = $_POST['descripcion'];

    // Verificar si el producto ya existe
    $check_stmt = $conn->prepare("SELECT * FROM productos WHERE nombre = ?");
    $check_stmt->bind_param("s", $nombre);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        $alert_message = "El producto con el nombre '$nombre' ya existe.";
        $alert_class = "alert-error";
    } else {
        // Insertar nuevo producto
        $insert_stmt = $conn->prepare("INSERT INTO productos (nombre, cantidad, precio, descripcion) VALUES (?, ?, ?, ?)");
        $insert_stmt->bind_param("sids", $nombre, $cantidad, $precio, $descripcion);

        if ($insert_stmt->execute()) {
            $alert_message = "Nuevo producto agregado exitosamente.";
            $alert_class = "alert-success";
        } else {
            $alert_message = "Error: " . $insert_stmt->error;
            $alert_class = "alert-error";
        }
    }

    $check_stmt->close();
    $insert_stmt->close();
}

// Actualizar cantidad de producto
if (isset($_POST['action']) && $_POST['action'] == 'update') {
    $nombre_actualizar = $_POST['nombre_actualizar'];
    $cantidad_actualizar = intval($_POST['cantidad_actualizar']);

    // Obtener la cantidad actual del producto
    $select_stmt = $conn->prepare("SELECT cantidad FROM productos WHERE nombre = ?");
    $select_stmt->bind_param("s", $nombre_actualizar);
    $select_stmt->execute();
    $result = $select_stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $cantidad_actual = $row['cantidad'];

        // Verificar si la cantidad ajustada es válida
        if (($cantidad_actual + $cantidad_actualizar) >= 0) {
            $update_stmt = $conn->prepare("UPDATE productos SET cantidad = cantidad + ? WHERE nombre = ?");
            $update_stmt->bind_param("is", $cantidad_actualizar, $nombre_actualizar);

            if ($update_stmt->execute()) {
                if ($update_stmt->affected_rows > 0) {
                    $alert_message = "Producto actualizado exitosamente.";
                    $alert_class = "alert-success";
                } else {
                    $alert_message = "No se encontró el producto '$nombre_actualizar'.";
                    $alert_class = "alert-error";
                }
            } else {
                $alert_message = "Error actualizando el producto: " . $update_stmt->error;
                $alert_class = "alert-error";
            }

            $update_stmt->close();
        } else {
            $alert_message = "La cantidad ajustada resultaría en un stock negativo.";
            $alert_class = "alert-error";
        }
    } else {
        $alert_message = "No se encontró el producto '$nombre_actualizar'.";
        $alert_class = "alert-error";
    }

    $select_stmt->close();
}

// Mostrar alertas
if ($alert_message) {
    echo "<div class='alert $alert_class'>$alert_message</div>";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inventario</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            height: 100vh;
            background: linear-gradient(135deg, 
                #d0e1f9, /* Azul claro */
                #4b79a1, /* Azul intermedio */
                #0c1b33, /* Azul oscuro */
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
            display: flex;
            flex: 1;
            margin: 20px;
        }

        .left-container, .right-container {
            background-color: rgba(255, 255, 255, 0.95);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            margin: 0 10px;
            flex: 1;
        }

        .left-container {
            width: 60%;
        }

        .right-container {
            width: 40%;
        }

        h1, h2 {
            color: #3CBC19;
        }

        h2 {
            color: #1628e1;
        }

        .logout-form {
            text-align: right;
            margin-bottom: 20px;
        }

        .logout-button {
            background-color: #f44336; /* Rojo */
            color: white;
            border: none;
            cursor: pointer;
            padding: 6px 12px;
            font-size: 14px;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }

        .logout-button:hover {
            background-color: #d32f2f; /* Rojo oscuro */
        }

        a {
            text-decoration: none;
            color: #4b79a1;
            font-weight: bold;
            transition: color 0.3s;
        }

        a:hover {
            color: #0c1b33;
        }

        form {
            margin-bottom: 20px;
            text-align: left;
        }

        label {
            font-weight: bold;
            color: #333;
            display: block;
            margin-top: 5px;
        }

        input, textarea {
            width: calc(100% - 12px);
            padding: 8px;
            margin-top: 3px;
            border-radius: 4px;
            border: 1px solid #ccc;
            font-size: 14px;
            color: #333;
        }

        button {
            background-color: #4b79a1;
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
            padding: 8px 16px;
            font-size: 14px;
            border-radius: 4px;
            margin-top: 10px;
            display: inline-block;
        }

        button:hover {
            background-color: #0c1b33;
        }

        .button-add {
            background-color: #28a745; /* Verde */
        }

        .button-add:hover {
            background-color: #218838; /* Verde oscuro */
        }

        .button-update {
            background-color: #ffc107; /* Amarillo */
        }

        .button-update:hover {
            background-color: #e0a800; /* Amarillo oscuro */
        }

        .form-actions {
            display: flex;
            gap: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid #ccc;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
        }

        .alert-error a {
            color: #721c24;
            font-weight: bold;
        }

        .alert-error a:hover {
            color: #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="left-container">
            <h1>Gestión de Inventario</h1>

            <div class="logout-form">
                <form action="" method="POST">
                    <button type="submit" name="logout" class="logout-button">Cerrar Sesión</button>
                </form>
            </div>

            <h2>Agregar Nuevo Producto</h2>
            <form action="index.php" method="POST">
                <label for="nombre">Nombre del Producto:</label>
                <input type="text" id="nombre" name="nombre" required>
                <label for="cantidad">Cantidad:</label>
                <input type="number" id="cantidad" name="cantidad" required>
                <label for="precio">Precio (USD):</label>
                <input type="number" step="0.01" id="precio" name="precio" required>
                <label for="descripcion">Descripción:</label>
                <textarea id="descripcion" name="descripcion" required></textarea>
                <div class="form-actions">
                    <button type="submit" class="button-add" name="action" value="insert">Agregar Producto</button>
                </div>
            </form>

            <h2>Actualizar Cantidad</h2>
            <form action="index.php" method="POST">
                <label for="nombre_actualizar">Nombre del Producto:</label>
                <input type="text" id="nombre_actualizar" name="nombre_actualizar" required>
                <label for="cantidad_actualizar">Cantidad a Ajustar:</label>
                <input type="number" id="cantidad_actualizar" name="cantidad_actualizar" required>
                <div class="form-actions">
                    <button type="submit" class="button-update" name="action" value="update">Actualizar Producto</button>
                </div>
            </form>
        </div>

        <div class="right-container">
            <h2>Lista de Productos</h2>
            <table>
                <tr>
                    <th>Nombre</th>
                    <th>Cantidad</th>
                    <th>Precio (USD)</th>
                    <th>Descripción</th>
                </tr>

                <?php
                $sql = "SELECT * FROM productos";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['nombre'] . "</td>";
                        echo "<td>" . $row['cantidad'] . "</td>";
                        echo "<td>" . $row['precio'] . "</td>";
                        echo "<td>" . $row['descripcion'] . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No hay productos en el inventario.</td></tr>";
                }

                $conn->close();
                ?>
            </table>
        </div>
    </div>
</body>
</html>
