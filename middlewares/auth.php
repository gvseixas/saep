<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function exigirLogin(): array {
    if (empty($_SESSION['usuario'])) {
        header('Location: login.php');
        exit;
    }
    return $_SESSION['usuario'];
}

function isAdmin(): bool {
    return isset($_SESSION['usuario']['perfil']) && $_SESSION['usuario']['perfil'] === 'ADMIN';
}

function exigirAdmin(): void {
    exigirLogin();
    if (!isAdmin()) {
        http_response_code(403);
        exit('Acesso negado: apenas Administradores podem excluir registros.');
    }
}