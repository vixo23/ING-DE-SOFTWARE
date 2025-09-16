<?php
session_start();
include "../conexion.php";
include "includes/header.php";

// Empresa de sesión
$id_empresa = isset($_SESSION['idempresa']) ? (int)$_SESSION['idempresa'] : 0;

// Procesamiento del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nombre'], $_POST['estado'])) {

    $descripcion = mysqli_real_escape_string($conexion, trim($_POST['nombre']));
    $estado = (int)$_POST['estado'];

    if ($id_empresa <= 0) {
        echo '<div class="alert alert-danger">Error: Empresa no válida en la sesión.</div>';
    } elseif (empty($descripcion)) {
        echo '<div class="alert alert-warning">Debe ingresar una descripción válida.</div>';
    } else {
        $sql = "INSERT INTO tipo_vacaciones (descripcion, status, id_empresas) VALUES ('$descripcion', $estado, $id_empresa)";
        $query_insert = mysqli_query($conexion, $sql);

        if ($query_insert) {
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    Tipo de vacación registrado correctamente.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                  </div>';
        } else {
            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    Error al registrar el tipo de vacación: ' . mysqli_error($conexion) . '
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                  </div>';
        }
    }
}

// Función para obtener todos los tipos de vacaciones
function getTiposVacaciones($conexion, $id_empresa) {
    $html = '';
    $query = mysqli_query($conexion, "SELECT * FROM tipo_vacaciones WHERE id_empresas='$id_empresa' ORDER BY descripcion");
    while ($data = mysqli_fetch_assoc($query)) {
        $estado = ($data['status'] == 1) ? '<span class="badge badge-success">Activo</span>' : '<span class="badge badge-danger">Inactivo</span>';
        $html .= "<tr>
                    <td>{$data['id_tipovacacion']}</td>
                    <td>{$data['descripcion']}</td>
                    <td>{$estado}</td>
                  </tr>";
    }
    return $html;
}
?>

<div class="container mt-4">
    <h2>Tipo de Vacaciones</h2>
    <form id="formulario" method="post">
        <div class="form-group">
            <label for="nombre">Descripción</label>
            <input type="text" name="nombre" id="nombre" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="estado">Estado</label>
            <select name="estado" id="estado" class="form-control" required>
                <option value="1">Activo</option>
                <option value="0">Inactivo</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary mt-2">Registrar</button>
        <button type="button" class="btn btn-info mt-2" data-toggle="modal" data-target="#modalTiposVacaciones">Ver Todos</button>
    </form>
</div>

<!-- Modal para mostrar todos los tipos de vacaciones -->
<div class="modal fade" id="modalTiposVacaciones" tabindex="-1" role="dialog" aria-labelledby="modalTiposVacacionesLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTiposVacacionesLabel">Tipos de Vacaciones</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <table class="table table-bordered table-striped" id="tablaTipos">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Descripción</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php echo getTiposVacaciones($conexion, $id_empresa); ?>
            </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
$(document).ready(function(){

    // Interceptar el envío del formulario
    $('#formulario').submit(function(e){
        e.preventDefault(); // Evitar recarga

        $.ajax({
            type: 'POST',
            url: '', // mismo archivo
            data: $(this).serialize(),
            success: function(){
                // Limpiar formulario
                $('#formulario')[0].reset();

                // Recargar tabla dentro del modal
                $.ajax({
                    url: 'tipo_vacaciones.php',
                    type: 'GET',
                    data: {ajax: 1},
                    success: function(data){
                        var html = $(data).find('#tablaTipos tbody').html();
                        $('#tablaTipos tbody').html(html);
                    }
                });
            }
        });
    });

});
</script>

<?php include "includes/footer.php"; ?>