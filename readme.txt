=== Vitrine Submarino ===
Contributors: bernabauer
Donate link: http://bernabauer.com/
Tags: brasil, monetização, submarino, vitrine 
Requires at least: 3.0
Tested up to: 3.1
Stable tag: 3.6.1

Mostra vitrine de produtos do Submarino.com. A vitrine pode ser mostrada através de widget na sidebar do blog e também pode ser incluído automaticamente antes ou após o texto dos seus artigos. O plugin permite ainda mostrar produtos de maneira contextual se for utilizado o plugin [Palavras de Monetização](http://bernabauer.com/wp-plugins/palavras-de-monetizacao/ "Palavras de Monetização").

== Description ==

Mostre produtos do Submarino baseados em palavras chaves. A funcionalidade de pegar produtos através do feed RSS foi retirado nesta versão por que o Submarino deixou de atualizar os produtos desta maneira e não comunicou ou avisou as mudanças.

São duas funcionalidades distintas para o plugin. Widget e Vitrine Simples/Contextual.

**Widget**

Requer tema compatível com Widgets e mostra produtos baseados na escolha de uma palavra chave definida na página de administração.

**Vitrine Contextual**

Busca palavras cadastradas através do plugin Palavras de Monetização para mostrar produtos contextuais que estão na loja do Submarino.

Os produtos da Vitrine Contextual e do widget ficam armazenados localmente na base de dados do seu blog. O cache de produtos sempre é descartado a cada 24 horas pegando os produtos com suas descrições e preços a cada primeira visita que a palavra é pedida e não está na base de dados. Na página de administração do plugin é possível ver quantos produtos e palavras compõe o cache.

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

= Como desinstalo o plugin completamente? =

Para remover todas as informações do plugin, basta escolher a opção "Excluir" na página de administraçãode plugins do WordPress. Isto removerá todos os arquivos do plugin, assim como suas configurações e dados de cache.

== Changelog ==

= 3.6.1 =
* Correção na inclusão de produtos que já tenham "?" no seu link

= 3.6 = 
* Implementado nofollow nos links da vitrine
* Ganhos acumulados podem ser vistos na página de administração do blog e recebidos por email
* Nova vitrine com produtos mais vendidos e com maiores descontos
* Fonte de produtos agora pode ser de tags do artigo, além do plugin Palavras de Monetização
* Cache não apaga mais todos os produtos diariamente, mas sim os produtos com mais de 24 horas no cache
* Vitrine com menos produtos do que configurado agora tem tamanho correto para cada produto
* Correções de códigos PHP e melhor compatibilidade com WordPress 3.0

= 3.5 =
* Revisão do código para compatibilizar com as normas de desenvolvimento para o WordPress 3.0
* Retirada de códigos desnecessários e não utilizados.
* Consertado código de teste de produtos válidos para palavra informada.
* Inclusão do novo banner 300x250 para os anuncios alternativos.
* Inclusão de formatos de banner (728x90, 468x60 e 250x250) para as vitrines.
* Suporte para processo de desinstalação nativo do WordPress para remover o plugin.

= 3.4.1 =
* Ajuste para tornar compatível com o WordPress 3.0

= 3.4 =
* Widget agora é contextual na página de um único artigo, basta escolher entre utilizar a palavra padrão ou palavras cadastradas pelo [Palavras de Monetização](http://bernabauer.com/wp-plugins/palavras-de-monetizacao/ "Palavras de Monetização")
* Grandes mudanças na utilização das funções.
* Incluido links para o fórum de suporte.

= 3.3.1 =
* Links de comparação de preços não estava funcionando corretamente.
* Mensagem de warning ao processar resultado de pesquisa era mostrada após atualização das opções do plugin.

= 3.3 =
* plugin agora entende as páginas de resultado de pesquisa para determinados produtos.
* consertado problema do link de afiliado que não aparecia na sintaxe correta.
* melhoria no tratamento da vitrine.
* introdução do primeiro slot diferenciado para pesquisa de preços. Slot diferenciado só aparece se existirem palavras cadastradas através do Palavras de Monetização.
* remoção das fontes de produtos via RSS (culpa do submarino que parou de atualizar o RSS com produtos novos.)

