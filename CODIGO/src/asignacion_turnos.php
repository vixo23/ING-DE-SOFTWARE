<?php
session_start();
include "../conexion.php";
include "includes/header.php";

// Reemplazado $_SESSION['idempresa'] por 'id_empresa' para ser coherente con la BD
$id_empresa = $_SESSION['idempresa'];
?>
<head>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>



<div class="table-responsive">
    <div class="card-header">
        <h2>Asignacion de Turnos</h2>
    </div>
    <table class="table table-hover table-striped table-bordered mt-2" id="tbl">
        <thead class="thead-dark">
            <tr>
                <th>RUT</th>
                <th>Colaboradores</th>
                <th>Turnos Asignado</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Consulta para unir las tablas y mostrar el nombre del turno
            $query = mysqli_query($conexion, "SELECT u.rut,u.id_usuarios, u.nombres, u.status, t.nombreTurno, t.id_turnos 
                                              FROM usuarios u 
                                              LEFT JOIN turnos t ON u.turnos_id_turnos = t.id_turnos 
                                              WHERE u.id_empresa = '$id_empresa' 
                                              ORDER BY u.nombres");
            
            $result = mysqli_num_rows($query);
            if ($result > 0) {
                while ($data = mysqli_fetch_assoc($query)) {
                    $estado = ($data['status'] == 1) ? '<span class="badge badge-success">Activo</span>' : '<span class="badge badge-danger">Inactivo</span>';
                    ?>
                    <tr>
                        <td><?php echo $data['rut']; ?></td>
                        <td><?php echo $data['nombres']; ?></td>
                        <td><?php echo $data['nombreTurno'] ? $data['nombreTurno'] : 'No asignado'; ?></td>
                        <td><?php echo $estado; ?></td>
                        <td>
                            <a href="#" onclick="AsignarTurno(<?php echo $data['id_usuarios']; ?>, '<?php echo $data['id_turnos']; ?>')" class="btn btn-success">
                                <i class=''></i> ASIGNAR
                        </td>
                    </tr>
            <?php }
            } ?>
        </tbody>
    </table>
</div>

<div class="modal fade" id="editarModal" tabindex="-1" role="dialog" aria-labelledby="editarModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editarModalLabel">Asignar Turno</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="actualizar_asignacion_turnos.php" method="post" id="formEditar">
                    <input type="hidden" id="idUsuario" name="id_usuario">
                    <div class="form-group">
                        <label for="turnoEditar">Turno</label>
                        <select class="form-control" id="turnoEditar" name="id_turno" required>
                            <option value="">Seleccione un turno</option>
                            <?php
                                 
                                // Consulta SQL para obtener los turnos de la empresa
                                $sql_turnos = "SELECT id_turnos, nombreTurno FROM turnos WHERE id_empresa = '$id_empresa' AND status = 1 ORDER BY nombreTurno ASC";
                                $resultado_turnos = mysqli_query($conexion, $sql_turnos);

                                if (mysqli_num_rows($resultado_turnos) > 0) {
                                    while($fila_turno = mysqli_fetch_assoc($resultado_turnos)) {
                                        echo '<option value="' . $fila_turno["id_turnos"] . '">' . htmlspecialchars($fila_turno["nombreTurno"]) . '</option>';
                                    }
                                } else {
                                    echo '<option value="">No hay turnos disponibles</option>';
                                }
                                // No cerrar la conexión aquí si se usa en otros lugares
                            ?>
                        </select>
                    </div>
                    <input type="submit" value="Actualizar" class="btn btn-primary">
                </form>
            </div>
        </div>
    </div>
</div>



<div class="modal fade" id="alertModal" tabindex="-1" role="dialog" aria-labelledby="alertModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="alertModalLabel">Alerta</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="alertMessage">Este es un mensaje de alerta.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">Aceptar</button>
            </div>
        </div>
    </div>
</div>

<script>
// La función `editarturno` ahora recibe dos parámetros: el ID del usuario y el ID del turno
function AsignarTurno(idUsuario, idTurno) {
    // Establecer el ID del usuario en el campo oculto
    $('#idUsuario').val(idUsuario);
    // Seleccionar el turno actual en el combobox
    $('#turnoEditar').val(idTurno);
    // Abrir el modal
    $('#editarModal').modal('show'); 
}

// Función para abrir el modal de confirmación
let tipocontratoId;
function abrirModalConfirmacion(id) {
    tipocontratoId = id;
    $('#confirmModal').modal('show');
}




function mostrarAlerta(mensaje) {
    $('#alertMessage').text(mensaje);
    $('#alertModal').modal('show');
}
</script>

<?php include_once "includes/footer.php"; ?>