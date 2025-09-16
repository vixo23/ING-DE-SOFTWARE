<?php
ob_start();
session_start();
include "../conexion.php";

$id_empresa = isset($_SESSION['idempresa']) ? (int)$_SESSION['idempresa'] : 0;
include "includes/header.php";

// --- Procesar formulario ---
if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST['id_usuario'], $_POST['nombre_persona'], $_POST['apellido'], $_POST['rut'],
          $_POST['horas'], $_POST['fecha'], $_POST['motivo'],
          $_POST['goce'], $_POST['estado'], $_POST['id_motivo'], $_POST['fecha_final'])
) {
    $id_usuario = (int)$_POST['id_usuario'];
    $nombre     = mysqli_real_escape_string($conexion, trim($_POST['nombre_persona']));
    $apellido   = mysqli_real_escape_string($conexion, trim($_POST['apellido']));
    $rut        = mysqli_real_escape_string($conexion, trim($_POST['rut']));
    $horas      = (int)$_POST['horas'];
    $fecha_ini  = mysqli_real_escape_string($conexion, $_POST['fecha']);
    $fecha_fin  = mysqli_real_escape_string($conexion, $_POST['fecha_final']);
    $motivoTxt  = mysqli_real_escape_string($conexion, trim($_POST['motivo']));
    $goce       = ($_POST['goce'] === "Si" || $_POST['goce'] == "1") ? 1 : 0;
    $estado     = (int)$_POST['estado'];
    $id_motivo  = (int)$_POST['id_motivo'];

    if ($id_usuario > 0 && $nombre !== '' && $apellido !== '' && $rut !== '' && $motivoTxt !== '' && $id_motivo > 0) {
        $sql = "INSERT INTO permisos 
                (id_usuario, id_motivo, id_empresa, fecha_ini, fecha_fin, total_horas, observaciones, goce, status, nombre, apellido, rut, adjunto, creado)
                VALUES 
                ($id_usuario, $id_motivo, $id_empresa, '$fecha_ini', '$fecha_fin', $horas, '$motivoTxt', $goce, $estado,
                '$nombre', '$apellido', '$rut', NULL, NOW())";
        $query_insert = mysqli_query($conexion, $sql);

        if ($query_insert) {
            header("Location: permisos_aprobados.php?success=1");
            exit();
        } else {
            echo '<div class="alert alert-danger">Error al registrar: '.mysqli_error($conexion).'</div>';
        }
    }
}
?>
<head>

<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.0/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</head>

<div class="card">
    <div class="card-header">
        <h2>Ingreso de Permisos</h2>
    </div>
    <div class="card-body">
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">Permiso registrado correctamente.</div>
        <?php endif; ?>

        <form action="" method="post" autocomplete="off" id="formulario">
            <div class="row">
                <!-- Usuario -->
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="id_usuario">Usuario</label>
                        <select class="form-control" name="id_usuario" id="id_usuario" required>
                            <option value="">Seleccione un usuario</option>
                            <?php
                            $usuarios = mysqli_query($conexion, "SELECT id_usuarios, nombres, apellido1, rut FROM usuarios ORDER BY id_usuarios ASC");
                            while ($row = mysqli_fetch_assoc($usuarios)) {
                                echo "<option value='".$row['id_usuarios']."' 
                                          data-nombre='".$row['nombres']."' 
                                          data-apellido='".$row['apellido1']."' 
                                          data-rut='".$row['rut']."'>
                                          ".$row['id_usuarios']." - ".$row['nombres']." ".$row['apellido1']."
                                      </option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <!-- Nombre -->
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="nombre_persona">Nombre</label>
                        <input type="text" class="form-control" name="nombre_persona" id="nombre_persona" required>
                    </div>
                </div>
                <!-- Apellido -->
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="apellido">Apellido</label>
                        <input type="text" class="form-control" name="apellido" id="apellido" required>
                    </div>
                </div>
                <!-- RUT -->
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="rut">RUT</label>
                        <input type="text" class="form-control" name="rut" id="rut" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Horas -->
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="horas">Horas</label>
                        <input type="number" class="form-control" name="horas" id="horas" min="1" required>
                    </div>
                </div>
                <!-- Fecha inicial -->
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="fecha">Fecha inicio</label>
                        <input type="date" class="form-control" name="fecha" id="fecha" required>
                    </div>
                </div>
                <!-- Fecha final -->
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="fecha_final">Fecha final</label>
                        <input type="date" class="form-control" name="fecha_final" id="fecha_final">
                    </div>
                </div>
                <!-- Motivo -->
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="id_motivo">Tipo de Motivo</label>
                        <select class="form-control" name="id_motivo" id="id_motivo" required>
                            <option value="">Seleccione un motivo</option>
                            <?php
                            $motivos = mysqli_query($conexion, "SELECT id_motivo, descripcion FROM motivos_permisos WHERE id_empresa='$id_empresa' AND status=1");
                            while ($row = mysqli_fetch_assoc($motivos)) {
                                echo "<option value='".$row['id_motivo']."'>".$row['descripcion']."</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <!-- Detalle -->
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="motivo">Detalle</label>
                        <textarea class="form-control" name="motivo" id="motivo" rows="2" required></textarea>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Goce -->
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="goce">Goce de Sueldo</label>
                        <select id="goce" class="form-control" name="goce" required>
                            <option value="1">Sí</option>
                            <option value="0">No</option>
                        </select>
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
            </div>

            <input type="submit" value="Registrar" class="btn btn-primary" id="btnAccion">
            <input type="button" value="Limpiar" class="btn btn-success" id="btnNuevo" onclick="limpiar()">
        </form>
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