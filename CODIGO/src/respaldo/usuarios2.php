<?php
session_start();
ob_start(); // Inicia el buffer de salida
include "../conexion.php";
include "includes/header.php";

// Consulta para obtener los roles activos
$query_roles = mysqli_query($conexion, "SELECT id, nombre FROM rol WHERE estado = 1");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Asegúrate de que todos los campos estén presentes
    if (!empty($_POST['nombre']) && !empty($_POST['correo']) && !empty($_POST['rol'])) {
        // Verificar si se envía un ID para actualizar
        if (isset($_POST['id'])) {
            // Actualizar el usuario
            actualizarUsuario($conexion, $_POST['id'], $_POST['nombre'], $_POST['correo'], $_POST['rol'], $_POST['pass']);
        } else {
            // Agregar nuevo usuario
            agregarUsuario($conexion, $_POST['nombre'], $_POST['correo'], $_POST['pass'], $_POST['rol']);
        }
    }
}

// Función para actualizar un usuario existente
function actualizarUsuario($conexion, $id, $nombre, $correo, $rol, $pass) {
    $pass_cifrada = !empty($pass) ? md5($pass) : null;

    $query_update = "UPDATE usuarios SET nombre='$nombre', correo='$correo', rol='$rol'" . 
        ($pass_cifrada ? ", pass='$pass_cifrada'" : "") . 
        " WHERE id=$id";
    
    if (mysqli_query($conexion, $query_update)) {
        header("Location: usuarios.php");
        exit();
    } else {
        echo "Error al actualizar el usuario: " . mysqli_error($conexion);
    }
}

// Función para agregar un nuevo usuario
function agregarUsuario($conexion, $nombre, $correo, $pass, $rol) {
    $pass_cifrada = md5($pass);
    $estado = 1;

    $query_check_email = mysqli_query($conexion, "SELECT * FROM usuarios WHERE correo = '$correo'");
    if (mysqli_num_rows($query_check_email) > 0) {
        echo "<script>alert('El correo ya está registrado.');</script>";
        return;
    }

    $insert_query = "INSERT INTO usuarios (nombre, correo, pass, rol, estado) VALUES ('$nombre', '$correo', '$pass_cifrada', '$rol', '$estado')";
    if (!mysqli_query($conexion, $insert_query)) {
        echo "Error: " . mysqli_error($conexion);
    } else {
        header("Location: usuarios.php");
        exit();
    }
}

// Para cargar los datos del usuario cuando se haga clic en el botón de editar
if (isset($_GET['id'])) {
    $id_editar = $_GET['id'];
    $query_usuario = mysqli_query($conexion, "SELECT * FROM usuarios WHERE id = $id_editar");
    $usuario = mysqli_fetch_assoc($query_usuario);
}

if (isset($_POST['id_estado']) && isset($_POST['nuevo_estado'])) {
    cambiarEstadoUsuario($conexion, $_POST['id_estado'], $_POST['nuevo_estado']);
}


// Función para cambiar el estado del usuario
function cambiarEstadoUsuario($conexion, $id, $nuevo_estado) {
$query = "UPDATE usuarios SET estado = $nuevo_estado WHERE id = $id";

if (mysqli_query($conexion, $query)) {
    header("Location: usuarios.php");
    exit();
} else {
    echo "Error al cambiar el estado: " . mysqli_error($conexion);
}
}


?>

<!-- Resto del código HTML -->

<div class="card">
    <div class="card-body">
        <form action="" method="post" autocomplete="off">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="nombre">Nombre</label>
                        <input type="text" class="form-control" placeholder="Ingrese Nombre" name="nombre" id="nombre">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="correo">Correo</label>
                        <input type="email" class="form-control" placeholder="Ingrese correo Electrónico" name="correo" id="correo">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="rol">Rol</label>
                        <select id="rol" class="form-control" name="rol" required>
                            <option value="">Seleccionar</option>
                            <?php
                            if (mysqli_num_rows($query_roles) > 0) {
                                while ($row = mysqli_fetch_assoc($query_roles)) {
                                    $selected = (isset($usuario) && $usuario['rol'] == $row['id']) ? 'selected' : '';
                                    echo '<option value="' . $row['id'] . '" ' . $selected . '>' . $row['nombre'] . '</option>';
                                }
                            } else {
                                echo '<option>No hay roles disponibles</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="pass">Contraseña</label>
                        <input type="password" class="form-control" placeholder="Ingrese Contraseña" name="pass" id="pass">
                    </div>
                </div>
            </div>
            <input type="submit" value="Registrar" class="btn btn-primary">
        </form>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-hover table-striped table-bordered mt-2" id="tbl">
        <thead class="thead-dark">
            <tr>
                <th>#</th>
                <th>Nombre</th>
                <th>Correo</th>
                <th>Rol</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Asegúrate de que solo el administrador tenga acceso a esta parte
            $query = mysqli_query($conexion, "SELECT * FROM usuarios");
            while ($data = mysqli_fetch_assoc($query)) {
                $rol_nombre = '';
                $rol_query = mysqli_query($conexion, "SELECT nombre FROM rol WHERE id = '{$data['rol']}'");
                if (mysqli_num_rows($rol_query) > 0) {
                    $rol_data = mysqli_fetch_assoc($rol_query);
                    $rol_nombre = $rol_data['nombre'];
                }

                // Cambiar el estado a texto
                $estado_texto = ($data['estado'] == 1) ? 'Activo' : 'Inactivo';
            ?>
                <tr>
                    <td><?php echo $data['id']; ?></td>
                    <td><?php echo $data['nombre']; ?></td>
                    <td><?php echo $data['correo']; ?></td>
                    <td><?php echo $rol_nombre; ?></td>
                    <td><?php echo $estado_texto; ?></td>
                    <td>
                        <a href="#" class="btn btn-success" data-toggle="modal" data-target="#editarUsuarioModal" data-id="<?php echo $data['id']; ?>" data-nombre="<?php echo $data['nombre']; ?>" data-correo="<?php echo $data['correo']; ?>" data-rol="<?php echo $data['rol']; ?>" data-pass="<?php echo $data['pass']; ?>" style="width: 100px; display: inline-flex; align-items: center; justify-content: center;">
                            <i class='fas fa-edit'></i> Editar
                        </a>
                        <a href="#" onclick="abrirModalCambioEstado(<?php echo $data['id']; ?>, <?php echo $data['estado']; ?>)" class="btn btn-warning" style="width: 150px; display: inline-flex; align-items: center; justify-content: center;">
                            <i class='fas fa-exchange-alt'></i> Cambiar Estado
                        </a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>


    </table>
</div>




<!-- Modal de Confirmación -->
<div class="modal fade" id="confirmarCambioEstadoModal" tabindex="-1" role="dialog" aria-labelledby="confirmarCambioEstadoModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmarCambioEstadoModalLabel">Confirmar Cambio de Estado</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                ¿Estás seguro de que deseas cambiar el estado de este usuario?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmarCambioEstado">Confirmar</button>
            </div>
        </div>
    </div>
</div>

<script>
    var usuarioId;
    var nuevoEstado;

    function abrirModalCambioEstado(id, estado) {
        usuarioId = id;
        nuevoEstado = estado == 1 ? 0 : 1; // Cambia el estado
        $('#confirmarCambioEstadoModal').modal('show');
    }

    document.getElementById('confirmarCambioEstado').addEventListener('click', function() {
        // Crear un formulario y enviarlo
        var form = document.createElement('form');
        form.method = 'post';
        form.action = '';

        var inputId = document.createElement('input');
        inputId.type = 'hidden';
        inputId.name = 'id_estado';
        inputId.value = usuarioId;
        form.appendChild(inputId);

        var inputEstado = document.createElement('input');
        inputEstado.type = 'hidden';
        inputEstado.name = 'nuevo_estado';
        inputEstado.value = nuevoEstado;
        form.appendChild(inputEstado);

        document.body.appendChild(form);
        form.submit();
    });
</script>



<?php include "includes/footer.php"; ?>

<!-- Modal de edición -->
<div class="modal fade" id="editarUsuarioModal" tabindex="-1" role="dialog" aria-labelledby="editarUsuarioModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editarUsuarioModalLabel">Editar Usuario</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formEditarUsuario" method="post" action="">
                    <div class="form-group">
                        <label for="modalNombre">Nombre</label>
                        <input type="text" class="form-control" id="modalNombre" name="nombre" required>
                    </div>
                    <div class="form-group">
                        <label for="modalCorreo">Correo</label>
                        <input type="email" class="form-control" id="modalCorreo" name="correo" required>
                    </div>
                    <div class="form-group">
                        <label for="modalRol">Rol</label>
                        <select id="modalRol" class="form-control" name="rol" required>
                            <option value="">Seleccionar</option>
                            <?php
                            mysqli_data_seek($query_roles, 0);
                            while ($row = mysqli_fetch_assoc($query_roles)) {
                                echo '<option value="' . $row['id'] . '">' . $row['nombre'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="modalPass">Contraseña</label>
                        <input type="password" class="form-control" id="modalPass" name="pass">
                        <small>Deje en blanco si no desea cambiar la contraseña</small>
                    </div>
                    <input type="hidden" name="id" id="modalId">
                    <button type="submit" class="btn btn-primary">Actualizar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Abrir modal y llenar campos con datos del usuario
    $('#editarUsuarioModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var id = button.data('id');
        var nombre = button.data('nombre');
        var correo = button.data('correo');
        var rol = button.data('rol');

        var modal = $(this);
        modal.find('#modalId').val(id);
        modal.find('#modalNombre').val(nombre);
        modal.find('#modalCorreo').val(correo);
        modal.find('#modalRol').val(rol);
    });
</script>