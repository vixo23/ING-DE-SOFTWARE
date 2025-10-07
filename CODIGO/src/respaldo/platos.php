<?php
session_start();
if ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 2|| $_SESSION['rol'] == 3) {
    include "../conexion.php";

    // Cambiar estado del plato
if (isset($_POST['guardar_cambio_estado'])) {
    $id_plato = isset($_POST['id_plato']) ? $_POST['id_plato'] : '';
    $nuevo_estado = isset($_POST['nuevo_estado']) ? $_POST['nuevo_estado'] : '';

// Depuración
    error_log("ID Plato: $id_plato, Nuevo Estado: $nuevo_estado"); // Verifica en el log

    // Verifica si $id_plato y $nuevo_estado no están vacíos
    if (!empty($id_plato) && ($nuevo_estado === '0' || $nuevo_estado === '1')) {
        $query = "UPDATE platos SET estado = '$nuevo_estado' WHERE id = '$id_plato'";
        $resultado = mysqli_query($conexion, $query);

        if ($resultado) {
            echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Estado actualizado',
                    text: 'El estado del plato ha sido actualizado correctamente.',
                    confirmButtonText: 'Aceptar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'platos.php'; // Redirige a platos.php
                    }
                });
            </script>";
        } else {
            echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Hubo un problema al actualizar el estado: " . mysqli_error($conexion) . ".',
                    confirmButtonText: 'Aceptar'
                });
            </script>";
        }
    } else {
        echo "<script>
            Swal.fire({
                icon: 'warning',
                title: 'Error',
                text: 'Por favor, complete todos los campos requeridos.',
                confirmButtonText: 'Aceptar'
            });
        </script>";
    }
}

    if (!empty($_POST)) {
        $id = $_POST['id'];
        $plato = $_POST['plato'];
        $precio = $_POST['precio'];
        $id_impresora = $_POST['id_impresora'];
        $foto_actual = $_POST['foto_actual'];
        $foto = $_FILES['foto'];
        $fecha = date('YmdHis');

        if (empty($plato) || empty($precio) || $precio < 0 || empty($id_impresora)) {
            // Se eliminan las alertas
            echo "<script>
                Swal.fire({
                    icon: 'warning',
                    title: 'Por favor, complete todos los campos requeridos.',
                    showConfirmButton: false,
                    timer: 1500
                });
            </script>";
        } else {
            $nombre = null;
            if (!empty($foto['name'])) {
                $nombre = '../assets/img/platos/' . $fecha . '.jpg';
            } else if (!empty($foto_actual) && empty($foto['name'])) {
                $nombre = $foto_actual;
            }

            if (empty($id)) {
                // Insertar nuevo plato
                $query = mysqli_query($conexion, "SELECT * FROM platos WHERE nombre = '$plato' AND estado = 1");
                $result = mysqli_fetch_array($query);
                if ($result > 0) {
                    echo "<script>
                        Swal.fire({
                            icon: 'warning',
                            title: 'El plato ya existe.',
                            showConfirmButton: false,
                            timer: 1500
                        });
                    </script>";
                } else {
                    $query_insert = mysqli_query($conexion, "INSERT INTO platos (nombre, precio, imagen, id_impresora) VALUES ('$plato', '$precio', '$nombre', '$id_impresora')");
                    if ($query_insert) {
                        if (!empty($foto['name'])) {
                            move_uploaded_file($foto['tmp_name'], $nombre);
                        }
                        echo "<script>
                            Swal.fire({
                                icon: 'success',
                                title: 'Plato registrado correctamente.',
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                window.location='platos.php';
                            });
                        </script>";
                    } else {
                        echo "<script>
                            Swal.fire({
                                icon: 'error',
                                title: 'Error al registrar el plato.',
                                showConfirmButton: false,
                                timer: 1500
                            });
                        </script>";
                    }
                }
            } else {
                // Actualizar plato existente
                $query_update = mysqli_query($conexion, "UPDATE platos SET nombre = '$plato', precio = $precio, imagen = '$nombre', id_impresora = '$id_impresora' WHERE id = $id");
                if ($query_update) {
                    if (!empty($foto['name'])) {
                        move_uploaded_file($foto['tmp_name'], $nombre);
                    }
                    echo "<script>
                        Swal.fire({
                            icon: 'success',
                            title: 'Plato actualizado correctamente.',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            window.location='platos.php';
                        });
                    </script>";
                } else {
                    echo "<script>
                        Swal.fire({
                            icon: 'error',
                            title: 'Error al actualizar el plato.',
                            showConfirmButton: false,
                            timer: 1500
                        });
                    </script>";
                }
            }
        }
    }

    // Obtener impresoras con estado = 1
    $sql_impresoras = "SELECT * FROM impresoras WHERE estado = 1";
    $query_impresoras = mysqli_query($conexion, $sql_impresoras);

    // Obtener los datos del plato si se está editando
    $plato_data = null;
    if (isset($_GET['id'])) {
        $id_plato = $_GET['id'];
        $sql_plato = "SELECT platos.*, impresoras.nombre as impresora FROM platos INNER JOIN impresoras ON impresoras.id_impresora = platos.id_impresora WHERE platos.id = $id_plato";
        $query_plato = mysqli_query($conexion, $sql_plato);
        $plato_data = mysqli_fetch_assoc($query_plato);
    }
}
include_once "includes/header.php";
?>
<style>
    .table-header {
        background-color: black;
        color: white; /* Cambia el color del texto a blanco */
    }
</style>

<div class="card shadow-lg">
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form action="" method="post" autocomplete="off" id="formulario" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <input type="hidden" id="id" name="id" value="<?php echo isset($plato_data['id']) ? $plato_data['id'] : ''; ?>">
                                        <input type="hidden" id="foto_actual" name="foto_actual" value="<?php echo isset($plato_data['imagen']) ? $plato_data['imagen'] : ''; ?>">
                                        <label for="plato" class="text-dark font-weight-bold">Plato</label>
                                        <input type="text" placeholder="Ingrese nombre del plato" name="plato" id="plato" class="form-control" value="<?php echo isset($plato_data['nombre']) ? $plato_data['nombre'] : ''; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="precio" class="text-dark font-weight-bold">Precio</label>
                                        <input type="text" placeholder="Ingrese precio" class="form-control" name="precio" id="precio" value="<?php echo isset($plato_data['precio']) ? $plato_data['precio'] : ''; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="foto" class="text-dark font-weight-bold">Foto (512px - 512px)</label>
                                        <input type="file" class="form-control" name="foto" id="foto">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="id_impresora" class="text-dark font-weight-bold">Impresora</label>
                                        <select name="id_impresora" id="id_impresora" class="form-control" required>
                                            <option value="">Seleccione una impresora</option>
                                            <?php while ($impresora = mysqli_fetch_assoc($query_impresoras)) { ?>
                                                <option value="<?php echo $impresora['id_impresora']; ?>" <?php echo (isset($plato_data['id_impresora']) && $plato_data['id_impresora'] == $impresora['id_impresora']) ? 'selected' : ''; ?>>
                                                    <?php echo $impresora['nombre']; ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3 form-group">
                                    <label for="">Acciones</label> <br>
                                    <input type="submit" value="Registrar" class="btn btn-primary" id="btnAccion">
                                    <input type="button" value="Nuevo" onclick="limpiar()" class="btn btn-success" id="btnNuevo">
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="col-md-12">
                    <div class="table-responsive">
                    <table class="table table-striped table-bordered" id="tbl">
                            <thead>
                                <tr class="table-header"> <!-- Agrega la clase aquí -->
                                    <th>#</th>
                                    <th>Plato</th>
                                    <th>Precio</th>
                                    <th>Impresora</th>
                                    <th>Imagen</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sqlplatos = "SELECT platos.*, impresoras.nombre as impresora FROM platos INNER JOIN impresoras ON impresoras.id_impresora = platos.id_impresora";
                                $query_platos = mysqli_query($conexion, $sqlplatos);
                                while ($plato = mysqli_fetch_assoc($query_platos)) {
                                    ?>
                                    <tr>
                                        <td><?php echo $plato['id']; ?></td>
                                        <td><?php echo $plato['nombre']; ?></td>
                                        <td><?php echo $plato['precio']; ?></td>
                                        <td><?php echo $plato['impresora']; ?></td>
                                        <td><img src="<?php echo $plato['imagen']; ?>" alt="" width="60"></td>
                                        <td><?php echo $plato['estado'] == 1 ? 'Activo' : 'Inactivo'; ?></td>
                                        <td>
                                            <a href="platos.php?id=<?php echo $plato['id']; ?>" class="btn btn-info btn-sm" style="width: 150px;">Editar</a>
                                            <a href="#" onclick="abrirModalCambioEstado(<?php echo $plato['id']; ?>, <?php echo $plato['estado']; ?>)" class="btn btn-warning btn-sm" style="width: 150px;">
                                                <i class='fas fa-exchange-alt'></i> Cambiar Estado
                                            </a>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function limpiar() {
        document.getElementById("id").value = "";
        document.getElementById("plato").value = "";
        document.getElementById("precio").value = "";
        document.getElementById("foto").value = "";
        document.getElementById("id_impresora").value = "";
        document.getElementById("btnAccion").value = "Registrar";
    }

    document.querySelectorAll("#btnEliminar").forEach(function (button) {
        button.addEventListener("click", function () {
            const id = this.getAttribute("data-id");
            Swal.fire({
                title: '¿Está seguro de que desea eliminar este plato?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminarlo!'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('id_eliminar', id);
                    formData.append('accion', 'eliminar');

                    fetch('', {
                        method: 'POST',
                        body: formData
                    }).then(response => response.text())
                      .then(data => {
                          Swal.fire({
                              title: 'Eliminado!',
                              text: 'Plato eliminado correctamente.',
                              icon: 'success',
                              timer: 1500
                          }).then(() => {
                              location.reload();
                          });
                      });
                }
            });
        });
    });
</script>

<!-- Modal para confirmar el cambio de estado -->
<div class="modal fade" id="modalCambioEstado" tabindex="-1" aria-labelledby="modalCambioEstadoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCambioEstadoLabel">Confirmar Cambio de Estado</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                ¿Está seguro de que desea cambiar el estado de este plato?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnConfirmarCambioEstado">Confirmar</button>
            </div>
        </div>
    </div>
</div><?php include_once "includes/footer.php"; ?>


<script>
    let idPlato; // Variable para almacenar el ID del plato a cambiar de estado
    let estadoActual; // Variable para almacenar el estado actual del plato

    // Función para abrir el modal de cambio de estado
    function abrirModalCambioEstado(id, estado) {
        idPlato = id; // Guarda el ID del plato
        estadoActual = estado; // Guarda el estado actual del plato
        $('#modalCambioEstado').modal('show'); // Muestra el modal
    }

    
    document.getElementById('btnConfirmarCambioEstado').addEventListener('click', function () {
    const nuevoEstado = estadoActual === 1 ? 0 : 1; // Cambia el estado (de 1 a 0 o de 0 a 1)

    console.log(`Cambiando estado del plato ${idPlato} de ${estadoActual} a ${nuevoEstado}`); // Verifica los valores

    $.post('', {
        guardar_cambio_estado: true,
        id_plato: idPlato,
        nuevo_estado: nuevoEstado
    }, function (response) {
        $('#modalCambioEstado').modal('hide'); // Cierra el modal
        location.reload(); // Recarga la página
    });
});


</script>



