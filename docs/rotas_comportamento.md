# Análise de Comportamento das Rotas no Sistema

## Resultados dos Testes

Realizamos testes abrangentes para verificar como o sistema gerencia rotas, exibe páginas e registra ações de usuário. Abaixo estão os resultados e a análise das configurações.

### 1. Comportamento de Roteamento

**Configuração atual:**
- No arquivo `settings.json`:
  - `white.action` está definido como "folder"
  - `black.prelanding.action` está definido como "none"
  - `black.landing.action` está definido como "folder"
  - `tds.mode` está definido como "off"

**Comportamento observado:**
- Quando acessamos a rota raiz `http://localhost:8003/volume/`, o sistema envia todo o tráfego diretamente para a página black (landing)
- A página exibida é da pasta "landing" conforme configurado em `black.landing.folder.names`
- URLs relativas são mantidas corretamente através da tag `<base href="/volume/landing/">`
- Não há redirecionamento para a pasta prelanding porque `black.prelanding.action` está definido como "none"

### 2. Registros de Ações de Usuário

Os cliques e envios de formulários são registrados corretamente nas estatísticas:

1. **Envio de Formulário:**
   - Ao enviar dados para `form-processor.php`, um novo lead é criado e registrado
   - O contador de conversões nas estatísticas aumenta (de 10 para 11 em nosso teste)
   - O lead é listado na página `/admin/index.php?filter=leads&password=12345`

2. **Status dos Leads:**
   - Leads recém-criados são registrados com status "Lead" (após a correção)
   - A coluna "Hold" nas estatísticas mostra os leads em espera
   - Os status "Reject" e "Trash" podem ser atualizados via postback

### 3. Configuração Correta do Servidor

**Problema resolvido:**
- O servidor deve ser iniciado na pasta raiz do projeto e não dentro da pasta volume
- Comando CORRETO: `php -S localhost:8003 -t .` (na pasta raiz do projeto)
- Comando INCORRETO: `cd volume && php -S localhost:8003 -t .` (dentro da pasta volume)

**Impacto da correção:**
- Agora a rota `http://localhost:8003/volume/` funciona corretamente
- Os caminhos base são detectados automaticamente pela função `get_base_path()`
- As URLs relativas funcionam adequadamente considerando o prefixo `/volume/`

## Como Funciona o Roteamento

O sistema determina qual página exibir com base nestes fatores:

1. **Verificação do TDS (Traffic Distribution System):**
   - Como `tds.mode` está definido como "off", todo o tráfego é enviado para a página black

2. **Verificação de IP da Whitelist:**
   - O sistema verifica se o IP do cliente está na whitelist
   - Se estiver (como mostrado no log: "IP está na whitelist"), envia diretamente para a black page

3. **Escolha entre Prelanding e Landing:**
   - Como `black.prelanding.action` é "none", o sistema pula a prelanding
   - Usa diretamente `black.landing.action` como "folder" e serve o conteúdo da pasta landing

4. **Servir Arquivos:**
   - A função `serve_file()` lida com a exibição do conteúdo
   - Para arquivos HTML, adiciona uma tag `<base>` para garantir que links relativos funcionem
   - Também adiciona script para rastrear cliques em botões com ID "ctaButton"

## Caminhos e URLs

### Caminhos no Servidor
- Quando o servidor é iniciado na pasta raiz: `php -S localhost:8003 -t .`
- A estrutura de caminhos no servidor fica:
  - `/` -> Raiz do projeto
  - `/volume/` -> Pasta principal da aplicação
  - `/volume/landing/` -> Pasta da landing page
  - `/volume/admin/` -> Pasta de administração

### URLs para Acesso
- URL principal: `http://localhost:8003/volume/`
- URL da landing: `http://localhost:8003/volume/landing/`
- URL do admin: `http://localhost:8003/volume/admin/`
- URL de estatísticas: `http://localhost:8003/volume/admin/statistics.php?password=12345`

## Recomendações

1. **Sempre inicie o servidor na pasta raiz:**
   ```bash
   php -S localhost:8003 -t .
   ```

2. **Use URLs relativas nas suas páginas considerando o prefixo `/volume/`**

3. **Para processamento de formulários, sempre use:**
   ```html
   <form action="/volume/form-processor.php" method="POST">
   ```

4. **Para links entre páginas, use caminhos relativos à raiz:**
   ```html
   <a href="/volume/landing/page2.html">Página 2</a>
   ```

## Conclusão

O sistema está funcionando conforme a configuração, enviando todo o tráfego para a black page (landing) devido ao TDS estar desativado e o IP estar na whitelist. Os cliques e conversões são registrados corretamente nas estatísticas. Com a correção na configuração do servidor, agora a aplicação funciona adequadamente quando acessada via `http://localhost:8003/volume/`. 