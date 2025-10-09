<?php
session_start();
include "../conexion.php";
$id_empresa = $_SESSION['idempresa'];

// --- PROCESAR FORMULARIO DE INSERCIÓN ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accion']) && $_POST['accion'] == 'insertar') {
    $nombre = mysqli_real_escape_string($conexion, trim($_POST['nombre']));
    $direccion = mysqli_real_escape_string($conexion, trim($_POST['direccion']));
    $comuna = mysqli_real_escape_string($conexion, trim($_POST['comuna']));
    $estado = (int)$_POST['estado'];
    $tipo = mysqli_real_escape_string($conexion, trim($_POST['tipo']));

    if (!empty($nombre) && !empty($direccion) && !empty($comuna) && !empty($tipo)) {
        // CORRECCIÓN: Usar el nombre de columna correcto 'statusSucursal'
        $query_insert = mysqli_query($conexion, "INSERT INTO sucursal (nombre, direccion, comuna, status, empresas_id_empresas, tipo) 
                                                  VALUES ('$nombre', '$direccion', '$comuna', $estado, $id_empresa, '$tipo')");

        if ($query_insert) {
            $_SESSION['msg'] = '<div class="alert alert-success">Sucursal registrada correctamente.</div>';
        } else {
            $_SESSION['msg'] = '<div class="alert alert-danger">Error al registrar la sucursal.</div>';
        }
        header("Location: sucursal.php");
        exit;
    }
}

// --- NUEVO: PROCESAR FORMULARIO DE ACTUALIZACIÓN ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accion']) && $_POST['accion'] == 'actualizar') {
    $id_sucursal = (int)$_POST['id_sucursal'];
    $nombre = mysqli_real_escape_string($conexion, trim($_POST['nombre']));
    $direccion = mysqli_real_escape_string($conexion, trim($_POST['direccion']));
    $comuna = mysqli_real_escape_string($conexion, trim($_POST['comuna']));
    $estado = (int)$_POST['estado'];
    $tipo = mysqli_real_escape_string($conexion, trim($_POST['tipo']));

    if ($id_sucursal > 0 && !empty($nombre)) {
        // CORRECCIÓN: Usar el nombre de columna correcto 'statusSucursal'
        $query_update = "UPDATE sucursal SET nombre = ?, direccion = ?, comuna = ?, status = ?, tipo = ? WHERE id_sucursal = ? AND empresas_id_empresas = ?";
        $stmt = mysqli_prepare($conexion, $query_update);
        mysqli_stmt_bind_param($stmt, "sssisii", $nombre, $direccion, $comuna, $estado, $tipo, $id_sucursal, $id_empresa);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['msg'] = '<div class="alert alert-success">Sucursal actualizada correctamente.</div>';
        } else {
            $_SESSION['msg'] = '<div class="alert alert-danger">Error al actualizar la sucursal.</div>';
        }
        mysqli_stmt_close($stmt);
        header("Location: sucursal.php");
        exit;
    }
}

include "includes/header.php";
?>
<head>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<div class="card">
    <div class="card-header">
        <h2>Sucursal</h2>
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
                <div class="col-md-3"><div class="form-group"><label for="nombre">Nombre</label><input type="text" class="form-control" placeholder="Ingrese Nombre" name="nombre" id="nombre" required></div></div>
                <div class="col-md-3"><div class="form-group"><label for="direccion">Dirección</label><input type="text" class="form-control" placeholder="Ingrese Dirección" name="direccion" id="direccion" required></div></div>
                <div class="col-md-3"><div class="form-group"><label for="comuna">Comuna</label><input type="text" class="form-control" placeholder="Ingrese Comuna" name="comuna" id="comuna" required></div></div>
                <div class="col-md-3"><div class="form-group"><label for="estado">Estado</label><select id="estado" class="form-control" name="estado" required><option value="1">Activo</option><option value="0">Inactivo</option></select></div></div>
                <div class="col-md-3"><div class="form-group"><label for="tipo">Tipo</label><select id="tipo" class="form-control" name="tipo" required><option value="Matriz">Matriz</option><option value="Sucursal">Sucursal</option></select></div></div>
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
                <th>Dirección</th>
                <th>Comuna</th>
                <th>Tipo</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $query = mysqli_query($conexion, "SELECT * FROM sucursal WHERE empresas_id_empresas='$id_empresa' ORDER BY nombre");
            while ($data = mysqli_fetch_assoc($query)) {
                $estado = ($data['status'] == 1) ? '<span class="badge badge-success">Activo</span>' : '<span class="badge badge-danger">Inactivo</span>';
                ?>
                <tr>
                    <td><?php echo $data['id_sucursal']; ?></td>
                    <td><?php echo htmlspecialchars($data['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($data['direccion']); ?></td>
                    <td><?php echo htmlspecialchars($data['comuna']); ?></td>
                    <td><?php echo htmlspecialchars($data['tipo']); ?></td>
                    <td><?php echo $estado; ?></td>
                    <td>
                        <a href="#" onclick="editarsucursal(
                            <?php echo $data['id_sucursal']; ?>,
                            '<?php echo addslashes($data['nombre']); ?>',
                            '<?php echo addslashes($data['direccion']); ?>',
                            '<?php echo addslashes($data['comuna']); ?>',
                            '<?php echo addslashes($data['tipo']); ?>',
                            <?php echo $data['status']; ?>
                        )" class="btn btn-success btn-sm">
                            <i class='fas fa-edit'></i>
                        </a>
                        <a href="#" onclick="abrirModalConfirmacion(<?php echo $data['id_sucursal']; ?>)" class="btn btn-warning btn-sm">
                            <i class='fas fa-exchange-alt'></i> Cambiar Estado
                        </a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="confirmModalLabel">Confirmar Acción</h5><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>
            <div class="modal-body">¿Está seguro de que desea modificar el estado de esta sucursal?</div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button><button type="button" class="btn btn-primary" id="confirmBtn">Modificar Estado</button></div>
        </div>
    </div>
</div>

<div class="modal fade" id="editarModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Sucursal</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form action="" method="post">
                    <input type="hidden" name="accion" value="actualizar">
                    <input type="hidden" id="idEditar" name="id_sucursal">
                    
                    <div class="form-group"><label for="nombreEditar">Nombre</label><input type="text" class="form-control" id="nombreEditar" name="nombre" required></div>
                    <div class="form-group"><label for="direccionEditar">Dirección</label><input type="text" class="form-control" id="direccionEditar" name="direccion" required></div>
                    <div class="form-group"><label for="comunaEditar">Comuna</label><input type="text" class="form-control" id="comunaEditar" name="comuna" required></div>
                    
                    <div class="form-group">
                        <label for="tipoEditar">Tipo</label>
                        <select id="tipoEditar" class="form-control" name="tipo" required>
                            <option value="Matriz">Matriz</option>
                            <option value="Sucursal">Sucursal</option>
                        </select>
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
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

<script>
let sucursalId;

function abrirModalConfirmacion(id) {
    sucursalId = id;
    $('#confirmModal').modal('show');
}

$('#confirmBtn').on('click', function() {
    if (sucursalId) {
        window.location.href = `cambiar_estado_sucursal.php?id=${sucursalId}`;
    }
});

function editarsucursal(id, nombre, direccion, comuna, tipo, estado) {
    $('#idEditar').val(id);
    $('#nombreEditar').val(nombre);
    $('#direccionEditar').val(direccion);
    $('#comunaEditar').val(comuna);
    $('#tipoEditar').val(tipo);
    $('#estadoEditar').val(estado);
    $('#editarModal').modal('show');
}

function limpiar() {
    $('#formulario')[0].reset();
}
</script>

<?php include_once "includes/footer.php"; ?>