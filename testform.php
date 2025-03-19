<?php
require_once __DIR__ . "/cookies.php";

// Gerar um subid aleatório
$subid = uniqid();
ywbsetcookie('subid', $subid, time() + 86400);
ywbsetcookie('landing', 'offertest', time() + 86400);
ywbsetcookie('prelanding', 'prelandtest', time() + 86400);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulário de Teste - Postback S2S</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="email"],
        input[type="tel"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #45a049;
        }
        .info {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <h1>Formulário de Teste - Postback S2S</h1>
    
    <div class="info">
        <h3>Informações do Teste</h3>
        <p><strong>SubID:</strong> <?php echo $subid; ?></p>
        <p><strong>Landing:</strong> offertest</p>
        <p><strong>Prelanding:</strong> prelandtest</p>
    </div>
    
    <form action="send.php" method="POST">
        <div class="form-group">
            <label for="name">Nome:</label>
            <input type="text" id="name" name="name" required>
        </div>
        
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
        </div>
        
        <div class="form-group">
            <label for="phone">Telefone:</label>
            <input type="tel" id="phone" name="phone" required>
        </div>
        
        <button type="submit">Enviar Lead</button>
    </form>
    
    <div style="margin-top: 30px;">
        <h3>Próximos passos:</h3>
        <ol>
            <li>Preencha o formulário e envie</li>
            <li>Verifique se o lead foi registrado no banco de dados</li>
            <li>Verifique se o postback foi enviado verificando os logs em /pblogs/</li>
            <li>Verifique se você foi redirecionado para thankyou.php</li>
        </ol>
    </div>
</body>
</html> 