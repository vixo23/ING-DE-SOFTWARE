<?php
// --- CORRECCIÓN 1: Lógica de guardado al principio ---
ob_start(); // Inicia el buffer de salida para evitar errores de "headers already sent"
session_start();
include "../conexion.php";
$id_empresa = $_SESSION['idempresa'];

// --- PROCESAR FORMULARIO DE INSERCIÓN ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nombre'], $_POST['estado'])) {
    $descripcion = mysqli_real_escape_string($conexion, trim($_POST['nombre']));
    $estado = (int)$_POST['estado'];
    $fechaCreacion = date('Y-m-d'); // Fecha actual

    if (!empty($descripcion)) {
        $query_insert = mysqli_query($conexion, "INSERT INTO motivos_permisos (descripcion, status, id_empresa, fecha_Creacion) VALUES ('$descripcion', $estado, $id_empresa, '$fechaCreacion')");

        if ($query_insert) {
            $_SESSION['msg'] = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                                Motivo de permiso registrado correctamente.
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                              </div>';
        } else {
            $_SESSION['msg'] = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                                Error al registrar el motivo de permiso.
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                              </div>';
        }
        // Redirigir para limpiar el POST y mostrar el mensaje
        header("Location: motivo_permisos.php");
        exit;
    }
}

include "includes/header.php";
?>

<head>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>

<div class="card">
    <div class="card-header">
        <h2>Motivos de Permisos</h2>
    </div>
    <div class="card-body">
        
        <?php
        // Mostrar el mensaje de la sesión si existe
        if (isset($_SESSION['msg'])) {
            echo $_SESSION['msg'];
            unset($_SESSION['msg']);
        }
        ?>

        <form action="" method="post" autocomplete="off" id="formulario">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="nombre">Descripción</label>
                        <input type="text" class="form-control" placeholder="Ingrese Descripción" name="nombre" id="nombre" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="estado">Estado</label>
                        <select id="estado" class="form-control" name="estado" required>
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </div>
            </div>
            <input type="submit" value="Registrar" class="btn btn-primary" id="btnAccion">
            <input type="button" value="Limpiar" class="btn btn-success" id="btnNuevo" onclick="limpiar()">
        </form>
    </div>
</div>

<div class="table-responsive mt-3">
    <table class="table table-hover table-striped table-bordered" id="tbl">
        <thead class="thead-dark">
            <tr>
                <th>#</th>
                <th>Nombre</th>
                <th>Fecha de Creación</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $query = mysqli_query($conexion, "SELECT * FROM motivos_permisos WHERE id_empresa='$id_empresa' ORDER BY descripcion");
            $result = mysqli_num_rows($query);
            if ($result > 0) {
                while ($data = mysqli_fetch_assoc($query)) {
                    $estado = ($data['status'] == 1) ? '<span class="badge badge-success">Activo</span>' : '<span class="badge badge-danger">Inactivo</span>';
            ?>
                    <tr>
                        <td><?php echo $data['id_motivo']; ?></td>
                        <td><?php echo htmlspecialchars($data['descripcion']); ?></td>
                        <td><?php echo $data['fecha_Creacion']; // CORRECCIÓN: Nombre de columna corregido ?></td>
                        <td><?php echo $estado; ?></td>
                        <td>
                            
                            <a href="#" onclick="abrirModalConfirmacion(<?php echo $data['id_motivo']; ?>)" class="btn btn-warning btn-sm">
                                <i class='fas fa-exchange-alt'></i> Cambiar Estado
                            </a>
                        </td>
                    </tr>
            <?php }
            } ?>
        </tbody>
    </table>
</div>

<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalLabel">Confirmar Acción</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                ¿Está seguro de que desea modificar el estado de este motivo?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmBtn">Modificar Estado</button>
            </div>
        </div>
    </div>
</div>



<script>
// --- CORRECCIÓN 2: Lógica JavaScript Corregida ---
let motivoId; // Variable global para guardar el ID

function abrirModalConfirmacion(id) {
    motivoId = id; // Guardar el ID
    $('#confirmModal').modal('show');
}

// Evento para el botón de confirmar
$('#confirmBtn').on('click', function() {
    // Usamos la variable 'motivoId' que llenamos antes
    if (motivoId) {
        // Debes crear este archivo PHP para manejar el cambio de estado
        window.location.href = `cambiar_estado_motivo_permisos.php?id=${motivoId}`;
    }
});



function limpiar() {
    $('#formulario')[0].reset();
}
</script>

<?php 
include_once "includes/footer.php"; 
ob_end_flush(); // Envía el buffer de salida al navegador
?>