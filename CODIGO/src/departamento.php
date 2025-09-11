<?php
session_start();
include "../conexion.php";
include "includes/header.php";
$id_empresa = $_SESSION['idempresa'];
?>

<head>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>

<div class="card">
    <div class="card-body">
        <form action="guardar_departamento.php" method="post" autocomplete="off" id="formulario">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="nombre">Descripcion</label>
                        <input type="text" class="form-control" placeholder="Ingrese Descripcion" name="nombre" id="nombre" required>
                        <input type="hidden" id="id" name="id">
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
            <input type="button" value="Nuevo" class="btn btn-success" id="btnNuevo" onclick="limpiar()">
        </form>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-hover table-striped table-bordered mt-2" id="tbl">
        <thead class="thead-dark">
            <tr>
                <th>#</th>
                <th>Nombre</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $query = mysqli_query($conexion, "SELECT * FROM departamentos WHERE id_empresas='$id_empresa' ORDER BY descripcion");
            $result = mysqli_num_rows($query);
            if ($result > 0) {
                while ($data = mysqli_fetch_assoc($query)) {
                    $estado = ($data['status'] == 1) ? '<span class="badge badge-success">Activo</span>' : '<span class="badge badge-danger">Inactivo</span>';
            ?>
                    <tr>
                        <td><?php echo $data['id_departamento']; ?></td>
                        <td><?php echo $data['descripcion']; ?></td>
                        <td><?php echo $estado; ?></td>
                        <td>
                            <a href="#" onclick="editardepartamento(<?php echo $data['id_departamento']; ?>, '<?php echo $data['descripcion']; ?>', <?php echo $data['status']; ?>)" class="btn btn-success">
                                <i class='fas fa-edit'></i>
                            </a>
                            <a href="#" onclick="abrirModalConfirmacion(<?php echo $data['id_departamento']; ?>)" class="btn btn-warning">
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
                ¿Está seguro de que desea modificar el estado de este departamento?
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
                <h5 class="modal-title" id="editarModalLabel">Editar Departamento</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="actualizar_departamento.php" method="post" id="formEditar">
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

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
// Variable para guardar el ID del departamento
let departamentoId;

// Funcion para abrir el modal de confirmacion
function abrirModalConfirmacion(id) {
    departamentoId = id; // Guardar el ID
    $('#confirmModal').modal('show'); // Mostrar el modal
}

// Funcion para confirmar el cambio de estado
$('#confirmBtn').on('click', function() {
    if (departamentoId) {
        window.location.href = `cambiar_estado_departamento.php?id=${departamentoId}`;
    }
});

// Funcion para abrir el modal de edicion
function editardepartamento(id, nombre, estado) {
    // Rellenar el formulario de edicion
    $('#idEditar').val(id);
    $('#nombreEditar').val(nombre);
    $('#editarModal').modal('show'); // Mostrar el modal de edicion
}

// Funcion para limpiar el formulario
function limpiar() {
    $('#formulario')[0].reset(); // Restablecer el formulario
}

// Funcion para mostrar alerta
function mostrarAlerta(mensaje) {
    $('#alertMessage').text(mensaje); // Rellenar el mensaje de alerta
    $('#alertModal').modal('show'); // Mostrar el modal de alerta
}
</script>

<?php include_once "includes/footer.php"; ?>