<?php
session_start();
include "../conexion.php"; // Asegúrate de tener un archivo de conexión a la base de datos.

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener datos del formulario
    $id = isset($_POST['id']) ? $_POST['id'] : '';
    $nombre = $_POST['nombre'];
    $color = $_POST['color'];
    $estado = $_POST['estado'];

    // Validar y sanitizar los datos
    $nombre = mysqli_real_escape_string($conexion, $nombre);
    $color = mysqli_real_escape_string($conexion, $color);
    $estado = mysqli_real_escape_string($conexion, $estado);

    // Inicializar variable para mensajes de error
    $error = '';


    // Si hay errores, almacenarlos en la sesión y redirigir
    if (!empty($error)) {
        $_SESSION['alert'] = $error; // Mensaje de error
        header("Location: grupos.php"); // Cambia a la ruta deseada
        exit();
    }

    // Si no hay errores, continuar con la inserción o actualización
    if (!empty($id)) {
        $query = "UPDATE grupos SET nombre='$nombre', color='$color', estado='$estado' WHERE id='$id'";
        $message = "Grupo actualizado con éxito.";
    } else {
        $query = "INSERT INTO grupos (nombre, color, estado) VALUES ('$nombre', '$color', '$estado')";
        $message = "Grupo registrado con éxito.";
    }

    // Ejecutar la consulta
    if (mysqli_query($conexion, $query)) {
        $_SESSION['alert'] = $message; // Mensaje de éxito
    } else {
        $_SESSION['alert'] = "Error: " . mysqli_error($conexion); // Mensaje de error
    }

    // Redirigir de vuelta al formulario o a la página deseada
    header("Location: grupos.php"); // Cambia index.php por la ruta a donde quieras redirigir
    exit();
} else {
    // Si no se accede al script a través de POST, redirigir a la página de inicio
    header("Location: grupos.php");
    exit();
}
?>
