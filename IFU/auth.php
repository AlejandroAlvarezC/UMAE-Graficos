<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function usuarioActual(): ?array
{
    if (!isset($_SESSION['usuario'])) {
        return null;
    }

    return $_SESSION['usuario'];
}

function requiereLogin(): array
{
    $usuario = usuarioActual();

    if ($usuario === null) {
        jsonResponse([
            'ok' => false,
            'message' => 'Debes iniciar sesión',
        ], 401);
    }

    return $usuario;
}

function requiereAdmin(): array
{
    $usuario = requiereLogin();

    if (($usuario['rol'] ?? '') !== 'admin') {
        jsonResponse([
            'ok' => false,
            'message' => 'Acceso permitido solo para administradores',
        ], 403);
    }

    return $usuario;
}
