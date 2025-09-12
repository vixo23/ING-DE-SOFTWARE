<?php
// === INICIO DEL BLOQUE DE ACTUALIZACIÓN (AJAX) ===
include "../conexion.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    header('Content-Type: application/json');

    // Añadimos 'id_tipovacacion' a la validación
    if (empty($_POST['id_usuario']) || empty($_POST['dias']) || empty($_POST['fecha_inicio']) || empty($_POST['fecha_termino']) || empty($_POST['id_tipovacacion'])) {
        echo json_encode(['status' => 'error', 'message' => 'Faltan datos para procesar la solicitud.']);
        exit;
    }

    $id_usuario = intval($_POST['id_usuario']);
    $dias = intval($_POST['dias']);
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_termino = $_POST['fecha_termino'];
    $id_tipovacacion = intval($_POST['id_tipovacacion']); // Nuevo campo

    // Actualizamos la consulta para incluir 'id_tipovacacion'
    $query = "UPDATE vacaciones SET dias = ?, fecha_inicio = ?, fecha_termino = ?, id_tipovacacion = ? WHERE id_usuario = ?";
    $stmt = mysqli_prepare($conexion, $query);

    if ($stmt) {
        // Cambiamos el tipo de los parámetros a "issii" (integer, string, string, integer, integer)
        mysqli_stmt_bind_param($stmt, "issii", $dias, $fecha_inicio, $fecha_termino, $id_tipovacacion, $id_usuario);
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No se pudo actualizar el registro en la base de datos.']);
        }
        mysqli_stmt_close($stmt);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al preparar la consulta SQL.']);
    }

    mysqli_close($conexion);
    exit;
}
// === FIN DEL BLOQUE DE ACTUALIZACIÓN (AJAX) ===

session_start();
include "includes/header.php";

if (!isset($_SESSION['idempresa'])) {
    die("No se pudo obtener la empresa del usuario.");
}
$id_empresa = $_SESSION['idempresa'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asignar Vacaciones</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>

<div class="card mt-4">
    <div class="card-header">
        <h2>Vacaciones de Empleados</h2>
    </div>
    <div class="card-body">
        <form action="procesar_asignacion_vacaciones.php" method="post">
            <div class="table-responsive">
                <table class="table table-hover table-striped table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th>Seleccionar</th>
                            <th>Nombre Completo</th>
                            <th>Ficha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // 1. MODIFICAMOS LA CONSULTA para unir con 'tipo_vacaciones' y obtener la descripción
                        $query_sql = "SELECT 
                                        u.id_usuarios, 
                                        v.dias, 
                                        v.fecha_inicio, 
                                        v.fecha_termino, 
                                        CONCAT(u.nombres, ' ', u.apellido1, ' ', u.apellido2) AS nombre_completo,
                                        tv.descripcion AS tipo_vacacion,
                                        v.id_tipovacacion
                                    FROM usuarios u 
                                    INNER JOIN vacaciones v ON u.id_usuarios = v.id_usuario 
                                    INNER JOIN tipo_vacaciones tv ON v.id_tipovacacion = tv.id_tipovacacion
                                    WHERE u.id_empresa = '$id_empresa' AND u.status = 1 
                                    ORDER BY u.nombres";
                        $query = mysqli_query($conexion, $query_sql);
                        
                        if (mysqli_num_rows($query) > 0) {
                            while ($data = mysqli_fetch_assoc($query)) {
                                ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" name="empleados_seleccionados[]" value="<?php echo $data['id_usuarios']; ?>">
                                    </td>
                                    <td><?php echo $data['nombre_completo']; ?></td>
                                    <td>
                                        <a href="#" onclick="fichavacaciones(
                                            <?php echo $data['id_usuarios']; ?>,
                                            '<?php echo addslashes($data['nombre_completo']); ?>',
                                            '<?php echo addslashes($data['dias']); ?>',
                                            '<?php echo addslashes($data['fecha_inicio']); ?>',
                                            '<?php echo addslashes($data['fecha_termino']); ?>',
                                            '<?php echo addslashes($data['tipo_vacacion']); ?>',
                                            <?php echo $data['id_tipovacacion']; ?>
                                        )" class="btn btn-success">
                                            <i class='fas fa-id-card'></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo "<tr><td colspan='3' class='text-center'>No se encontraron empleados con vacaciones asignadas.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-primary"> Asignar vacaciones</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="editarModal" tabindex="-1" role="dialog" aria-labelledby="editarModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editarModalLabel">Ficha de Vacaciones</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="formVacaciones">
        <div class="modal-body">
            <p><strong>Nombre Completo:</strong> <span id="modal_nombre_completo"></span></p>
            <hr>
            <input type="hidden" id="modal_id_usuario" name="id_usuario">
            
            <div class="form-group">
                <label for="modal_tipovacaciones"><strong>Tipo de Vacaciones</strong></label>
                <select class="form-control" id="modal_tipovacaciones" name="id_tipovacacion" disabled required>
                    <?php
                        // Obtenemos todos los tipos de vacaciones para poblar el menú
                        $query_tipos = mysqli_query($conexion, "SELECT id_tipovacacion, descripcion FROM tipo_vacaciones ORDER BY descripcion");
                        while ($tipo = mysqli_fetch_assoc($query_tipos)) {
                            echo "<option value='{$tipo['id_tipovacacion']}'>{$tipo['descripcion']}</option>";
                        }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label for="modal_dias"><strong>Días Solicitados:</strong></label>
                <input type="number" class="form-control" id="modal_dias" name="dias" readonly required>
            </div>
            
            <div class="form-group">
                <label for="modal_fecha_inicio"><strong>Fecha de Inicio:</strong></label>
                <input type="date" class="form-control" id="modal_fecha_inicio" name="fecha_inicio" readonly required>
            </div>

            <div class="form-group">
                <label for="modal_fecha_termino"><strong>Fecha de Término:</strong></label>
                <input type="date" class="form-control" id="modal_fecha_termino" name="fecha_termino" readonly required>
            </div>
        </div>

        <div class="modal-footer" id="footer-vista">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-info" id="btn-editar">Editar</button>
        </div>

        <div class="modal-footer" id="footer-edicion" style="display: none;">
          <button type="button" class="btn btn-secondary" id="btn-cancelar">Cancelar</button>
          <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.0/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
<script>
var valoresOriginales = {};

// La función ahora recibe 'tipoVacacionId'
function fichavacaciones(id, nombreCompleto, dias, fechaInicio, fechaTermino, tipoVacacionDesc, tipoVacacionId) {
    valoresOriginales = { id, nombreCompleto, dias, fechaInicio, fechaTermino, tipoVacacionId };

    // Rellenamos todos los campos del formulario
    $('#modal_id_usuario').val(id);
    $('#modal_nombre_completo').text(nombreCompleto);
    $('#modal_dias').val(dias);
    $('#modal_fecha_inicio').val(fechaInicio);
    $('#modal_fecha_termino').val(fechaTermino);
    $('#modal_tipovacaciones').val(tipoVacacionId); // Seleccionamos la opción correcta en el <select>

    // Nos aseguramos de que el modal siempre inicie en MODO VISTA
    $('#formVacaciones input, #formVacaciones select').prop('disabled', true); // Deshabilitamos todo
    $('#formVacaciones input[type="number"], #formVacaciones input[type="date"]').prop('readonly', true); // Mantenemos readonly para inputs
    $('#footer-vista').show();
    $('#footer-edicion').hide();

    $('#editarModal').modal('show');
}

$(document).ready(function() {

    $('#btn-editar').on('click', function() {
        // Habilitamos todos los campos del formulario para edición
        $('#formVacaciones input, #formVacaciones select').prop('disabled', false);
        $('#formVacaciones input[type="number"], #formVacaciones input[type="date"]').prop('readonly', false);
        $('#footer-vista').hide();
        $('#footer-edicion').show();
    });

    $('#btn-cancelar').on('click', function() {
        // Restauramos todos los valores originales
        $('#modal_dias').val(valoresOriginales.dias);
        $('#modal_fecha_inicio').val(valoresOriginales.fechaInicio);
        $('#modal_fecha_termino').val(valoresOriginales.fechaTermino);
        $('#modal_tipovacaciones').val(valoresOriginales.tipoVacacionId);
        
        // Volvemos al MODO VISTA
        $('#formVacaciones input, #formVacaciones select').prop('disabled', true);
        $('#formVacaciones input[type="number"], #formVacaciones input[type="date"]').prop('readonly', true);
        $('#footer-vista').show();
        $('#footer-edicion').hide();
    });
    
    $('#formVacaciones').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();

        $.ajax({
            type: 'POST',
            url: 'vacaciones.php',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.status == 'success') {
                    alert('¡Vacaciones actualizadas correctamente!');
                    $('#editarModal').modal('hide');
                    location.reload();
                } else {
                    alert('Error al guardar: ' + response.message);
                }
            },
            error: function() {
                alert('Hubo un error de conexión con el servidor.');
            }
        });
    });
});
</script>
<?php 
include_once "includes/footer.php"; 
?>