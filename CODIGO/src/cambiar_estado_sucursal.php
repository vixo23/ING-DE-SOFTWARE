<?php
// Incluir la conexión a la base de datos
include '../conexion.php'; // Asegúrate de que este archivo contenga la conexión a tu base de datos

// Verificar si se ha enviado el ID del Tipo contrato
if (isset($_GET['id'])) {
    // Obtener el ID del contrato
    $id = intval($_GET['id']);

    // Consulta para obtener el estado actual del garzón
    $query = mysqli_query($conexion, "SELECT status FROM sucursal WHERE id_sucursal = '$id'");

    // Verificar si se encontró el garzón
    if (mysqli_num_rows($query) > 0) {
        $data = mysqli_fetch_assoc($query);

        // Cambiar el estado
        $nuevo_estado = ($data['status'] == 1) ? 0 : 1;

        // Actualizar en la base de datos
        $update = mysqli_query($conexion, "UPDATE sucursal SET status = '$nuevo_estado' WHERE id_sucursal = '$id'");

        // Verificar si la actualización fue exitosa
        if ($update) {
            header("Location: sucursal.php?mensaje=Estado cambiado con éxito");
        } else {
            header("Location: sucursal.php?error=Error al cambiar el estado");
        }
    } else {
        // Si no se encuentra el garzón, redirigir con un mensaje de error
        header("Location: tu_pagina.php?error=Sucursal no encontrado");
    }
} else {
    // Si no se envía el ID, redirigir con un mensaje de error
    header("Location: tu_pagina.php?error=ID no válido");
}
?>
