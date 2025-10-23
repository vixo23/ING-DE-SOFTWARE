<?php
session_start();
// Redirigir si no hay sesión iniciada, por seguridad.
if (!isset($_SESSION['idempresa'])) {
    header('Location: login.php');
    exit();
}

include "../conexion.php";

$id_empresa = $_SESSION['idempresa'];
$mensaje_exportacion = '';

// --- 1. LÓGICA PARA EXPORTAR A CSV (POST) ---
if (isset($_POST['exportar_csv']) && !empty($_POST['marcas']) && !empty($_POST['nombre_archivo'])) {
    $marcas_seleccionadas = $_POST['marcas'];
    $nombre_archivo = $_POST['nombre_archivo'];

    if (substr($nombre_archivo, -4) !== ".csv") {
        $nombre_archivo .= ".csv";
    }

    $placeholders = implode(',', array_fill(0, count($marcas_seleccionadas), '?'));
    $sql_export = "SELECT m.fecha, m.hora, u.rut, u.nombres, u.apellido1, u.apellido2
                   FROM marcas m
                   INNER JOIN usuarios u ON m.id_usuario = u.id_usuarios
                   WHERE m.id_marcas IN ($placeholders)
                   ORDER BY u.nombres, m.fecha, m.hora";

    $stmt_export = $conexion->prepare($sql_export);
    $tipos = str_repeat('i', count($marcas_seleccionadas));
    $stmt_export->bind_param($tipos, ...$marcas_seleccionadas);
    $stmt_export->execute();
    $resultado_export = $stmt_export->get_result();

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $nombre_archivo . '"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['Fecha', 'Hora', 'RUT', 'Nombre', 'Apellido Paterno', 'Apellido Materno']);

    while ($fila = $resultado_export->fetch_assoc()) {
        fputcsv($output, $fila);
    }
    
    fclose($output);
    $stmt_export->close();
    exit();
}

// --- 2. LÓGICA PARA EL FILTRADO DE LA VISTA PRINCIPAL (GET) ---
include "includes/header.php";

// Detectar acción e impresión automática
$action = $_GET['action'] ?? '';
$print  = isset($_GET['print']) ? $_GET['print'] : '';

// Fechas para consulta
if ($action === 'informe_diario') {
    // Forzar día actual
    $fecha_desde = date('Y-m-d');
    $fecha_hasta = date('Y-m-d');
    $nombre_usuario = $_GET['nombre_usuario'] ?? '';
} else {
    $fecha_desde = $_GET['fecha_desde'] ?? date('Y-m-01');
    $fecha_hasta = $_GET['fecha_hasta'] ?? date('Y-m-d');
    $nombre_usuario = $_GET['nombre_usuario'] ?? '';
}

// ===== NUEVO: construir leyenda de día + fecha (solo para informe diario) =====
$legibleHoy = '';
if ($action === 'informe_diario') {
    $tz = new DateTimeZone('America/Santiago');
    $hoyDT = new DateTime('now', $tz);
    if (class_exists('IntlDateFormatter')) {
        $fmt = new IntlDateFormatter(
            'es_CL',
            IntlDateFormatter::FULL,
            IntlDateFormatter::NONE,
            'America/Santiago',
            IntlDateFormatter::GREGORIAN,
            "EEEE d 'de' MMMM 'de' y"
        );
        $legibleHoy = ucfirst($fmt->format($hoyDT));
    } else {
        // Fallback sin intl
        $dias = ['domingo','lunes','martes','miércoles','jueves','viernes','sábado'];
        $meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
        $d = (int)$hoyDT->format('w');
        $dia = $dias[$d];
        $dd = (int)$hoyDT->format('d');
        $mm = (int)$hoyDT->format('m');
        $yyyy = $hoyDT->format('Y');
        $legibleHoy = ucfirst("$dia $dd de {$meses[$mm-1]} de $yyyy");
    }
}

// Armado de consulta con prepared statements
$condiciones = ["u.id_empresa = ?"];
$tipos_params = "i";
$params = [$id_empresa];

// Si quieres blindarlo 100% como "solo hoy" del lado servidor cuando es informe_diario, descomenta:
// if ($action === 'informe_diario') {
//     $condiciones[] = "m.fecha = CURDATE()";
// } else {
    if (!empty($fecha_desde)) { $condiciones[] = "m.fecha >= ?"; $tipos_params .= "s"; $params[] = $fecha_desde; }
    if (!empty($fecha_hasta)) { $condiciones[] = "m.fecha <= ?"; $tipos_params .= "s"; $params[] = $fecha_hasta; }
// }

if (!empty($nombre_usuario)) {
    $condiciones[] = "u.nombres LIKE ?";
    $tipos_params .= "s";
    $params[] = "%$nombre_usuario%";
}

$clausula_where = implode(" AND ", $condiciones);

$sql = "SELECT m.id_marcas, m.fecha, m.hora, u.id_usuarios, u.nombres 
        FROM marcas m 
        JOIN usuarios u ON m.id_usuario = u.id_usuarios 
        WHERE $clausula_where
        ORDER BY u.nombres, m.fecha, m.hora ASC";

$stmt = $conexion->prepare($sql);
$stmt->bind_param($tipos_params, ...$params);
$stmt->execute();
$query_asistencia = $stmt->get_result();

// bandera para JS (auto imprimir al recargar)
$AUTO_PRINT_TODAY = ($action === 'informe_diario' && $print === '1');
?>

<!-- Formulario de Filtros -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-light">
        <h4 class="mb-0"><i class="fas fa-filter mr-2 text-primary"></i>Informe de Asistencia</h4>
    </div>
    <div class="card-body">
        <form action="" method="get" id="formFiltros">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="nombre_usuario">Nombre:</label>
                        <input type="text" class="form-control" id="nombre_usuario" name="nombre_usuario" value="<?= htmlspecialchars($nombre_usuario); ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="fecha_desde">Desde:</label>
                        <input type="date" class="form-control" id="fecha_desde" name="fecha_desde" value="<?= htmlspecialchars($fecha_desde); ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="fecha_hasta">Hasta:</label>
                        <input type="date" class="form-control" id="fecha_hasta" name="fecha_hasta" value="<?= htmlspecialchars($fecha_hasta); ?>">
                    </div>
                </div>
                <div class="col-md-2 align-self-end d-flex">
                    <button type="submit" class="btn btn-primary btn-block mr-2">Filtrar</button>
                    <!-- NUEVO: botón Informe Diario (usa GET action=informe_diario & print=1) -->
                    <button type="button" class="btn btn-success btn-block" id="btnInformeDiario">Informe Diario</button>
                    <input type="hidden" name="action" id="actionFiltro" value="">
                    <!-- NUEVO: bandera para disparar impresión tras recargar -->
                    <input type="hidden" name="print" id="printFlag" value="">
                </div>
            </div>
        </form>

        <!-- NUEVO: cintillo con día + fecha actual cuando es Informe Diario -->
        <?php if ($action === 'informe_diario' && $legibleHoy): ?>
            <div class="alert alert-info d-flex align-items-center mt-3 mb-0" role="alert">
                <i class="fas fa-calendar-day mr-2"></i>
                <div><strong>Informe de asistencia diario</strong> — <?= htmlspecialchars($legibleHoy) ?></div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Resultados del Informe -->
<div class="card shadow-sm mt-4">
    <div class="card-body">
        <form action="" method="post">
            <div class="table-responsive">
                <table class="table table-hover table-striped table-bordered" id="tabla_asistencia">
                    <thead class="thead-dark">
                        <tr>
                            <th><input type="checkbox" id="seleccionarTodos"></th>
                            <th>ID Usuario</th>
                            <th>Nombre Usuario</th>
                            <th>Fecha</th>
                            <th>Hora</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($query_asistencia && $query_asistencia->num_rows > 0): ?>
                            <?php while ($data = $query_asistencia->fetch_assoc()): ?>
                                <tr>
                                    <td><input type="checkbox" class="selectRow" name="marcas[]" value="<?= $data['id_marcas'] ?>"></td>
                                    <td><?= htmlspecialchars($data['id_usuarios']); ?></td>
                                    <td><?= htmlspecialchars($data['nombres']); ?></td>
                                    <td><?= date('d-m-Y', strtotime($data['fecha'])); ?></td>
                                    <td><?= htmlspecialchars($data['hora']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">No hay registros para los filtros seleccionados.</td>
                            </tr>
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

<!-- Scripts de JS -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/v/bs4/dt-1.10.21/datatables.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/dt-1.10.21/datatables.min.css"/>

<script>
    // bandera server->cliente para auto imprimir
    const AUTO_PRINT_TODAY = <?= $AUTO_PRINT_TODAY ? 'true' : 'false' ?>;

    $(document).ready(function() {
        // Inicializar DataTables
        const dt = $('#tabla_asistencia').DataTable({
            "language": {"url": "//cdn.datatables.net/plug-ins/1.10.21/i18n/Spanish.json"},
            "pageLength": 10,
            "lengthChange": true,
            "ordering": true,
            "columnDefs": [{ "orderable": false, "targets": 0 }]
        });

        // Seleccionar Todo
        $('#seleccionarTodos').on('change', function(e) {
            $('.selectRow').prop('checked', e.target.checked);
        });

        // Informe Diario -> pone hoy en fechas, setea action+print y envía
        $('#btnInformeDiario').on('click', function() {
            const hoy = new Date();
            const yyyy = hoy.getFullYear();
            const mm = String(hoy.getMonth() + 1).padStart(2, '0');
            const dd = String(hoy.getDate()).padStart(2, '0');
            const hoyStr = `${yyyy}-${mm}-${dd}`;

            $('#fecha_desde').val(hoyStr);
            $('#fecha_hasta').val(hoyStr);
            $('#actionFiltro').val('informe_diario');
            $('#printFlag').val('1'); // hará que al recargar se dispare la impresión
            $('#formFiltros').trigger('submit');
        });

        // Si venimos de Informe Diario con print=1, auto imprimir HOY
        if (AUTO_PRINT_TODAY) {
            setTimeout(imprimirHoy, 250);
        }
    });

    // === Imprime SOLO filas con la fecha de HOY (todas las páginas de DT) con día + fecha en título ===
    function imprimirHoy() {
        const dt = $('#tabla_asistencia').DataTable();

        // Hoy en formato dd-mm-YYYY como se muestra en la tabla
        const hoy = new Date();
        const dd  = String(hoy.getDate()).padStart(2, '0');
        const mm  = String(hoy.getMonth() + 1).padStart(2, '0');
        const yyyy = hoy.getFullYear();
        const hoyTabla = `${dd}-${mm}-${yyyy}`;

        // Nombre del día para el título
        const dias = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
        const dayName = dias[hoy.getDay()];
        const tituloDia = `${dayName} ${hoyTabla}`;

        const nodes = dt.rows({ page: 'all' }).nodes(); // todas las páginas

        // 0 checkbox, 1 ID, 2 Nombre, 3 Fecha, 4 Hora
        const filasHoy = Array.from(nodes).filter(tr => {
            const tds = tr.querySelectorAll('td');
            return tds && tds.length >= 5 && tds[3].textContent.trim() === hoyTabla;
        });

        if (filasHoy.length === 0) {
            alert('No hay marcas del día de hoy para imprimir.');
            return;
        }

        const thead = $('#tabla_asistencia thead').clone();
        thead.find('th:first').remove();

        let cuerpo = '<tbody>';
        filasHoy.forEach(tr => {
            const clon = tr.cloneNode(true);
            clon.deleteCell(0); // quitar checkbox
            cuerpo += clon.outerHTML;
        });
        cuerpo += '</tbody>';

        const htmlTabla = '<thead>' + thead.html() + '</thead>' + cuerpo;

        const win = window.open('', '_blank');
        win.document.write('<html><head><title>Informe de Asistencia (Hoy)</title>');
        win.document.write('<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">');
        win.document.write('<style> body{padding:20px;} table{width:100%;} </style>');
        win.document.write('</head><body>');
        win.document.write('<h1>Informe de Asistencia — ' + tituloDia + '</h1>');
        win.document.write('<table class="table table-bordered table-striped">' + htmlTabla + '</table>');
        win.document.write('</body></html>');
        win.document.close();
        win.onload = function(){ win.print(); };
    }

    // Tu impresión por selección (se mantiene igual)
    function imprimirSeleccion() {
        const tabla = document.getElementById('tabla_asistencia');
        let contenidoParaImprimir = '';
        
        const encabezado = tabla.querySelector('thead').innerHTML;
        contenidoParaImprimir += '<thead>' + encabezado.replace(/<th><input.*<\/th>/, '') + '</thead>';

        const filas = tabla.querySelectorAll('tbody tr');
        let hayFilasSeleccionadas = false;
        let cuerpoTabla = '<tbody>';

        filas.forEach(fila => {
            const checkbox = fila.querySelector('.selectRow');
            if (checkbox && checkbox.checked) {
                const filaClonada = fila.cloneNode(true);
                filaClonada.deleteCell(0);
                cuerpoTabla += filaClonada.outerHTML;
                hayFilasSeleccionadas = true;
            }
        });
        cuerpoTabla += '</tbody>';

        if (!hayFilasSeleccionadas) {
            alert('Por favor, selecciona al menos una fila para imprimir.');
            return;
        }
        contenidoParaImprimir += cuerpoTabla;

        const ventanaImpresion = window.open('', '_blank');
        ventanaImpresion.document.write('<html><head><title>Informe de Asistencia</title>');
        ventanaImpresion.document.write('<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">');
        ventanaImpresion.document.write('<style> body { padding: 20px; } table { width: 100%; } </style>');
        ventanaImpresion.document.write('</head><body>');
        ventanaImpresion.document.write('<h1>Informe de Asistencia</h1>');
        ventanaImpresion.document.write('<table class="table table-bordered table-striped">' + contenidoParaImprimir + '</table>');
        ventanaImpresion.document.write('</body></html>');
        ventanaImpresion.document.close();
        
        ventanaImpresion.onload = function() {
            ventanaImpresion.print();
        };
    }
</script>

<?php 
$stmt->close();
include_once "includes/footer.php"; 
?>
