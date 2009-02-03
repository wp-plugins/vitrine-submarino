=== Vitrine Submarino ===
Contributors: Bernardo Bauer
Donate link: http://bernabauer.com/wp-plugins
Tags: brasil, monetização, submarino, vitrine 
Requires at least: 2.5
Tested up to: 2.7
Stable tag: 3.1

Mostra vitrine de produtos do Submarino.com.

== Description ==

Mostre produtos do Submarino de acordo com as fontes RSS que o Submarino oferece. Você pode mostrar Mais Vendidos, Lançamentos e Promoções. Os produtos são coletados através do RSS e mostrados de maneira aleatória nas vitrines. É possível escolher uma categoria para cada tipo de vitrine de produtos.

São duas funcionalidades distintas para o plugin. Widget e Vitrine Simples.

**Widget**

Requer tema compatível com Widgets e mostra produtos baseados na escolha de uma palavra chave definida na página de administração.

**Vitrine Simples**

Vitrine Simples é a antiga vitrine contextual. Como não é mais possível pesquisar produtos via scraping, a contextualização já não faz mais sentido. Neste momento a Vitrine Simples mostra apenas os produtos da categoria que for escolhida através da página de administração.

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

= Meu hospedeiro é a GoDaddy, e o plugin não funciona!!! =

A GoDaddy é um tanto restritiva quanto ao que roda em seus sevidores. Apesar de ter criado um código especifico para a GoDaddy, a vitrine ainda assim parece não funcionar.

= Como faço um reset nas configurações do plugin? Como removo o plugin totalmente? =

Basta habilitar a opção `Remover opções ao desativar`, atualizar as opções e depois desativar o plugin. Se quiser reabilitar o plugin, as configurações voltarão ao estado "de fábrica".