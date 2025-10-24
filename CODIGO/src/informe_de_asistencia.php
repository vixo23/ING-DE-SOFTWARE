<?php
session_start();
if (!isset($_SESSION['idempresa'])) {
    header('Location: login.php');
    exit();
}

date_default_timezone_set('America/Santiago');
include "../conexion.php";

$id_empresa = (int)$_SESSION['idempresa'];

/* ============================
 * Helper: normaliza hora y crea DateTime
 * ============================ */
function dt_from_time($timeStr) {
    if (!$timeStr) return null;
    // Normaliza "HH:MM" → "HH:MM:00"
    if (preg_match('/^\d{2}:\d{2}$/', $timeStr)) {
        $timeStr .= ':00';
    }
    // Normaliza "HH:MM:S" → "HH:MM:SS"
    if (preg_match('/^\d{2}:\d{2}:\d{1}$/', $timeStr)) {
        $timeStr .= '0';
    }
    return DateTime::createFromFormat('Y-m-d H:i:s', '1970-01-01 ' . $timeStr);
}

/* ============================
 * 1) EXPORTACIÓN CSV (POST)
 * ============================ */
if (isset($_POST['exportar_csv']) && !empty($_POST['marcas']) && !empty($_POST['nombre_archivo'])) {
    $marcas_seleccionadas = array_map('intval', $_POST['marcas']);
    $nombre_archivo = $_POST['nombre_archivo'];
    if (substr($nombre_archivo, -4) !== ".csv") {
        $nombre_archivo .= ".csv";
    }

    $placeholders = implode(',', array_fill(0, count($marcas_seleccionadas), '?'));
    $sql_export = "
        SELECT m.fecha, m.hora, u.rut, u.digitoRut, u.nombres, u.apellido1, u.apellido2
        FROM marcas m
        INNER JOIN usuarios u ON m.id_usuario = u.id_usuarios
        WHERE m.id_marcas IN ($placeholders) AND u.id_empresa = ?
        ORDER BY u.nombres, m.fecha, m.hora
    ";

    $stmt_export = $conexion->prepare($sql_export);
    if ($stmt_export === false) {
        header('Content-Type: text/plain; charset=utf-8');
        echo "Error preparando exportación: " . $conexion->error;
        exit;
    }

    $tipos = str_repeat('i', count($marcas_seleccionadas)) . 'i';
    $marcas_seleccionadas[] = $id_empresa;
    $stmt_export->bind_param($tipos, ...$marcas_seleccionadas);
    $stmt_export->execute();
    $resultado_export = $stmt_export->get_result();

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $nombre_archivo . '"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['Fecha', 'Hora', 'RUT', 'DV', 'Nombre', 'Apellido Paterno', 'Apellido Materno']);

    while ($fila = $resultado_export->fetch_assoc()) {
        fputcsv($output, $fila);
    }
    
    fclose($output);
    $stmt_export->close();
    exit();
}

/* ============================
 * 2) FILTROS (GET) + Informe Diario
 * ============================ */
$action = $_GET['action'] ?? '';
$nombre_usuario = $_GET['nombre_usuario'] ?? '';

if ($action === 'informe_diario') {
    // Fuerza siempre HOY desde el servidor (ignora cualquier parámetro de fecha en GET)
    $fecha_desde = date('Y-m-d');
    $fecha_hasta = date('Y-m-d');
} else {
    $fecha_desde = $_GET['fecha_desde'] ?? date('Y-m-01');
    $fecha_hasta = $_GET['fecha_hasta'] ?? date('Y-m-d');
}

/* Texto bonito para el cintillo cuando es diario */
$legibleHoy = '';
if ($action === 'informe_diario') {
    if (class_exists('IntlDateFormatter')) {
        $fmt = new IntlDateFormatter('es_CL', IntlDateFormatter::FULL, IntlDateFormatter::NONE, 'America/Santiago', IntlDateFormatter::GREGORIAN, "EEEE d 'de' MMMM 'de' y");
        $legibleHoy = ucfirst($fmt->format(new DateTime('now', new DateTimeZone('America/Santiago'))));
    } else {
        $dias  = ['domingo','lunes','martes','miércoles','jueves','viernes','sábado'];
        $meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
        $d = (int)date('w'); $dd = (int)date('d'); $mm = (int)date('m'); $yyyy = date('Y');
        $legibleHoy = ucfirst("{$dias[$d]} $dd de {$meses[$mm-1]} de $yyyy");
    }
}

include "includes/header.php";

/* ============================
 * 3) CONSULTA MARCAS + TURNO
 *    - Lista todas las marcas del rango
 *    - Incluye turno ASIGNADO al usuario (join directo)
 *    - En PHP marcamos Entrada/Salida/Intermedia
 * ============================ */
$condiciones = ["u.id_empresa = ?"];
$tipos_params = "i";
$params = [$id_empresa];

if (!empty($fecha_desde)) {
    $condiciones[] = "m.fecha >= ?";
    $tipos_params .= "s";
    $params[] = $fecha_desde;
}
if (!empty($fecha_hasta)) {
    $condiciones[] = "m.fecha <= ?";
    $tipos_params .= "s";
    $params[] = $fecha_hasta;
}
if (!empty($nombre_usuario)) {
    $condiciones[] = "u.nombres LIKE ?";
    $tipos_params .= "s";
    $params[] = "%$nombre_usuario%";
}

$where = implode(" AND ", $condiciones);

$sql = "
    SELECT 
        m.id_marcas, m.fecha, m.hora, m.tipoMarca,
        u.id_usuarios, u.rut, u.digitoRut, u.nombres, u.apellido1,
        -- Turno ASIGNADO (no depende del día de semana)
        tu.nombreTurno,
        tu.horaEntrada AS turno_entrada,
        tu.horaSalida  AS turno_salida
    FROM marcas m
    INNER JOIN usuarios u 
        ON m.id_usuario = u.id_usuarios
    LEFT JOIN turnos tu 
        ON tu.id_turnos  = u.turnos_id_turnos
       AND tu.id_empresa = u.id_empresa
    WHERE $where
    ORDER BY u.nombres, m.fecha, m.hora
";

$stmt = $conexion->prepare($sql);
if ($stmt === false) {
    echo '<div class="container mt-4"><div class="alert alert-danger">
            Error en la consulta: '.htmlspecialchars($conexion->error).'
          </div></div>';
    include "includes/footer.php";
    exit;
}
$stmt->bind_param($tipos_params, ...$params);
$stmt->execute();
$rs = $stmt->get_result();

/* Agrupar por (usuario, fecha) para clasificar Entrada/Salida/Intermedia */
$grupos = [];
while ($row = $rs->fetch_assoc()) {
    $key = $row['id_usuarios'].'|'.$row['fecha'];
    if (!isset($grupos[$key])) $grupos[$key] = [];
    $grupos[$key][] = $row; // ya ordenado por hora
}

/* Clasificar marcas dentro de cada día por usuario */
$marcasCalculadas = [];
foreach ($grupos as $key => $lista) {
    $count = count($lista);
    foreach ($lista as $i => $m) {
        if ($count === 1) {
            $m['tipoCalculado'] = 'Entrada';
        } else {
            if ($i === 0) {
                $m['tipoCalculado'] = 'Entrada';
            } elseif ($i === $count-1) {
                $m['tipoCalculado'] = 'Salida';
            } else {
                $m['tipoCalculado'] = 'Intermedia';
            }
        }
        $marcasCalculadas[] = $m;
    }
}

/* Reordenar por el mismo criterio para presentar (Nombre→Fecha→Hora) */
usort($marcasCalculadas, function($a,$b){
    $cmp = strcmp($a['nombres'], $b['nombres']);
    if ($cmp !== 0) return $cmp;
    $cmp = strcmp($a['fecha'], $b['fecha']);
    if ($cmp !== 0) return $cmp;
    return strcmp($a['hora'], $b['hora']);
});

/* Flag impresión auto si es diario (opcional, no auto-imprime por defecto) */
$AUTO_PRINT_TODAY = false;

?>

<!-- Filtros -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-light">
        <h4 class="mb-0"><i class="fas fa-filter mr-2 text-primary"></i>Informe de Marcas</h4>
    </div>
    <div class="card-body">
        <form action="" method="get" id="formFiltros">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="nombre_usuario">Nombre:</label>
                        <input type="text" class="form-control" id="nombre_usuario" name="nombre_usuario" value="<?= htmlspecialchars($nombre_usuario) ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="fecha_desde">Desde:</label>
                        <input type="date" class="form-control" id="fecha_desde" name="fecha_desde" value="<?= htmlspecialchars($fecha_desde) ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="fecha_hasta">Hasta:</label>
                        <input type="date" class="form-control" id="fecha_hasta" name="fecha_hasta" value="<?= htmlspecialchars($fecha_hasta) ?>">
                    </div>
                </div>
                <div class="col-md-2 align-self-end d-flex">
                    <button type="submit" class="btn btn-primary btn-block mr-2">Filtrar</button>
                    <button type="button" class="btn btn-success btn-block" id="btnInformeDiario">Informe Diario</button>
                </div>
            </div>
        </form>

        <?php if ($action === 'informe_diario' && $legibleHoy): ?>
            <div class="alert alert-info d-flex align-items-center mt-3 mb-0" role="alert">
                <i class="fas fa-calendar-day mr-2"></i>
                <div><strong>Marcas del día</strong> — <?= htmlspecialchars($legibleHoy) ?></div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Resultados -->
<div class="card shadow-sm mt-4">
    <div class="card-body">
        <form action="" method="post">
            <div class="table-responsive">
                <table class="table table-hover table-striped table-bordered" id="tabla_marcas">
                    <thead class="thead-dark">
                        <tr>
                            <th><input type="checkbox" id="seleccionarTodos"></th>
                            <th>ID Usuario</th>
                            <th>RUT</th>
                            <th>Nombre</th>
                            <th>Turno</th>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Tipo (calculado)</th>
                            <th>Atraso</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($marcasCalculadas)): ?>
                            <?php foreach ($marcasCalculadas as $m): 
                                $rut_full = htmlspecialchars(($m['rut'] ?? '').(($m['digitoRut'] ?? '') !== '' ? '-'.$m['digitoRut'] : ''));
                                $nombre   = htmlspecialchars(trim(($m['nombres'] ?? '').' '.($m['apellido1'] ?? '')));
                                $turnoTxt = '—';
                                if (!empty($m['turno_entrada']) && !empty($m['turno_salida'])) {
                                    $turnoTxt = substr($m['turno_entrada'],0,5).' - '.substr($m['turno_salida'],0,5);
                                }
                                if (!empty($m['nombreTurno'])) {
                                    $turnoTxt = htmlspecialchars($m['nombreTurno']).' ('.$turnoTxt.')';
                                }

                                // ---- Cálculo de atraso SOLO para la primera marca (Entrada) ----
                                // Lógica: horaEntrada del turno + 10 min como umbral.
                                // Si la primera marca del día es posterior a ese umbral, atraso = diferencia en minutos.
                                $atrasoTxt = '—';
                                if (($m['tipoCalculado'] ?? '') === 'Entrada' && !empty($m['turno_entrada']) && !empty($m['hora'])) {
                                    $dtTurno   = dt_from_time($m['turno_entrada']); // "HH:MM" o "HH:MM:SS"
                                    $dtEntrada = dt_from_time($m['hora']);          // "HH:MM:SS"
                                    if ($dtTurno && $dtEntrada) {
                                        $dtUmbral = clone $dtTurno;
                                        $dtUmbral->modify('+10 minutes'); // tolerancia fija de 10 min
                                        if ($dtEntrada > $dtUmbral) {
                                            $min = (int) round(($dtEntrada->getTimestamp() - $dtUmbral->getTimestamp()) / 60);
                                            $atrasoTxt = $min . ' min';
                                        } else {
                                            $atrasoTxt = '0 min';
                                        }
                                    }
                                }
                            ?>
                            <tr>
                                <td><input type="checkbox" class="selectRow" name="marcas[]" value="<?= (int)$m['id_marcas'] ?>"></td>
                                <td><?= (int)$m['id_usuarios'] ?></td>
                                <td><?= $rut_full ?></td>
                                <td><?= $nombre ?></td>
                                <td><?= $turnoTxt ?></td>
                                <td><?= htmlspecialchars(date('d-m-Y', strtotime($m['fecha']))) ?></td>
                                <td><?= htmlspecialchars(substr($m['hora'], 0, 8)) ?></td>
                                <td>
                                    <?php
                                        $t = $m['tipoCalculado'];
                                        if ($t === 'Entrada')      echo '<span class="badge badge-success">Entrada</span>';
                                        elseif ($t === 'Salida')   echo '<span class="badge badge-primary">Salida</span>';
                                        else                       echo '<span class="badge badge-secondary">Intermedia</span>';
                                    ?>
                                </td>
                                <td><?= $atrasoTxt ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="9" class="text-center">No hay marcas para los filtros seleccionados.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <hr>
            <div class="row mt-3">
                <div class="col-md-6">
                     <button type="button" class="btn btn-info" onclick="imprimirSeleccion()">
                        <i class="fas fa-print"></i> Imprimir Selección
                    </button>
                </div>
                <div class="col-md-6">
                    <div class="form-row">
                         <div class="col">
                            <input type="text" name="nombre_archivo" class="form-control" placeholder="Nombre del archivo CSV" required>
                        </div>
                        <div class="col-auto">
                            <button type="submit" name="exportar_csv" class="btn btn-success">
                                <i class="fas fa-file-csv"></i> Exportar a CSV
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>        
    </div>
</div>

<!-- JS / CSS -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/v/bs4/dt-1.10.21/datatables.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/dt-1.10.21/datatables.min.css"/>

<script>
$(document).ready(function() {
    // DataTable
    $('#tabla_marcas').DataTable({
        language: { url: "//cdn.datatables.net/plug-ins/1.10.21/i18n/Spanish.json" },
        pageLength: 10,
        lengthChange: true,
        ordering: true,
        columnDefs: [{ orderable: false, targets: 0 }]
    });

    // Seleccionar Todo
    $('#seleccionarTodos').on('change', function(e) {
        $('.selectRow').prop('checked', e.target.checked);
    });

    // Botón Informe Diario → fuerza HOY (GET con action=informe_diario)
    $('#btnInformeDiario').on('click', function() {
        const hoy = new Date();
        const yyyy = hoy.getFullYear();
        const mm   = String(hoy.getMonth()+1).padStart(2,'0');
        const dd   = String(hoy.getDate()).padStart(2,'0');
        const s    = `${yyyy}-${mm}-${dd}`;

        // Setea visualmente los campos (opcional, para que el usuario vea las fechas cambiadas)
        $('#fecha_desde').val(s);
        $('#fecha_hasta').val(s);

        // Enviar GET con action=informe_diario (el servidor ignorará cualquier fecha y usará HOY)
        const form = document.getElementById('formFiltros');
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'informe_diario';
        form.appendChild(actionInput);
        form.submit();
    });
});

// Imprimir selección
function imprimirSeleccion() {
    const tabla = document.getElementById('tabla_marcas');
    let contenidoParaImprimir = '';
    
    // Encabezado (sin el checkbox de selección)
    const encabezado = tabla.querySelector('thead').cloneNode(true);
    encabezado.querySelector('tr').deleteCell(0);
    contenidoParaImprimir += '<thead>' + encabezado.innerHTML + '</thead>';

    // Filas seleccionadas
    const filas = tabla.querySelectorAll('tbody tr');
    let hay = false;
    let cuerpo = '<tbody>';

    filas.forEach(fila => {
        const checkbox = fila.querySelector('.selectRow');
        if (checkbox && checkbox.checked) {
            const clon = fila.cloneNode(true);
            clon.deleteCell(0);
            cuerpo += clon.outerHTML;
            hay = true;
        }
    });
    cuerpo += '</tbody>';

    if (!hay) {
        alert('Por favor, selecciona al menos una fila para imprimir.');
        return;
    }

    contenidoParaImprimir += cuerpo;

    const win = window.open('', '_blank');
    win.document.write('<html><head><title>Informe de Marcas</title>');
    win.document.write('<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">');
    win.document.write('<style> body { padding: 20px; } table { width: 100%; } </style>');
    win.document.write('</head><body>');
    win.document.write('<h1>Informe de Marcas</h1>');
    win.document.write('<table class="table table-bordered table-striped">' + contenidoParaImprimir + '</table>');
    win.document.write('</body></html>');
    win.document.close();
    win.onload = function() {
        win.print();
    };
}
</script>

<?php 
$stmt->close();
include_once "includes/footer.php"; 
?>
