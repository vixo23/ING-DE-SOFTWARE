<?php
// Conexión a la base de datos
include '../conexion.php';

// Iniciar sesión si es necesario para obtener el ID de la empresa
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recoger los datos del formulario
    $id_usuario = $_POST['id_usuario'];
    $id_turno = $_POST['id_turno'];
    
    // Obtener el ID de la empresa de la sesión para seguridad
    $id_empresa = $_SESSION['idempresa']; // Asegúrate de que esta variable de sesión esté definida.

    // Validar que los datos no estén vacíos
    if (!empty($id_usuario) && !empty($id_turno)) {
        // Actualizar el turno del usuario en la base de datos
        // La consulta ahora se dirige a la tabla 'usuarios'
        $query = "UPDATE usuarios SET turnos_id_turnos = ? WHERE id_usuarios = ? AND id_empresa = ?";
        
        // Preparar la consulta
        if ($stmt = mysqli_prepare($conexion, $query)) {
            // Vincular los parámetros. 'iii' significa tres enteros.
            // Primero el ID del turno, luego el ID del usuario, y finalmente el ID de la empresa.
            mysqli_stmt_bind_param($stmt, 'iii', $id_turno, $id_usuario, $id_empresa);

            // Ejecutar la consulta
            if (mysqli_stmt_execute($stmt)) {
                // Redirigir a la página principal con un mensaje de éxito
                header('Location: asignacion_turnos.php?mensaje=Turno actualizado exitosamente');
                exit();
            } else {
                echo "Error al actualizar: " . mysqli_error($conexion);
            }

            // Cerrar la declaración
            mysqli_stmt_close($stmt);
        } else {
            echo "Error en la preparación de la consulta: " . mysqli_error($conexion);
        }
    } else {
        echo "El ID de usuario y el ID de turno son obligatorios.";
    }
}

// Cerrar la conexión a la base de datos
mysqli_close($conexion);
?>