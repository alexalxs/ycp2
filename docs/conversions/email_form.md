# Documentação de Integração do Script de Captura de Formulários  

## Visão Geral  

Este documento aborda a integração de um script JavaScript para capturar formulários HTML na página e redirecionar o usuário após a submissão. O objetivo é configurar um comportamento específico para os formulários, abrindo-os em uma nova aba e redirecionando o usuário após alguns segundos.  

## Diagrama de Fluxo  

```mermaid  
graph TD;  
    A[Início] --> B[Usuário Preenche o Formulário];  
    B --> C[Usuário Clica em Enviar];  
    C --> D{Verifica se o Formulário é Válido};  
    D -- Sim --> E[Adicionar Target _blank ao Formulário];  
    D -- Não --> F[Fim];  
    E --> G[Submeter o Formulário];  
    G --> H[Abre Nova Aba];  
    H --> I[Aguardar 2 segundos];  
    I --> J[Redirecionar para a URL {REDIRECT}];  
    J --> K[Fim];  
    