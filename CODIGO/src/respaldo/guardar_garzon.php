<?php
session_start();
include "../conexion.php"; // Asegúrate de tener un archivo de conexión a la base de datos.

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener datos del formulario
    $id = isset($_POST['id']) ? $_POST['id'] : '';
    $nombre = $_POST['nombre'];
    $pass = $_POST['pass'];
    $color = $_POST['color'];
    $estado = $_POST['estado'];

    // Validar y sanitizar los datos
    $nombre = mysqli_real_escape_string($conexion, $nombre);
    $pass = mysqli_real_escape_string($conexion, $pass);
    $color = mysqli_real_escape_string($conexion, $color);
    $estado = mysqli_real_escape_string($conexion, $estado);

    // Inicializar variable para mensajes de error
    $error = '';

    // Verificar si la contraseña o el color ya existen
    if (empty($id)) {
        // Solo verificamos si no es una actualización
        $checkPasswordQuery = "SELECT * FROM garzones WHERE Contrasenia='$pass'";
        $checkColorQuery = "SELECT * FROM garzones WHERE Color_Garzon='$color'";

        $resultPassword = mysqli_query($conexion, $checkPasswordQuery);
        $resultColor = mysqli_query($conexion, $checkColorQuery);

        if (mysqli_num_rows($resultPassword) > 0) {
            $error .= "La contraseña ya está en uso.<br>";
        }

        if (mysqli_num_rows($resultColor) > 0) {
            $error .= "El color ya está en uso.<br>";
        }
    } else {
        // Verificar en la actualización que no exista otro garzón con la misma contraseña o color
        $checkPasswordQuery = "SELECT * FROM garzones WHERE Contrasenia='$pass' AND Id_Garzones != '$id'";
        $checkColorQuery = "SELECT * FROM garzones WHERE Color_Garzon='$color' AND Id_Garzones != '$id'";

        $resultPassword = mysqli_query($conexion, $checkPasswordQuery);
        $resultColor = mysqli_query($conexion, $checkColorQuery);

        if (mysqli_num_rows($resultPassword) > 0) {
            $error .= "La contraseña ya está en uso.<br>";
        }

        if (mysqli_num_rows($resultColor) > 0) {
            $error .= "El color ya está en uso.<br>";
        }
    }

    // Si hay errores, almacenarlos en la sesión y redirigir
    if (!empty($error)) {
        $_SESSION['alert'] = $error; // Mensaje de error
        header("Location: garzones.php"); // Cambia a la ruta deseada
        exit();
    }

    // Si no hay errores, continuar con la inserción o actualización
    if (!empty($id)) {
        $query = "UPDATE garzones SET Nombre='$nombre', Contrasenia='$pass', Color_Garzon='$color', Estado_Garzon='$estado' WHERE Id_Garzones='$id'";
        $message = "Garzón actualizado con éxito.";
    } else {
        $query = "INSERT INTO garzones (Nombre, Contrasenia, Color_Garzon, Estado_Garzon) VALUES ('$nombre', '$pass', '$color', '$estado')";
        $message = "Garzón registrado con éxito.";
    }

    // Ejecutar la consulta
    if (mysqli_query($conexion, $query)) {
        $_SESSION['alert'] = $message; // Mensaje de éxito
    } else {
        $_SESSION['alert'] = "Error: " . mysqli_error($conexion); // Mensaje de error
    }

    // Redirigir de vuelta al formulario o a la página deseada
    header("Location: garzones.php"); // Cambia index.php por la ruta a donde quieras redirigir
    exit();
} else {
    // Si no se accede al script a través de POST, redirigir a la página de inicio
    header("Location: garzon.php");
    exit();
}
?>
