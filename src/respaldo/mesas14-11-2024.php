<?php
session_start();
ob_start(); // Inicia el búfer de salida
include "../conexion.php";

// Incluir SweetAlert2
echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';

// Verifica que el usuario tenga los permisos necesarios
if ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 2 || $_SESSION['rol'] == 3) {
    $id = $_GET['id_sala'];
    $mesas = $_GET['mesas'];

    // Obtener el nombre del salón
    $salon = mysqli_query($conexion, "SELECT nombre FROM salas WHERE id = $id");
    if ($salon && mysqli_num_rows($salon) > 0) {
        $results = mysqli_fetch_assoc($salon);
        $nombre_salon = $results['nombre'];
    } else {
        // Mensaje de error si no se encuentra el salón
        $error_message = "Salón no encontrado";
        echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: '¡Error!',
                    text: '$error_message',
                });
              </script>";
    }

    include_once "includes/header.php"; // Incluir encabezado

    // Verificación de contraseña en el formulario
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $password = mysqli_real_escape_string($conexion, $_POST['password']);
        $mesa = $_POST['mesa']; // Captura la mesa seleccionada enviada por el formulario
        
        // Verificar si la contraseña existe en la base de datos
        $query = mysqli_query($conexion, "SELECT * FROM garzones WHERE Contrasenia = '$password'");
        $garzon = mysqli_fetch_assoc($query);
        
        if (mysqli_num_rows($query) > 0) {
            $_SESSION['password'] = true;
            $_SESSION['garzon_name'] = $garzon['Nombre'];
            $_SESSION['garzon_color'] = $garzon['Color_Garzon'];
            $_SESSION['garzon_id'] = $garzon['Id_Garzones'];  // Esto es importante

            // Verificar si la mesa ya está ocupada por otro garzón
            $consulta = mysqli_query($conexion, "SELECT Color_Garzon FROM pedidos WHERE id_sala = $id AND num_mesa = $mesa AND estado = 'PENDIENTE'");
            $mesaOcupada = mysqli_fetch_assoc($consulta);

            // Si la mesa está ocupada, comprobar si el garzón tiene el mismo color
            if (!empty($mesaOcupada)) {
                if ($mesaOcupada['Color_Garzon'] !== $_SESSION['garzon_color']) {
                    $error_message = "La mesa está siendo atendida por otro garzón.";
                    echo "<script>
                            Swal.fire({
                                icon: 'error',
                                title: '¡Error!',
                                text: '$error_message',
                            });
                          </script>";
                } else {
                    // Redirigir a la página de pedido si el garzón tiene el color correcto
                    header("Location: pedido.php?id_sala=$id&mesas=$mesas&mesa=$mesa");
                    exit();
                }
            } else {
                // Si la mesa no está ocupada, redirigir a la página de pedido
                header("Location: pedido.php?id_sala=$id&mesas=$mesas&mesa=$mesa");
                exit();
            }
        } else {
            // Mensaje de error si la contraseña es incorrecta
            $error_message = "Contraseña incorrecta. Intenta de nuevo.";
            echo "<script>
                    Swal.fire({
                        icon: 'error',
                        title: '¡Error!',
                        text: '$error_message',
                    });
                  </script>";
        }
    }
    // Verificación de contraseña en el formulario "Finalizar"
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password2'])) {
        $password = mysqli_real_escape_string($conexion, $_POST['password2']);
        $mesa = $_POST['mesa']; // Captura la mesa seleccionada enviada por el formulario
        
        // Verificar si la contraseña existe en la base de datos
        $query = mysqli_query($conexion, "SELECT * FROM garzones WHERE Contrasenia = '$password'");
        $garzon = mysqli_fetch_assoc($query);
        
        if (mysqli_num_rows($query) > 0) {
            $_SESSION['password'] = true;
            $_SESSION['garzon_name'] = $garzon['Nombre'];
            $_SESSION['garzon_color'] = $garzon['Color_Garzon'];
            $_SESSION['garzon_id'] = $garzon['Id_Garzones'];  // Esto es importante

            // Redirigir a la página de finalizar si la contraseña es correcta
            header("Location: finalizar.php?id_sala=$id&mesas=$mesas&mesa=$mesa");
            exit();
        } else {
            // Mensaje de error si la contraseña es incorrecta
            $error_message = "Contraseña incorrecta. Intenta de nuevo.";
            echo "<script>
                    Swal.fire({
                        icon: 'error',
                        title: '¡Error!',
                        text: '$error_message',
                    });
                  </script>";
        }
    }

?>



<head>
    <!-- Incluir SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Incluir jQuery (requerido por Bootstrap 4) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Incluir CSS de Bootstrap 4 -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Incluir JS de Bootstrap 4 (con jQuery) -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</head>

<?php if (!isset($_SESSION['password']) || !$_SESSION['password']) { ?>
    <div class="modal fade" id="modalPassword" tabindex="-1" aria-labelledby="modalPasswordLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title w-100 text-center" id="modalPasswordLabel">Verificar Garzón</h5>
                </div>
                <div class="modal-body">
                    <form method="POST">
                        <!-- Campo oculto para el número de mesa -->
                        <input type="hidden" id="mesa" name="mesa">
                        
                        <!-- Contraseña Input -->
                        <div class="mb-3 text-center">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="text" class="form-control w-50 mx-auto d-block text-center" id="password" name="password" readonly required>
                        </div>

                        <!-- Botones para ingresar los dígitos -->
                        <div class="row justify-content-center">
                            <?php for ($i = 1; $i <= 9; $i++) { ?>
                                <div class="col-4 mb-2">
                                    <button type="button" class="btn btn-light w-100 btn-lg" onclick="addDigit('<?php echo $i; ?>')"><?php echo $i; ?></button>
                                </div>
                            <?php } ?>
                            <div class="col-4 mb-2">
                                <button type="button" class="btn btn-light w-100 btn-lg" onclick="addDigit('0')">0</button>
                            </div>
                            <div class="col-4 mb-2">
                                <button type="button" class="btn btn-danger w-100 btn-lg" onclick="clearPassword()">C</button>
                            </div>
                        </div>

                        <!-- Botón de verificación -->
                        <div class="mb-3 mt-3">
                            <button type="submit" class="btn btn-primary w-100 btn-lg">Verificar</button>
                        </div>

                        <!-- Mensaje de error -->
                        <?php if (isset($error_message)) { ?>
                            <div class="text-danger text-center"><?php echo $error_message; ?></div>
                        <?php } ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php } ?>

<!-- Formulario Finalizar-->

<?php if (!isset($_SESSION['password']) || !$_SESSION['password']) { ?>
    <div class="modal fade" id="modalPassword2" tabindex="-1" aria-labelledby="modalPasswordLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title w-100 text-center" id="modalPasswordLabel">Verificar Garzón</h5>
                </div>
                <div class="modal-body">
                    <form method="POST">
                        <!-- Campo oculto para el número de mesa -->
                        <input type="hidden" id="mesa" name="mesa">
                        
                        <!-- Contraseña Input -->
                        <div class="mb-3 text-center">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="text" class="form-control w-50 mx-auto d-block text-center" id="password2" name="password2" readonly required>
                        </div>

                        <!-- Botones para ingresar los dígitos -->
                        <div class="row justify-content-center">
                            <?php for ($i = 1; $i <= 9; $i++) { ?>
                                <div class="col-4 mb-2">
                                    <button type="button" class="btn btn-light w-100 btn-lg" onclick="addDigit2('<?php echo $i; ?>')"><?php echo $i; ?></button>
                                </div>
                            <?php } ?>
                            <div class="col-4 mb-2">
                                <button type="button" class="btn btn-light w-100 btn-lg" onclick="addDigit2('0')">0</button>
                            </div>
                            <div class="col-4 mb-2">
                                <button type="button" class="btn btn-danger w-100 btn-lg" onclick="clearPassword2()">C</button>
                            </div>
                        </div>

                        <!-- Botón de verificación -->
                        <div class="mb-3 mt-3">
                            <button type="submit" class="btn btn-primary w-100 btn-lg">Verificar</button>
                        </div>

                        <!-- Mensaje de error -->
                        <?php if (isset($error_message)) { ?>
                            <div class="text-danger text-center"><?php echo $error_message; ?></div>
                        <?php } ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php } ?>


<!-- Mostrar el salón y las mesas -->

<div class="card">
    <div class="card-header text-center">
        <h4 class="my-3 text-center"><span class="badge badge-secondary">Salón <?php echo $nombre_salon; ?></span></h4>
    </div>
    <div class="card-header text-center">
        <a class="btn btn-info" href="index.php">Volver a Salones</a>
    </div>
    <div class="card-body">
        <div class="row">
            <?php
            $query = mysqli_query($conexion, "SELECT * FROM salas WHERE id = $id");
            $result = mysqli_num_rows($query);
            if ($result > 0) {
                $data = mysqli_fetch_assoc($query);
                if ($data['mesas'] == $mesas) {
                    $item = 1;
                    for ($i = 0; $i < $mesas; $i++) {
                        // Consulta para obtener pedido pendiente (si existe)
                        $consulta = mysqli_query($conexion, "SELECT Color_Garzon, id_garzon, estado FROM pedidos WHERE id_sala = $id AND num_mesa = $item AND estado = 'PENDIENTE'");
                        $resultPedido = mysqli_fetch_assoc($consulta);

                        // Asignar color verde solo si no hay un pedido pendiente
                        $mesaColor = empty($resultPedido) ? 'bg-success' : '';
                        $colorStyle = empty($resultPedido) ? '' : 'background-color: ' . $resultPedido['Color_Garzon'] . ';';
            ?>
                        <div class="col-md-3">
                            <div class="card card-widget widget-user">
                                <div class="widget-user-header <?php echo $mesaColor; ?>" style="<?php echo $colorStyle; ?>">
                                    <h3 class="widget-user-username">MESA</h3>
                                    <h5 class="widget-user-desc"><?php echo $item; ?></h5>
                                </div>
                                <div class="widget-user-image">
                                    <img class="img-circle elevation-2" src="../assets/img/mesa.jpg" alt="User Avatar">
                                </div>
                                <div class="card-footer">
                                    <div class="description-block">
                                        <?php 
                                        // Verificar si el rol es 1 o 2 para permitir acciones en mesas
                                        if ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 2|| $_SESSION['rol'] == 3) {
                                            // Si no hay pedido pendiente, mostrar el botón "Atender"
                                            if (empty($resultPedido)) {
                                                echo '<a class="btn btn-outline-info" href="#" data-toggle="modal" data-target="#modalPassword" onclick="setMesa('.$item.')">Atender</a>';
                                            } else {
                                                // Si el pedido está pendiente, solo mostrar el botón "Atender"
                                                echo '<a class="btn btn-outline-info" href="#" data-toggle="modal" data-target="#modalPassword" onclick="setMesa('.$item.')">Atender</a>';
                                                
                                                // Si el pedido no está pendiente, mostrar "Finalizar"
                                                if ($resultPedido['estado'] = 'PENDIENTE') {
                                                    echo '<a class="btn btn-outline-success" data-toggle="modal" data-target="#modalPassword2" href="finalizar.php?id_sala=' . $id . '&mesa=' . $item . '">Finalizar</a>';
                                                }
                                            }
                                        } elseif (empty($resultPedido)) {
                                            // Si el rol no es 1 ni 2 y no hay pedido, mostrar el botón "Atender"
                                            echo '<a class="btn btn-outline-info" href="#" data-toggle="modal" data-target="#modalPassword" onclick="setMesa('.$item.')">Atender</a>';
                                        } elseif (!empty($resultPedido)) {
                                            // Si ya existe un pedido, mostrar "Atender"
                                            echo '<a class="btn btn-outline-info" href="#" data-toggle="modal" data-target="#modalPassword" onclick="setMesa('.$item.')">Atender</a>';
                                            
                                            // Mostrar el botón "Finalizar" solo si el estado no es 'PENDIENTE'
                                            if ($resultPedido['estado'] != 'PENDIENTE') {
                                                echo '<a class="btn btn-outline-success"data-toggle="modal" data-target="#modalPassword2"  href="finalizar.php?id_sala=' . $id . '&mesa=' . $item . '">Finalizar</a>';
                                            }
                                        } else {
                                            // Si la mesa está ocupada por otro garzón, mostrar el mensaje de ocupada
                                            echo '<button class="btn btn-outline-secondary" disabled>Ocupada por otro garzón</button>';
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
            <?php 
                        $item++;
                    }
                }
            } ?>
        </div>
    </div>
</div>




<?php 
include_once "includes/footer.php";
} else {
    header('Location: permisos.php');
    exit();
}
?>

<script>
// Función para agregar dígitos al campo de contraseña
function addDigit(digit) {
    var passwordField = document.getElementById("password");
    passwordField.value += digit;
}

// Función para borrar el contenido del campo de contraseña
function clearPassword() {
    var passwordField = document.getElementById("password");
    passwordField.value = "";
}

// Función para establecer la mesa seleccionada en el campo oculto
function setMesa(mesa) {
    document.getElementById("mesa").value = mesa;
}
</script>


<script>
// Función para agregar dígitos al campo de contraseña
function addDigit2(digit) {
    var passwordField = document.getElementById("password2");
    passwordField.value += digit;
}

// Función para borrar el contenido del campo de contraseña
function clearPassword2() {
    var passwordField = document.getElementById("password2");
    passwordField.value = "";
}

// Función para establecer la mesa seleccionada en el campo oculto
function setMesa2(mesa) {
    document.getElementById("mesa").value = mesa;
}



</script>

<script type="text/javascript">
    window.onbeforeunload = function() {
        // Enviar una solicitud para destruir la sesión
        <?php
            // Asegúrate de destruir la sesión al salir de la página
            if (isset($_SESSION['password'])) {
                //$_SESSION['password'] = false;  // O puedes usar session_destroy()
            }
        ?>
    };
</script>
