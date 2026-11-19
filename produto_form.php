<?php
session_start();
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/middlewares/auth.php';
$usuarioLogado = exigirLogin();

$erro = "";

$id_produto = (int) ($_GET["id"] ?? 0);
$nome       = "";
$quantidade = "";
$valor      = "";
$data       = date("Y-m-d");


if ($id_produto > 0) {
    $st = $pdo->prepare("SELECT * FROM produto WHERE id_produto = ?");
    $st->execute([$id_produto]);
    $produtoExistente = $st->fetch();
    if ($produtoExistente) {
        $nome       = $produtoExistente["nome"];
        $quantidade = $produtoExistente["quantidade_estoque"];
        $valor      = $produtoExistente["valor_unitario"];
        $data       = $produtoExistente["data_cadastro"];
    } else {
        $id_produto = 0;
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nome       = trim($_POST["nome"] ?? "");
    $quantidade = trim($_POST["quantidade"] ?? "");
    $valor      = trim($_POST["valor"] ?? "");
    $data       = trim($_POST["data"] ?? "");

    if ($nome === "" || $quantidade === "" || $valor === "" || $data === "") {
        $erro = "Preencha todos os campos!";
    } elseif ((int) $quantidade < 0 || (float) $valor < 0) {
        $erro = "Quantidade e valor não podem ser negativos.";
    } else {
        $st = $pdo->prepare("SELECT id_produto FROM produto WHERE nome = ? AND id_produto != ?");
        $st->execute([$nome, $id_produto]);
        if ($st->fetch()) {
            $erro = "Produto já cadastrado!";
        }
    }

    if ($erro === "") {
        if ($id_produto > 0) {
            $st = $pdo->prepare("UPDATE produto SET nome = ?, quantidade_estoque = ?, valor_unitario = ?, data_cadastro = ? WHERE id_produto = ?");
            $st->execute([$nome, (int) $quantidade, (float) $valor, $data, $id_produto]);
        } else {
            $st = $pdo->prepare("INSERT INTO produto (nome, quantidade_estoque, valor_unitario, data_cadastro) VALUES (?, ?, ?, ?)");
            $st->execute([$nome, (int) $quantidade, (float) $valor, $data]);
        }

        header("Location: produtos.php?salvo=1");
        exit;
    }
}

require_once __DIR__ . '/includes/menu.php';
?>
    <main class="flex-1 flex flex-col">
        <div class="bg-white border-b border-gray-200 px-6 py-4">
            <div class="flex items-center gap-2 text-xs text-gray-400 mb-1">
                <a href="produtos.php" class="hover:text-senai-blue">Produtos</a> ›
                <span class="text-gray-700 font-semibold"><?= $id_produto > 0 ? "Editar Produto" : "Novo Produto" ?></span>
            </div>
            <h1 class="text-xl font-extrabold text-gray-800"><?= $id_produto > 0 ? "Editar Produto" : "Novo Produto" ?></h1>
        </div>
        <div class="p-6 flex-1 max-w-xl">

            <?php if (!empty($erro)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <?= $erro ?>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <form method="post">
                    <div class="mb-4">
                        <label class="form-label">Nome *</label>
                        <input type="text" name="nome" class="form-input" required maxlength="100" placeholder="Ex: Detergente" value="<?= htmlspecialchars((string) $nome) ?>">
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Quantidade em estoque *</label>
                        <input type="number" name="quantidade" class="form-input" required min="0" step="1" placeholder="Ex: 25" value="<?= htmlspecialchars((string) $quantidade) ?>">
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Valor unitário (R$) *</label>
                        <input type="number" name="valor" class="form-input" required min="0" step="0.01" placeholder="Ex: 18.90" value="<?= htmlspecialchars((string) $valor) ?>">
                    </div>
                    <div class="mb-6">
                        <label class="form-label">Data de cadastro *</label>
                        <input type="date" name="data" class="form-input" required value="<?= htmlspecialchars((string) $data) ?>">
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="bg-senai-blue text-white font-bold px-5 py-2.5 rounded-lg text-sm hover:bg-senai-blue-dark transition">💾 Salvar</button>
                        <a href="produtos.php" class="bg-gray-100 text-gray-600 font-semibold px-5 py-2.5 rounded-lg text-sm hover:bg-gray-200 transition">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>