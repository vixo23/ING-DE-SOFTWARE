<?php
session_start();
include "../conexion.php";

$id_empresa=$_SESSION['idempresa'];
// Lógica para eliminar un turno
if (!empty($_GET['action']) && $_GET['action'] == 'eliminar' && !empty($_GET['id'])) {
    $id_turno = $_GET['id'];
    $id_empresa = $_SESSION['idempresa'];

    // Verificar que el turno pertenece a la empresa del usuario actual para seguridad
    $query_check = mysqli_query($conexion, "SELECT * FROM turnos WHERE id_turnos = '$id_turno' AND id_empresa = '$id_empresa'");
    if (mysqli_num_rows($query_check) > 0) {
        $query_delete = mysqli_query($conexion, "DELETE FROM turnos WHERE id_turnos = '$id_turno'");
        if ($query_delete) {
            header("Location: listadoturnos.php?mensaje=Turno eliminado con éxito");
        } else {
            header("Location: listadoturnos.php?error=Error al eliminar el turno");
        }
    } else {
        header("Location: listadoturnos.php?error=No tiene permiso para eliminar este turno");
    }
    exit();
}

// Lógica para editar una jornada existente
if (isset($_POST['action']) && $_POST['action'] == 'editar') {
    if (empty($_POST['id_turno']) || empty($_POST['nombre_jornada']) || empty($_POST['hora_inicio_jornada']) || empty($_POST['hora_fin_jornada']) || empty($_POST['dia_entrada']) || empty($_POST['dia_salida'])) {
        header("Location: listadoturnos.php?error=Todos los campos son obligatorios para editar");
        exit();
    }

    $id_turno = $_POST['id_turno'];
    $nombre_jornada = mysqli_real_escape_string($conexion, $_POST['nombre_jornada']);
    $hora_inicio = mysqli_real_escape_string($conexion, $_POST['hora_inicio_jornada']);
    $hora_fin = mysqli_real_escape_string($conexion, $_POST['hora_fin_jornada']);
    $dia_entrada = mysqli_real_escape_string($conexion, $_POST['dia_entrada']);
    $dia_salida = mysqli_real_escape_string($conexion, $_POST['dia_salida']);
    $id_empresa = $_SESSION['idempresa'];

    // Verificar que el turno pertenece a la empresa del usuario actual para seguridad
    $query_check = mysqli_query($conexion, "SELECT * FROM turnos WHERE id_turnos = '$id_turno' AND id_empresa = '$id_empresa'");
    if (mysqli_num_rows($query_check) > 0) {
        $query_update = mysqli_query($conexion, "UPDATE turnos SET nombreTurno = '$nombre_jornada', horaEntrada = '$hora_inicio', horaSalida = '$hora_fin', diaEntrada = '$dia_entrada', diaSalida = '$dia_salida' WHERE id_turnos = '$id_turno'");
        if ($query_update) {
            header("Location: listadoturnos.php?mensaje=Turno actualizado con éxito");
        } else {
            $error_msg = mysqli_error($conexion);
            header("Location: listadoturnos.php?error=Error al actualizar el turno: " . urlencode($error_msg));
        }
    } else {
        header("Location: listadoturnos.php?error=No tiene permiso para editar este turno");
    }
    exit();
}
include "includes/header.php";
?>
<head>

<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.0/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</head>


<!-- Listado de Jornadas -->
<div class="card mt-4">
    <div class="card-header">
        <h2>Listado de Jornadas</h2>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-striped table-bordered" id="table_jornadas">
                <thead class="thead-dark">
                    <tr>
                        <th>Nombre Jornada</th>
                        <th>Día Inicio</th>                        
                        <th>Hora Inicio</th>
                        <th>Entrada Colacion</th>
                        <th>Salida Colacion</th>
                        <th>Hora Fin</th>
                        <th>Día Fin</th>
                        <th>Toleracia</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query_jornadas_list = mysqli_query($conexion, "SELECT * FROM turnos WHERE id_empresa = '$id_empresa'");
                    if ($query_jornadas_list && mysqli_num_rows($query_jornadas_list) > 0) {
                        while ($data_jornada = mysqli_fetch_assoc($query_jornadas_list)) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($data_jornada['nombreTurno']); ?></td>
                                <td><?php echo htmlspecialchars($data_jornada['diaEntrada']); ?></td>
                                <td><?php echo htmlspecialchars($data_jornada['horaEntrada']); ?></td>
                                <td><?php echo htmlspecialchars($data_jornada['entradaColacion']); ?></td>
                                <td><?php echo htmlspecialchars($data_jornada['salidaColacion']); ?></td>
                                <td><?php echo htmlspecialchars($data_jornada['horaSalida']); ?></td>
                                <td><?php echo htmlspecialchars($data_jornada['diaSalida']); ?></td>
                                <td><?php echo htmlspecialchars($data_jornada['tolerancia']); ?></td>
                                <td>
                                    <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#modalEditarJornada"
                                            data-id="<?php echo $data_jornada['id_turnos']; ?>"
                                            data-nombre="<?php echo htmlspecialchars($data_jornada['nombreTurno']); ?>"
                                            data-dia-entrada="<?php echo htmlspecialchars($data_jornada['diaEntrada']); ?>"
                                            data-hora-inicio="<?php echo htmlspecialchars($data_jornada['horaEntrada']); ?>"
                                            data-entrada-colacion="<?php echo htmlspecialchars($data_jornada['entradaColacion']); ?>"
                                            data-salida-colacion="<?php echo htmlspecialchars($data_jornada['salidaColacion']); ?>"
                                            data-hora-fin="<?php echo htmlspecialchars($data_jornada['horaSalida']); ?>"
                                            data-dia-salida="<?php echo htmlspecialchars($data_jornada['diaSalida']); ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="#" onclick="abrirModalEliminar(<?php echo $data_jornada['id_turnos']; ?>)" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php }
    } else {
        echo "<tr><td colspan='7' class='text-center'>No hay jornadas registradas</td></tr>";
    } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal para Editar Jornada -->
<div class="modal fade" id="modalEditarJornada" tabindex="-1" role="dialog" aria-labelledby="modalEditarLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditarLabel">Editar Jornada</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="listadoturnos.php" method="post" autocomplete="off">
                <input type="hidden" name="action" value="editar">
                <div class="modal-body">
                                        <input type="hidden" id="edit_id_turno" name="id_turno">
                    <div class="form-group">
                        <label for="edit_nombre_jornada">Nombre de la Jornada</label>
                        <input type="text" class="form-control" id="edit_nombre_jornada" name="nombre_jornada" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_dia_entrada">Día de Entrada</label>
                        <select class="form-control" id="edit_dia_entrada" name="dia_entrada" required>
                            <option value="Lunes">Lunes</option>
                            <option value="Martes">Martes</option>
                            <option value="Miércoles">Miércoles</option>
                            <option value="Jueves">Jueves</option>
                            <option value="Viernes">Viernes</option>
                            <option value="Sábado">Sábado</option>
                            <option value="Domingo">Domingo</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_hora_inicio_jornada">Hora de Inicio</label>
                        <input type="time" class="form-control" id="edit_hora_inicio_jornada" name="hora_inicio_jornada" required>
                    </div>
                    <div class="form-group">
                        <label for="entrada_colacion">Entrada Colacion</label>
                        <input type="time" class="form-control" id="entrada_colacion" name="entrada_colacion" required>
                    </div>
                    <div class="form-group">
                        <label for="salida_colacion">Salida Colacion</label>
                        <input type="time" class="form-control" id="salida_colacion" name="salida_colacion" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_hora_fin_jornada">Hora de Fin</label>
                        <input type="time" class="form-control" id="edit_hora_fin_jornada" name="hora_fin_jornada" required>
                    </div>
                
                    <div class="form-group">
                        <label for="edit_dia_salida">Día de Salida</label>
                        <select class="form-control" id="edit_dia_salida" name="dia_salida" required>
                            <option value="Lunes">Lunes</option>
                            <option value="Martes">Martes</option>
                            <option value="Miércoles">Miércoles</option>
                            <option value="Jueves">Jueves</option>
                            <option value="Viernes">Viernes</option>
                            <option value="Sábado">Sábado</option>
                            <option value="Domingo">Domingo</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Confirmación de Eliminación -->
<div class="modal fade" id="eliminarModal" tabindex="-1" role="dialog" aria-labelledby="eliminarModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eliminarModalLabel">Confirmar Eliminación</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                ¿Está seguro de que desea eliminar este turno?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <a href="#" id="confirmEliminarBtn" class="btn btn-danger">Eliminar</a>
            </div>
        </div>
    </div>
</div>


<!-- Modal de Alerta -->
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

<!-- jQuery y Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
function abrirModalEliminar(id) {
    document.getElementById('confirmEliminarBtn').href = 'listadoturnos.php?action=eliminar&id=' + id;
    $('#eliminarModal').modal('show');
}

$('#modalEditarJornada').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget); // Botón que activó el modal
    var id = button.data('id');
    var nombre = button.data('nombre');
    var diaEntrada = button.data('dia-entrada');
    var horaInicio = button.data('hora-inicio');
    var EntradaColacion = button.data('entrada-colacion');
    var SalidaColacion = button.data('salida-colacion');
    var horaFin = button.data('hora-fin');
    var diaSalida = button.data('dia-salida');

    var modal = $(this);
    modal.find('.modal-body #edit_id_turno').val(id);
    modal.find('.modal-body #edit_nombre_jornada').val(nombre);
    modal.find('.modal-body #edit_dia_entrada').val(diaEntrada);
    modal.find('.modal-body #edit_hora_inicio_jornada').val(horaInicio);
    modal.find('.modal-body #entrada_colacion').val(EntradaColacion);
    modal.find('.modal-body #salida_colacion').val(SalidaColacion);
    modal.find('.modal-body #edit_hora_fin_jornada').val(horaFin);
    modal.find('.modal-body #edit_dia_salida').val(diaSalida);
});
</script>

<?php include_once "includes/footer.php"; ?>