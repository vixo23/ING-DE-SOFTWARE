<?php
session_start();
include "../conexion.php";
$id_empresa = $_SESSION['idempresa'];

// Procesar el formulario ANTES de cargar HTML
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nombre'], $_POST['direccion'], $_POST['comuna'], $_POST['estado'], $_POST['tipo'])) {
    $nombre = mysqli_real_escape_string($conexion, trim($_POST['nombre']));
    $direccion = mysqli_real_escape_string($conexion, trim($_POST['direccion']));
    $comuna = mysqli_real_escape_string($conexion, trim($_POST['comuna']));
    $estado = (int)$_POST['estado'];
    $tipo = mysqli_real_escape_string($conexion, trim($_POST['tipo']));

    if (!empty($nombre) && !empty($direccion) && !empty($comuna) && !empty($tipo)) {
        $query_insert = mysqli_query($conexion, "INSERT INTO sucursal (nombre, direccion, comuna, status, empresas_id_empresas, tipo) 
                                                 VALUES ('$nombre', '$direccion', '$comuna', $estado, $id_empresa, '$tipo')");

        if ($query_insert) {
            $_SESSION['msg'] = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                                    Sucursal registrada correctamente.
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>';
        } else {
            $_SESSION['msg'] = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    Error al registrar la sucursal.
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>';
        }
        //  Redirigir para evitar duplicados al refrescar
        header("Location: sucursal.php");
        exit;
    }
}
include "includes/header.php";
?>
<head>

<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.0/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</head>
<div class="card">
    <div class="card-header">
        <h2>Sucursal</h2>
    </div>
    <div class="card-body">
        <!-- Mostrar mensajes de éxito o error -->
        <?php
        if (isset($_SESSION['msg'])) {
            echo $_SESSION['msg'];
            unset($_SESSION['msg']);
        }
        ?>

        <form action="" method="post" autocomplete="off" id="formulario">       
            <div class="row">
                <!-- Nombre -->
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="nombre">Nombre</label>
                        <input type="text" class="form-control" placeholder="Ingrese Nombre" name="nombre" id="nombre" required>
                    </div>
                </div>
                <!-- Dirección -->
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="direccion">Dirección</label>
                        <input type="text" class="form-control" placeholder="Ingrese Dirección" name="direccion" id="direccion" required>
                    </div>
                </div>
                <!-- Comuna -->
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="comuna">Comuna</label>
                        <input type="text" class="form-control" placeholder="Ingrese Comuna" name="comuna" id="comuna" required>
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
                <!-- Tipo -->
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="tipo">Tipo</label>
                        <select id="tipo" class="form-control" name="tipo" required>
                            <option value="Matriz">Matriz</option>
                            <option value="Sucursal">Sucursal</option>
                        </select>
                    </div>
                </div>
            </div>
            <!-- Botones -->
            <input type="submit" value="Registrar" class="btn btn-primary" id="btnAccion">
            <input type="button" value="limpiar" class="btn btn-success" id="btnNuevo" onclick="limpiar()">
        </form>
    </div>
</div>


<div class="table-responsive">
    <table class="table table-hover table-striped table-bordered mt-2" id="tbl">
        <thead class="thead-dark">
            <tr>
                <th>#</th>
                <th>Nombre</th>
                <th>Direccion</th>
                <th>Comuna</th>
                <th>Tipo</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $query = mysqli_query($conexion, "SELECT * FROM sucursal WHERE empresas_id_empresas='$id_empresa' ORDER BY nombre");
            $result = mysqli_num_rows($query);
            if ($result > 0) {
                while ($data = mysqli_fetch_assoc($query)) {
                    $estado = ($data['status'] == 1) ? '<span class="badge badge-success">Activo</span>' : '<span class="badge badge-danger">Inactivo</span>';
                    ?>
                    <tr>
                        <td><?php echo $data['id_sucursal']; ?></td>
                        <td><?php echo $data['nombre']; ?></td>
                        <td><?php echo $data['direccion']; ?></td>
                        <td><?php echo $data['comuna']; ?></td>
                        <td><?php echo $data['tipo']; ?></td>
                        <td><?php echo $estado; ?></td>
                        <td>
                            <a href="#" onclick="editarsucursal(<?php echo $data['id_sucursal']; ?>, '<?php echo $data['nombre']; ?>',  <?php echo $data['status']; ?>)" class="btn btn-success">
                                <i class='fas fa-edit'></i>
                            </a>
                            <a href="#" onclick="abrirModalConfirmacion(<?php echo $data['id_sucursal']; ?>)" class="btn btn-warning">
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
                ¿Está seguro de que desea modificar el estado de este tipo contrato?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmBtn">Modificar Estado</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para editar tipo contrato -->
<div class="modal fade" id="editarModal" tabindex="-1" role="dialog" aria-labelledby="editarModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editarModalLabel">Editar Sucursal</h5>
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


<!-- Modal de Alerta -->
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

<!-- jQuery y Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
// Variable para guardar el ID del tipo de contrato
let tipocontratoId;

// Función para abrir el modal de confirmación
function abrirModalConfirmacion(id) {
    tipocontratoId = id; // Guardar el ID del garzón
    $('#confirmModal').modal('show'); // Mostrar el modal
}

// Función para confirmar el cambio de estado
$('#confirmBtn').on('click', function() {
    if (tipocontratoId) {
        // Redirigir a cambiar_estado.php con el ID del garzón
        window.location.href = `cambiar_estado.php?id=${tipocontratoId}`;
    }
});

// Función para abrir el modal de edición
function editarsucursal(id, direccion, estado) {
    // Rellenar el formulario de edición
    $('#idEditar').val(id);
    $('#nombreEditar').val(direccion);
    $('#editarModal').modal('show'); // Mostrar el modal de edición
}

// Función para limpiar el formulario
function limpiar() {
    $('#formulario')[0].reset(); // Restablecer el formulario
}

// Función para mostrar alerta
function mostrarAlerta(mensaje) {
    $('#alertMessage').text(mensaje); // Rellenar el mensaje de alerta
    $('#alertModal').modal('show'); // Mostrar el modal de alerta
}
</script>

<?php include_once "includes/footer.php"; ?>