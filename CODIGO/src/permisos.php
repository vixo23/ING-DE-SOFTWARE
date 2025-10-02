<?php
session_start();
// Redirigir si no hay sesión iniciada, por seguridad.
if (!isset($_SESSION['idempresa'])) {
    header('Location: login.php');
    exit();
}

include "../conexion.php";
include "includes/header.php";

$id_empresa = $_SESSION['idempresa'];

// --- 1. RECOGER FILTROS DE LA URL ---
// Se usan los valores de $_GET si existen, de lo contrario se usa una cadena vacía.
$nombre = $_GET['nombre'] ?? '';
$apellido = $_GET['apellido'] ?? '';
$rut = $_GET['rut'] ?? '';

// --- 2. CONSTRUCCIÓN DE CONSULTA DINÁMICA Y SEGURA ---
// Se inicia con la condición obligatoria del id_empresa.
$condiciones = ["u.id_empresa = ?"];
$param_types = "i"; // El primer tipo de parámetro es un entero (integer).
$params = [$id_empresa]; // El primer parámetro es el id_empresa.

// Se añaden condiciones y parámetros adicionales solo si los filtros no están vacíos.
if (!empty($nombre)) {
    $condiciones[] = "u.nombres LIKE ?";
    $param_types .= "s"; // Se añade un string a los tipos.
    $params[] = "%$nombre%"; // Se añade el valor del nombre al array de parámetros.
}
if (!empty($apellido)) {
    $condiciones[] = "u.apellido1 LIKE ?";
    $param_types .= "s";
    $params[] = "%$apellido%";
}
if (!empty($rut)) {
    $condiciones[] = "u.rut LIKE ?";
    $param_types .= "s";
    $params[] = "%$rut%";
}

// Se unen todas las condiciones con "AND".
$clausula_where = implode(" AND ", $condiciones);

// Consulta SQL final - Se cambia a INNER JOIN para mostrar solo usuarios con permisos.
$sql = "SELECT u.rut, u.nombres, u.apellido1, p.id_permisos, p.observaciones 
        FROM usuarios u 
        INNER JOIN permisos p ON u.id_usuarios = p.id_usuario 
        WHERE $clausula_where
        ORDER BY u.nombres, u.apellido1";

// --- 3. PREPARAR Y EJECUTAR LA CONSULTA ---
$stmt = $conexion->prepare($sql);
// Se usa el "splat operator" (...) para pasar el array de parámetros a bind_param.
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$query = $stmt->get_result();
?>

<!-- Contenedor principal con diseño mejorado -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white border-bottom">
        <h4 class="mb-0"><i class="fas fa-user-check mr-2 text-primary"></i>Listado de Permisos de Personal</h4>
    </div>
    <div class="card-body">
        <!-- Botón para mostrar/ocultar el panel de filtros -->
        <div class="mb-2 d-flex justify-content-start">
            <button type="button" id="btnMostrarFiltros" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-filter mr-1"></i>Filtrar
            </button>
        </div>

        <!-- Formulario de filtros (inicialmente oculto) -->
        <form method="get" id="formFiltros" style="display:none;" class="form-inline mb-3 p-3 bg-light rounded border">
            <input type="text" name="nombre" placeholder="Nombre" class="form-control mb-2 mr-sm-2" value="<?= htmlspecialchars($nombre) ?>">
            <input type="text" name="apellido" placeholder="Apellido" class="form-control mb-2 mr-sm-2" value="<?= htmlspecialchars($apellido) ?>">
            <input type="text" name="rut" placeholder="RUT" class="form-control mb-2 mr-sm-2" value="<?= htmlspecialchars($rut) ?>">
            <button type="submit" class="btn btn-primary btn-sm mb-2">Buscar</button>
            <a href="?_clean_filters" class="btn btn-secondary btn-sm mb-2 ml-2">Limpiar</a>
        </form>

        <!-- Tabla de resultados -->
        <div class="table-responsive">
            <table id="tablaPermisos" class="table table-striped table-bordered table-hover w-100">
                <thead class="thead-dark">
                    <tr>
                        <th><input type="checkbox" id="seleccionarTodos" title="Seleccionar todos"></th>
                        <th>ID Permiso</th>
                        <th>RUT</th>
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>Descripción del Permiso</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($data = $query->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <!-- Solo se muestra el checkbox si existe un permiso para seleccionar -->
                                <?php if (!empty($data['id_permisos'])): ?>
                                    <input type="checkbox" class="check_permiso" value="<?= $data['id_permisos'] ?>">
                                <?php endif; ?>
                            </td>
                            <td><?= $data['id_permisos'] ?? '<span class="badge badge-secondary">N/A</span>' ?></td>
                            <td><?= htmlspecialchars($data['rut']) ?></td>
                            <td><?= htmlspecialchars($data['nombres']) ?></td>
                            <td><?= htmlspecialchars($data['apellido1']) ?></td>
                            <td>
                                <?= !empty($data['observaciones']) 
                                    ? htmlspecialchars($data['observaciones']) 
                                    : '<span class="text-muted font-italic">Sin permisos registrados</span>' ?>
                            </td>
                            <td>
                                <?php if (!empty($data['id_permisos'])): ?>
                                    <button class="btn btn-sm btn-danger" title="Eliminar Permiso"><i class="fas fa-trash"></i></button>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Inclusión de librerías JS y CSS -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/v/bs4/dt-1.10.21/datatables.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/dt-1.10.21/datatables.min.css"/>

<script>
$(document).ready(function() {
    // --- 4. INICIALIZACIÓN DE DATATABLES ---
    var table = $('#tablaPermisos').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.21/i18n/Spanish.json"
        },
        "pageLength": 10,
        "lengthChange": false, // Oculta el selector de "mostrar X entradas".
        "ordering": true,
        // Deshabilita la ordenación en la primera columna (checkbox) y la última (acciones).
        "columnDefs": [
            { "orderable": false, "targets": [0, 6] }
        ]
    });

    // --- 5. LÓGICA DE LA INTERFAZ DE USUARIO (jQuery) ---

    // Mostrar/Ocultar panel de filtros con una animación suave.
    $('#btnMostrarFiltros').on('click', function() {
        $('#formFiltros').slideToggle();
    });

    // Funcionalidad de "Seleccionar/Deseleccionar todos".
    $('#seleccionarTodos').on('click', function() {
        // Busca todos los checkboxes con la clase 'check_permiso' dentro de la tabla y cambia su estado.
        $('.check_permiso', table.rows().nodes()).prop('checked', this.checked);
    });

    // Si un checkbox individual es desmarcado, el "seleccionarTodos" también se desmarca.
    $('#tablaPermisos tbody').on('change', '.check_permiso', function() {
        if (!this.checked) {
            $('#seleccionarTodos').prop('checked', false);
        }
    });
});
</script>

<?php 
// --- 6. CERRAR RECURSOS Y MOSTRAR PIE DE PÁGINA ---
$stmt->close();
include "includes/footer.php"; 
?>
