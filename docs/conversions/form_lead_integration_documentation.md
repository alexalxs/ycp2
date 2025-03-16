# Documentação: Integração de Formulário na Landing Page para Registro de Lead

## Documentação Detalhada

A integração de formulários na landing page para registro de leads é um processo
fundamental para capturar informações de potenciais clientes sem alterar a
aparência ou elementos do formulário original. Este documento descreve em
detalhes como esta funcionalidade está implementada e como ela funciona do ponto
de vista do administrador do sistema.

### Visão Geral

O sistema permite que formulários existentes em landing pages sejam integrados
ao fluxo de captura de leads sem modificar seus elementos visuais ou
estruturais. Isso é conseguido através de um mecanismo de interceptação que
captura os dados submetidos, processa-os internamente, e então os encaminha para
o destino original do formulário.

### Fluxo do Ponto de Vista do Administrador

1. **Configuração do Sistema**:
   - O administrador acessa o painel administrativo
   - Configura as opções de tratamento de formulários em "Configurações de
     Landing Page"
   - Define o caminho para o script de processamento original do formulário

2. **Monitoramento de Leads**:
   - O administrador pode visualizar os leads capturados no painel
     administrativo
   - Pode filtrar leads por fonte, data, e status de conversão
   - Pode exportar os dados dos leads para análise externa

3. **Personalização da Página de Agradecimento**:
   - O administrador pode optar por usar a página de agradecimento padrão do
     provedor de pagamento ou uma página personalizada
   - Para usar uma página personalizada, basta ativar a opção correspondente no
     painel administrativo

### Implementação Técnica

A integração ocorre principalmente através dos seguintes mecanismos:

1. **Interceptação via send.php**:
   - Quando o usuário submete o formulário, o arquivo `send.php` intercepta os
     dados
   - Os dados são extraídos independentemente dos nomes dos campos (suporta
     várias convenções de nomenclatura)
   - Um identificador único (subid) é associado aos dados para rastreamento

2. **Verificação de Duplicidade**:
   - O sistema verifica se o lead já foi registrado anteriormente usando cookies
   - Leads duplicados são redirecionados diretamente para a página de
     agradecimento sem reprocessamento

3. **Processamento Baseado no Tipo de Formulário**:
   - Se o formulário tiver uma URL externa como destino, os dados são enviados
     para essa URL
   - Se for um script local, o sistema constrói o caminho completo e envia os
     dados para ele

4. **Cookies e Rastreamento**:
   - Cookies são definidos para armazenar informações do lead (nome, telefone,
     timestamp)
   - Estes cookies são usados para personalizar a página de agradecimento e
     evitar duplicidades

### Limitações e Considerações

1. **Compatibilidade com Formulários**:
   - O sistema suporta diversos formatos comuns de campos (name/fio, phone/tel,
     etc.)
   - Formulários altamente personalizados podem requerer adaptações adicionais

2. **Processamento de Redirecionamentos**:
   - O sistema identifica respostas com código HTTP 302 e segue o
     redirecionamento
   - Respostas com código HTTP 200 são tratadas como conteúdo de página de
     agradecimento

3. **Segurança e Validação de Dados**:
   - O sistema não valida os dados do formulário além de verificar se campos
     obrigatórios foram preenchidos
   - Implementações específicas podem requerer validações adicionais

### Exemplos e Casos de Uso

**Exemplo 1: Formulário com Destino Externo**

```html
<form action="https://payment-provider.com/process" method="post">
    <input type="text" name="name" placeholder="Seu nome">
    <input type="tel" name="phone" placeholder="Seu telefone">
    <button type="submit">Enviar</button>
</form>
```

Neste caso, o sistema interceptará o envio, processará e registrará o lead, e
então enviará os dados para "https://payment-provider.com/process".

**Exemplo 2: Formulário com Script Local**

```html
<form action="order.php" method="post">
    <input type="text" name="fio" placeholder="Seu nome">
    <input type="tel" name="tel" placeholder="Seu telefone">
    <button type="submit">Enviar</button>
</form>
```

O sistema interceptará o envio, processará e registrará o lead, e então enviará
os dados para "order.php" localizado no mesmo diretório da landing page.

## Tabela de Controle com o Conteúdo Inicial

# To Fix ( deve alterar a funcionalidade pois esta incorreta):

# To Do ( deve criar a funcionalidade pois esta faltando):

# Test ( não deve auterar a funcionalidade pois esta em fase de teste pelo usuário):

# Done ( não deve auterar a funcionalidade pois esta correta):

- Interceptação de envio de formulário sem modificar seus elementos visuais
- Extração de dados de formulário suportando diferentes convenções de
  nomenclatura (name/fio, phone/tel)
- Verificação de leads duplicados via cookies para evitar múltiplos registros
- Definição de cookies com dados do lead para personalização da página de
  agradecimento
- Suporte a formulários com destino externo (URLs) e scripts locais
- Redirecionamento para página de agradecimento personalizada ou padrão do
  provedor
- Processamento de diferentes tipos de resposta HTTP (302, 200)
- Associação de subid para rastreamento da origem do lead
