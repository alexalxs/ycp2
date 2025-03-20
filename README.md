```                
                            Yellow Cloaker  
    _            __     __  _ _             __          __  _     
   | |           \ \   / / | | |            \ \        / / | |    
   | |__  _   _   \ \_/ /__| | | _____      _\ \  /\  / /__| |__  
   | '_ \| | | |   \   / _ \ | |/ _ \ \ /\ / /\ \/  \/ / _ \ '_ \ 
   | |_) | |_| |    | |  __/ | | (_) \ V  V /  \  /\  /  __/ |_) |
   |_.__/ \__, |    |_|\___|_|_|\___/ \_/\_/    \/  \/ \___|_.__/ 
           __/ |                                                  
          |___/             https://yellowweb.top                 

Se você gostou deste script, POR FAVOR FAÇA UMA DOAÇÃO!  sim
USDT TRC20: TKeNEVndhPSKXuYmpEwF4fVtWUvfCnWmra
Bitcoin: bc1qqv99jasckntqnk0pkjnrjtpwu0yurm0qd0gnqv  
Ethereum: 0xBC118D3FDE78eE393A154C29A4545c575506ad6B  
```

# Yellow Cloaker por [Yellow Web](https://yellowweb.top)
Versão em inglês desta ajuda está abaixo 👇 Aviso: esta ajuda está desatualizada! Agora todas as configurações são feitas usando a interface: */admin?password=12345*
**Use PHP versão 7.2 ou superior e crie certificados HTTPS para todos os seus domínios!**

# Suporte
Se você quer que este projeto continue se desenvolvendo, [**apoye o autor com uma doação**!](https://t.me/yellowwebdonate_bot)

# Descrição
Script de cloaking modificado para arbitragem de tráfego, originalmente encontrado no [Black Hat World](http://blackhatworld.com).

# Materiais de Referência
- [ÚLTIMO stream onde toda a interface gráfica do cloaker é explicada](https://youtu.be/-ikmpq-L8ZE)
- [Stream onde o cloaker é detalhado com todas as funções](https://www.youtube.com/watch?v=XMua15r2dwg&feature=youtu.be)
- [Vídeo com visão geral das novas funcionalidades aqui.](https://www.youtube.com/watch?v=x-Z2Y4lEOc0&t=656s)
- [Descrição da configuração das primeiras versões aqui!](https://yellowweb.top/%d0%ba%d0%bb%d0%be%d0%b0%d0%ba%d0%b8%d0%bd%d0%b3-%d0%b4%d0%bb%d1%8f-%d0%b1%d0%b5%d0%b4%d0%bd%d0%be%d0%b3%d0%be-%d0%bd%d0%be-%d1%83%d0%bc%d0%bd%d0%be%d0%b3%d0%be-%d0%b0%d1%80%d0%b1%d0%b8%d1%82%d1%80%d0%b0/)

# Instalação
Baixe a última versão de todos os arquivos deste repositório e faça upload para seu servidor. O servidor deve ter **PHP versão 7.2 ou superior** habilitado e você deve **criar um certificado HTTPS para seu domínio. Sem HTTPS o cloaker não funcionará corretamente e a opção de simplesmente habilitar HTTPS no CloudFlare não funciona! Sim, se você usar CloudFlare, depois que um certificado normal for emitido, habilite HTTPS no modo Full!** Posso [recomendar o servidor Beget para o cloaker](https://yellowweb.top/beget), é simples e conveniente e você pode emitir um certificado HTTPS com alguns cliques.

Se você tiver pré-landings e landings locais, crie uma pasta para cada um deles na pasta raiz do cloaker e copie seus arquivos cada um em sua própria pasta.
*Por exemplo:*
Se você tiver 2 pré-landings e 2 landings, crie 2 pastas para pré-landings: p1 e p2. E duas pastas para landings: land1, land2.

# Configuração
Para configurar o cloaker, uma interface de usuário foi criada, acessível em: https://seu.dominio/admin?password=12345 Não se esqueça de alterar a senha de acesso!

## Configuração da Página Branca
A página branca é a página mostrada ao visitante que não passou pelos filtros do cloaker. São visitantes indesejados.

Primeiro, você precisa decidir qual tipo de página branca deseja usar. O cloaker pode:
- mostrar páginas brancas locais
- redirecionar para qualquer outro site
- carregar conteúdo de qualquer outro site via CURL
- retornar qualquer código HTTP (por exemplo, erro 404 ou simplesmente 200)

Depois de decidir, altere o valor para um dos seguintes:

### Página branca local da pasta
Isso é para páginas brancas locais. Você deve criar uma pasta na raiz do cloaker, por exemplo *white* e copiar todos os arquivos da página branca para lá. Em seguida, escreva o nome da pasta no campo correspondente

### Redirecionamento
Isso é para redirecionar todo o tráfego branco para outro site. Digite o endereço do site e escolha o tipo de redirecionamento. Pode ser: 301, 302, 303 ou 307. Pesquise a diferença se isso for importante para você.

### Curl
Isso é para carregar conteúdo de qualquer outro site. Escreva o endereço do site no campo correspondente.

### Retorno de código HTTP
Você pode retornar qualquer erro HTTP para o tráfego branco. Por exemplo: *404*. Ou código *200* para mostrar uma página em branco.

## Páginas brancas individuais para diferentes domínios
Se você tiver vários domínios (ou subdomínios) vinculados ao seu servidor e estiver direcionando tráfego para eles, você pode fazer com que diferentes páginas brancas sejam mostradas para diferentes domínios, alterando a configuração correspondente.

Em seguida, preencha os campos. O formato é assim:
`seu.dominio => whiteaction:valor`
Por exemplo:
`https://meudominio.com => curl:https://ya.ru`
Todos os valores possíveis de whiteaction: *folder, curl, redirect, error*

## Configuração do Funil
O cloaker pode trabalhar com os seguintes funis:
- landing local (ou várias landings)
- pré-landing local (pré-landings) -> landings locais
- pré-landings locais + redirecionamento para landing em outro site
- redirecionamento imediato para outro site

Vamos analisar todas essas configurações.

### Landings Locais
Você pode usar uma ou várias landings. O tráfego será dividido igualmente entre elas. Por exemplo, para duas landings será 50/50. Cada landing deve estar em sua própria pasta. Defina **"Não usar pré-landing"** e o método de carregamento das landings como **"Landings locais da pasta"**. Se houver várias landings, use vírgula como separador. Por exemplo:
`land1,land2`

### Pré-landings Locais - Landings Locais
Faça tudo o mesmo que no item sobre **Landings Locais** mas também preencha o campo **"Pastas onde estão as pré-landings"**. Por exemplo, para duas pré-landings:
`p1,p2`

### Pré-landings Locais + redirecionamento
Preencha os nomes das pastas das pré-landings. Por exemplo, para duas pré-landings:
`p1,p2`
Em seguida, altere **"Método de carregamento das landings"** para *Redirecionamento*. Último passo: preencha o endereço de redirecionamento.

### Redirecionamento Imediato
Se você simplesmente quiser redirecionar todo o tráfego que passa pelos filtros do cloaker, então use **$black_action = *'redirect'*** e preencha o endereço de redirecionamento **$black_redirect_url**. Também escolha o tipo de redirecionamento: 301, 302, 303 ou 307. Pesquise a diferença se isso for importante para você. Digite o tipo de redirecionamento em **$black_redirect_type**.

### Configuração do script de conversão da landing local
Cada landing tem a capacidade de enviar leads para a rede de afiliados (cap!). E cada rede de afiliados tem sua própria mecânica para enviar esses leads.

Por padrão, o cloaker procura pelo arquivo *order.php*, localizado na pasta da landing. Se o script da sua rede de afiliados tiver um nome diferente, renomeie o valor na variável **$black_land_conversion_script**. Para entender como o script de envio é chamado, abra o arquivo index da landing e procure por qualquer formulário - *<form*. Veja o atributo *action* do formulário. É lá que o script está escrito. Se não houver atributo *action*, significa que o lead é enviado pelo arquivo index!

Se o script estiver em alguma pasta, digite o caminho relativo para o script, por exemplo:
`$black_land_conversion_script='pasta/conversao.php';`

Depois de configurar tudo isso, envie um lead de teste. Se o lead não aparecer nas estatísticas da rede de afiliados, abra o script de envio de leads e procure por linhas como:
`exit();`
Se houver, remova ou comente essas linhas (considerando a sintaxe da linguagem!!!).

### Configuração da página de Agradecimento
O visitante chega à página de Agradecimento depois de enviar seus dados da landing *ou da página branca*! O conteúdo da página é carregado da pasta *thankyou* do cloaker. Se você olhar, há vários arquivos html lá, nomeados com códigos de duas letras para idiomas. Digite o idioma desejado da página de agradecimento em **$thankyou_page_language**.

Se não houver página de Agradecimento para seu idioma - crie uma! É tão fácil quanto carregar a versão em inglês da página de Agradecimento no navegador Chrome e traduzir usando o tradutor embutido para o idioma desejado. Em seguida, salve a tradução com o nome apropriado, por exemplo *PT.html*.

**Atenção**: abra a página traduzida em um editor de texto e certifique-se de que os 2 macros *{NAME}* e *{PHONE}* NÃO foram traduzidos. Se foram - coloque-os de volta!

Se você quiser usar sua própria página de Agradecimento, renomeie-a com o código de duas letras do idioma e coloque todos os arquivos necessários na pasta *thankyou*.

#### Coleta de e-mails na página "Obrigado"
A página Obrigado padrão tem um formulário de coleta de e-mail embutido. Se você não precisar dele - simplesmente delete-o no código. Mas se precisar, você precisa criar mais uma página: aquela para onde o visitante será redirecionado DEPOIS de enviar o formulário de e-mail. Ela deve ser nomeada usando o mesmo código de duas letras do idioma + email no final. Por exemplo: *PTemail.html*. Na pasta *thankyou* há um exemplo de tal página.

## Configuração de Pixels
Você pode adicionar vários pixels em suas pré-landings e landings. A lista completa inclui:
- Yandex Metrika
- Google Tag Manager
- Facebook Pixel

### Yandex Metrika
Para adicionar o script do Yandex Metrika em suas pré-landings e landings, simplesmente preencha seu ID do Yandex Metrika. Coloque-o em **$ya_id**.

### Google Tag Manager
Para adicionar o script do Google Tag Manager em suas pré-landings e landings, simplesmente preencha seu ID do GTM. Coloque-o em **$gtm_id**.

### Facebook Pixel
O ID do pixel do Facebook é obtido do link. Ele deve estar no formato: *px=1234567890*. Por exemplo:
`https://seu.dominio?px=5499284990`
Se a URL tiver o parâmetro *px*, então o código completo do pixel do Facebook será adicionado à página Obrigado. Você pode definir o evento do pixel do Facebook na variável **$fb_thankyou_event**. Por padrão é *Lead*, mas você pode mudá-lo para *Purchase* ou qualquer outro que precisar.

Você também pode usar o evento *PageView* do pixel. Para fazer isso, mude **$fb_use_pageview** para *true*. Se fizer isso, o código do pixel será adicionado a todas as suas pré-landings e landings locais e elas enviarão o evento *PageView* para o Facebook para cada visitante.

Use o plugin Facebook Pixel Helper para Google Chrome para verificar se os eventos do pixel estão sendo disparados corretamente!

## Configuração dos Filtros do Cloaker
O cloaker pode filtrar tráfego com base em:
- Banco de dados IP integrado
- Sistema operacional do visitante
- País do visitante
- User Agent do visitante (navegador)
- ISP do visitante (provedor)
- Presença de referência
- Qualquer parte do link pelo qual foi feita a transição

*Nota:* em todos os lugares onde você quiser usar vários parâmetros, use vírgula como separador!

Primeiro, coloque todos os sistemas operacionais permitidos em **$os_white**. A lista completa é:
- Android
- iOS
- Windows
- Linux
- OS X
- e outros menos populares...

Escolha os que você precisar.

Em seguida, coloque todos os códigos de duas letras dos países permitidos em **$country_white**. Por exemplo: *BR,PT,ES,IT*.

Agora, remova todos os provedores de internet indesejados. Adicione-os em **$isp_black**. Por exemplo: *google,facebook,yandex*. Se você quiser proteger suas landings de serviços de spy, adicione aqui todos os provedores de nuvem, como: *amazon,azure* etc.

Adicione em **$ua_black** palavras pelas quais os User Agents indesejados serão filtrados.
Por exemplo: *facebook,Facebot,curl,gce-spider*

Adicione em **$tokens_black** palavras que podem estar no link pelo qual o visitante chegou, que indicam que ele deve ver a página branca, ou deixe essa variável vazia - ''.

Se você tiver uma lista adicional de endereços IP dos quais quer se livrar - adicione-os em **$ip_black**.

E finalmente: se você quiser bloquear visitantes *diretos*, então mude **$block_without_referer** para *true*. **Aviso**: alguns sistemas operacionais e navegadores não passam a referência corretamente ou não a passam de todo. Então, se quiser usar esse recurso, teste isso primeiro com uma pequena quantidade de tráfego, ou você pode perder dinheiro.

## Configuração da Distribuição de Tráfego
Você pode desativar temporariamente todos os filtros do cloaker e enviar todo o tráfego para a página branca. Por exemplo, durante a moderação. Para fazer isso, mude **$full_cloak_on** para *true*.

Você também pode desativar todos os filtros do cloaker e sempre mostrar a página preta. Por exemplo, para fins de teste. Para fazer isso, mude **$disable_tds** para *true*.

Você pode salvar o "caminho" do usuário (ou seja, as pré-landings e landings que ele verá no funil). Assim, ele sempre verá as mesmas páginas, quantas vezes entrar. Para fazer isso, mude **$save_user_flow** para *true*.

## Configuração de Estatísticas e Postback
Suas estatísticas são protegidas com senha, para defini-la, por favor preencha a variável **$log_password**.

Se você nomear seus criativos adequadamente e passar seus nomes da fonte de tráfego, você poderá ver o número de cliques para cada criativo nas Estatísticas. Para fazer isso, coloque o nome do parâmetro em que você passa o nome do criativo na variável **$creative_sub_name**. Por exemplo, se seu link se parece com isso:
`https://seu.dominio?meucriativo=otimocriativo`
então você precisa fazer assim:
`$creative_sub_name = 'meucriativo';`

### Configuração do Postback
O cloaker é capaz de receber postbacks da sua rede de afiliados e mostrar o status dos leads nas estatísticas. Primeiro, você precisa passar o ID único do visitante - subid. O subid é criado automaticamente para cada visitante e armazenado em um cookie. Você deve perguntar ao seu gerente como passar o subid para a rede de afiliados (eles geralmente conhecem esse parâmetro como clickid) e qual sub-parâmetro você deve usar. Geralmente é feito usando sub-parâmetros como *sub1* ou *subacc*. Vamos usar *sub1* para este exemplo. Então, devemos editar o array **$sub_ids**, a parte que tem *subid* no lado esquerdo para ficar assim:
`$sub_ids = array("subid"=> "sub1", .....);`
Desta forma, dizemos ao cloaker para pegar o valor de *subid* e adicioná-lo a todos os formulários na landing na forma de *sub1* (ou adicioná-lo ao seu link de redirecionamento, se você não tiver landing local). Então, se o *subid* fosse *12uion34i2*, teremos:
- no caso de landing local
`<input type="hidden" name="sub1" value="12uion34i2"`
- no caso de redirecionamento `http://link.redirecionamento?sub1=12uion34i2`

Agora precisamos dizer à rede de afiliados para onde enviar as informações do postback. O cloaker tem o arquivo *postback.php* em sua pasta raiz. É o arquivo que recebe e processa os postbacks. Precisamos receber 2 parâmetros da rede de afiliados: *subid* e status do lead. Usando essas duas coisas, o cloaker pode mudar o status do lead em seus logs e mostrar essa mudança nas Estatísticas.

Procure na ajuda ou pergunte ao seu gerente: qual macro sua rede usa para enviar o *status*, geralmente é chamado assim mesmo: *{status}*. Então, voltando ao nosso exemplo: enviamos o *subid* em *sub1*, então o macro para receber nosso *subid* será *{sub1}*. Vamos criar a URL completa do postback. Você deve colocar esta URL no campo Postback da sua Rede de Afiliados. Por exemplo:
`https://seu.dominio/postback.php?subid={sub1}&status={status}`

Agora, pergunte ao seu gerente de afiliados ou procure na seção de ajuda deles, quais são os status que eles nos enviam no postback. Geralmente são:
- Lead
- Purchase
- Reject
- Trash

Se sua rede de afiliados usa outros status, então mude os valores dessas variáveis de acordo:
- **$lead_status_name**
- **$purchase_status_name**
- **$reject_status_name**
- **$trash_status_name**

Depois de configurar, envie um lead de teste e observe na página Leads como o status muda para *Trash* após um tempo.

## Configuração de Scripts Adicionais
### Desativar Botão Voltar
Você pode desativar o botão voltar no navegador do visitante para que ele não possa sair da sua página. Para fazer isso, mude **$$disable_back_button** para *true*.

### Substituir Botão Voltar
Você pode substituir a URL do botão voltar no navegador do visitante. Assim, depois que ele clicar nele, será redirecionado para outro lugar, por exemplo para outra oferta. Para fazer isso, mude **$replace_back_button** para *true* e coloque a URL que você quer em **$replace_back_address**.

**Aviso:** Não use este script junto com o script **Desativar Botão Voltar**!!!

### Desativar Seleção de Texto, Ctrl+S e Menu de Contexto
Você pode desativar a capacidade de selecionar texto em suas pré-landings e landings, desativar a capacidade de salvar a página usando as teclas Ctrl+S e também desativar o menu de contexto do navegador. Para fazer isso, simplesmente mude **$disable_text_copy** para *true*.

### Substituir Pré-landing
Você pode fazer o cloaker abrir a landing page em uma aba separada do navegador e então redirecionar a aba com a pré-landing para outra URL. Depois que o usuário fechar a aba da sua landing page, ele verá a aba com esta URL. Use isso para mostrar outra oferta ao usuário. Para fazer isso, mude **$replace_prelanding** para *true* e coloque sua URL em **$replace_prelanding_address*.

### Máscaras para Telefones
Você pode dizer ao cloaker para usar máscaras para o campo de telefone em suas landings locais. Quando você fizer isso, o visitante não poderá adicionar letras no campo de telefone, apenas números. A máscara define a contagem de números e delimitadores. Para ativar as máscaras, simplesmente mude **$black_land_use_phone_mask** para *true* e edite sua máscara em **$black_land_phone_mask*.

# Verificação
Adicione seu próprio país aos filtros do cloaker para poder ver a página preta. Em seguida, passe por todos os componentes do funil. Envie um lead de teste, verifique se ele chegou à sua rede de afiliados.

# Executando Tráfego e Estatísticas
Depois que você começar a executar tráfego, você pode monitorá-lo e também olhar as estatísticas. Para fazer isso, simplesmente vá para um link como este:
`https://seu.dominio/logs?password=sua_senha`
onde *sua_senha* é o valor de **$log_password** do arquivo *settings.php*.

# Integração Javascript
Você pode conectar este cloaker a qualquer site ou construtor de sites que permita adicionar Javascript. Por exemplo: *GitHub, Wix, Shopify* e assim por diante.
Quando você fizer isso, você direciona tráfego para o construtor de sites e depois que o visitante chegar a este site, um pequeno script verifica se ele tem permissão para ver a página preta. Se tiver, então 2 coisas podem acontecer:
- Um redirecionamento para sua página preta
- O conteúdo do construtor de sites é substituído pela página preta

## Redirecionamento
Simplesmente adicione este script ao seu construtor de sites:
`<script src="https://seu.dominio/js/indexr.php"></script>`

## Substituição de Conteúdo
Simplesmente adicione este script ao seu construtor de sites:
`<script src="https://seu.dominio/js"></script>`
Não use este método se você tiver apenas landings sem pré-landings!

# Detalhes Técnicos
## Componentes Usados
Este cloaker usa:
- Bancos de Dados MaxMind para detecção de ISP, País e Cidade
- Faixas de IP de Bot de várias fontes coletadas em toda a Internet no formato CIDR
- Sinergi BrowserDetector para (surpresa!) detecção de navegador
- IP Utils do Symphony para verificar se o endereço IP está em uma faixa selecionada
- Ícones de https://www.flaticon.com/free-icons/question

## Fluxo de Tráfego
Depois que o visitante passa pelos filtros do cloaker, geralmente é mostrada a pré-landing (se você tiver uma). Na pré-landing, todos os links são substituídos pelo link para o script *landing.php*. Depois que o visitante clica no link, o script *landing.php* obtém o conteúdo da landing, substitui a action de todos os formulários para *send.php*, adiciona todos os scripts adicionais e mostra o conteúdo ao visitante. Quando o visitante preenche o formulário e o envia, *send.php* chama o script de envio original e então remove todos os redirecionamentos dele. Depois disso, *send.php* redireciona para *thankyou.php* que mostra a página de agradecimento como descrito nas seções acima.
