<?php
session_start();
// Redirigir si no hay sesión iniciada, por seguridad.
if (!isset($_SESSION['idempresa'])) {
    header('Location: login.php');
    exit();
}

include "../conexion.php";

// =================== ELIMINAR (tu lógica actual por GET) ===================
if (!empty($_GET['action']) && $_GET['action'] == 'eliminar' && !empty($_GET['id'])) {
    $id_permiso = $_GET['id'];
    $id_empresa = $_SESSION['idempresa'];

    // Verificar que el permiso pertenece a la empresa
    $query_check = mysqli_query($conexion,
        "SELECT p.* FROM permisos p 
         INNER JOIN usuarios u ON p.id_usuario = u.id_usuarios 
         WHERE p.id_permisos = '$id_permiso' AND u.id_empresa = '$id_empresa'"
    );

    if (mysqli_num_rows($query_check) > 0) {
        $query_delete = mysqli_query($conexion, "DELETE FROM permisos WHERE id_permisos = '$id_permiso'");
        if ($query_delete) {
            $_SESSION['mensaje'] = "Permiso eliminado con éxito";
        } else {
            $_SESSION['error'] = "Error al eliminar el permiso";
        }
    } else {
        $_SESSION['error'] = "No tiene permiso para eliminar este registro";
    }
    header("Location: permisos.php");
    exit();
}

include "includes/header.php";

$id_empresa = $_SESSION['idempresa'];

// =================== FILTROS ===================
$nombre = $_GET['nombre'] ?? '';
$apellido = $_GET['apellido'] ?? '';
$rut = $_GET['rut'] ?? '';

// =================== CONSULTA SEGURA ===================
$condiciones = ["u.id_empresa = ?"];
$param_types = "i";
$params = [$id_empresa];

if ($nombre !== '') { $condiciones[] = "u.nombres LIKE ?";  $param_types .= "s"; $params[] = "%{$nombre}%"; }
if ($apellido !== '') { $condiciones[] = "u.apellido1 LIKE ?"; $param_types .= "s"; $params[] = "%{$apellido}%"; }
if ($rut !== '') { $condiciones[] = "u.rut LIKE ?"; $param_types .= "s"; $params[] = "%{$rut}%"; }

$clausula_where = implode(" AND ", $condiciones);

$sql = "SELECT u.rut, u.nombres, u.apellido1, p.id_permisos, p.observaciones 
        FROM usuarios u 
        INNER JOIN permisos p ON u.id_usuarios = p.id_usuario 
        WHERE $clausula_where
        ORDER BY u.nombres, u.apellido1";

$stmt = $conexion->prepare($sql);
if ($stmt === false) {
    echo '<div class="container mt-4"><div class="alert alert-danger">Error en la consulta.</div></div>';
    include "includes/footer.php";
    exit;
}
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$query = $stmt->get_result();
?>

<!-- Contenedor principal con diseño mejorado -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white border-bottom">
        <h4 class="mb-0">
            <i class="fas fa-user-check mr-2 text-primary"></i>
            Listado de Permisos de Personal
        </h4>
    </div>
    <div class="card-body">

        <!-- Mensajes -->
        <?php if (isset($_SESSION['mensaje'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['mensaje']) ?></div>
            <?php unset($_SESSION['mensaje']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Botones -->
        <div class="mb-3 d-flex justify-content-start flex-wrap">
            <button type="button" id="btnMostrarFiltros" class="btn btn-outline-secondary btn-sm mr-2 mb-2">
                <i class="fas fa-filter mr-1"></i>Filtrar
            </button>
            <button type="button" id="btnImprimirPDFs" class="btn btn-success mb-2" disabled>
                <i class="fas fa-print mr-1"></i>Imprimir PDFs
            </button>
        </div>

        <!-- Filtros -->
        <form method="get" id="formFiltros" style="display:none;" class="form-inline mb-3 p-3 bg-light rounded border">
            <input type="text" name="nombre" placeholder="Nombre" class="form-control mb-2 mr-sm-2" value="<?= htmlspecialchars($nombre) ?>">
            <input type="text" name="apellido" placeholder="Apellido" class="form-control mb-2 mr-sm-2" value="<?= htmlspecialchars($apellido) ?>">
            <input type="text" name="rut" placeholder="RUT" class="form-control mb-2 mr-sm-2" value="<?= htmlspecialchars($rut) ?>">
            <button type="submit" class="btn btn-primary btn-sm mb-2">Buscar</button>
            <a href="?_clean_filters" class="btn btn-secondary btn-sm mb-2 ml-2">Limpiar</a>
        </form>

        <!-- Tabla -->
        <div class="table-responsive">
            <table id="tablaPermisos" class="table table-striped table-bordered table-hover w-100">
                <thead class="thead-dark">
                    <tr>
                        <th style="width:30px;"><input type="checkbox" id="seleccionarTodos" title="Seleccionar todos"></th>
                        <th>ID Permiso</th>
                        <th>RUT</th>
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>Descripción del Permiso</th>
                        <th style="width:100px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($data = $query->fetch_assoc()):
                        // Empaquetar datos para impresión (puedes añadir más campos si amplías el SELECT)
                        $permiso_data = [
                            'id_permisos'     => $data['id_permisos'],
                            'rut'             => $data['rut'],
                            'nombre_completo' => trim(($data['nombres'] ?? '') . ' ' . ($data['apellido1'] ?? '')),
                            'observaciones'   => $data['observaciones']
                        ];
                        $permiso_json = htmlspecialchars(json_encode($permiso_data), ENT_QUOTES, 'UTF-8');
                    ?>
                        <tr>
                            <td>
                                <?php if (!empty($data['id_permisos'])): ?>
                                    <input type="checkbox"
                                           class="check_permiso"
                                           value="<?= (int)$data['id_permisos'] ?>"
                                           data-permiso='<?= $permiso_json ?>'>
                                <?php endif; ?>
                            </td>
                            <td><?= isset($data['id_permisos']) ? (int)$data['id_permisos'] : '<span class="badge badge-secondary">N/A</span>' ?></td>
                            <td><?= htmlspecialchars($data['rut'] ?? '') ?></td>
                            <td><?= htmlspecialchars($data['nombres'] ?? '') ?></td>
                            <td><?= htmlspecialchars($data['apellido1'] ?? '') ?></td>
                            <td>
                                <?= !empty($data['observaciones'])
                                    ? htmlspecialchars($data['observaciones'])
                                    : '<span class="text-muted font-italic">Sin permisos registrados</span>' ?>
                            </td>
                            <td>
                                <?php if (!empty($data['id_permisos'])): ?>
                                    <button class="btn btn-sm btn-danger" title="Eliminar" onclick="abrirModalEliminar(<?= (int)$data['id_permisos'] ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
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

<!-- Modal de Confirmación de Eliminación (tu versión) -->
<div class="modal fade" id="eliminarModal" tabindex="-1" role="dialog" aria-labelledby="eliminarModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Eliminación</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                ¿Está seguro de que desea eliminar este permiso?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <a href="#" id="confirmEliminarBtn" class="btn btn-danger">Eliminar</a>
            </div>
        </div>
    </div>
</div>

<!-- CSS/JS (DataTables + jsPDF) -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/dt-1.10.21/datatables.min.css"/>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.datatables.net/v/bs4/dt-1.10.21/datatables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.0/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<script>
// =================== Selección + Impresión masiva ===================
let permisosSeleccionados = [];

$(document).ready(function() {
    // DataTables
    var table = $('#tablaPermisos').DataTable({
        language: { url: "//cdn.datatables.net/plug-ins/1.10.21/i18n/Spanish.json" },
        pageLength: 10,
        lengthChange: false,
        ordering: true,
        columnDefs: [{ orderable: false, targets: [0, 6] }]
    });

    // Filtros
    $('#btnMostrarFiltros').on('click', function() {
        $('#formFiltros').slideToggle();
    });

    // Check individual -> maneja array + resaltar fila
    $('#tablaPermisos tbody').on('change', '.check_permiso', function() {
        const $cb = $(this);
        const fila = $cb.closest('tr');
        const dataAttr = $cb.attr('data-permiso');
        if (!dataAttr) return;

        const permiso = JSON.parse(dataAttr);
        const idx = permisosSeleccionados.findIndex(p => p.id_permisos == permiso.id_permisos);

        if ($cb.is(':checked')) {
            if (idx === -1) permisosSeleccionados.push(permiso);
            fila.addClass('table-info');
        } else {
            if (idx > -1) permisosSeleccionados.splice(idx, 1);
            fila.removeClass('table-info');
        }
        updateActionButtonsState(table);
    });

    // Seleccionar/Deseleccionar todos
    $('#seleccionarTodos').on('click', function() {
        const checked = this.checked;
        const $checkboxes = $('.check_permiso', table.rows().nodes());
        $checkboxes.prop('checked', checked);

        permisosSeleccionados = [];
        $checkboxes.each(function() {
            const $cb = $(this);
            const fila = $cb.closest('tr');
            const dataAttr = $cb.attr('data-permiso');
            if (!dataAttr) return;

            const permiso = JSON.parse(dataAttr);
            if (checked) {
                permisosSeleccionados.push(permiso);
                fila.addClass('table-info');
            } else {
                fila.removeClass('table-info');
            }
        });

        updateActionButtonsState(table);
    });

    // Botón imprimir
    $('#btnImprimirPDFs').on('click', function() {
        imprimirPDFs();
    });

    // Estado inicial
    updateActionButtonsState(table);
});

function updateActionButtonsState(table) {
    const selectedCount = permisosSeleccionados.length;
    const totalCheckboxes = $('.check_permiso', table.rows().nodes()).length;
    const allChecked = totalCheckboxes > 0 && selectedCount === totalCheckboxes;

    $('#btnImprimirPDFs').prop('disabled', selectedCount === 0);
    $('#seleccionarTodos').prop('checked', allChecked);

    if (selectedCount === 1) {
        $('#btnImprimirPDFs').html('<i class="fas fa-print mr-1"></i>Imprimir PDF');
    } else if (selectedCount > 1) {
        $('#btnImprimirPDFs').html('<i class="fas fa-print mr-1"></i>Imprimir PDFs (' + selectedCount + ')');
    } else {
        $('#btnImprimirPDFs').html('<i class="fas fa-print mr-1"></i>Imprimir PDFs');
    }
}

function imprimirPDFs() {
    if (permisosSeleccionados.length === 0) {
        alert("Por favor, seleccione al menos un permiso para imprimir.");
        return;
    }

    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    permisosSeleccionados.forEach((permiso, index) => {
        if (index > 0) doc.addPage();

        doc.setFontSize(20);
        doc.text("Ficha de Permiso", 105, 18, null, null, "center");
        doc.setFontSize(12);

        let y = 36;
        doc.text(`ID Permiso: ${permiso.id_permisos ?? ''}`, 20, y); y += 8;
        doc.text(`Nombre Completo: ${permiso.nombre_completo ?? ''}`, 20, y); y += 8;
        doc.text(`RUT: ${permiso.rut ?? ''}`, 20, y); y += 8;

        // Observaciones (manejo de salto de línea simple)
        const obs = permiso.observaciones ? String(permiso.observaciones) : 'N/A';
        const obsLines = doc.splitTextToSize(`Observaciones/Motivo: ${obs}`, 170);
        doc.text(obsLines, 20, y); 
        y += (obsLines.length * 7) + 10;

        // Firmas
        doc.text("________________________", 20, y);
        doc.text("Firma del Empleado", 20, y + 6);
        doc.text("________________________", 120, y);
        doc.text("Firma del Autorizador", 120, y + 6);
    });

    doc.save('Fichas-Permisos.pdf');
}

// Modal eliminar (tu versión)
function abrirModalEliminar(id) {
    document.getElementById('confirmEliminarBtn').href = 'permisos.php?action=eliminar&id=' + id;
    $('#eliminarModal').modal('show');
}
</script>

<?php 
$stmt->close();
$conexion->close();
include "includes/footer.php"; 
?>
