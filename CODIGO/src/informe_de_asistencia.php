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

// --- 1. LÓGICA PARA EXPORTAR A CSV ---
// Esto se ejecuta primero porque necesita enviar cabeceras HTTP y luego terminar la ejecución.
if (isset($_POST['exportar_csv']) && !empty($_POST['marcas']) && !empty($_POST['nombre_archivo'])) {
    $marcas_seleccionadas = $_POST['marcas'];
    $nombre_archivo = $_POST['nombre_archivo'];

    // Asegurar que el nombre del archivo termine en .csv
    if (substr($nombre_archivo, -4) !== ".csv") {
        $nombre_archivo .= ".csv";
    }

    // Preparar la consulta para exportar solo las marcas seleccionadas
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

    // Enviar cabeceras para forzar la descarga del archivo
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $nombre_archivo . '"');

    $output = fopen('php://output', 'w');
    // Escribir la fila de encabezados
    fputcsv($output, ['Fecha', 'Hora', 'RUT', 'Nombre', 'Apellido Paterno', 'Apellido Materno']);

    // Escribir los datos de las marcas
    while ($fila = $resultado_export->fetch_assoc()) {
        fputcsv($output, $fila);
    }
    
    fclose($output);
    $stmt_export->close();
    exit(); // Terminar el script después de la descarga
}

// --- 2. LÓGICA PARA EL FILTRADO DE LA VISTA PRINCIPAL ---
include "includes/header.php";

$fecha_desde = $_GET['fecha_desde'] ?? date('Y-m-01');
$fecha_hasta = $_GET['fecha_hasta'] ?? date('Y-m-d');
$nombre_usuario = $_GET['nombre_usuario'] ?? '';

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
?>

<!-- Formulario de Filtros -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-light">
        <h4 class="mb-0"><i class="fas fa-filter mr-2 text-primary"></i>Informe de Asistencia</h4>
    </div>
    <div class="card-body">
        <form action="" method="get">
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
                <div class="col-md-2 align-self-end">
                    <button type="submit" class="btn btn-primary btn-block">Filtrar</button>
                </div>
            </div>
        </form>
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
    $(document).ready(function() {
        // Inicializar DataTables
        $('#tabla_asistencia').DataTable({
            "language": {"url": "//cdn.datatables.net/plug-ins/1.10.21/i18n/Spanish.json"},
            "pageLength": 10,
            "lengthChange": true,
            "ordering": true,
            "columnDefs": [{ "orderable": false, "targets": 0 }] // Deshabilitar orden en la columna de checkboxes
        });

        // Lógica para el checkbox "Seleccionar Todo"
        $('#seleccionarTodos').on('change', function(e) {
            $('.selectRow').prop('checked', e.target.checked);
        });
    });

    function imprimirSeleccion() {
        const tabla = document.getElementById('tabla_asistencia');
        let contenidoParaImprimir = '';
        
        // Encabezado de la tabla
        const encabezado = tabla.querySelector('thead').innerHTML;
        contenidoParaImprimir += '<thead>' + encabezado.replace(/<th><input.*<\/th>/, '') + '</thead>';

        // Filas seleccionadas
        const filas = tabla.querySelectorAll('tbody tr');
        let hayFilasSeleccionadas = false;
        let cuerpoTabla = '<tbody>';

        filas.forEach(fila => {
            const checkbox = fila.querySelector('.selectRow');
            if (checkbox && checkbox.checked) {
                // Clonar la fila y eliminar la celda del checkbox
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

        // Crear una nueva ventana para la impresión
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