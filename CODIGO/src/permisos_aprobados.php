<?php
ob_start(); // Inicia el buffer de salida
session_start();
include "../conexion.php";

// --- Variables de sesión ---
$id_empresa = isset($_SESSION['idempresa']) ? (int)$_SESSION['idempresa'] : 0;

include "includes/header.php";

?>

<head>
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.0/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>


<div class="card-header">
        <h2>Permisos Aprobados</h2>
    </div>
<!-- Tabla de permisos -->
<div class="table-responsive mt-4">
    <table class="table table-hover table-striped table-bordered">
        <thead class="thead-dark">
            <tr>
                <th>Usuario</th>
                <th>RUT</th>
                <th>Horas</th>
                <th>Fecha</th>
                <th>Motivo</th>
                <th>Detalle</th>
                <th>Goce</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sqlList = "SELECT p.*, m.descripcion AS motivo_nombre, u.nombres AS usuario_nombre, CONCAT(u.rut, '-', u.digitorut) AS rut_completo 
                        FROM permisos p
                        LEFT JOIN motivos_permisos m ON p.id_motivo = m.id_motivo
                        LEFT JOIN usuarios u ON p.id_usuario = u.id_usuarios
                        WHERE p.id_empresa = '$id_empresa'
                        ORDER BY p.id_permisos DESC";
            $query = mysqli_query($conexion, $sqlList);
            if ($query && mysqli_num_rows($query) > 0) {
                while ($data = mysqli_fetch_assoc($query)) {
                    $estadoBadge = ($data['status'] == 1)
                        ? '<span class="badge badge-success">Activo</span>'
                        : '<span class="badge badge-danger">Inactivo</span>';
                    echo '<tr>
                            <td>'.$data['usuario_nombre'].'</td>
                            <td>'.$data['rut_completo'].'</td>
                            <td>'.$data['total_horas'].'</td>
                            <td>'.$data['fecha_ini'].'</td>
                            <td>'.$data['motivo_nombre'].'</td>
                            <td>'.$data['observaciones'].'</td>
                            <td>'.($data['goce'] ? "Sí" : "No").'</td>
                            <td>'.$estadoBadge.'</td>
                          </tr>';
                }
            }
            ?>
        </tbody>
    </table>
</div>

<script>
document.getElementById('id_usuario').addEventListener('change', function() {
    var selected = this.options[this.selectedIndex];
    document.getElementById('nombre_persona').value = selected.getAttribute('data-nombre') || '';
    document.getElementById('apellido').value = selected.getAttribute('data-apellido') || '';
    document.getElementById('rut').value = selected.getAttribute('data-rut') || '';
});

function limpiar() {
  $('#formulario')[0].reset();
}
</script>

<?php 
include_once "includes/footer.php"; 
ob_end_flush(); // Libera el buffer
?>