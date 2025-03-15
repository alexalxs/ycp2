# Documentação Detalhada: Métricas e Pixels

Esta documentação detalha a seção "Métricas e Pixels" do arquivo `admin/editsettings.php`, correlacionando-a com o fluxo de execução principal da aplicação.

## Introdução

A seção "Métricas e Pixels" é crucial para a coleta de dados e rastreamento de atividades dentro da aplicação. Ela permite a configuração de diferentes ferramentas de análise e pixels de rastreamento, que são integrados ao fluxo principal da aplicação para monitorar o comportamento do usuário e medir a eficácia das campanhas.

## Configurações de Métricas e Pixels

### Google Tag Manager

- **ID do Google Tag Manager:**
  - Campo: `pixels.gtm.id`
  - Descrição: Permite a inserção do ID do Google Tag Manager para rastreamento avançado de eventos e dados na aplicação.

### Yandex.Metrika

- **ID do Yandex.Metrika:**
  - Campo: `pixels.ya.id`
  - Descrição: Configura o ID do Yandex.Metrika para análise de tráfego e comportamento do usuário.

### Pixel do Facebook

- **Nome do parâmetro contendo o ID do Pixel do Facebook:**
  - Campo: `pixels.fb.subname`
  - Valor padrão: `px`
  - Descrição: Define o nome do parâmetro que contém o ID do Pixel do Facebook, utilizado para rastreamento de eventos específicos.

- **Adicionar evento PageView às páginas brancas?**
  - Campo: `pixels.fb.pageview`
  - Opções: Não / Sim, adicionar
  - Descrição: Determina se o evento PageView do Pixel do Facebook deve ser adicionado às páginas brancas da aplicação.

- **Adicionar evento ViewContent após visualizar a página dentro do tempo especificado?**
  - Campo: `pixels.fb.viewcontent.use`
  - Opções: Não / Sim, adicionar
  - Descrição: Configura se o evento ViewContent deve ser disparado após um tempo específico de visualização da página.

- **Tempo em segundos após o qual ViewContent é enviado:**
  - Campo: `pixels.fb.viewcontent.time`
  - Valor padrão: 30 segundos
  - Descrição: Define o tempo em segundos após o qual o evento ViewContent será enviado. Se 0, o evento não será disparado.

- **Porcentagem de rolagem da página antes do evento ViewContent:**
  - Campo: `pixels.fb.viewcontent.percent`
  - Valor padrão: 75%
  - Descrição: Especifica a porcentagem de rolagem da página necessária para disparar o evento ViewContent. Se 0, o evento não será disparado.

- **Qual evento usaremos para conversão no Facebook?**
  - Campo: `pixels.fb.conversion.event`
  - Valor padrão: Lead
  - Descrição: Define o evento que será utilizado para marcar conversões no Facebook, como Lead ou Purchase.

### Pixel do TikTok

- **Nome do parâmetro contendo o ID do Pixel do TikTok:**
  - Campo: `pixels.tt.subname`
  - Valor padrão: `tpx`
  - Descrição: Especifica o nome do parâmetro que contém o ID do Pixel do TikTok para rastreamento de eventos.

- **Adicionar evento PageView às páginas brancas?**
  - Campo: `pixels.tt.pageview`
  - Opções: Não / Sim, adicionar
  - Descrição: Determina se o evento PageView do Pixel do TikTok deve ser adicionado às páginas brancas da aplicação.

- **Adicionar evento ViewContent após visualizar a página dentro do tempo especificado?**
  - Campo: `pixels.tt.viewcontent.use`
  - Opções: Não / Sim, adicionar
  - Descrição: Configura se o evento ViewContent deve ser disparado após um tempo específico de visualização da página.

- **Tempo em segundos após o qual ViewContent é enviado:**
  - Campo: `pixels.tt.viewcontent.time`
  - Valor padrão: 30 segundos
  - Descrição: Define o tempo em segundos após o qual o evento ViewContent será enviado. Se 0, o evento não será disparado.

- **Porcentagem de rolagem da página antes do evento ViewContent:**
  - Campo: `pixels.tt.viewcontent.percent`
  - Valor padrão: 75%
  - Descrição: Especifica a porcentagem de rolagem da página necessária para disparar o evento ViewContent. Se 0, o evento não será disparado.

- **Qual evento usaremos para conversão no TikTok?**
  - Campo: `pixels.tt.conversion.event`
  - Valor padrão: Purchase
  - Descrição: Define o evento que será utilizado para marcar conversões no TikTok, como CompletePayment ou AddPaymentInfo.

## Correlação com o Fluxo de Execução Principal

O fluxo de execução principal da aplicação é afetado pelas configurações de métricas e pixels da seguinte maneira:

1. **Carregamento da Página:**
   - Ao carregar uma página, a aplicação verifica as configurações de métricas e pixels definidas em `admin/editsettings.php`.
   - Se configurado, o Google Tag Manager e o Yandex.Metrika são inicializados com seus respectivos IDs.

2. **Interação do Usuário:**
   - Eventos como PageView e ViewContent são disparados com base nas configurações de pixels do Facebook e TikTok.
   - A aplicação monitora o tempo de visualização e a rolagem da página para determinar quando disparar esses eventos.

3. **Conversões:**
   - Quando um usuário realiza uma ação que leva a uma conversão (como um Lead ou Purchase), a aplicação utiliza os eventos de conversão configurados para os pixels do Facebook e TikTok.
   - Esses eventos são enviados aos respectivos serviços de análise para rastreamento e relatórios.

4. **Processamento de Dados:**
   - Os dados coletados pelas métricas e pixels são processados e armazenados para análise posterior.
   - A aplicação pode utilizar esses dados para otimizar o fluxo de execução e melhorar a experiência do usuário.

## Conclusão

A seção "Métricas e Pixels" do arquivo `admin/editsettings.php` é fundamental para a coleta de dados e rastreamento de atividades na aplicação. As configurações definidas nesta seção influenciam diretamente o fluxo de execução principal, permitindo uma análise detalhada do comportamento do usuário e da eficácia das campanhas. A correta configuração dessas métricas e pixels é essencial para o sucesso da aplicação e para a tomada de decisões informadas baseadas em dados.
