<?php 
session_start();
$errorMsg = "";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>No Tiene Cuenta Activa</title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.css" rel="stylesheet">

    <style>
        /* Establecer el color de fondo del body */
        body {
            background-color: #f4f6f9; /* Color similar al fondo del primer sitio */
            height: 100vh; /* Para que el body ocupe toda la altura de la ventana */
            display: flex;
            align-items: center; /* Centrado vertical */
            justify-content: center; /* Centrado horizontal */
        }

        .login-form {
            width: 340px;
            font-size: 15px;
            background: #fff;
            box-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);
            padding: 30px;
            border: 1px solid #ddd;
            text-align: center; /* Centrar el contenido del formulario */
        }

        .login-form h2 {
            color: #636363;
            margin: 0 0 15px;
        }

        .error-message {
            color: #dc3545; /* Color de error */
            margin-bottom: 20px;
        }

        .form-control,
        .btn {
            min-height: 38px;
            border-radius: 2px;
        }

        .btn {
            font-size: 15px;
            font-weight: bold;
        }
    </style>

</head>

<body>

<div class="login-box">
  <div class="login-logo" style="text-align: center;">
    <img src="../assets/img/logo.png" alt="Logo" style="max-width: 100%; height: auto;">
    <br>
  </div>
  <!-- /.login-logo -->
  
  <div class="login-form">
    <h2>No Tiene Cuenta Activa</h2>
    <p class="error-message">No tiene la cuenta activa en este sitio web. Comuníquese con su administrador.</p>
    <div class="form-group">
        <button type="button" onclick="window.location.href='../index.php'" class="btn btn-primary btn-block" style="border-radius: 0%;">Volver</button>
    </div>
</div>



</body>
</html>
