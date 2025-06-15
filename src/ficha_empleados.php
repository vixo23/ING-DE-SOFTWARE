<?php
session_start();
include "../conexion.php";
include "includes/header.php";
$id_empresa = $_SESSION['idempresa'];
?>

<head>
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.0/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>

<div class="table-responsive">
    <table class="table table-hover table-striped table-bordered mt-2" id="tbl">
        <thead class="thead-dark">
            <tr>
                <th>#</th>
                <th>Nombre</th>
                <th>Cargo</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $query = mysqli_query($conexion, "SELECT * FROM usuarios WHERE id_empresa='$id_empresa' ORDER BY nombres");
            $result = mysqli_num_rows($query);
            if ($result > 0) {
                while ($data = mysqli_fetch_assoc($query)) {
                    $estado = ($data['status'] == 1) ? '<span class="badge badge-success">Activo</span>' : '<span class="badge badge-danger">Inactivo</span>';
                    ?>
                    <tr>
                        <td><?php echo $data['id_usuarios']; ?></td>
                        <td><?php echo $data['nombres']; ?></td>
                        <td><?php echo $data['cargo']; ?></td>
                        <td><?php echo $estado; ?></td>
                        <td>
                            <a href="#" onclick="editartipocontrato(
                              <?php echo $data['id_usuarios']; ?>,
                              '<?php echo addslashes($data['nombres']); ?>',
                              '<?php echo addslashes($data['apellido1']); ?>',
                              '<?php echo addslashes($data['apellido2']); ?>',
                              '<?php echo addslashes($data['email']); ?>',
                              '<?php echo addslashes($data['tipoContrato']); ?>',
                              '<?php echo addslashes($data['cargo']); ?>',
                              '<?php echo addslashes($data['telefono']); ?>',
                              '<?php echo addslashes($data['direccion']); ?>',
                              '<?php echo addslashes($data['fechaCreacion']); ?>'
                            )" class="btn btn-success">
                                <i class='fas fa-id-card'></i>
                            </a>
                        </td>
                    </tr>
            <?php }
            } ?>
        </tbody>
    </table>
</div>

<!-- Modal para mostrar ficha del empleado -->
<div class="modal fade" id="editarModal" tabindex="-1" role="dialog" aria-labelledby="editarModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editarModalLabel">Ficha Empleado</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p><strong>ID:</strong> <span id="id_usuarios"></span></p>
        <p><strong>Nombre:</strong> <span id="nombres"></span></p>
        <p><strong>Apellido Paterno:</strong> <span id="apellido1"></span></p>
        <p><strong>Apellido Materno:</strong> <span id="apellido2"></span></p>
        <p><strong>Correo:</strong> <span id="email"></span></p>
        <p><strong>Tipo Contrato:</strong> <span id="tipoContrato"></span></p>
        <p><strong>Cargo:</strong> <span id="cargo"></span></p>
        <p><strong>Teléfono:</strong> <span id="telefono"></span></p>
        <p><strong>Dirección:</strong> <span id="direccion"></span></p>
        <p><strong>Fecha Ingreso:</strong> <span id="fecha_ingreso"></span></p>
      </div>
    </div>
  </div>
</div>

<script>
function editartipocontrato(id, nombre, apellido1, apellido2, correo, tipoContrato, cargo, telefono, direccion, fechaIngreso) {
    document.getElementById('id_usuarios').textContent = id;
    document.getElementById('nombres').textContent = nombre;
    document.getElementById('apellido1').textContent = apellido1;
    document.getElementById('apellido2').textContent = apellido2;
    document.getElementById('email').textContent = correo;
    document.getElementById('tipoContrato').textContent = tipoContrato;
    document.getElementById('cargo').textContent = cargo;
    document.getElementById('telefono').textContent = telefono;
    document.getElementById('direccion').textContent = direccion;
    document.getElementById('fecha_ingreso').textContent = fechaIngreso;
    $('#editarModal').modal('show');
}

</script>

<?php include_once "includes/footer.php"; ?>