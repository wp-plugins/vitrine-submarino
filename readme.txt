=== Vitrine Submarino ===
Contributors: Bernardo Bauer
Donate link: http://bernabauer.com/wp-plugins
Tags: brasil, monetização, submarino, vitrine 
Requires at least: 2.5
Tested up to: 2.5
Stable tag: 1.1

Mostra vitrine de produtos do Submarino.com.

== Description ==

Inspirado em Script de Jobson Lemos. O plugin mostra uma quantidade de ofertas configuráveis ao gosto do freguês. 

São duas funcionalidades distintas para o plugin. Widget e Contextual.

**Widget**

Requer tema compatível com Widgets e mostra produtos baseados na escolha de uma palavra chave definida na página de administração.

**Contextual**

Requer plugins [Palavras de Monetização](http://www.bernabauer.com/wp-plugins/ "Conheça os meus plugins"). A partir de palavras cadastradas por este plugin o Vitrine Submarino faz uma pesquisa no site da loja e trás produtos que são mostrados antes ou depois do artigo. A vitrine pode ser exibida de maneira automática ou manual. O código para exibição manual estará disponível em breve.

Este plugin só foi testado com a versão 2.5 do Wordpress

== Installation ==

Atenção: Para utilização do widget, é necessário ter um tema compatível com widgets.

1. Faça o upload `vitrinesubmarino.php` para a pasta `/wp-content/plugins/`
2. Ative o plugin através do menu 'Plugins' na página de administração de seu WordPress
3. Configure o plugin através do menu 'Settings'
4. Inclua o widget no seu tema.

== Frequently Asked Questions ==

= Recebo a mensagem "* Parece que você atualizou a versão nova sem desativar o plugin!! Por favor desative e re-ative.", mas já desativei e reativei o plugin e nada do plugin funcionar. =

Esta mensagem aparece quando o plugin não consegue identificar a sua variável de opções. Observe a versão do seu Wordpress. Este plugin só funciona com Wordpress 2.5 e superiores. Fique atento também ao local de instalação do plugin. Ele deve ficar na raiz da pasta `Plugins`. 

= Rastrear cliques? O que é isto? =

Este plugin permite fazer este rastreamento de cliques nos produtos da vitrine. Para sabe quantos cliques e em que produtos eles ocorreram, é necessário ter uma conta no Google Analytics. Pra saber quantos cliques foram feitas em todas as vitrines basta procurar por "/sub/" em "Top Content" dentro da opção "Content" no seu Google Analytics. Para saber quantos foram os cliques na vitrine contextual basta procurar por "contextual" e no widget procure por "/widget/". Não esqueça de colocar as barras! Elas são importantes. 

= O que é um widget? =

Widget é uma palavra criada para denominar códigos que permitem agregar funcionalidades a um tema sem a necessidade de editar códigos de um plugin. Você pode ver mais a respeito [aqui](http://automattic.com/code/widgets/ "Saiba mais sobre widgets, em inglês").

= O plugin não funciona! =

O script do Jobson **requer PHP 5** ou superior para funcionar. Se o seu blog está usando a versão 4, não tem como o plugin mostrar a vitrine.

= Onde eu acho o plugin Palavras de Monetização? =

Este plugin é necessário para usar a parte contextual da vitrine. É outro plugin de minha autoria que você acha [aqui](http://www.bernabauer.com/wp-plugins/ "Conheça todos os meus plugins").

= Quem é Jobson Lemos ? =

Jobson Lemos é o autor do script que pega os produtos na página do submarino. O que eu fiz foi apenas criar uma página de administração e toda a personalização necessária para usar o script da maneira mais fácil possível dentro do Wordpress. O script original do Jobson pode ser encontrado [aqui](http://jobsonlemos.com/?p=64 "script criado pelo jobson").