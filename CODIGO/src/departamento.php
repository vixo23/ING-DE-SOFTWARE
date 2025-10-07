<?php
session_start();
include "../conexion.php";

$id_empresa = $_SESSION['idempresa'];

// ==========================
// 1️⃣ REGISTRAR NUEVO DEPARTAMENTO
// ==========================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nombre'], $_POST['estado']) && !isset($_POST['idEditar'])) {
    $descripcion = mysqli_real_escape_string($conexion, trim($_POST['nombre']));
    $estado = (int)$_POST['estado'];
    $fechaCreacion = date('Y-m-d');

    if (!empty($descripcion)) {
        $query_insert = mysqli_query($conexion, "INSERT INTO departamentos (descripcion, status, id_empresas, fechaCreacion) VALUES ('$descripcion', $estado, $id_empresa, '$fechaCreacion')");

        if ($query_insert) {
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    Departamento registrado correctamente.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                  </div>';
        } else {
            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    Error al registrar el departamento.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                  </div>';
        }
        header("Location: departamento.php");
        exit;
    }
}

// ==========================
// 2️⃣ ACTUALIZAR DEPARTAMENTO (AJAX)
// ==========================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['idEditar'], $_POST['nombreEditar'])) {
    $idEditar = (int)$_POST['idEditar'];
    $nombreEditar = mysqli_real_escape_string($conexion, trim($_POST['nombreEditar']));

    if (!empty($nombreEditar) && $idEditar > 0) {
        $query_update = mysqli_query($conexion, "UPDATE departamentos SET descripcion='$nombreEditar' WHERE id_departamento=$idEditar AND id_empresas='$id_empresa'");
        if ($query_update) {
            echo json_encode(["status" => "success", "message" => "Departamento actualizado correctamente."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error al actualizar el departamento."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Datos inválidos."]);
    }
    exit;
}

include "includes/header.php";
?>

<head>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>

<div class="card">
    <div class="card-header">
        <h2>Departamentos</h2>
    </div>
    <div class="card-body">
        <form action="" method="post" autocomplete="off" id="formulario">       
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="nombre">Descripción</label>
                        <input type="text" class="form-control" placeholder="Ingrese descripción del departamento" name="nombre" id="nombre" required>
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
            <input type="button" value="Limpiar" class="btn btn-success" id="btnNuevo" onclick="limpiar()">
        </form>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-hover table-striped table-bordered mt-2" id="tbl">
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
            $query = mysqli_query($conexion, "SELECT * FROM departamentos WHERE id_empresas='$id_empresa' ORDER BY descripcion");
            $result = mysqli_num_rows($query);
            if ($result > 0) {
                while ($data = mysqli_fetch_assoc($query)) {
                    $estado = ($data['status'] == 1) ? '<span class="badge badge-success">Activo</span>' : '<span class="badge badge-danger">Inactivo</span>';
            ?>
                    <tr>
                        <td><?php echo $data['id_departamento']; ?></td>
                        <td><?php echo $data['descripcion']; ?></td>
                        <td><?php echo $data['fechaCreacion']; ?></td>
                        <td><?php echo $estado; ?></td>
                        <td>
                            <a href="#" onclick="editardepartamento(<?php echo $data['id_departamento']; ?>, '<?php echo $data['descripcion']; ?>')" class="btn btn-success">
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

<!-- Modal Confirmación -->
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

<!-- Modal Editar -->
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
                <form id="formEditar">
                    <input type="hidden" id="idEditar" name="idEditar">
                    <div class="form-group">
                        <label for="nombreEditar">Descripción</label>
                        <input type="text" class="form-control" id="nombreEditar" name="nombreEditar" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Actualizar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Alerta -->
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

<script>
// Variable para guardar el ID del departamento
let departamentoId;

// Abrir modal de confirmación
function abrirModalConfirmacion(id) {
    departamentoId = id;
    $('#confirmModal').modal('show');
}

// Confirmar cambio de estado (redirige por ahora)
$('#confirmBtn').on('click', function() {
    if (departamentoId) {
        window.location.href = `cambiar_estado_departamento.php?id=${departamentoId}`;
    }
});

// Abrir modal de edición
function editardepartamento(id, nombre) {
    $('#idEditar').val(id);
    $('#nombreEditar').val(nombre);
    $('#editarModal').modal('show');
}

// Limpiar formulario
function limpiar() {
    $('#formulario')[0].reset();
}

// Mostrar alerta
function mostrarAlerta(mensaje) {
    $('#alertMessage').text(mensaje);
    $('#alertModal').modal('show');
}

// Enviar formulario de edición por AJAX
$('#formEditar').on('submit', function(e) {
    e.preventDefault();

    $.ajax({
        url: '', // mismo archivo PHP
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                $('#editarModal').modal('hide');
                mostrarAlerta(response.message);
                setTimeout(() => location.reload(), 1000);
            } else {
                mostrarAlerta(response.message);
            }
        },
        error: function() {
            mostrarAlerta('Error de comunicación con el servidor.');
        }
    });
});
</script>

<?php include_once "includes/footer.php"; ?>
