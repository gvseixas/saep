<?php
session_start();
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/middlewares/auth.php';
$usuarioLogado = exigirLogin();

$sucesso = "";
$erro = "";

if (isset($_GET["excluir"])) {
    exigirAdmin();
    try {
        $st = $pdo->prepare("DELETE FROM produto WHERE id_produto = ?");
        $st->execute([$_GET["excluir"]]);

        if ($st->rowCount() > 0) {
            $sucesso = "Produto removido com sucesso!";
        } else {
            $erro = "Não foi possível encontrar o produto!";
        }
    } catch (PDOException $e) {
        if ($e->errorInfo[1] == 1451) {
            $erro = "Este produto possui movimentações e não pode ser removido.";
        } else {
            throw $e;
        }
    }
}
if (isset($_GET["salvo"])) {
    $sucesso = "Produto salvo com sucesso!";
}


$filtroNome = trim($_GET["nome"] ?? "");
$filtroData = trim($_GET["data"] ?? "");
$pagina     = max(1, (int) ($_GET["pagina"] ?? 1));
$porPagina  = 5;

$where  = [];
$params = [];

if ($filtroNome !== "") {
    $where[]  = "nome LIKE ?";
    $params[] = "%" . $filtroNome . "%";
}
if ($filtroData !== "") {
    $where[]  = "data_cadastro = ?";
    $params[] = $filtroData;
}

$sqlWhere = $where ? "WHERE " . implode(" AND ", $where) : "";


$stTotal = $pdo->prepare("SELECT COUNT(*) FROM produto $sqlWhere");
$stTotal->execute($params);
$totalRegistros = (int) $stTotal->fetchColumn();
$totalPaginas   = max(1, (int) ceil($totalRegistros / $porPagina));
$pagina         = min($pagina, $totalPaginas);
$offset         = ($pagina - 1) * $porPagina;


$st = $pdo->prepare("SELECT * FROM produto $sqlWhere ORDER BY nome LIMIT $porPagina OFFSET $offset");
$st->execute($params);
$produtos = $st->fetchAll();

require_once __DIR__ . '/includes/menu.php';
?>
    <main class="flex-1 flex flex-col">

        <div class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-extrabold text-gray-800">Gerenciar Produtos</h1>
                <p class="text-sm text-gray-500">Cadastre, consulte, atualize e remova os produtos do estoque</p>
            </div>
            <a href="produto_form.php" class="bg-senai-green text-white font-bold px-4 py-2.5 rounded-lg text-sm hover:bg-green-600 transition flex items-center gap-2">
                + Novo Produto
            </a>
        </div>

        <div class="p-6 flex-1">

            
            <form method="get" class="bg-white rounded-xl shadow-sm p-4 mb-5 flex flex-wrap items-end gap-4">
                <div class="flex-1 min-w-[200px]">
                    <label class="form-label">Nome do produto</label>
                    <input type="text" name="nome" class="form-input" placeholder="Ex: Detergente" value="<?= htmlspecialchars($filtroNome) ?>">
                </div>
                <div>
                    <label class="form-label">Data de cadastro</label>
                    <input type="date" name="data" class="form-input" value="<?= htmlspecialchars($filtroData) ?>">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="bg-senai-blue text-white font-bold px-4 py-2.5 rounded-lg text-sm hover:bg-senai-blue-dark transition">🔍 Filtrar</button>
                    <a href="produtos.php" class="bg-gray-100 text-gray-600 font-semibold px-4 py-2.5 rounded-lg text-sm hover:bg-gray-200 transition">Limpar</a>
                </div>
            </form>

            <?php if (!empty($sucesso)): ?>
                <div class="bg-green-50 border border-green-300 text-green-700 rounded-lg p-3 mb-5 flex items-center gap-2 text-sm">
                    <span class="font-bold text-base">✓</span>
                    <span><?= $sucesso ?></span>
                    <button onclick="this.parentElement.remove()" class="ml-auto text-green-400 hover:text-green-700 text-lg leading-none">×</button>
                </div>
            <?php endif; ?>

            <?php if (!empty($erro)): ?>
                <div class="bg-red-50 border border-red-300 text-red-700 rounded-lg p-3 mb-5 flex items-center gap-2 text-sm">
                    <span class="font-bold text-base">!</span>
                    <span><?= $erro ?></span>
                    <button onclick="this.parentElement.remove()" class="ml-auto text-red-400 hover:text-red-700 text-lg leading-none">×</button>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-senai-blue text-white">
                        <tr>
                            <th class="px-4 py-3 text-left">Produto</th>
                            <th class="px-4 py-3 text-center">Valor unitário</th>
                            <th class="px-4 py-3 text-center">Quantidade</th>
                            <th class="px-4 py-3 text-center">Cadastro</th>
                            <th class="px-4 py-3 text-center">Valor total</th>
                            <th class="px-4 py-3 text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">

                    <?php if (!$produtos): ?>
                        <tr><td colspan="6" class="px-4 py-6 text-center text-gray-400">Nenhum produto encontrado!</td></tr>
                    <?php endif; ?>

                    <?php foreach ($produtos as $p): ?>
                        <?php $valorTotal = round($p["valor_unitario"] * $p["quantidade_estoque"], 2); ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3 text-gray-700 font-semibold"><?= htmlspecialchars($p["nome"]) ?></td>
                            <td class="px-4 py-3 text-center text-gray-600">R$ <?= number_format($p["valor_unitario"], 2, ",", ".") ?></td>
                            <td class="px-4 py-3 text-center text-gray-600"><?= $p["quantidade_estoque"] ?></td>
                            <td class="px-4 py-3 text-center text-gray-600"><?= date("d/m/Y", strtotime($p["data_cadastro"])) ?></td>
                            <td class="px-4 py-3 text-center text-gray-600 font-semibold">R$ <?= number_format($valorTotal, 2, ",", ".") ?></td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="produto.php?id=<?= $p["id_produto"] ?>" class="bg-senai-blue text-white text-xs px-2.5 py-1.5 rounded-md hover:bg-senai-blue-dark transition" title="Ver produto">🔍 Ver</a>
                                    <a href="produto_form.php?id=<?= $p["id_produto"] ?>" class="bg-indigo-600 text-white text-xs px-2.5 py-1.5 rounded-md hover:bg-indigo-700 transition" title="Editar produto">✏️ Editar</a>
                                    <a href="movimentacao_form.php?id_produto=<?= $p["id_produto"] ?>" class="bg-yellow-500 text-white text-xs px-2.5 py-1.5 rounded-md hover:bg-yellow-600 transition" title="Movimentar estoque">🔄 Movimentar</a>
                                    <?php if (isAdmin()): ?>
                                        <a onclick="return confirm('Tem certeza disso?')" class="bg-senai-red text-white text-xs px-2.5 py-1.5 rounded-md hover:bg-red-700 transition" href="?excluir=<?= $p["id_produto"] ?>">Excluir</a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    </tbody>
                </table>

               
                <div class="border-t border-gray-100 px-4 py-3 bg-gray-50 flex items-center justify-between">
                    <p class="text-xs text-gray-500">
                        Exibindo <?= count($produtos) ?> de <?= $totalRegistros ?> produtos (Página <?= $pagina ?> de <?= $totalPaginas ?>)
                    </p>
                    <div class="flex items-center gap-1">
                        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                            <?php $query = http_build_query(["nome" => $filtroNome, "data" => $filtroData, "pagina" => $i]); ?>
                            <a href="?<?= $query ?>" class="px-3 py-1 rounded text-xs font-bold transition <?= $i === $pagina ? 'bg-senai-blue text-white' : 'bg-white border border-gray-300 text-gray-600 hover:bg-gray-100' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>

        </div>
    </main>

</body>
</html>