<?php
session_start();
include "../conexion.php";
include "includes/header.php";
$id_empresa = $_SESSION['idempresa'];

// Crear la tabla de turnos si no existe
$create_table_query = "CREATE TABLE IF NOT EXISTS turnos (
    id_turno INT AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT NOT NULL,
    nombre_turno VARCHAR(100) NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    status TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
mysqli_query($conexion, $create_table_query);

// Verificar si la columna id_usuario existe
$check_column_query = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'biometrico' AND TABLE_NAME = 'turnos' AND COLUMN_NAME = 'id_usuario'";
$result = mysqli_query($conexion, $check_column_query);

if (mysqli_num_rows($result) == 0) {
    // La columna no existe, la agregamos
    $alter_table_query = "ALTER TABLE turnos ADD COLUMN id_usuario INT NOT NULL, ADD FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuarios) ON DELETE CASCADE";
    mysqli_query($conexion, $alter_table_query);
}

?>

<div class="card">
    <div class="card-header">
        Creación de Turnos
    </div>
    <div class="card-body">
        <form action="guardar_turno.php" method="post" autocomplete="off" id="formulario_turno">
            <input type="hidden" id="id_turno" name="id_turno">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="id_usuario">Empleado</label>
                        <select id="id_usuario" class="form-control" name="id_usuario" required>
                            <option value="" disabled selected>Seleccione un empleado</option>
                            <?php
                            $query_usuarios = mysqli_query($conexion, "SELECT id_usuarios, nombres FROM usuarios WHERE id_empresa = '$id_empresa' AND status = 1 ORDER BY nombres");
                            while ($usuario = mysqli_fetch_assoc($query_usuarios)) {
                                echo "<option value='{$usuario['id_usuarios']}'>{$usuario['nombres']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="id_turno_base">Jornada</label>
                        <select id="id_turno_base" class="form-control" name="id_turno_base" required>
                            <option value="" disabled selected>Seleccione una jornada</option>
                            <?php
                            // Asumiendo que las jornadas base tienen un id_usuario=0 o un campo específico
                            $query_jornadas = mysqli_query($conexion, "SELECT id_turno, nombre_turno FROM turnos WHERE id_turno IN (1, 2) AND id_empresa = '$id_empresa' ORDER BY nombre_turno");
                            while ($jornada = mysqli_fetch_assoc($query_jornadas)) {
                                echo "<option value='{$jornada['id_turno']}'>{$jornada['nombre_turno']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>
            <input type="submit" value="Registrar" class="btn btn-primary" id="btnAccion">
            <input type="button" value="Nuevo" class="btn btn-success" id="btnNuevo" onclick="limpiarFormulario()">
        </form>
    </div>
</div>

<div class="table-responsive mt-4">
    <table class="table table-hover table-striped table-bordered" id="table_turnos">
        <thead class="thead-dark">
            <tr>
                <th>Empleado</th>
                <th>Turno</th>
                <th>Hora Inicio</th>
                <th>Hora Fin</th>
                <th>Estado</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $query_turnos_asignados = mysqli_query($conexion, "SELECT t.*, u.nombres FROM turnos t JOIN usuarios u ON t.id_usuario = u.id_usuarios WHERE t.id_empresa = '$id_empresa' AND t.id_usuario IS NOT NULL AND t.id_usuario != 0");
            while ($data = mysqli_fetch_assoc($query_turnos_asignados)) { ?>
                <tr>
                    <td><?php echo $data['nombres']; ?></td>
                    <td><?php echo $data['nombre_turno']; ?></td>
                    <td><?php echo $data['hora_inicio']; ?></td>
                    <td><?php echo $data['hora_fin']; ?></td>
                    <td>
                        <?php if ($data['status'] == 1) { ?>
                            <span class="badge badge-pill badge-success">Activo</span>
                        <?php } else { ?>
                            <span class="badge badge-pill badge-danger">Inactivo</span>
                        <?php } ?>
                    </td>
                    <td>
                        <!-- Aquí puedes agregar botones para editar o eliminar si lo necesitas -->
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<script>
function limpiarFormulario() {
    document.getElementById('formulario_turno').reset();
    document.getElementById('id_turno').value = '';
    document.getElementById('btnAccion').value = 'Registrar';
}
</script>

<?php include_once "includes/footer.php"; ?>
