<?php
// Conexión a la base de datos
include '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recoger los datos del formulario
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];

    // Validar que los datos no estén vacíos
    if (!empty($id) && !empty($nombre)) {
        // Actualizar los datos del garzón en la base de datos
        $query = "UPDATE tipo_contrato SET descripcion = ?  WHERE id_tipocontrato = ?";
        
        // Preparar la consulta
        if ($stmt = mysqli_prepare($conexion, $query)) {
            // Vincular los parámetros
            mysqli_stmt_bind_param($stmt, 'si', $nombre, $id);

            // Ejecutar la consulta
            if (mysqli_stmt_execute($stmt)) {
                // Redirigir a la página principal o a donde desees después de la actualización
                header('Location: tipo_contrato.php?mensaje=Actualización exitosa');
                exit();
            } else {
                echo "Error al actualizar: " . mysqli_error($conexion);
            }

            // Cerrar la declaración
            mysqli_stmt_close($stmt);
        }
    } else {
        echo "Todos los campos son obligatorios.";
    }
}

// Cerrar la conexión a la base de datos
mysqli_close($conexion);
?>
