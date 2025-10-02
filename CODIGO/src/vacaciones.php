<?php
include "../conexion.php";
session_start();

if (!isset($_SESSION['idempresa'])) {
    die("No se pudo obtener la empresa del usuario.");
}
$id_empresa = $_SESSION['idempresa'];

// === INICIO DEL BLOQUE DE PROCESAMIENTO DE SOLICITUDES ===

// 1. PROCESAR SOLICITUDES POST (Crear, Actualizar, Eliminar)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Si la solicitud es AJAX (tiene 'action')
    if (isset($_POST['action'])) {
        header('Content-Type: application/json');

        // 1.1 Lógica para ACTUALIZAR (AJAX)
        if ($_POST['action'] == 'update') {
            if (empty($_POST['id_vacaciones']) || empty($_POST['dias']) || empty($_POST['fecha_inicio']) || empty($_POST['fecha_termino']) || empty($_POST['id_tipovacacion'])) {
                echo json_encode(['status' => 'error', 'message' => 'Faltan datos para procesar la solicitud.']);
                exit;
            }
            $id_vacaciones = intval($_POST['id_vacaciones']);
            $dias = intval($_POST['dias']);
            $fecha_inicio = $_POST['fecha_inicio'];
            $fecha_termino = $_POST['fecha_termino'];
            $id_tipovacacion = intval($_POST['id_tipovacacion']);
            $query = "UPDATE vacaciones SET dias = ?, fecha_inicio = ?, fecha_termino = ?, id_tipovacacion = ? WHERE id_vacaciones = ?";
            $stmt = mysqli_prepare($conexion, $query);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "issii", $dias, $fecha_inicio, $fecha_termino, $id_tipovacacion, $id_vacaciones);
                if (mysqli_stmt_execute($stmt)) {
                    echo json_encode(['status' => 'success']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'No se pudo actualizar el registro en la base de datos.']);
                }
                mysqli_stmt_close($stmt);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Error al preparar la consulta SQL.']);
            }
        } 
        // 1.2 Lógica para ELIMINAR (AJAX)
        elseif ($_POST['action'] == 'delete') {
            if (empty($_POST['vacaciones_seleccionadas'])) {
                echo json_encode(['status' => 'error', 'message' => 'No se seleccionaron vacaciones para eliminar.']);
                exit;
            }
            $ids_a_eliminar = array_map('intval', $_POST['vacaciones_seleccionadas']);
            $placeholders = implode(',', array_fill(0, count($ids_a_eliminar), '?'));
            $query = "DELETE FROM vacaciones WHERE id_vacaciones IN ($placeholders)";
            $stmt = mysqli_prepare($conexion, $query);
            if ($stmt) {
                $tipos = str_repeat('i', count($ids_a_eliminar));
                mysqli_stmt_bind_param($stmt, $tipos, ...$ids_a_eliminar);
                if (mysqli_stmt_execute($stmt)) {
                    echo json_encode(['status' => 'success']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Error al eliminar los registros.']);
                }
                mysqli_stmt_close($stmt);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Error al preparar la consulta de eliminación.']);
            }
        }
        mysqli_close($conexion);
        exit;
    }
    // 1.3 Si es un POST normal (CREAR nueva vacación desde el formulario)
    else {
        $id_usuario = intval($_POST['id_usuario']);
        $id_tipovacacion = intval($_POST['id_tipovacacion']);
        $dias = intval($_POST['dias']);
        $fecha_inicio = $_POST['fecha_inicio'];
        $fecha_termino = $_POST['fecha_termino'];
        $id_autoriza = isset($_POST['id_autoriza']) ? intval($_POST['id_autoriza']) : 0;
        
        // Valores adicionales para la inserción
        $anio = date('Y');
        $periodo = 1;
        $dia_usado = 0;
        $dia_restante = $dias;
        $aprobacion = 1;
        $status = 1;

        $query = "INSERT INTO vacaciones (id_tipovacacion, anio, fecha_inicio, fecha_termino, dias, periodo, dia_usado, dia_restante, id_usuario, aprobacion, status, id_autoriza) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conexion, $query);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "iisssiiiiiii", $id_tipovacacion, $anio, $fecha_inicio, $fecha_termino, $dias, $periodo, $dia_usado, $dia_restante, $id_usuario, $aprobacion, $status, $id_autoriza);
            if (mysqli_stmt_execute($stmt)) {
                // Redirigir para evitar reenvío del formulario
                header("Location: vacaciones.php?status=success");
                exit;
            } else {
                die("Error al guardar la vacación: " . mysqli_error($conexion));
            }
            mysqli_stmt_close($stmt);
        } else {
            die("Error al preparar la consulta de inserción: " . mysqli_error($conexion));
        }
        mysqli_close($conexion);
    }
}

// 2. PROCESAR SOLICITUD GET PARA IMPRESIÓN WEB (INDIVIDUAL)
if (isset($_GET['print']) && isset($_GET['id'])) {
    $id_vacaciones = intval($_GET['id']);

    $query_sql = "SELECT 
                    u.rut,
                    CONCAT(u.nombres, ' ', u.apellido1, ' ', u.apellido2) AS nombre_completo,
                    v.dias, 
                    v.fecha_inicio, 
                    v.fecha_termino,
                    tv.descripcion AS tipo_vacacion,
                    e.RazonSocial AS autorizador
                FROM vacaciones v
                INNER JOIN usuarios u ON v.id_usuario = u.id_usuarios
                INNER JOIN tipo_vacaciones tv ON v.id_tipovacacion = tv.id_tipovacacion
                LEFT JOIN empresas e ON v.id_autoriza = e.id_empresas
                WHERE v.id_vacaciones = ? AND u.id_empresa = ?";

    $stmt = mysqli_prepare($conexion, $query_sql);
    mysqli_stmt_bind_param($stmt, "ii", $id_vacaciones, $id_empresa);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);

    if (!$data) {
        die("Ficha de vacaciones no encontrada.");
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conexion);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ficha de Vacaciones - <?php echo $data['nombre_completo']; ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; color: #333; }
        .container { width: 80%; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
        h1 { text-align: center; color: #007bff; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 15px; }
        .info-row span { font-weight: bold; }
        .info-item { width: 48%; }
        @media print {
            body { margin: 0; }
            .container { width: 100%; border: none; box-shadow: none; padding: 0; }
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Ficha de Vacaciones</h1>
    <div class="info-row">
        <div class="info-item"><span>Nombre Completo:</span> <?php echo $data['nombre_completo']; ?></div>
        <div class="info-item"><span>RUT:</span> <?php echo $data['rut']; ?></div>
    </div>
    <div class="info-row">
        <div class="info-item"><span>Tipo de Vacación:</span> <?php echo $data['tipo_vacacion']; ?></div>
        <div class="info-item"><span>Días:</span> <?php echo $data['dias']; ?></div>
    </div>
    <div class="info-row">
        <div class="info-item"><span>Fecha de Inicio:</span> <?php echo date('d-m-Y', strtotime($data['fecha_inicio'])); ?></div>
        <div class="info-item"><span>Fecha de Término:</span> <?php echo date('d-m-Y', strtotime($data['fecha_termino'])); ?></div>
    </div>
    <div class="info-row">
        <div class="info-item"><span>Autorizado por:</span> <?php echo $data['autorizador'] ? $data['autorizador'] : 'N/A'; ?></div>
    </div>
</div>
<script>
    window.onload = function() {
        window.print();
        window.onafterprint = function() { window.close(); };
    };
</script>
</body>
</html>
<?php
    exit; // Termina la ejecución para no mostrar el resto de la página
}
// === FIN DEL BLOQUE DE PROCESAMIENTO ===

include "includes/header.php";

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vacaciones Asignadas</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>

<div class="card mt-4">
    <div class="card-header">
        <h2>Asignar Nuevas Vacaciones</h2>
    </div>
    <div class="card-body">
        <form action="vacaciones.php" method="post" autocomplete="off">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="id_usuario">Seleccionar Empleado</label>
                        <select id="id_usuario" class="form-control" name="id_usuario" required>
                            <option value="">-- Seleccione un empleado --</option>
                            <?php
                            $query_empleados = mysqli_query($conexion, "SELECT id_usuarios, CONCAT(nombres, ' ', apellido1, ' ', apellido2) AS nombre_completo FROM usuarios WHERE id_empresa = '$id_empresa' AND status = 1 ORDER BY nombres");
                            while ($empleado = mysqli_fetch_assoc($query_empleados)) {
                                echo "<option value='{$empleado['id_usuarios']}'>{$empleado['nombre_completo']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="id_tipovacacion">Tipo de Vacación</label>
                        <select id="id_tipovacacion" class="form-control" name="id_tipovacacion" required>
                            <?php
                            $query_tipos = mysqli_query($conexion, "SELECT id_tipovacacion, descripcion FROM tipo_vacaciones ORDER BY descripcion");
                            while ($tipo = mysqli_fetch_assoc($query_tipos)) {
                                echo "<option value='{$tipo['id_tipovacacion']}'>{$tipo['descripcion']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="dias">Días</label>
                        <input type="number" class="form-control" placeholder="Ingrese días" name="dias" id="dias" required>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="fecha_inicio">Fecha de Inicio</label>
                        <input type="date" class="form-control" name="fecha_inicio" id="fecha_inicio" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="fecha_termino">Fecha de Término</label>
                        <input type="date" class="form-control" name="fecha_termino" id="fecha_termino" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="id_autoriza">Autorizado por</label>
                        <select id="id_autoriza" class="form-control" name="id_autoriza">
                            <option value="">-- Seleccione --</option>
                            <?php
                            $query_autorizadores = mysqli_query($conexion, "SELECT RazonSocial FROM empresas WHERE status = 1 ORDER BY RazonSocial");
                            while ($autorizador = mysqli_fetch_assoc($query_autorizadores)) {
                                echo "<option value='{$autorizador['RazonSocial']}'>{$autorizador['RazonSocial']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>
            <input type="submit" value="Registrar" class="btn btn-primary">
            <input type="button" value="Limpiar" class="btn btn-success" onclick="this.form.reset();">
        </form>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <h2>Vacaciones Asignadas</h2>
    </div>
    <div class="card-body">
        <div class="mb-3 mt-3">
            <button type="button" class="btn btn-danger" id="btnEliminar">Eliminar Vacaciones Seleccionadas</button>
            <button type="button" class="btn btn-success" id="btnImprimirPDFs" disabled>
                <i class="fa-solid fa-print"></i> Imprimir Ficha(s)
            </button>
        </div>
        <form id="formEliminarVacaciones" onsubmit="return false;">
            <input type="hidden" name="action" value="delete">
            <div class="table-responsive">
                <table class="table table-hover table-striped table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th>Seleccionar</th>
                            <th>Nombre Completo</th>
                            <th>Tipo Vacación</th>
                            <th>Días</th>
                            <th>Fecha Inicio</th>
                            <th>Fecha Término</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query_sql = "SELECT 
                                        v.id_vacaciones,
                                        u.id_usuarios,
                                        u.rut,
                                        CONCAT(u.nombres, ' ', u.apellido1, ' ', u.apellido2) AS nombre_completo,
                                        v.dias, 
                                        v.fecha_inicio, 
                                        v.fecha_termino, 
                                        tv.descripcion AS tipo_vacacion,
                                        v.id_tipovacacion,
                                        e.RazonSocial AS autorizador,
                                        e2.RazonSocial AS RazonSocial
                                    FROM vacaciones v
                                    INNER JOIN usuarios u ON v.id_usuario = u.id_usuarios
                                    INNER JOIN tipo_vacaciones tv ON v.id_tipovacacion = tv.id_tipovacacion
                                    INNER JOIN empresas e ON u.id_empresa = e.id_empresas
                                    LEFT JOIN usuarios au ON v.id_autoriza = au.id_usuarios
                                    LEFT JOIN empresas e2 ON v.id_autoriza = e2.id_empresas
                                    WHERE u.id_empresa = '$id_empresa' AND u.status = 1 
                                    ORDER BY u.nombres";
                        $query = mysqli_query($conexion, $query_sql);
                        
                        if (mysqli_num_rows($query) > 0) {
                            while ($data = mysqli_fetch_assoc($query)) {
                                // Preparamos los datos para el JSON
                                $vacacion_data = [
                                    'id_vacaciones' => $data['id_vacaciones'],
                                    'nombre_completo' => $data['nombre_completo'],
                                    'rut' => $data['rut'],
                                    'tipo_vacacion' => $data['tipo_vacacion'],
                                    'dias' => $data['dias'],
                                    'fecha_inicio' => $data['fecha_inicio'],
                                    'fecha_termino' => $data['fecha_termino'],
                                    'autorizador' => $data['autorizador'] ? $data['autorizador'] : 'N/A'
                                ];
                                $vacacion_json = htmlspecialchars(json_encode($vacacion_data), ENT_QUOTES, 'UTF-8');
                                ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" name="vacaciones_seleccionadas[]" value="<?php echo $data['id_vacaciones']; ?>" 
                                            onclick='seleccionarFicha(this, <?php echo $vacacion_json; ?>)'>
                                    </td>
                                    <td><?php echo $data['nombre_completo']; ?></td>
                                    <td><?php echo $data['tipo_vacacion']; ?></td>
                                    <td><?php echo $data['dias']; ?></td>
                                    <td><?php echo $data['fecha_inicio']; ?></td>
                                    <td><?php echo $data['fecha_termino']; ?></td>
                                    <td>
                                        <a href="#" onclick="fichavacaciones(
                                            <?php echo $data['id_vacaciones']; ?>,
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
                            echo "<tr><td colspan='7' class='text-center'>No se encontraron empleados con vacaciones asignadas.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="editarModal" tabindex="-1" role="dialog" aria-labelledby="editarModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editarModalLabel">Editar Ficha de Vacaciones</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formVacaciones" onsubmit="return false;">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" id="modal_id_vacaciones" name="id_vacaciones">
                    <div class="form-group">
                        <label>Empleado</label>
                        <p id="modal_nombre_completo" class="form-control-plaintext"></p>
                    </div>
                    <div class="form-group">
                        <label for="modal_dias">Días</label>
                        <input type="number" class="form-control" id="modal_dias" name="dias" required>
                    </div>
                    <div class="form-group">
                        <label for="modal_fecha_inicio">Fecha de Inicio</label>
                        <input type="date" class="form-control" id="modal_fecha_inicio" name="fecha_inicio" required>
                    </div>
                    <div class="form-group">
                        <label for="modal_fecha_termino">Fecha de Término</label>
                        <input type="date" class="form-control" id="modal_fecha_termino" name="fecha_termino" required>
                    </div>
                    <div class="form-group">
                        <label for="modal_tipovacaciones">Tipo de Vacación</label>
                        <select id="modal_tipovacaciones" class="form-control" name="id_tipovacacion" required>
                            <?php
                            $query_tipos = mysqli_query($conexion, "SELECT id_tipovacacion, descripcion FROM tipo_vacaciones ORDER BY descripcion");
                            while ($tipo = mysqli_fetch_assoc($query_tipos)) {
                                echo "<option value='{$tipo['id_tipovacacion']}'>{$tipo['descripcion']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer" id="footer-edicion">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardar">Guardar Cambios</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.0/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<script>
    // Array para guardar los registros de vacaciones seleccionados para imprimir
    let vacacionesSeleccionadas = [];

    function fichavacaciones(id, nombre, dias, fechaInicio, fechaTermino, tipoVacacion, tipoVacacionId) {
        $('#modal_id_vacaciones').val(id);
        $('#modal_nombre_completo').text(nombre);
        $('#modal_dias').val(dias);
        $('#modal_fecha_inicio').val(fechaInicio);
        $('#modal_fecha_termino').val(fechaTermino);
        $('#modal_tipovacaciones').val(tipoVacacionId);
        $('#editarModal').modal('show');
    }

    /**
     * Adaptación de la función de selección para agregar/quitar registros del array.
     */
    function seleccionarFicha(checkbox, datosVacacion) {
        const fila = checkbox.closest('tr');
        const id = datosVacacion.id_vacaciones;
        const indice = vacacionesSeleccionadas.findIndex(v => v.id_vacaciones === id);

        if (checkbox.checked) {
            // Si el checkbox está marcado y no está en el array, lo agregamos
            if (indice === -1) {
                vacacionesSeleccionadas.push(datosVacacion);
                fila.classList.add('table-info');
            }
        } else {
            // Si el checkbox no está marcado y existe en el array, lo quitamos
            if (indice > -1) {
                vacacionesSeleccionadas.splice(indice, 1);
                fila.classList.remove('table-info');
            }
        }
        actualizarBotonImprimirPDF();
    }
    
    /**
     * Función para actualizar el estado del botón de impresión múltiple.
     */
    function actualizarBotonImprimirPDF() {
        const btnImprimir = document.getElementById('btnImprimirPDFs');
        const cantidad = vacacionesSeleccionadas.length;

        if (cantidad === 0) {
            btnImprimir.disabled = true;
            btnImprimir.innerHTML = '<i class="fa-solid fa-print"></i> Imprimir Ficha(s)';
        } else if (cantidad === 1) {
            btnImprimir.disabled = false;
            btnImprimir.innerHTML = '<i class="fa-solid fa-print"></i> Imprimir Ficha';
        } else {
            btnImprimir.disabled = false;
            btnImprimir.innerHTML = `<i class="fa-solid fa-print"></i> Imprimir Fichas (${cantidad})`;
        }
    }

    /**
     * Función para generar un único PDF con múltiples fichas de vacaciones.
     */
    function imprimirPDFs() {
        if (vacacionesSeleccionadas.length === 0) {
            alert("Por favor, seleccione al menos una ficha para imprimir.");
            return;
        }

        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        
        vacacionesSeleccionadas.forEach((vacacion, index) => {
            if (index > 0) {
                doc.addPage();
            }

            doc.setFontSize(20);
            doc.text("Ficha de Vacaciones", 105, 20, null, null, "center");
            doc.setFontSize(12);

            let y = 40;
            doc.text(`Nombre Completo: ${vacacion.nombre_completo}`, 20, y); y += 10;
            doc.text(`RUT: ${vacacion.rut}`, 20, y); y += 10;
            doc.text(`Tipo de Vacación: ${vacacion.tipo_vacacion}`, 20, y); y += 10;
            doc.text(`Días: ${vacacion.dias}`, 20, y); y += 10;
            doc.text(`Fecha de Inicio: ${vacacion.fecha_inicio}`, 20, y); y += 10;
            doc.text(`Fecha de Término: ${vacacion.fecha_termino}`, 20, y); y += 10;
            doc.text(`Autorizado por: ${vacacion.autorizador ? vacacion.autorizador : ''}`, 20, y);
        });

        doc.save('Fichas-Vacaciones.pdf');
    }

    $('#btnGuardar').on('click', function() {
        var formData = $('#formVacaciones').serialize();
        $.ajax({
            type: 'POST',
            url: 'vacaciones.php',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
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

    $('#selectAll').on('click', function() {
        const checkboxes = $('input[type="checkbox"][name="vacaciones_seleccionadas[]"]');
        checkboxes.prop('checked', this.checked);
        vacacionesSeleccionadas = [];
        if (this.checked) {
            checkboxes.each(function() {
                const fila = $(this).closest('tr');
                const data = JSON.parse(decodeURIComponent(fila.find('input[type="checkbox"]').attr('onclick').split(',')[1].replace(')', '').trim()));
                vacacionesSeleccionadas.push(data);
                fila.addClass('table-info');
            });
        } else {
            $('input[type="checkbox"]').closest('tr').removeClass('table-info');
        }
        actualizarBotonImprimirPDF();
    });
    

    $('#btnEliminar').on('click', function() {
        if ($('input[type="checkbox"][name="vacaciones_seleccionadas[]"]:checked').length === 0) {
            alert('Por favor, seleccione al menos una vacación para eliminar.');
            return;
        }
        if (confirm('¿Está seguro de que desea eliminar las vacaciones seleccionadas?')) {
            var formData = $('#formEliminarVacaciones').serialize();
            $.ajax({
                type: 'POST',
                url: 'vacaciones.php',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        alert('¡Vacaciones eliminadas correctamente!');
                        location.reload();
                    } else {
                        alert('Error al eliminar: ' + response.message);
                    }
                },
                error: function() {
                    alert('Hubo un error de conexión con el servidor.');
                }
            });
        }
    });

    // Nueva función para imprimir individualmente desde el modal
    $('#btnImprimir').on('click', function() {
        var idVacaciones = $('#modal_id_vacaciones').val();
        if (idVacaciones) {
            window.open('vacaciones.php?print=true&id=' + idVacaciones, '_blank');
        } else {
            alert('No se pudo obtener el ID de la vacación para imprimir.');
        }
    });

    // Evento para el nuevo botón de impresión múltiple
    $('#btnImprimirPDFs').on('click', function() {
        imprimirPDFs();
    });
</script>

<?php include_once "includes/footer.php"; ?>