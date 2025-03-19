<?php
require_once __DIR__ . "/db.php";

// Criar um novo lead de teste
$subid = uniqid('test_');
$name = "Nome Teste";
$email = "email.teste@exemplo.com";
$phone = "5511999887766";
$status = "Lead";

echo "<h2>Teste de Postback Detalhado</h2>";
echo "<p><strong>SubID:</strong> $subid</p>";
echo "<p><strong>Nome:</strong> $name</p>";
echo "<p><strong>Email:</strong> $email</p>";
echo "<p><strong>Telefone:</strong> $phone</p>";
echo "<p><strong>Status:</strong> $status</p>";

// Definir cookies para o teste
ywbsetcookie('subid', $subid, time() + 86400);
ywbsetcookie('landing', 'landing_teste', time() + 86400);
ywbsetcookie('prelanding', 'prelanding_teste', time() + 86400);

echo "<hr>";
echo "<h3>Enviando postback diretamente</h3>";

// Enviar o postback diretamente
$result = trigger_s2s_postback($subid, $status, $name, $email, $phone);

echo "<p>Postback enviado: " . ($result ? "Sucesso" : "Falha") . "</p>";
echo "<p>Verifique os logs detalhados em /pblogs/[data].pb.detailed.log</p>";

// Verificar logs
$log_file = __DIR__ . "/pblogs/" . date("d.m.y") . ".pb.detailed.log";
if (file_exists($log_file)) {
    echo "<hr>";
    echo "<h3>Conteúdo do log detalhado:</h3>";
    echo "<pre>" . htmlspecialchars(file_get_contents($log_file)) . "</pre>";
} else {
    echo "<p>Arquivo de log detalhado não encontrado!</p>";
}

echo "<hr>";
echo "<h3>Verificação do Webhook</h3>";
echo "<p>URL do webhook: <strong>" . htmlspecialchars($s2s_postbacks[0]['url']) . "</strong></p>";
echo "<p>Para verificar se o webhook recebeu corretamente os dados, você precisa acessar o sistema destino ou verificar seus logs.</p>"; 