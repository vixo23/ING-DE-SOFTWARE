<?php
session_start();
ob_start(); // Inicia el búfer de salida
include "../conexion.php";

// Incluir SweetAlert2
echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';

// Verifica que el usuario tenga los permisos necesarios
if ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 2 || $_SESSION['rol'] == 3) {
    $id = isset($_GET['id_sala']) ? (int)$_GET['id_sala'] : 0;
    $mesas = isset($_GET['mesas']) ? (int)$_GET['mesas'] : 0;
    $mesa = isset($_POST['mesa']) ? (int)$_POST['mesa'] : 0;
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
//$_SESSION['rol']
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Capturar el valor de la contraseña en base a la acción seleccionada
        $password = isset($_POST['password']) ? mysqli_real_escape_string($conexion, $_POST['password']) : mysqli_real_escape_string($conexion, $_POST['password2']);
        
        $accion = isset($_POST['accion']) ? $_POST['accion'] : null;
    
        // Verificar si la contraseña existe en la base de datos
        $query = mysqli_query($conexion, "SELECT * FROM garzones WHERE Contrasenia = '$password'");
        $garzon = mysqli_fetch_assoc($query);
    
        if (mysqli_num_rows($query) > 0){
            $_SESSION['password'] = true;
            $_SESSION['garzon_name'] = $garzon['Nombre'];
            $_SESSION['garzon_color'] = $garzon['Color_Garzon'];
            $_SESSION['garzon_id'] = $garzon['Id_Garzones'];
    
            $consulta = mysqli_query($conexion, "SELECT Color_Garzon FROM pedidos WHERE id_sala = $id AND num_mesa = $mesa AND estado = 'PENDIENTE'");
            $mesaOcupada = mysqli_fetch_assoc($consulta);
    
            if ($accion == 'atender') {
                // Lógica para atender la mesa
                if (!empty($mesaOcupada) && $mesaOcupada['Color_Garzon'] !== $_SESSION['garzon_color']) {
                    $error_message = "La mesa está siendo atendida por otro garzón.";
                    echo "<script>Swal.fire({icon: 'error', title: '¡Error!', text: '$error_message'});</script>";
                } else {
                    //header("Location: pedido.php?id_sala=$id&mesas=$mesas&mesa=$mesa");
					header("Location: grupo.php?id_sala=$id&mesas=$mesas&mesa=$mesa");
                    exit();
                }
            } else if ($accion == 'finalizar') {
                // Lógica para finalizar la mesa
                if (!empty($mesaOcupada) && $mesaOcupada['Color_Garzon'] !== $_SESSION['garzon_color']) {
                    $error_message = "La mesa está siendo atendida por otro garzón, no puedes finalizar.";
                    echo "<script>Swal.fire({icon: 'error', title: '¡Error!', text: '$error_message'});</script>";
                } else {
                    header("Location: finalizar.php?id_sala=$id&mesas=$mesas&mesa=$mesa");
                    exit();
                }
            }
        } else {
                $error_message = "Ingrese la Clave correcta, por favor.";
                    echo "<script>Swal.fire({icon: 'error', title: '¡Error!', text: '$error_message'});</script>";
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

<!-- Modal para Atender -->
<div class="modal fade" id="modalPasswordAtender" tabindex="-1" aria-labelledby="modalPasswordLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title w-100 text-center" id="modalPasswordLabel">Verificar Garzón - Atender</h5>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <!-- Campos ocultos para identificar acción y número de mesa -->
                    <input type="hidden" name="accion" value="atender">
                    <input type="hidden" id="mesaAtender" name="mesa">
                    
                    <!-- Contraseña Input -->
                    <div class="mb-3 text-center">
                        <label for="password" class="form-label">Clave</label>
                        <input type="text" class="form-control w-50 mx-auto d-block text-center" id="password" name="password">
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
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Finalizar -->
<div class="modal fade" id="modalPasswordFinalizar" tabindex="-1" aria-labelledby="modalPasswordLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title w-100 text-center" id="modalPasswordLabel">Verificar Garzón - Finalizar</h5>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <!-- Campos ocultos para identificar acción y número de mesa -->
                    <input type="hidden" name="accion" value="finalizar">
                    <input type="hidden" id="mesaFinalizar" name="mesa">
                    
                    <!-- Contraseña Input -->
                    <div class="mb-3 text-center">
                        <label for="password2" class="form-label">Clave</label>
                        <input type="text" class="form-control w-50 mx-auto d-block text-center" id="password2" name="password2" >
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
                </form>
            </div>
        </div>
    </div>
</div>




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
                                                echo '<a class="btn btn-outline-info" href="#" data-toggle="modal" data-target="#modalPasswordAtender" onclick="setMesaAtender(' . $item . ')">Atender</a>';
                                            } else {
                                                // Si el pedido está pendiente, solo mostrar el botón "Atender"
                                                echo '<a class="btn btn-outline-info" href="#" data-toggle="modal" data-target="#modalPasswordAtender" onclick="setMesaAtender(' . $item . ')">Atender</a>';
                                                
                                                // Si el pedido no está pendiente, mostrar "Finalizar"
                                                if ($resultPedido['estado'] = 'PENDIENTE') {
                                                    echo '<a class="btn btn-outline-success" href="#" data-toggle="modal" data-target="#modalPasswordFinalizar" onclick="setMesaFinalizar(' . $item . ')">Finalizar</a>';

                                                }
                                            }
                                        } elseif (empty($resultPedido)) {
                                            // Si el rol no es 1 ni 2 y no hay pedido, mostrar el botón "Atender"
                                            echo '<a class="btn btn-outline-info" href="#" data-toggle="modal" data-target="#modalPasswordAtender" onclick="setMesaAtender(' . $item . ')">Atender</a>';
                                        } elseif (!empty($resultPedido)) {
                                            // Si ya existe un pedido, mostrar "Atender"
                                            echo '<a class="btn btn-outline-info" href="#" data-toggle="modal" data-target="#modalPasswordAtender" onclick="setMesaAtender(' . $item . ')">Atender</a>';
                                            
                                            // Mostrar el botón "Finalizar" solo si el estado no es 'PENDIENTE'
                                            if ($resultPedido['estado'] != 'PENDIENTE') {
                                                echo '<a class="btn btn-outline-success" href="#" data-toggle="modal" data-target="#modalPasswordFinalizar" onclick="setMesaFinalizar(' . $item . ')">Finalizar</a>';

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
function setMesaAtender(mesa) {
    document.getElementById('mesaAtender').value = mesa;
}

function setMesaFinalizar(mesa) {
    document.getElementById('mesaFinalizar').value = mesa;
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


</script>

