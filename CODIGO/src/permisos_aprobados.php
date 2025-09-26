<?php
session_start();
include "../conexion.php";
include "includes/header.php";
$id_empresa=$_SESSION['idempresa'];
?>
<head>

<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.0/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</head>



<div class="table-responsive">
    <div class="card-header">
        <h2>Permisos Aprobados</h2>
    </div>
    <table class="table table-hover table-striped table-bordered mt-2" id="tbl">
        <thead class="thead-dark">
            <tr>
                <th>#</th>
                <th>Fecha</th>
                <th>Motivo</th>
                <th>Horas</th>
                <th>Fecha de inicio</th>
                <th>Fecha de fin</th>
                <th>Goce de sueldo</th>
                <!-- 🔹 Nueva columna para checkbox -->
                <th>Selección</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $query = mysqli_query($conexion, "SELECT * FROM permisos WHERE id_empresa='$id_empresa' ORDER BY observaciones");
            $result = mysqli_num_rows($query);
            if ($result > 0) {
                while ($data = mysqli_fetch_assoc($query)) {
                    $estado = ($data['goce'] == 1) ? '<span class="badge badge-success">Con goce</span>' : '<span class="badge badge-danger">Sin goce</span>';
                    ?>
                    <tr>
                        <td><?php echo $data['id_permisos']; ?></td>
                        <td><?php echo $data['creado']; ?></td>
                        <td><?php echo $data['observaciones']; ?></td>
                        <td><?php echo $data['total_horas']; ?></td>
                        <td><?php echo $data['fecha_ini']; ?></td>
                        <td><?php echo $data['fecha_fin']; ?></td>
                        <td><?php echo $estado; ?></td>
                        <!-- 🔹 Checkbox con ticket -->
                        <td>
                            <input type="checkbox" class="checkbox" id="permiso_<?php echo $data['id_permisos']; ?>">
                            <span id="seleccionado_<?php echo $data['id_permisos']; ?>"></span>
                        </td>
                    </tr>
            <?php }
            } ?>
        </tbody>
    </table>
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


// Función para limpiar el formulario
function limpiar() {
    $('#formulario')[0].reset(); // Restablecer el formulario
}

// Función para mostrar alerta
function mostrarAlerta(mensaje) {
    $('#alertMessage').text(mensaje); // Rellenar el mensaje de alerta
    $('#alertModal').modal('show'); // Mostrar el modal de alerta
}

// 🔹 Script de checkboxes
$(document).ready(function() {
    $(".checkbox").on("change", function() {
        const id = $(this).attr("id").split("_")[1];
        const span = $("#selecciondo_" + id);

        if ($(this).is(":checked")) {
            span.html("✔ Seleccionado").css("color", "green").css("font-weight", "bold");
        } else {
            span.html("");
        }
    });
});
</script>

<?php include_once "includes/footer.php"; ?>