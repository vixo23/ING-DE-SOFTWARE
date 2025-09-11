<?php
session_start();
if ($_SESSION['rol'] != 1) {
    header('Location: permisos.php');
    exit;
}
include "../conexion.php";
if (!empty($_POST)) {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $rol = $_POST['rol'];
    $alert = "";
    if (empty($nombre) || empty($correo) || empty($rol)) {
        echo $alert = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                    Todo los campos son obligatorio
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>';
    } else {
        if (empty($id)) {
            $pass = $_POST['pass'];
            if (empty($pass)) {
                echo $alert = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                    La contraseña es requerido
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>';
            } else {
                $pass = md5($_POST['pass']);
                $query = mysqli_query($conexion, "SELECT * FROM usuarios where correo = '$correo' AND estado = 1");
                $result = mysqli_fetch_array($query);
                if ($result > 0) {
                    echo $alert = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                    El correo ya existe
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>';
                } else {
                    $query_insert = mysqli_query($conexion, "INSERT INTO usuarios (nombre,correo,rol,pass) values ('$nombre', '$correo', '$rol', '$pass')");
                    if ($query_insert) {
                        echo $alert = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    Usuario Registrado
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>';
                    } else {
                        echo $alert = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    Error al registrar
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>';
                    }
                }
            }
        } else {
            $sql_update = mysqli_query($conexion, "UPDATE usuarios SET nombre = '$nombre', correo = '$correo' , rol = '$rol' WHERE idusuario = $id");
            if ($sql_update) {
                echo $alert = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    Usuario Modificado
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>';
            } else {
                echo $alert = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    Error al modificar
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>';
            }
        }
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
    <div class="card-body">
        <form action="guardar_grupo.php" method="post" autocomplete="off" id="formulario">
            
            
            <!-- Fila de Formulario -->
            <div class="row">
                <!-- Nombre -->
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="nombre">Nombre Grupo</label>
                        <input type="text" class="form-control" placeholder="Ingrese Nombre" name="nombre" id="nombre" required>
                        <input type="hidden" id="id" name="id">
                    </div>
                </div>


                <!-- Color del Garzón -->
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="color">Color del Bot&oacute;n</label>
                        <input type="color" class="form-control" name="color" id="color" required>
                    </div>
                </div>

                <!-- Estado del Garzón -->
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="estado">Estado del Grupo</label>
                        <select id="estado" class="form-control" name="estado" required>
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Botones -->
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
                <th>Color</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $query = mysqli_query($conexion, "SELECT * FROM grupos ORDER BY nombre");
            $result = mysqli_num_rows($query);
            if ($result > 0) {
                while ($data = mysqli_fetch_assoc($query)) {
                    $estado = ($data['estado'] == 1) ? '<span class="badge badge-success">Activo</span>' : '<span class="badge badge-danger">Inactivo</span>';
                    ?>
                    <tr>
                        <td><?php echo $data['id']; ?></td>
                        <td><?php echo $data['nombre']; ?></td>
                        <td><span style="background-color:<?php echo $data['color']; ?>; padding:5px 20px; display:inline-block;"></span></td>
                        <td><?php echo $estado; ?></td>
                        <td>
                            <a href="#" onclick="editarGrupo(<?php echo $data['id']; ?>, '<?php echo $data['nombre']; ?>', '<?php echo $data['color']; ?>', <?php echo $data['estado']; ?>)" class="btn btn-success">
                                <i class='fas fa-edit'></i>
                            </a>
                            <a href="#" onclick="abrirModalConfirmacion(<?php echo $data['id']; ?>)" class="btn btn-warning">
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
                ¿Está seguro de que desea modificar el estado de este Grupo?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmBtn">Modificar Estado</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para editar Grupo -->
<div class="modal fade" id="editarModal" tabindex="-1" role="dialog" aria-labelledby="editarModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editarModalLabel">Editar Grupo</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="actualizar_grupo.php" method="post" id="formEditar">
                    <input type="hidden" id="idEditar" name="id">
                    <div class="form-group">
                        <label for="nombreEditar">Nombre</label>
                        <input type="text" class="form-control" id="nombreEditar" name="nombre" required>
                    </div>
                    <div class="form-group">
                        <label for="colorEditar">Color del Bot&oacue;n</label>
                        <input type="color" class="form-control" id="colorEditar" name="color" required>
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
// Variable para guardar el ID del garzón
let garzonId;

// Función para abrir el modal de confirmación
function abrirModalConfirmacion(id) {
    grupoId = id; // Guardar el ID del garzón
    $('#confirmModal').modal('show'); // Mostrar el modal
}

// Función para confirmar el cambio de estado
$('#confirmBtn').on('click', function() {
    if (grupoId) {
        // Redirigir a cambiar_estado.php con el ID del garzón
        window.location.href = `cambiar_estado_grupo.php?id=${grupoId}`;
    }
});

// Función para abrir el modal de edición
function editarGrupo(id, nombre, color, estado) {
    // Rellenar el formulario de edición
    $('#idEditar').val(id);
    $('#nombreEditar').val(nombre);
    $('#colorEditar').val(color);
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