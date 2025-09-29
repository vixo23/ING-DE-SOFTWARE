<?php
session_start();
include "../conexion.php";
include "includes/header.php";
$id_empresa = $_SESSION['idempresa'];

// Lógica para el filtrado
$fecha_desde = isset($_POST['fecha_desde']) ? $_POST['fecha_desde'] : date('Y-m-01');
$fecha_hasta = isset($_POST['fecha_hasta']) ? $_POST['fecha_hasta'] : date('Y-m-d');
$id_usuario = isset($_POST['id_usuario']) ? mysqli_real_escape_string($conexion, $_POST['id_usuario']) : '';
$nombre_usuario = isset($_POST['nombre_usuario']) ? mysqli_real_escape_string($conexion, $_POST['nombre_usuario']) : '';

?>

<div class="card">
    <div class="card-header bg-primary text-white">
        Informe de Asistencia
    </div>
    <div class="card-body">
        <form action="informe_de_asistencia.php" method="post">
            <div class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="id_usuario">ID Usuario:</label>
                        <input type="text" class="form-control" id="id_usuario" name="id_usuario" value="<?php echo htmlspecialchars($id_usuario); ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="nombre_usuario">Nombre:</label>
                        <input type="text" class="form-control" id="nombre_usuario" name="nombre_usuario" value="<?php echo htmlspecialchars($nombre_usuario); ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="fecha_desde">Desde:</label>
                        <input type="date" class="form-control" id="fecha_desde" name="fecha_desde" value="<?php echo $fecha_desde; ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="fecha_hasta">Hasta:</label>
                        <input type="date" class="form-control" id="fecha_hasta" name="fecha_hasta" value="<?php echo $fecha_hasta; ?>">
                    </div>
                </div>
                <div class="col-md-1 align-self-end">
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header bg-info text-white">
        Resultados del Informe
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-striped table-bordered" id="table_asistencia">
                <thead class="thead-dark">
                    <tr>
                        <th><input type="checkbox" id="selectAll"></th>
                        <th>ID Usuario</th>
                        <th>Nombre Usuario</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Construcción de la consulta con filtros
                    $sql = "SELECT m.fecha, m.hora, u.id_usuarios, u.nombres 
                            FROM marcas m 
                            JOIN usuarios u ON m.id_usuario = u.id_usuarios 
                            WHERE u.id_empresa = '$id_empresa' 
                            AND m.fecha BETWEEN '$fecha_desde' AND '$fecha_hasta'";

                    if (!empty($id_usuario)) {
                        $sql .= " AND u.id_usuarios = '$id_usuario'";
                    }
                    if (!empty($nombre_usuario)) {
                        $sql .= " AND u.nombres LIKE '%$nombre_usuario%'";
                    }

                    $sql .= " ORDER BY u.nombres, m.fecha, m.hora ASC";

                    $query_asistencia = mysqli_query($conexion, $sql);
                    
                    if($query_asistencia && mysqli_num_rows($query_asistencia) > 0) {
                        while ($data = mysqli_fetch_assoc($query_asistencia)) {
                            $fecha = date('d-m-Y', strtotime($data['fecha']));
                            $hora = $data['hora'];
                    ?>
                            <tr>
                                <td><input type="checkbox" class="selectRow"></td>
                                <td><?php echo htmlspecialchars($data['id_usuarios']); ?></td>
                                <td><?php echo htmlspecialchars($data['nombres']); ?></td>
                                <td><?php echo $fecha; ?></td>
                                <td><?php echo $hora; ?></td>
                            </tr>
                    <?php 
                        }
                    } else if (!$query_asistencia) {
                        echo "<tr><td colspan='5' class='text-center text-danger'>Error en la consulta: " . mysqli_error($conexion) . "</td></tr>";
                    } else {
                        echo "<tr><td colspan='5' class='text-center'>No hay registros de asistencia para el período y filtros seleccionados.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <button type="button" class="btn btn-secondary mt-3" onclick="printSelectedRows()">
            <i class="fas fa-print"></i> Imprimir Selección
        </button>
    </div>
</div>

<script>
    function printSelectedRows() {
        const table = document.getElementById('table_asistencia');
        let selectedRows = '';
        
        // Encabezado de la tabla para el informe
        const header = table.querySelector('thead').innerHTML;
        selectedRows += '<thead>' + header.replace(/<th><input.*<\/th>/, '') + '</thead>';

        // Filtrar y obtener las filas seleccionadas
        const rows = table.querySelectorAll('tbody tr');
        let hasSelectedRows = false;
        rows.forEach(row => {
            const checkbox = row.querySelector('.selectRow');
            if (checkbox && checkbox.checked) {
                // Eliminar la columna del checkbox antes de imprimir
                const cells = Array.from(row.cells).slice(1);
                const rowHtml = '<tr>' + cells.map(cell => `<td>${cell.innerHTML}</td>`).join('') + '</tr>';
                selectedRows += rowHtml;
                hasSelectedRows = true;
            }
        });

        if (!hasSelectedRows) {
            alert('Por favor, selecciona al menos una fila para imprimir.');
            return;
        }

        // Crear una nueva ventana para el contenido a imprimir
        const printWindow = window.open('', '_blank');
        printWindow.document.write('<html><head><title>Informe de Asistencia</title>');
        
        // Incluir estilos de Bootstrap y CSS para la impresión
        printWindow.document.write('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">');
        printWindow.document.write('<style> body { font-family: sans-serif; } table { width: 100%; border-collapse: collapse; } th, td { border: 1px solid black; padding: 8px; text-align: left; } .table-responsive, .card-body { padding: 0 !important; } </style>');
        
        printWindow.document.write('</head><body>');
        printWindow.document.write('<h1>Informe de Asistencia</h1>');
        printWindow.document.write('<table class="table table-bordered table-striped">');
        printWindow.document.write(selectedRows);
        printWindow.document.write('</table>');
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        
        // Abrir el diálogo de impresión
        printWindow.onload = function() {
            printWindow.print();
        };
    }

    // Lógica para el checkbox "Seleccionar Todo"
    document.getElementById('selectAll').addEventListener('change', function(e) {
        const checkboxes = document.querySelectorAll('.selectRow');
        checkboxes.forEach(checkbox => {
            checkbox.checked = e.target.checked;
        });
    });
</script>

<?php include_once "includes/footer.php"; ?>