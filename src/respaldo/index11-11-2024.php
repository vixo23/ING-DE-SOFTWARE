<?php
session_start();
if ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 3) {
    include_once "includes/header.php";
    // Verificar la contraseña al inicio
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        include "../conexion.php";
        $password = mysqli_real_escape_string($conexion, $_POST['password']);
    
        // Verificar si la contraseña existe en la base de datos
        $query = mysqli_query($conexion, "SELECT * FROM garzones WHERE Contrasenia = '$password'");
    
        if (mysqli_num_rows($query) > 0) {
            // Si la contraseña es correcta, guardar en la sesión para mostrar el contenido
            $garzon = mysqli_fetch_assoc($query); // Obtener la información del garzón
            $_SESSION['password_verified'] = true;
            $_SESSION['garzon_name'] = $garzon['Nombre']; // Guardar el nombre del garzón en la sesión
            $_SESSION['garzon_color'] = $garzon['Color_Garzon']; // Guardar el color del garzón en la sesión
            $_SESSION['garzon_id'] = $garzon['Id_Garzones']; // Guardar el Id_Garzones en la sesión
            
        } else {
            $error_message = "Contraseña incorrecta. Intenta de nuevo.";
        }
    }
    
?>

<!-- Modal para verificar la contraseña -->
<?php if (!isset($_SESSION['password_verified']) || !$_SESSION['password_verified']) { ?>
    <div class="modal fade" id="modalPassword" tabindex="-1" aria-labelledby="modalPasswordLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title w-100 text-center" id="modalPasswordLabel">Verificar Garzón</h5>
                </div>
                <div class="modal-body">
                    <form method="POST">
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

<!-- Contenido principal de la página -->
<?php if (isset($_SESSION['password_verified']) && $_SESSION['password_verified']) { ?>
    <div class="card">
        <div class="card-header text-center">
            Salones
        </div>
        
        <div class="card-body text-center">
            <h5>Bienvenido, <?php echo $_SESSION['garzon_name']; ?>!</h5> <!-- Mostrar nombre del garzón -->
            <div style="display: inline-block; width: 50px; height: 20px; background-color: <?php echo $_SESSION['garzon_color']; ?>;"></div> <!-- Mostrar color del garzón -->
        </div>
    
        <div class="card-body">
            <div class="row">
                <?php
                include "../conexion.php";
                $query = mysqli_query($conexion, "SELECT * FROM salas WHERE estado = 1");
                $result = mysqli_num_rows($query);
                if ($result > 0) {
                    while ($data = mysqli_fetch_assoc($query)) { ?>
                        <div class="col-md-3 shadow-lg">
                            <div class="col-12">
                                <img src="../assets/img/salones.jpeg" class="product-image" alt="Product Image">
                            </div>
                            <h6 class="my-3 text-center"><span class="badge badge-info"><?php echo $data['nombre']; ?></span></h6>

                            <div class="mt-4">
                                <a class="btn btn-primary btn-block btn-flat" href="mesas.php?id_sala=<?php echo $data['id']; ?>&mesas=<?php echo $data['mesas']; ?>">
                                    <i class="far fa-eye mr-2"></i>
                                    Ver Mesas
                                </a>
                            </div>
                        </div>
                <?php }
                } ?>
            </div>
        </div>
    </div>

    <!-- Nueva sección para mostrar el nombre del garzón -->
    

<?php } ?>

<?php include_once "includes/footer.php"; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Mostrar el modal si no está verificado
    <?php if (!isset($_SESSION['password_verified']) || !$_SESSION['password_verified']) { ?>
    window.onload = function() {
        $('#modalPassword').modal('show');
    };
    <?php } ?>

    // Función para agregar dígitos al campo de contraseña
    function addDigit(digit) {
        let passwordField = document.getElementById('password');
        passwordField.value += digit;
    }

    // Función para borrar el campo de contraseña
    function clearPassword() {
        let passwordField = document.getElementById('password');
        passwordField.value = '';
    }
</script>

<?php
} else {
    header('Location: permisos.php');
}
?>
