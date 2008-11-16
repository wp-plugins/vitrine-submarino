=== Vitrine Submarino ===
Contributors: Bernardo Bauer
Donate link: http://bernabauer.com/wp-plugins
Tags: brasil, monetização, submarino, vitrine 
Requires at least: 2.5
Tested up to: 2.7
Stable tag: 3.0

Mostra vitrine de produtos do Submarino.com.

== Description ==

O plugin mostra uma quantidade de ofertas configuráveis ao gosto do freguês. Até o meio de outubro o plugin funcionava perfeitamente, porém o Submarino mudou o site e agora não é mais possível usar o script que o Jobson criou. Após algumas mensagens com o Submarino consegui montar um esquema de webservice para continuar mostrando os produtos conforme o plugin sempre fez. Para tal, meu servidor passou a importar a base de produtos do Submarino. Com isto a carga de rede e processamento subiu bastante e por isto, em alguns casos o meu código de afiliado será exibido. Assim será possível baixar os custos que acabei assumindo para continuar oferecendo o plugin.

São duas funcionalidades distintas para o plugin. Widget e Contextual.

**Widget**

Requer tema compatível com Widgets e mostra produtos baseados na escolha de uma palavra chave definida na página de administração.

**Contextual**

Requer plugins [Palavras de Monetização](http://www.bernabauer.com/wp-plugins/ "Conheça os meus plugins"). A partir de palavras cadastradas por este plugin o Vitrine Submarino faz uma pesquisa no site da loja e trás produtos que são mostrados antes ou depois do artigo. A vitrine pode ser exibida de maneira automática ou manual. O código para inserção manual da vitrine está disponível na página de administração do `Vitrine Submarino`.

Se o `Palavras de Monetização` não estiver ativo, serão mostrados produtos encontrados através da palavra chave cadastrada na página de administração do `Vitrine Submarino`.

A nova vitrine com abas só funciona com o `Palavras de Monetização` ativo, pois ele pega as palavras cadastradas por este plugin para mostrar as diversas vitrines. Quanto mais palavras cadastradas, mais tempo sua página demorará para ser carregada.

== Installation ==

Atenção: Para utilização do widget, é necessário ter um tema compatível com widgets.

1. Faça o upload da pasta `vitrinesubmarino` para a pasta `/wp-content/plugins/`
2. Ative o plugin através do menu 'Plugins' na página de administração de seu WordPress
3. Configure o plugin através do menu 'Settings'
4. Inclua o widget no seu tema ou insira manualmente o código para exibição da vitrine. Você pode também ativar a exibição automática de produtos, basta ativar a opção na página de configuração do plugin

== Frequently Asked Questions ==

= Como atualizo o plugin? = 

Recomendo desativar o plugin, atualizar e depois reativar. Assim você garante que todas as atualizações foram bem feitas. 

= Recebo a mensagem "* Parece que você atualizou a versão nova sem desativar o plugin!! Por favor desative e re-ative.", mas já desativei e reativei o plugin e nada do plugin funcionar. =

Esta mensagem aparece quando o plugin não consegue identificar a sua variável de opções. Observe a versão do seu Wordpress. Este plugin só funciona com Wordpress 2.5 e superiores. Fique atento também ao local de instalação do plugin. Ele deve ficar na raiz da pasta `Plugins`. 

= Rastrear cliques? O que é isto? =

Este plugin permite fazer este rastreamento de cliques nos produtos da vitrine. Para saber quantos cliques e em que produtos eles ocorreram, é necessário ter uma conta no Google Analytics. Pra saber quantos cliques foram feitas em todas as vitrines basta procurar por "/sub/" em "Top Content" dentro da opção "Content" no seu Google Analytics. Para saber quantos foram os cliques na vitrine contextual basta procurar por "contextual" e no widget procure por "/widget/". Não esqueça de colocar as barras! Elas são importantes. 

= O que é um widget? =

Widget é uma palavra criada para denominar códigos que permitem agregar funcionalidades a um tema sem a necessidade de editar códigos de um plugin. Você pode ver mais a respeito [aqui](http://automattic.com/code/widgets/ "Saiba mais sobre widgets, em inglês").

= Meu tema não tem suporte a widgets! Como mostro a vitrine? =

Sei lá. Eu recomendo o uso de widgets. É uma mão na roda, não sei por que você não usa! Trate logo de trocar o seu tema, ou inclua o suporte você mesmo no tema. Não é complicado e você vai ganhar uma facilidade enorme para alterar sua `sidebar`.

= O plugin não funciona! =

O plugin **requer PHP 5** ou superior para funcionar. Se o seu blog está usando a versão 4, não tem como o plugin mostrar a vitrine. Sorry! :-(

= Onde eu acho o plugin Palavras de Monetização? =

Este plugin é necessário para usar a parte contextual da vitrine. É outro plugin de minha autoria que você acha [aqui](http://www.bernabauer.com/wp-plugins/ "Conheça todos os meus plugins").

= Meu hospedeiro é a GoDaddy, e o plugin não funciona!!! =

A GoDaddy é um tanto restritiva quanto ao que roda em seus sevidores. Apesar de ter criado um código especifico para a GoDaddy, a vitrine ainda assim parece não funcionar.

= Como faço um reset nas configurações do plugin? Como removo o plugin totalmente? =

Basta habilitar a opção `Remover opções ao desativar`, atualizar as opções e depois desativar o plugin. Se quiser reabilitar o plugin, as configurações voltarão ao estado "de fábrica".

= Onde eu posso buscar mais ajuda para o meu problema? = 

Você pode tentar buscar ajuda no [fórum do plugin] (http://forum.bernabauer.com/ "fórum"). Lá você pode publicar sua dúvida ou problema. A comunidade poderá ajudar e eu tentarei responder a seua mensagem assim que possível.
