<?php
session_start();
require_once __DIR__ . '/conexao.php'; 

$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome   = trim($_POST['nome'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $senha  = $_POST['senha'] ?? '';
    $perfil = in_array($_POST['perfil'] ?? '', ['OPERADOR', 'ADMIN'], true) ? $_POST['perfil'] : 'OPERADOR';

    if ($nome === '' || $email === '' || $senha === '') {
        $erro = 'Preencha todos os campos.';
    } else {
        $st = $pdo->prepare("SELECT id_user FROM `user` WHERE email = ?");
        $st->execute([$email]);
        if ($st->fetch()) {
            $erro = 'E-mail já cadastrado.';
        } else {
            $hash = password_hash($senha, PASSWORD_DEFAULT);
            $st = $pdo->prepare("INSERT INTO `user` (nome, email, senha, perfil) VALUES (?, ?, ?, ?)");
            $st->execute([$nome, $email, $hash, $perfil]);
            header('Location: login.php?cadastrado=1');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastro | Estoque SAEP</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-xl shadow-sm w-full max-w-md">
        <h1 class="text-xl font-extrabold text-gray-800 mb-4">Cadastrar Usuário</h1>
        <?php if ($erro): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded mb-4 text-sm"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>
        <form method="post" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Nome</label>
                <input type="text" name="nome" required class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">E-mail</label>
                <input type="email" name="email" required class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Senha</label>
                <input type="password" name="senha" required class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Perfil de Acesso</label>
                <select name="perfil" class="w-full border rounded-lg px-3 py-2 text-sm">
                    <option value="OPERADOR">Operador de Almoxarifado</option>
                    <option value="ADMIN">Administrador do Sistema</option>
                </select>
            </div>
            <button type="submit" class="w-full bg-green-600 text-white font-bold py-2.5 rounded-lg text-sm">Cadastrar</button>
        </form>
        <p class="text-xs text-center text-gray-500 mt-4"><a href="login.php" class="text-blue-700 font-semibold hover:underline">Voltar para o login</a></p>
    </div>
</body>
</html>