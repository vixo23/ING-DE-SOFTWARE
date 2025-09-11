<?php
// Conexión a la base de datos
include '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recoger los datos del formulario
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $contrasenia = $_POST['pass'];
    $color = $_POST['color'];

    // Validar que los datos no estén vacíos
    if (!empty($id) && !empty($nombre) && !empty($contrasenia) && !empty($color)) {
        // Actualizar los datos del garzón en la base de datos
        $query = "UPDATE garzones SET Nombre = ?, Contrasenia = ?, Color_Garzon = ? WHERE Id_Garzones = ?";
        
        // Preparar la consulta
        if ($stmt = mysqli_prepare($conexion, $query)) {
            // Vincular los parámetros
            mysqli_stmt_bind_param($stmt, 'sssi', $nombre, $contrasenia, $color, $id);

            // Ejecutar la consulta
            if (mysqli_stmt_execute($stmt)) {
                // Redirigir a la página principal o a donde desees después de la actualización
                header('Location: garzones.php?mensaje=Actualización exitosa');
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
