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
    // Corregido: Las variables $_POST ahora coinciden con los nombres de los campos del formulario.
    $horas      = (int)$_POST['horas'];
    $fecha_ini  = mysqli_real_escape_string($conexion, $_POST['fecha']);
    $fecha_fin  = mysqli_real_escape_string($conexion, $_POST['fecha_final']);
    $motivoTxt  = mysqli_real_escape_string($conexion, trim($_POST['motivo']));
    $goce       = ($_POST['goce'] === "Si" || $_POST['goce'] == "1") ? 1 : 0;
    $estado     = (int)$_POST['estado'];
    $id_motivo  = (int)$_POST['id_motivo'];

    if ($id_usuario > 0  && $motivoTxt !== '' && $id_motivo > 0) {
        $sql = "INSERT INTO permisos 
                (id_usuario, id_motivo, id_empresa, fecha_ini, fecha_fin, total_horas, observaciones, goce, status,  creado)
                VALUES 
                ($id_usuario, $id_motivo, $id_empresa, '$fecha_ini', '$fecha_fin', $horas, '$motivoTxt', $goce, $estado, NOW())";
        $query_insert = mysqli_query($conexion, $sql);

        if ($query_insert) {
            header("Location: ingreso_permisos.php?success=1");
            exit();
        } else {
            echo '<div class="alert alert-danger">Error al registrar: '.mysqli_error($conexion).'</div>';
        }
    }
}
?>
<head>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@x.x.x/dist/select2-bootstrap4.min.css">
    <style>
        /* Pequeños ajustes para un look más limpio */
        body { background-color: #f8f9fa; }
        .card { border: none; }
    </style>
</head>

<div class="container py-4">
    <div class="card ">
        <div class="card-header bg-light">
            <h2 class="mb-0 h4"><i class="fas fa-file-alt mr-2"></i>Ingreso de Permisos de Personal</h2>
        </div>
        <div class="card-body">
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle mr-2"></i>Permiso registrado correctamente.</div>
            <?php endif; ?>

            <form action="" method="post" autocomplete="off" id="formulario">
                
                <h5><i class="fas fa-user-circle text-primary mr-2"></i>Datos del Empleado</h5>
                <hr class="mt-2 mb-4">
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="id_usuario">Seleccione el Usuario</label>
                        <select class="form-control" name="id_usuario" id="id_usuario" required>
                            <option></option> <?php
                            $usuarios = mysqli_query($conexion, "SELECT id_usuarios, nombres, apellido1, rut FROM usuarios ORDER BY nombres ASC");
                            while ($row = mysqli_fetch_assoc($usuarios)) {
                                echo "<option value='".$row['id_usuarios']."' data-nombre='".$row['nombres']."' data-apellido='".$row['apellido1']."' data-rut='".$row['rut']."'>".htmlspecialchars($row['nombres']." ".$row['apellido1'])."</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="rut">RUT</label>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-id-card"></i></span></div>
                            <input type="text" class="form-control" name="rut" id="rut" required readonly>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="nombre_persona">Nombre</label>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-user"></i></span></div>
                            <input type="text" class="form-control" name="nombre_persona" id="nombre_persona" required readonly>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="apellido">Apellido</label>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-user"></i></span></div>
                            <input type="text" class="form-control" name="apellido" id="apellido" required readonly>
                        </div>
                    </div>
                </div>

                <h5 class="mt-4"><i class="fas fa-info-circle text-primary mr-2"></i>Detalles del Permiso</h5>
                <hr class="mt-2 mb-4">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="id_motivo">Tipo de Motivo</label>
                        <select class="form-control" name="id_motivo" id="id_motivo" required>
                            <option></option> <?php
                            $motivos = mysqli_query($conexion, "SELECT id_motivo, descripcion FROM motivos_permisos WHERE id_empresa='$id_empresa' AND status=1");
                            while ($row = mysqli_fetch_assoc($motivos)) {
                                echo "<option value='".$row['id_motivo']."'>".htmlspecialchars($row['descripcion'])."</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="horas">Total de Horas</label>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-clock"></i></span></div>
                            <input type="number" class="form-control" name="horas" id="horas" min="1" required>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="fecha">Fecha de Inicio</label>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-calendar-alt"></i></span></div>
                            <input type="date" class="form-control" name="fecha" id="fecha" required>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="fecha_final">Fecha Final (Opcional)</label>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-calendar-check"></i></span></div>
                            <input type="date" class="form-control" name="fecha_final" id="fecha_final">
                        </div>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label for="motivo">Detalle / Observaciones</label>
                        <textarea class="form-control" name="motivo" id="motivo" rows="3" required></textarea>
                    </div>
                </div>

                 <h5 class="mt-4"><i class="fas fa-cog text-primary mr-2"></i>Configuración</h5>
                <hr class="mt-2 mb-4">
                
                <div class="row">
                     <div class="col-md-6 mb-3">
                        <label for="goce">¿Con Goce de Sueldo?</label>
                        <select id="goce" class="form-control" name="goce" required>
                            <option value="1">Sí</option>
                            <option value="0">No</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="estado">Estado del Permiso</label>
                        <select id="estado" class="form-control" name="estado" required>
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </div>
                
                <div class="text-right mt-4">
                    <button type="button" class="btn btn-secondary" id="btnNuevo" onclick="limpiar()">
                        <i class="fas fa-eraser mr-2"></i>Limpiar
                    </button>
                    <button type="submit" class="btn btn-primary" id="btnAccion">
                        <i class="fas fa-check mr-2"></i>Registrar Permiso
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.0/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
// La lógica se mantiene, solo se adapta para Select2
$(document).ready(function() {
    

    // Lógica para llenar automáticamente los campos (sin cambios)
    $('#id_usuario').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        
        if ($(this).val()) {
            $('#nombre_persona').val(selectedOption.data('nombre'));
            $('#apellido').val(selectedOption.data('apellido'));
            $('#rut').val(selectedOption.data('rut'));
        } else {
            $('#nombre_persona, #apellido, #rut').val('');
        }
    });
});

// Función para limpiar el formulario, adaptada para Select2
function limpiar() {
    $('#formulario')[0].reset();
    // Resetea los campos de Select2 a su placeholder
    $('#id_usuario, #id_motivo').val(null).trigger('change');
}
</script>

<?php include_once "includes/footer.php"; ?>