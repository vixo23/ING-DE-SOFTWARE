<?php
session_start();
include_once "includes/header.php";
include "../conexion.php";

// Inicializar la variable para los mensajes
$mensaje = "";

// Inserción o actualización en la base de datos al enviar el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $estado = $_POST['estado'];

    if (!empty($nombre) && isset($estado)) {
        $query = "INSERT INTO impresoras (nombre, estado) VALUES ('$nombre', '$estado')";
        $result = mysqli_query($conexion, $query);

        if ($result) {
            $mensaje = "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Impresora registrada',
                    text: 'Impresora registrada exitosamente.',
                    confirmButtonText: 'Aceptar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.reload();
                    }
                });
            </script>";
        } else {
            $mensaje = "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al registrar: " . mysqli_error($conexion) . ".',
                    confirmButtonText: 'Aceptar'
                });
            </script>";
        }
    } else {
        $mensaje = "<script>
            Swal.fire({
                icon: 'warning',
                title: 'Advertencia',
                text: 'Por favor, completa todos los campos.',
                confirmButtonText: 'Aceptar'
            });
        </script>";
    }
}

// Cambiar estado de la impresora
if (isset($_POST['guardar_cambio_estado'])) {
    $id_impresora = isset($_POST['id_impresora']) ? $_POST['id_impresora'] : '';
    $nuevo_estado = isset($_POST['nuevo_estado']) ? $_POST['nuevo_estado'] : '';

    $query = "UPDATE impresoras SET estado = '$nuevo_estado' WHERE id_impresora = '$id_impresora'";
    $resultado = mysqli_query($conexion, $query);

    if ($resultado) {
        $mensaje = "<script>
            Swal.fire({
                icon: 'success',
                title: 'Estado actualizado',
                text: 'El estado de la impresora ha sido actualizado correctamente.',
                confirmButtonText: 'Aceptar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.reload();
                }
            });
        </script>";
    } else {
        $mensaje = "<script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Hubo un problema al actualizar el estado: " . mysqli_error($conexion) . ".',
                confirmButtonText: 'Aceptar'
            });
        </script>";
    }
}

// Consulta para obtener los datos de la tabla impresoras
$query = "SELECT * FROM impresoras";
$resultado = mysqli_query($conexion, $query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Impresoras</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/10.16.7/sweetalert2.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/10.16.7/sweetalert2.min.js"></script>
</head>
<body>
    <div class="container">
        <?php echo $mensaje; ?> <!-- Mostrar mensajes de SweetAlert -->
        <div class="card">
            <div class="card-body">
                <form action="" method="post" autocomplete="off" id="formulario">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="nombre">Nombre de la impresora: </label>
                                <input type="text" class="form-control" placeholder="Ingrese Nombre" name="nombre" id="nombre" required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="estado">Estado: </label>
                                <select name="estado" id="estado" class="form-control" required>
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-5 text-center">
                            <label for="">Acciones</label> <br>
                            <input type="submit" value="Registrar" class="btn btn-primary" id="btnAccion">
                            <input type="button" value="Nuevo" class="btn btn-success" id="btnNuevo" onclick="limpiar()">
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla de Impresoras -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" id="tbl">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nombre</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($fila = mysqli_fetch_assoc($resultado)): ?>
                                <tr>
                                    <td><?php echo $fila['id_impresora']; ?></td>
                                    <td><?php echo $fila['nombre']; ?></td>
                                    <td><?php echo ($fila['estado'] == 1) ? "Activo" : "Inactivo"; ?></td>
                                    <td>
                                        <a href="#" onclick="abrirModalCambioEstado(<?php echo $fila['id_impresora']; ?>, <?php echo $fila['estado']; ?>)" class="btn btn-warning">
                                            <i class='fas fa-exchange-alt'></i> Cambiar Estado
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
    function abrirModalCambioEstado(idImpresora, estadoActual) {
        document.getElementById('id_impresora').value = idImpresora;

        // Cambiar el nuevo estado automáticamente basado en el estado actual
        var nuevoEstado = (estadoActual == 1) ? 0 : 1; // Cambia 1 a 0 y 0 a 1
        document.getElementById('nuevo_estado').value = nuevoEstado; // Asigna el nuevo estado al campo oculto

        $('#modalCambioEstado').modal('show');
    }
    </script>

    <!-- Modal para Cambiar Estado -->
    <div class="modal fade" id="modalCambioEstado" tabindex="-1" role="dialog" aria-labelledby="modalCambioEstadoLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCambioEstadoLabel">Confirmar Acción</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="" method="post">
                    <div class="modal-body">
                        <input type="hidden" name="id_impresora" id="id_impresora">
                        <input type="hidden" name="nuevo_estado" id="nuevo_estado"> <!-- Campo oculto para el nuevo estado -->
                        <p>¿Está seguro de que desea modificar el estado de esta impresora?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" name="guardar_cambio_estado" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include_once "includes/footer.php"; ?>
</body>
</html>
