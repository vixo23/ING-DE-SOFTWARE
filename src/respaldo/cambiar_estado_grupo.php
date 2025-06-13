<?php
// Incluir la conexión a la base de datos
include '../conexion.php'; // Asegúrate de que este archivo contenga la conexión a tu base de datos

// Verificar si se ha enviado el ID del grupo
if (isset($_GET['id'])) {
    // Obtener el ID del grupo
    $id = intval($_GET['id']);

    // Consulta para obtener el estado actual del grupo
    $query = mysqli_query($conexion, "SELECT estado FROM grupos WHERE id = '$id'");
    
    // Verificar si se encontró el grupo
    if (mysqli_num_rows($query) > 0) {
        $data = mysqli_fetch_assoc($query);

        // Cambiar el estado
        $nuevo_estado = ($data['estado'] == 1) ? 0 : 1;

        // Actualizar en la base de datos
        $update = mysqli_query($conexion, "UPDATE grupos SET estado = '$nuevo_estado' WHERE id = '$id'");

        // Verificar si la actualización fue exitosa
        if ($update) {
            header("Location: grupos.php?mensaje=Estado cambiado con éxito");
        } else {
            header("Location: grupos.php?error=Error al cambiar el estado");
        }
    } else {
        // Si no se encuentra el grupo, redirigir con un mensaje de error
        header("Location: tu_pagina.php?error=Grupo no encontrado");
    }
} else {
    // Si no se envía el ID, redirigir con un mensaje de error
    header("Location: tu_pagina.php?error=ID no válido");
}
?>
