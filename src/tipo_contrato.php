<?php
session_start();
include "../conexion.php";
include "includes/header.php";
$id_empresa = $_SESSION['idempresa'];
?>
<head>
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.0/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>

<div class="card">
    <div class="card-body">
        <form action="guardar_tipocontrato.php" method="post" autocomplete="off" id="formulario">
            <div class="row">
                <!-- Nombre -->
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="nombre">Descripcion</label>
                        <input type="text" class="form-control" placeholder="Ingrese Descripcion" name="nombre" id="nombre" required>
                        <input type="hidden" id="id" name="id">
                    </div>
                </div>

                <!-- Estado -->
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="estado">Estado</label>
                        <select id="estado" class="form-control" name="estado" required>
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </div>

                <!-- Descripción del Turno -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="descripcion_turno">Descripción del Turno</label>
                        <textarea class="form-control" id="descripcion_turno" name="descripcion_turno" placeholder="Agregue una descripción para este turno..." rows="3"></textarea>
                    </div>
                </div>
            </div>

            <!-- Botones -->
            <input type="submit" value="Registrar" class="btn btn-primary" id="btnAccion">
            <input type="button" value="Nuevo" class="btn btn-success" id="btnNuevo" onclick="limpiar()">
        </form>

        <!-- Vista previa de descripción -->
        <div class="mt-3">
            <h5>Descripción del Turno (no se guarda):</h5>
            <div id="mostrarDescripcion" class="alert alert-info" style="display: none;"></div>
            <div id="mensajeRestaurado" class="alert alert-success mt-2" style="display: none;">
                Descripción restaurada desde sesión anterior.
            </div>
        </div>
    </div>
</div>

<!-- Tabla -->
<div class="table-responsive">
    <table class="table table-hover table-striped table-bordered mt-2" id="tbl">
        <thead class="thead-dark">
            <tr>
                <th>#</th>
                <th>Nombre</th>
                <th>Descripción</th> <!-- Nueva columna -->
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $query = mysqli_query($conexion, "SELECT * FROM tipo_contrato WHERE id_empresa='$id_empresa' ORDER BY descripcion");
            $result = mysqli_num_rows($query);
            if ($result > 0) {
                while ($data = mysqli_fetch_assoc($query)) {
                    $estado = ($data['status'] == 1) ? '<span class="badge badge-success">Activo</span>' : '<span class="badge badge-danger">Inactivo</span>';
                    ?>
                    <tr>
                        <td><?php echo $data['id_tipocontrato']; ?></td>
                        <td><?php echo htmlspecialchars($data['descripcion']); ?></td>
                        <td><?php echo htmlspecialchars($data['descripcion_turno']); ?></td> <!-- Mostrar descripción -->
                        <td><?php echo $estado; ?></td>
                        <td>
                            <a href="#" onclick="editartipocontrato(<?php echo $data['id_tipocontrato']; ?>, '<?php echo addslashes($data['descripcion']); ?>',  <?php echo $data['status']; ?>)" class="btn btn-success">
                                <i class='fas fa-edit'></i>
                            </a>
                            <a href="#" onclick="abrirModalConfirmacion(<?php echo $data['id_tipocontrato']; ?>)" class="btn btn-warning">
                                <i class='fas fa-exchange-alt'></i> Cambiar Estado
                            </a>
                        </td>
                    </tr>
            <?php }
            } ?>
        </tbody>
    </table>
</div>

<!-- Modales -->
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
                ¿Está seguro de que desea modificar el estado de este tipo contrato?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmBtn">Modificar Estado</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editarModal" tabindex="-1" role="dialog" aria-labelledby="editarModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editarModalLabel">Editar tipo contrato</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="actualizar_tipocontrato.php" method="post" id="formEditar">
                    <input type="hidden" id="idEditar" name="id">
                    <div class="form-group">
                        <label for="nombreEditar">Descripcion</label>
                        <input type="text" class="form-control" id="nombreEditar" name="nombre" required>
                    </div>
                    <input type="submit" value="Actualizar" class="btn btn-primary">
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="alertModal" tabindex="-1" role="dialog" aria-labelledby="alertModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="alertModalLabel">Alerta</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="alertMessage">Este es un mensaje de alerta.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">Aceptar</button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
let tipocontratoId;

function abrirModalConfirmacion(id) {
    tipocontratoId = id;
    $('#confirmModal').modal('show');
}

$('#confirmBtn').on('click', function() {
    if (tipocontratoId) {
        window.location.href = `cambiar_estado.php?id=${tipocontratoId}`;
    }
});

function editartipocontrato(id, nombre, estado) {
    $('#idEditar').val(id);
    $('#nombreEditar').val(nombre);
    $('#editarModal').modal('show');
}

function limpiar() {
    $('#formulario')[0].reset();
    $('#descripcion_turno').val('');
    $('#mostrarDescripcion').hide();
    sessionStorage.removeItem('descripcion_turno');
    $('#mensajeRestaurado').hide();
}

// Mostrar alerta
function mostrarAlerta(mensaje) {
    $('#alertMessage').text(mensaje);
    $('#alertModal').modal('show');
}

// Guardar y mostrar descripción del turno
$('#formulario').on('submit', function () {
    const descripcion = $('#descripcion_turno').val().trim();
    if (descripcion !== '') {
        sessionStorage.setItem('descripcion_turno', descripcion);
        $('#mostrarDescripcion').text(descripcion).show();
    } else {
        sessionStorage.removeItem('descripcion_turno');
        $('#mostrarDescripcion').hide();
    }
});

// Restaurar descripción desde sessionStorage
$(document).ready(function () {
    const descripcionGuardada = sessionStorage.getItem('descripcion_turno');
    if (descripcionGuardada) {
        $('#descripcion_turno').val(descripcionGuardada);
        $('#mostrarDescripcion').text(descripcionGuardada).show();
        $('#mensajeRestaurado').fadeIn();
        setTimeout(() => { $('#mensajeRestaurado').fadeOut(); }, 4000);
    }
});
</script>

<?php include_once "includes/footer.php"; ?>
