<?php
session_start();
include "../conexion.php";
include "includes/header.php";

// Empresa de sesión
$id_empresa = isset($_SESSION['idempresa']) ? (int)$_SESSION['idempresa'] : 0;

// Procesamiento del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nombre'], $_POST['estado'])) {

    $descripcion = mysqli_real_escape_string($conexion, trim($_POST['nombre']));
    $estado = (int)$_POST['estado'];

    if ($id_empresa <= 0) {
        echo '<div class="alert alert-danger">Error: Empresa no válida en la sesión.</div>';
    } elseif (empty($descripcion)) {
        echo '<div class="alert alert-warning">Debe ingresar una descripción válida.</div>';
    } else {
        $sql = "INSERT INTO tipo_vacaciones (descripcion, status, id_empresas) VALUES ('$descripcion', $estado, $id_empresa)";
        $query_insert = mysqli_query($conexion, $sql);

        if ($query_insert) {
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    Tipo de vacación registrado correctamente.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                  </div>';
        } else {
            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    Error al registrar el tipo de vacación: ' . mysqli_error($conexion) . '
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                  </div>';
        }
    }
}

?>

<div class="card ">
    <h2>Tipo de Vacaciones</h2>
    <form id="formulario" method="post">
        <div class="form-group">
            <label for="nombre">Descripción</label>
            <input type="text" name="nombre" id="nombre" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="estado">Estado</label>
            <select name="estado" id="estado" class="form-control" required>
                <option value="1">Activo</option>
                <option value="0">Inactivo</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary mt-2">Registrar</button>
        <button type="button" class="btn btn-info mt-2" data-toggle="modal" data-target="#modalTiposVacaciones">Ver Todos</button>
    </form>
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
            $query = mysqli_query($conexion, "SELECT * FROM tipo_vacaciones WHERE id_empresas='$id_empresa' ORDER BY descripcion");
            $result = mysqli_num_rows($query);
            if ($result > 0) {
                while ($data = mysqli_fetch_assoc($query)) {
                    $estado = ($data['status'] == 1) ? '<span class="badge badge-success">Activo</span>' : '<span class="badge badge-danger">Inactivo</span>';
                    ?>
                    <tr>
                        <td><?php echo $data['id_tipovacacion']; ?></td>
                        <td><?php echo $data['descripcion']; ?></td>
                        <td><?php echo $estado; ?></td>
                        <td>
                            <a href="#" onclick="abrirModalConfirmacion(<?php echo $data['id_tipovacacion']; ?>)" class="btn btn-warning">
                                <i class='fas fa-exchange-alt'></i> Cambiar Estado
                            </a>
                        </td>
                    </tr>
            <?php }
            } ?>
        </tbody>
    </table>
</div>
<!-- Modal de Confirmación -->
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
                ¿Está seguro de que desea modificar el estado de este tipo de vacacion?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmBtn">Modificar Estado</button>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
// Variable para guardar el ID del tipo de contrato
let tipovacacionId;

// Función para abrir el modal de confirmación
function abrirModalConfirmacion(id) {
    tipovacacionId = id; // Guardar el ID del garzón
    $('#confirmModal').modal('show'); // Mostrar el modal
}

// Función para confirmar el cambio de estado
$('#confirmBtn').on('click', function() {
    if (tipovacacionId) {
        // Redirigir a cambiar_estado.php con el ID del garzón
        window.location.href = `cambiar_estado_tipo_vacaciones.php?id=${tipovacacionId}`;
    }
});
</script>

<?php include "includes/footer.php"; ?>