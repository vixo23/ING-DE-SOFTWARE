<?php
session_start();
include_once "includes/header.php";
include "../conexion.php";

// Inicializar la variable para los mensajes
$mensaje = "";
$id_empresa=$_SESSION['idempresa'];

// Inserción o actualización en la base de datos al enviar el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $status = $_POST['status'];

    if (!empty($nombre) && isset($status)) {
        $query = "INSERT INTO tipo_contrato (id_empresa, descripcion, status) VALUES ('$id_empresa','$nombre', '$status')";
        $result = mysqli_query($conexion, $query);

        if ($result) {
            $mensaje = "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Tipo Contrato Registrado',
                    text: 'Tipo contrato registrado exitosamente.',
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

// Cambiar estado
if (isset($_POST['guardar_cambio_estado'])) {
    $id_tipocontrato = isset($_POST['id_tipocontrato']) ? $_POST['id_tipocontrato'] : '';
    $nuevo_estado = isset($_POST['nuevo_estado']) ? $_POST['nuevo_estado'] : '';

    $query = "UPDATE tipo_contrato SET status = '$nuevo_estado' WHERE id_tipocontrato = '$id_tipocontrato'";
    $resultado = mysqli_query($conexion, $query);

    if ($resultado) {
        $mensaje = "<script>
            Swal.fire({
                icon: 'success',
                title: 'Estado actualizado',
                text: 'El estado ha sido actualizado correctamente.',
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
$query = "SELECT * FROM tipo_contrato ORDER BY descripcion";
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
                                <label for="nombre">Descripcion: </label>
                                <input type="text" class="form-control" placeholder="Ingrese Nombre" name="nombre" id="nombre" required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="status">Estado: </label>
                                <select name="status" id="status" class="form-control" required>
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

        <!-- Tabla de registros -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" id="tbl">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Descripcion</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($fila = mysqli_fetch_assoc($resultado)): ?>
                                <tr>
                                    <td><?php echo $fila['id_tipocontrato']; ?></td>
                                    <td><?php echo $fila['descripcion']; ?></td>
                                    <td><?php echo ($fila['status'] == 1) ? "Activo" : "Inactivo"; ?></td>
                                    <td>
                                        <a href="#" onclick="abrirModalCambioEstado(<?php echo $fila['id_tipocontrato']; ?>, <?php echo $fila['ststus']; ?>)" class="btn btn-warning">
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
    function abrirModalCambioEstado(id_tipocontrato, estadoActual) {
        document.getElementById('id_tipocontrato').value = id_tipocontrato;

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
                    <h5 class="modal-title" id="modalCambioEstadoLabel">Confirmar Accion</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="" method="post">
                    <div class="modal-body">
                        <input type="hidden" name="id_tipocontrato" id="id_tipocontrato">
                        <input type="hidden" name="nuevo_estado" id="nuevo_estado"> <!-- Campo oculto para el nuevo estado -->
                        <p>¿Esta seguro de que desea modificar el estado ?</p>
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
