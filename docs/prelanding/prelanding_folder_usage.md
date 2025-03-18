# Guia de Uso: Prelanding com Ação "folder" e Testes AB

## Introdução

Este documento explica como utilizar a configuração
`prelanding.action = "folder"` para servir páginas de prelanding diretamente na
URL raiz do site, sem redirecionamento ou subpasta, e como realizar testes A/B
entre prelanding e landing pages.

## 1. Configuração da Prelanding na URL Raiz

### Como Funciona

Quando a opção `"folder"` está configurada, o sistema serve os arquivos da pasta
especificada (ex: "preland1") diretamente na URL raiz, sem criar
redirecionamentos. Os arquivos permanecem em suas pastas originais, apenas são
servidos a partir da URL raiz.

### Configuração no settings.json

Para configurar a prelanding na URL raiz, utilize a seguinte configuração no
settings.json:

```json
"prelanding": {
    "action": "folder",
    "folders": [
        "preland1"
    ]
}
```

**Parâmetros:**

- `action`: Define o tipo de ação. Use `"folder"` para servir conteúdo da pasta
  diretamente.
- `folders`: Array com nomes das pastas que contêm as páginas de prelanding.
  Pode conter múltiplas pastas para testes A/B.

### Exemplo Prático

1. Você tem uma pasta `preland1` com arquivos HTML, CSS, JS e imagens
2. Configura `"action": "folder"` e `"folders": ["preland1"]`
3. Quando um usuário acessa `https://seudominio.com/`, verá o conteúdo de
   `preland1/index.html`
4. Todos os links e recursos são servidos como se estivessem na raiz

## 2. Executando Testes A/B entre Prelanding e Landing

### Configurando Múltiplas Prelandings

Para testar variações de páginas preliminares, adicione múltiplas pastas ao
array `folders`:

```json
"prelanding": {
    "action": "folder",
    "folders": [
        "preland1",
        "preland2",
        "preland3"
    ]
}
```

O sistema selecionará aleatoriamente uma das pastas para cada visitante e
manterá essa escolha em cookies para consistência.

### Configurando Múltiplas Landings

Similar à configuração de prelanding, você pode configurar múltiplas landing
pages:

```json
"landing": {
    "action": "folder",
    "folder": {
        "names": [
            "site3",
            "site4"
        ]
    }
}
```

### Executando Testes A/B Completos

Para testar a combinação de prelanding e landing pages, use o seguinte comando
AB em seu terminal:

```bash
ab -n 100 -c 10 -C "landingCookie= Cookie: prelanding=preland1" https://seudominio.com/
ab -n 100 -c 10 -C "landingCookie= Cookie: prelanding=preland2" https://seudominio.com/
```

**Parâmetros:**

- `-n`: Número total de requisições
- `-c`: Concorrência (requisições simultâneas)
- `-C`: Define cookies para a requisição (útil para forçar uma versão
  específica)

### Monitorando Resultados

Os resultados dos testes A/B são registrados no banco de dados e podem ser
visualizados no painel administrativo em:

```
/admin/statistics.php
```

## 3. Exemplo de Implementação Completa

### Estrutura de Diretórios

```
/
├── preland1/
│   ├── index.html
│   ├── css/
│   ├── js/
│   └── img/
├── preland2/
│   ├── index.html
│   └── ...
├── site3/
│   ├── index.html
│   └── ...
├── site4/
│   ├── index.html
│   └── ...
├── index.php
└── settings.json
```

### settings.json

```json
{
    "prelanding": {
        "action": "folder",
        "folders": [
            "preland1",
            "preland2"
        ]
    },
    "black": {
        "landing": {
            "action": "folder",
            "folder": {
                "names": [
                    "site3",
                    "site4"
                ]
            }
        }
    }
}
```

### Fluxo de Usuário

1. Usuário acessa `https://seudominio.com/`
2. Sistema seleciona aleatoriamente entre `preland1` e `preland2`
3. Usuário vê conteúdo da prelanding na URL raiz
4. Ao clicar no botão CTA, o usuário é direcionado para uma landing page
   (`/site3/` ou `/site4/`)
5. Sistema registra a jornada completa para análise

## 4. Considerações Importantes

1. **Não modificar arquivos originais**: Os arquivos nas pastas `preland1`,
   `preland2`, etc., não devem ser modificados diretamente. O sistema serve-os
   como estão.

2. **Caminhos relativos**: Os caminhos relativos no HTML funcionarão como
   esperado, pois o sistema ajusta automaticamente quando necessário.

3. **Cookies e Sessões**: O sistema usa cookies para manter consistência nas
   escolhas de prelanding e landing.

4. **Cache**: Considere configurar cache adequadamente para melhor desempenho.

## 5. Resolução de Problemas

- **Links quebrados**: Verifique se todos os links internos usam caminhos
  relativos corretos
- **Redirecionamentos não funcionam**: Verifique se o buttonlog.php está sendo
  chamado corretamente quando o botão CTA é clicado
- **Falha na seleção aleatória**: Limpe os cookies do navegador para obter nova
  seleção aleatória
- **Prelanding não aparece na URL raiz**: Verifique se a configuração no
  settings.json está correta com `"action": "folder"` e se as pastas
  especificadas existem no servidor
- **Recursos não carregam**: Inspecione o console do navegador para verificar
  erros de carregamento e confirme se os caminhos estão corretos

## 6. Atualizações Recentes

### 6.1 Melhorias na Exibição da Prelanding na URL Raiz

Melhoramos a implementação da funcionalidade para garantir que:

1. **Prelanding tem prioridade**: O sistema agora verifica primeiro se existe
   uma configuração de prelanding com ação "folder" antes de processar a landing

2. **Tag Base Automática**: Uma tag `<base>` é adicionada automaticamente ao
   HTML das prelandings para garantir que os links relativos funcionem
   corretamente

3. **Tracking de Cliques Aprimorado**: Implementamos um sistema mais robusto
   para rastreamento de cliques no botão CTA via JavaScript e buttonlog.php

### 6.2 Buttonlog.php

Um novo arquivo `buttonlog.php` foi implementado para:

1. Registrar eventos de clique no botão CTA
2. Armazenar informações em logs/button_clicks.log
3. Retornar a URL da landing para redirecionamento

Exemplo de implementação JavaScript para o botão CTA:

```javascript
document.getElementById("ctaButton").addEventListener("click", function (e) {
    fetch("/buttonlog.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
        },
        body: JSON.stringify({
            event: "lead_click",
            prelanding: "preland1", // Nome da prelanding atual
            timestamp: new Date().toISOString(),
        }),
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.redirect) {
                window.location.href = data.redirect;
            }
        })
        .catch((error) => console.error("Erro:", error));
});
```

### 6.3 Compatibilidade com Tipos de Arquivos

Aprimoramos o suporte para diferentes tipos de arquivos servidos diretamente das
pastas de prelanding:

- Suporte adequado para todos os tipos MIME (HTML, CSS, JS, imagens, fontes)
- Cabeçalhos de cache otimizados para melhor desempenho
- Tratamento correto de arquivos PHP quando presentes nas pastas de prelanding

### 6.4 Integração com o Sistema de Estatísticas

As visualizações de prelanding e cliques no botão CTA agora são registrados
corretamente no sistema de estatísticas:

- Visualizações são registradas no banco de dados com subID único
- Eventos de clique são registrados em arquivo de log dedicado
- Dados podem ser visualizados no painel administrativo
