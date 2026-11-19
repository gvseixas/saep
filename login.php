<?php
session_start();
require_once __DIR__ . '/conexao.php'; 

$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    $st = $pdo->prepare("SELECT * FROM `user` WHERE email = ?");
    $st->execute([$email]);
    $usuario = $st->fetch();

    if ($usuario && (password_verify($senha, $usuario['senha']) || $usuario['senha'] === $senha)) {
        $_SESSION['usuario'] = [
            'id_user' => $usuario['id_user'],
            'nome'    => $usuario['nome'],
            'email'   => $usuario['email'],
            'perfil'  => $usuario['perfil'],
        ];
        header('Location: index.php');
        exit;
    }
    $erro = 'E-mail ou senha inválidos.';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Login | Estoque SAEP</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-xl shadow-sm w-full max-w-md">
        <h1 class="text-xl font-extrabold text-gray-800 mb-4">Login - Estoque SAEP</h1>
        <?php if (isset($_GET['cadastrado'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded mb-4 text-sm">Cadastro realizado! Faça login.</div>
        <?php endif; ?>
        <?php if ($erro): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded mb-4 text-sm"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>
        <form method="post" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">E-mail</label>
                <input type="email" name="email" required class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Senha</label>
                <input type="password" name="senha" required class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
            <button type="submit" class="w-full bg-blue-700 text-white font-bold py-2.5 rounded-lg text-sm">Entrar</button>
        </form>
        <p class="text-xs text-center text-gray-500 mt-4">Não possui conta? <a href="cadastro.php" class="text-blue-700 font-semibold hover:underline">Cadastre-se</a></p>
    </div>
</body>
</html>