<?php
session_start();
include "../conexion.php"; // Asegúrate de tener un archivo de conexión a la base de datos.
$id_empresa=$_SESSION['idempresa'];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener datos del formulario
    $id = isset($_POST['id']) ? $_POST['id'] : '';
    $nombre = $_POST['nombre'];
    $estado = $_POST['estado'];

    // Validar y sanitizar los datos
    $nombre = mysqli_real_escape_string($conexion, $nombre);
    $estado = mysqli_real_escape_string($conexion, $estado);

    // Inicializar variable para mensajes de error
    $error = '';

    // Verificar si tipo contrato ya existen
    if (empty($id)) {
        // Solo verificamos si no es una actualización
        $checkQuery = "SELECT * FROM tipo_contrato WHERE id_empresa='$id_empresa' AND descripcion='$nombre'";
        $resultName = mysqli_query($conexion, $checkQuery);
        if (mysqli_num_rows($resultName) > 0) {
            $error .= "La descripcion ya está en uso.<br>";
        }
    } else {
        // Verificar en la actualización que no exista tipo contrato
        $checkQuery = "SELECT * FROM tipo_contrato WHERE id_empresa='$id_empresa' AND descripcion='$nombre'";
        $resultName = mysqli_query($conexion, $checkQuery);
        if (mysqli_num_rows($resultName) > 0) {
            $error .= "tipo contrato ya está en uso.<br>";
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
        $query = "UPDATE tipo_contrato SET descripcion='$nombre', status='$estado' WHERE Id_tipocontrato='$id'";
        $message = "Actualizado con éxito.";
    } else {
        $query = "INSERT INTO tipo_contrato (id_empresa, descripcion, status) VALUES ('$id_empresa','$nombre', '$estado')";
        $message = "Registrado con éxito.";
    }

    // Ejecutar la consulta
    if (mysqli_query($conexion, $query)) {
        $_SESSION['alert'] = $message; // Mensaje de éxito
    } else {
        $_SESSION['alert'] = "Error: " . mysqli_error($conexion); // Mensaje de error
    }

    // Redirigir de vuelta al formulario o a la página deseada
    header("Location: tipo_contrato.php"); // Cambia index.php por la ruta a donde quieras redirigir
    exit();
} else {
    // Si no se accede al script a través de POST, redirigir a la página de inicio
    header("Location: tipo_contrato.php");
    exit();
}
?>
