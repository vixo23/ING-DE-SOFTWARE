<?php
session_start();
include "../conexion.php";

$id_empresa = $_SESSION['idempresa'];

// ---------------- Procesar formulario ----------------
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!empty($_POST['descripcion'])) {
        $descripcion = mysqli_real_escape_string($conexion, $_POST['descripcion']);
        $estado = isset($_POST['status']) ? (int)$_POST['status'] : 1;
        $fechaHoy = date("Y-m-d"); // Se guarda automáticamente

        $query_insert = mysqli_query($conexion, "INSERT INTO centro_costo (descripcion, status, id_empresa, fechaCreacion) 
                                                 VALUES ('$descripcion', $estado, $id_empresa, '$fechaHoy')");

        if ($query_insert) {
            $_SESSION['msg'] = '<div class="alert alert-success alert-dismissible fade show">Centro de costo registrado correctamente.
                                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                                </div>';
        } else {
            $_SESSION['msg'] = '<div class="alert alert-danger alert-dismissible fade show">Error al registrar: ' . mysqli_error($conexion) . '
                                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                                </div>';
        }
        header("Location: centrocosto.php");
        exit;
    }
}

// ---------------- Cambiar estado ----------------
if (isset($_GET['cambiar_estado'])) {
    $id_centro = (int)$_GET['cambiar_estado'];
    $query = mysqli_query($conexion, "SELECT status FROM centro_costo WHERE id_centro=$id_centro AND id_empresa=$id_empresa");
    if ($data = mysqli_fetch_assoc($query)) {
        $nuevo_estado = ($data['status'] == 1) ? 0 : 1;
        mysqli_query($conexion, "UPDATE centro_costo SET status=$nuevo_estado WHERE id_centro=$id_centro");
    }
    header("Location: centrocosto.php");
    exit;
}

// ---------------- Consultar registros ----------------
$query = mysqli_query($conexion, "SELECT * FROM centro_costo WHERE id_empresa='$id_empresa' ORDER BY descripcion");

include "includes/header.php";
?>

<head>
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>


<div class="card">
    <div class="card-header">
        <h2>Centro de Costo</h2>
    </div>
    <div class="card-body">
        <?php
        if (isset($_SESSION['msg'])) {
            echo $_SESSION['msg'];
            unset($_SESSION['msg']);
        }
        ?>
        <form action="" method="post" autocomplete="off" id="formulario">
            <div class="row">
                <!-- Descripción -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="descripcion">Descripción Centro de Costo</label>
                        <input type="text" class="form-control" name="descripcion" id="descripcion" placeholder="Ingrese descripción" required>
                    </div>
                </div>

                <!-- Estado -->
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="status">Estado</label>
                        <select name="status" id="status" class="form-control">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </div>

                <!-- Botón -->
                <div class="col-md-3 align-self-end">
                    <button type="submit" class="btn btn-primary">Registrar</button>
                    <button type="button" class="btn btn-success" onclick="limpiar()">Limpiar</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Tabla -->
<div class="table-responsive mt-3">
    <table class="table table-hover table-striped table-bordered">
        <thead class="thead-dark">
            <tr>
                <th>ID</th>
                <th>Descripción</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($data = mysqli_fetch_assoc($query)) { ?>
                <tr>
                    <td><?php echo $data['id_centro']; ?></td>
                    <td><?php echo htmlspecialchars($data['descripcion']); ?></td>
                    <td>
                        <?php echo ($data['status'] == 1) 
                            ? '<span class="badge badge-success">Activo</span>' 
                            : '<span class="badge badge-danger">Inactivo</span>'; ?>
                    </td>
                    <td>
                        <a href="centrocosto.php?cambiar_estado=<?php echo $data['id_centro']; ?>" 
                           class="btn btn-warning btn-sm">
                            <i class="fas fa-exchange-alt"></i> Cambiar Estado
                        </a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script>
function limpiar() {
    $('#formulario')[0].reset();
}
</script>

<?php include "includes/footer.php"; ?>