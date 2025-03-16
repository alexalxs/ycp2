# Resumo para Criação de Novas Landing Pages Integradas com `send.php`

## Objetivo
Desenvolver landing pages que coletam informações de leads e se integram eficientemente com `send.php` para processamento seguro e assíncrono dos dados.

## Características Essenciais

### 1. Configuração do Formulário
- **Atributo `action` Apontando para `send.php`:**
  ```html
  <form id="leadForm" action="send.php" method="POST">
  ```
  - Direciona os dados do formulário para `send.php` para processamento no servidor.
  
- **Método `POST`:**
  ```html
  <form id="leadForm" action="send.php" method="POST">
  ```
  - Envia dados de forma segura no corpo da requisição HTTP.
  
- **ID Único para o Formulário:**
  ```html
  <form id="leadForm" action="send.php" method="POST">
  ```
  - Facilita a manipulação via JavaScript.

### 2. Campos do Formulário
- **Campos Obrigatórios:**
  - **Nome:**
    ```html
    <div class="form-group">
        <label for="lead-name">Seu nome:</label>
        <input type="text" id="lead-name" name="name" required>
    </div>
    ```
  - **Email:**
    ```html
    <div class="form-group">
        <label for="lead-email">Seu email:</label>
        <input type="email" id="lead-email" name="email" required>
        <div class="error-message" id="emailError">Por favor, insira um email válido.</div>
    </div>
    ```
  - **Botão de Submissão:**
    ```html
    <button type="submit">Enviar</button>
    ```

### 3. JavaScript para Submissão Assíncrona (AJAX)
- **Interceptação da Submissão:**
  ```javascript
  document.getElementById('leadForm').addEventListener('submit', async function(e) {
      e.preventDefault();
      // Validação e envio de dados
  });
  ```
  - Evita recarregamento da página ao interceptar o evento de submissão.
  
- **Validação de Dados:**
  ```javascript
  function isValidEmail(email) {
      const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
      return re.test(String(email).toLowerCase());
  }
  ```
  
- **Envio Assíncrono dos Dados:**
  ```javascript
  async function handleFormSubmit(e) {
      e.preventDefault();

      const name = document.getElementById('lead-name').value.trim();
      const email = document.getElementById('lead-email').value.trim();
      const emailError = document.getElementById('emailError');

      if (!isValidEmail(email)) {
          emailError.style.display = 'block';
          return;
      }

      emailError.style.display = 'none';

      try {
          const formData = new URLSearchParams();
          formData.append('action', 'email');
          formData.append('name', name);
          formData.append('email', email);

          const urlParams = getUrlParams();
          Object.keys(urlParams).forEach(key => {
              formData.append(key, urlParams[key]);
          });

          const subid = document.cookie.split('; ').find(row => row.startsWith('subid='))?.split('=')[1] || '';
          if (subid) {
              formData.append('subid', subid);
          }

          const response = await fetch('/send.php', {
              method: 'POST',
              headers: {
                  'Content-Type': 'application/x-www-form-urlencoded',
              },
              body: formData.toString()
          });

          if (response.ok) {
              const result = await response.json();
              console.log('Resposta do servidor:', result);
              // Manipular resposta de sucesso
              document.getElementById('modalForm').style.display = 'none';
              document.getElementById('successMessage').style.display = 'block';
              setTimeout(() => {
                  window.location.href = 'https://dekoola.com/ch/hack/';
              }, 2000);
          } else {
              throw new Error('Falha ao enviar os dados para o servidor');
          }
      } catch (error) {
          console.error('Erro:', error);
          alert('Ocorreu um erro ao processar seu pedido. Por favor, tente novamente.');
      }
  }
  ```

### 4. Configuração CORS no Servidor (`send.php`)
- **Permissão de Origens:**
  ```php
  header("Access-Control-Allow-Origin: *");
  header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
  header("Access-Control-Allow-Headers: Content-Type");
  ```
  
- **Manuseio de Solicitações `OPTIONS`:**
  ```php
  if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
      http_response_code(200);
      exit;
  }
  ```

### 5. Processamento e Validação no Servidor (`send.php`)
- **Validação de Campos Obrigatórios:**
  ```php
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'email') {
      if (!isset($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
          http_response_code(400);
          echo json_encode(['error' => 'Email inválido']);
          exit;
      }
      // Continuação do processamento...
  }
  ```
  
- **Sanitização de Dados:**
  ```php
  $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
  $name = htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8');
  ```
  
- **Uso de Cookies para Rastreamento:**
  ```php
  if (empty($subid)) {
      $subid = md5(uniqid(rand(), true));
      setcookie('subid', $subid, time() + (86400 * 30), '/'); // Expira em 30 dias
  }
  ```

### 6. Feedback e Redirecionamento ao Usuário
- **Resposta em JSON de Sucesso:**
  ```php
  http_response_code(200);
  echo json_encode([
      'success' => true, 
      'message' => 'Lead registrado com sucesso',
      'webhook_status' => $webhook_status
  ]);
  exit;
  ```
  
- **Redirecionamento Após Submissão Bem-Sucedida:**
  ```javascript
  setTimeout(() => {
      window.location.href = 'https://dekoola.com/ch/hack/';
  }, 2000);
  ```

### 7. Segurança Adicional
- **Proteção contra CSRF:**
  - Implemente tokens CSRF nos formulários e valide-os no servidor.
  
- **Sanitização Completa dos Inputs:**
  - Garanta que todos os dados recebidos sejam sanitizados para prevenir SQL Injection e XSS.

### 8. Manutenção de Compatibilidade
- **Verificação de Parâmetros Necessários:**
  - Assegure que todos os parâmetros obrigatórios estão presentes antes de processar os dados.

### 9. Exemplos de Implementação Completa
- **Exemplo de Formulário HTML Completo:**
  ```html
  <!DOCTYPE html>
  <html lang="pt-BR">
  <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Landing Page</title>
      <style>
          /* Estilos CSS detalhados */
      </style>
  </head>
  <body>
      <form id="leadForm" action="send.php" method="POST">
          <div class="form-group">
              <label for="lead-name" class="form-label">Seu nome:</label>
              <input type="text" id="lead-name" name="name" class="form-input" placeholder="Digite seu nome" required>
          </div>
          <div class="form-group">
              <label for="lead-email" class="form-label">Seu melhor email:</label>
              <input type="email" id="lead-email" name="email" class="form-input" placeholder="Seu email aqui" required>
              <div class="error-message" id="emailError">Por favor, insira um email válido.</div>
          </div>
          <button type="submit" class="form-submit">Enviar</button>
      </form>

      <script>
          // Implementação completa do JavaScript para submissão assíncrona
      </script>
  </body>
  </html>
  ```
  
## Conclusão
Este resumo facilita a criação de landing pages eficientes e seguras, garantindo a coleta e processamento adequado dos dados de leads através da integração com `send.php`. Isso melhora a experiência do usuário e otimiza o gerenciamento de leads.
