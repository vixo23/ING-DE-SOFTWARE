<?php
session_start();
include "../conexion.php";
$id_empresa=$_SESSION['idempresa'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accion']) && $_POST['accion'] === "insertar") {
    $descripcion = mysqli_real_escape_string($conexion, trim($_POST['nombre']));
    $estado = (int)$_POST['estado'];
    $descripcion_turno = mysqli_real_escape_string($conexion, trim($_POST['descripcion_turno']));

    // Validar que la descripción no esté vacía
    if (!empty($descripcion)) {
        $query_insert = mysqli_query($conexion, "INSERT INTO tipo_contrato (descripcion, status, descripcion_turno, id_empresa) 
                                                  VALUES ('$descripcion', $estado, '$descripcion_turno', $id_empresa)");

        if ($query_insert) {
            $_SESSION['msg'] = '<div class="alert alert-success alert-dismissible fade show">Tipo de contrato registrado correctamente.
                                  <button type="button" class="close" data-dismiss="alert">&times;</button>
                                </div>';
        } else {
            $_SESSION['msg'] = '<div class="alert alert-danger alert-dismissible fade show">Error al registrar el tipo de contrato.
                                  <button type="button" class="close" data-dismiss="alert">&times;</button>
                                </div>';
        }
    } else {
        $_SESSION['msg'] = '<div class="alert alert-warning alert-dismissible fade show">El campo descripción es obligatorio.
                              <button type="button" class="close" data-dismiss="alert">&times;</button>
                            </div>';
    }
    header("Location: tipo_contrato.php");
    exit;
}

// Actualizar contrato
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accion']) && $_POST['accion'] === "actualizar") {
    $id = (int)$_POST['id'];
    $descripcion = mysqli_real_escape_string($conexion, trim($_POST['nombre']));
    $estado = (int)$_POST['estado'];
    $descripcion_turno = mysqli_real_escape_string($conexion, trim($_POST['descripcion_turno']));

    $query_update = mysqli_query($conexion, "UPDATE tipo_contrato 
                                             SET descripcion='$descripcion', status=$estado, descripcion_turno='$descripcion_turno' 
                                             WHERE id_tipocontrato=$id AND id_empresa=$id_empresa");

    if ($query_update) {
        $_SESSION['msg'] = '<div class="alert alert-success alert-dismissible fade show">Tipo contrato actualizado correctamente.
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                            </div>';
    } else {
        $_SESSION['msg'] = '<div class="alert alert-danger alert-dismissible fade show">Error al actualizar el tipo contrato.
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                            </div>';
    }
    header("Location: tipo_contrato.php");
    exit;
}
// Cambiar estado
if (isset($_GET['cambiar_estado'])) {
    $id = (int)$_GET['cambiar_estado'];
    $query = mysqli_query($conexion, "SELECT status FROM tipo_contrato WHERE id_tipocontrato=$id AND id_empresa=$id_empresa");
    if ($data = mysqli_fetch_assoc($query)) {
        $nuevo_estado = ($data['status'] == 1) ? 0 : 1;
        mysqli_query($conexion, "UPDATE tipo_contrato SET status=$nuevo_estado WHERE id_tipocontrato=$id AND id_empresa=$id_empresa");
        $_SESSION['msg'] = '<div class="alert alert-success alert-dismissible fade show">Estado modificado correctamente.
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                            </div>';
    }
    header("Location: tipo_contrato.php");
    exit;
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
        <h2>Tipo de Contrato</h2>
    </div>
    <div class="card-body">
        <?php
        if (isset($_SESSION['msg'])) {
            echo $_SESSION['msg'];
            unset($_SESSION['msg']);
        }
        ?>
        <form action="" method="post" autocomplete="off" id="formulario">
            <input type="hidden" name="accion" value="insertar">
            <div class="row">
                <!-- Nombre -->
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="nombre">Descripcion</label>
                        <input type="text" class="form-control" placeholder="Ingrese Descripcion" name="nombre" id="nombre" required>
                    </div>
                </div>

                <!-- Estado -->
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="estado">Estado</label>
                        <select id="estado" class="form-control" name="estado" required>
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </div>

                <!-- Descripción del Turno -->
                <div class="col-md-7">
                    <div class="form-group">
                        <label for="descripcion_turno">Descripción del Turno</label>
                        <textarea class="form-control" id="descripcion_turno" name="descripcion_turno" maxlength="100" placeholder="Agregue una descripción..." rows="2"></textarea>
                    </div>
                </div>
            </div>

            <!-- Botones -->
            <input type="submit" value="Registrar" class="btn btn-primary">
            <input type="button" value="Limpiar" class="btn btn-success" onclick="limpiar()">
        </form>
    </div>
</div>

<!-- Tabla -->
<div class="table-responsive mt-3">
    <table class="table table-hover table-striped table-bordered">
        <thead class="thead-dark">
            <tr>
                <th>#</th>
                <th>Nombre</th>
                <th>Descripción Turno</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $query = mysqli_query($conexion, "SELECT * FROM tipo_contrato WHERE id_empresa='$id_empresa' ORDER BY descripcion");
            while ($data = mysqli_fetch_assoc($query)) {
                $estado = ($data['status'] == 1) ? '<span class="badge badge-success">Activo</span>' : '<span class="badge badge-danger">Inactivo</span>';
                ?>
                <tr>
                    <td><?php echo $data['id_tipocontrato']; ?></td>
                    <td><?php echo htmlspecialchars($data['descripcion']); ?></td>
                    <td><?php echo htmlspecialchars($data['descripcion_turno']); ?></td>
                    <td><?php echo $estado; ?></td>
                    <td>
                        <a href="#" onclick="editartipocontrato(
                            <?php echo $data['id_tipocontrato']; ?>,
                            '<?php echo addslashes($data['descripcion']); ?>',
                            '<?php echo addslashes($data['descripcion_turno']); ?>',
                            <?php echo $data['status']; ?>
                        )" class="btn btn-success btn-sm"><i class='fas fa-edit'></i></a>

                        <a href="tipo_contrato.php?cambiar_estado=<?php echo $data['id_tipocontrato']; ?>" class="btn btn-warning btn-sm">
                            <i class='fas fa-exchange-alt'></i> Cambiar Estado
                        </a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<!-- Modal Editar -->
<div class="modal fade" id="editarModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Tipo Contrato</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form action="" method="post">
                    <input type="hidden" name="accion" value="actualizar">
                    <input type="hidden" id="idEditar" name="id">
                    <div class="form-group">
                        <label for="nombreEditar">Descripcion</label>
                        <input type="text" class="form-control" id="nombreEditar" name="nombre" required>
                    </div>
                    <div class="form-group">
                        <label for="descripcionTurnoEditar">Descripción Turno</label>
                        <textarea class="form-control" id="descripcionTurnoEditar" name="descripcion_turno" maxlength="100" rows="2"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="estadoEditar">Estado</label>
                        <select id="estadoEditar" class="form-control" name="estado" required>
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                    <input type="submit" value="Actualizar" class="btn btn-primary">
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
function limpiar() {
    $('#formulario')[0].reset();
}

function editartipocontrato(id, nombre, descripcion_turno, estado) {
    $('#idEditar').val(id);
    $('#nombreEditar').val(nombre);
    $('#descripcionTurnoEditar').val(descripcion_turno);
    $('#estadoEditar').val(estado);
    $('#editarModal').modal('show');
}
</script>

<?php include_once "includes/footer.php"; ?>
