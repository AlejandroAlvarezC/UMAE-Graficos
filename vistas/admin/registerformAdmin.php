<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <title>Registro de nuevo administrador | Administrador</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="icon" href="../../logo-imss.png" type="image/png" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet" />
    <style>
        body {
            background-color: #f5f5f5;
            color: #212529;
        }

        .navbar {
            background-color: #7a123a;
        }

        .form-container {
            max-width: 450px;
            margin: 3rem auto;
            padding: 2rem;
            background-color: #fff;
            border-radius: 14px;
            box-shadow: 0 4px 14px rgb(0 0 0 / 0.1);
        }

        .btn-dark-custom {
            background-color: #212529;
            color: white;
            border-radius: 6px;
            width: 100%;
        }

        .btn-dark-custom:hover {
            background-color: #000;
            color: white;
        }

        .error {
            color: #d9534f;
            font-size: 0.9rem;
        }
    </style>
</head>

<body style="background-color: aliceblue;">


    <!-- FORMULARIO -->
    <br><br><br><br><br><div class="form-container">
        <h3 class="mb-4 text-center">Registro de nuevo administrador</h3>

        <?php if (isset($_SESSION['register_error'])) : ?>
            <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['register_error']) ?></div>
            <?php unset($_SESSION['register_error']); ?>
        <?php endif; ?>

        <?php if (isset($_GET['msg'])) : ?>
            <p class="text-center" style="color: #e6dcb8;"><?= htmlspecialchars($_GET['msg']) ?></p>
        <?php endif; ?>

        <form action="registerauthAdmin.php" method="post" onsubmit="return validarFormulario2()">
            <div class="mb-3">
                <input type="text" id="names" name="names" placeholder="Nombre de Usuario" class="form-control" required />
                <span id="errorNames" class="error"></span>
            </div>

            <div class="mb-3">
                <input type="email" id="email" name="email" placeholder="Email" class="form-control" required />
                <span id="errorEmai" class="error"></span>
            </div>

          <div class="mb-3 position-relative">
    <input type="password" id="password" name="password" placeholder="Contraseña" class="form-control pr-5" required />
    <i class="bi bi-eye-slash" id="togglePassword" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;"></i>
    <span id="errorPasword" class="error"></span>
</div>

            <button type="submit" name="btnRegister" class="btn btn-dark-custom mb-3">Registrar</button>
        </form>

        <a href="../roles/index.php" class="btn btn-outline-dark w-100">Volver al Inicio</a>
    </div>

<script>
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');

    togglePassword.addEventListener('click', function () {
        const isPassword = passwordInput.type === 'password';
        passwordInput.type = isPassword ? 'text' : 'password';

        // Alterna el ícono
        this.classList.toggle('bi-eye');
        this.classList.toggle('bi-eye-slash');
    });
</script>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="../../js/scripts.js" defer></script>

</body>

</html>
