<?php
session_start();
if (!isset($_SESSION['idempresa'])) {
    // Redirigir si no hay sesión iniciada, por seguridad
    header('Location: login.php');
    exit();
}

include "../conexion.php";
include "includes/header.php";

$id_empresa = $_SESSION['idempresa'];

// --- 1. CONSULTA SEGURA CON SENTENCIAS PREPARADAS ---
// Corregido: 'observaciones' y un ORDER BY más lógico
// --- CONSULTA SEGURA Y FILTRADA ---
$sql = "SELECT u.rut, u.nombres, u.apellido1, p.id_permisos, p.observaciones 
        FROM usuarios u 
        INNER JOIN permisos p ON u.id_usuarios = p.id_usuario 
        WHERE u.id_empresa = ? 
        ORDER BY u.nombres, u.apellido1";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_empresa);
$stmt->execute();
$query = $stmt->get_result();

?>
<div class="card">
    <div class="card-header bg-light">
        <h2 class="mb-0 h4"><i class="fas fa-user-check mr-2"></i>Listado de Permisos de Personal</h2>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="tablaPermisos" class="table table-hover table-striped table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>ID Permiso</th>
                        <th>RUT</th>
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>Descripción del Permiso</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($data = $query->fetch_assoc()) { ?>
                        <tr>
                            <td>
                                <?php echo $data['id_permisos'] ?? '<span class="badge badge-secondary">N/A</span>'; ?>
                            </td>
                            <td><?php echo htmlspecialchars($data['rut']); ?></td>
                            <td><?php echo htmlspecialchars($data['nombres']); ?></td>
                            <td><?php echo htmlspecialchars($data['apellido1']); ?></td>
                            <td>
                                <?php echo !empty($data['observaciones']) ? htmlspecialchars($data['observaciones']) : '<span class="font-italic text-muted">Sin permisos registrados</span>'; ?>
                            </td>
                            <td>
                                <?php if (!empty($data['id_permisos'])): ?>
                                    
                                    <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/v/bs4/dt-1.10.21/datatables.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/dt-1.10.21/datatables.min.css"/>

<script>
// 6. MEJORA OPCIONAL: CONVERTIR LA TABLA EN INTERACTIVA
$(document).ready(function() {
    $('#tablaPermisos').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.21/i18n/Spanish.json"
        }
    });
});
</script>

<?php 
$stmt->close();
include "includes/footer.php"; 
?>