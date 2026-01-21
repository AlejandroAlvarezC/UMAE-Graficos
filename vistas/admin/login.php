<?php session_start();?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../css/bootstrap.min.css" defer>
    <link rel="stylesheet" href="../../css/styles.css" defer>
    <link rel="icon" type="image/png" href="../../logo-imss.png">
    <title>Inicio de sesión | Administrador </title>
</head>

<body
    style="background-image: url('../../img/imss4.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat;">
    <div class="d-flex justify-content-center align-items-center vh-100">
        <div class="card1">
            <div class="card2">
                <h1 class="text-center">Iniciar Sesión</h1>
               <?php
if (isset($_GET['msg'])) {
    echo '<div class="alert alert-success d-flex align-items-center" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <div>' . htmlspecialchars($_GET['msg']) . '</div>
          </div>';
}
?>
                <?php
                if (isset($_SESSION['login_error'])) {
                    echo '<div class="alert alert-danger">' . $_SESSION['login_error'] . '</div>';
                    unset($_SESSION['login_error']);
                }
                ?>
                <form action="loginauth.php" method="post">
                    <div class="mb-3">
                        <input type="email" name="email" placeholder="Email" class="form-control" required>
                    </div>
                    <div class="mb-3 position-relative">
    <input type="password" name="password" id="loginPassword" placeholder="Password" class="form-control" required />
    <i class="bi bi-eye-slash" id="toggleLoginPassword" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;"></i>
</div>
                    <div class="d-grid">
                        <br><input type="submit" name="btnLogin" class="btn bg-verde text-white" value="Iniciar Sesión">
                    </div>
                    <div class="d-grid">
                        <br><a href="verificacionAdmin.php" class="btn bg-vino text-white">Registrar Nuevo</a><br><br>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    const toggleLoginPassword = document.getElementById('toggleLoginPassword');
    const loginPasswordInput = document.getElementById('loginPassword');

    toggleLoginPassword.addEventListener('click', function () {
        const isPassword = loginPasswordInput.type === 'password';
        loginPasswordInput.type = isPassword ? 'text' : 'password';

        this.classList.toggle('bi-eye');
        this.classList.toggle('bi-eye-slash');
    });
</script>


    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous">
    </script>

    <script src="../../js/scripts.js" defer></script>
    <script src="../../js/bootstrap.bundle.min.js" defer></script>
</body>

</html>