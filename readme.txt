=== wpshop ===
Contributors: Eoxia
Tags: shop, boutique, produits, e-commerce, commerce
Donate link: http://www.eoxia.com/
Requires at least: 3.0.4
Tested up to: 3.0.4
Stable tag: 1.2.1.1

Plugin de gestion de produits sous wordpress

== Description ==

Plugin to manage e-commerce under wordpress / Plugin de gestion de produits sous wordpress


== Installation ==

L'installation du plugin peut se faire de 2 fa&ccedil;ons :

* M&eacute;thode 1

1. T&eacute;l&eacute;chargez le fichier zip depuis le site de wordpress
2. Envoyez le dossier `wpshop` dans le r&eacute;pertoire `/wp-content/plugins/`
3. Activer le plugin dans le menu `Extensions` de Wordpress

* M&eacute;thode 2

1. Rechercher le plugin "WPSHOP" &agrave; partir du menu "Extension" de Wordpress
2. Lancer l'installation du plugin


== Frequently Asked Questions ==

Question 1 : Comment ajouter un menu avec mon catalogue dans la partie visible du site ?

Pour le moment vous ne pouvez ajouter le contenu de votre catalogue sous forme de menu qu'&agrave; travers un widget. Pour cela rendez-vous dans la partie administration des widgets puis ajoutez le widget correspondant aux cat&eacute;gories de produit &agrave; l'endroit d&eacute;sir&eacute;. Vous pouvez lui donner un titre, si aucun titre n'est d&eacute;fini alors le titre par d&eacute;faut sera "Catalogue"

Question 2 : Mes produits et cat&eacute;gories ne sont pas accessible dans la partie visible du site ?

Il faut v&eacute;rifier que le r&eacute;glage des permaliens pour votre site est bien r&eacute;gl&eacute; sur "/%postname%"


== Screenshots ==

1. Interface de gestion des cat&eacute;gories (Aucune cat&eacute;gorie)
2. Interface de gestion des cat&eacute;gories (Avec une cat&eacute;gorie)
3. Interface d'&eacute;dition d'une cat&eacute;gorie
4. Fiche d'une cat&eacute;gorie sans sous-cat&eacute;gorie ni produit
5. Fiche d'une cat&eacute;gorie avec ses sous-&eacute;l&eacute;ments
6. Interface de listing des produits
7. Interface d'&eacute;dition des produits
8. Fiche d'un produit dans la partie publique
9. Liste des attributs
10. Interface d'&eacute;dition d'un attribut
11. Interface de gestion des attributs dans les groupes (drag and drop) . Permet d'organiser l'ordre et les attributs pr&eacute;sents.


== Changelog ==


= Version 1.2.1.1 =

Am&eacute;liorations 

* Ajout de la box permettant l'insertion d'un shortcode dans les articles 
* Affichage d'un bloc indiquant que le produit est inexistant si insertion d'un shortcode erron&eacute; 

Corrections 

* Le formulaire permettant de modifier les informations concernant les photos envoy&eacute;es ne s'affichait plus (L'encodage du fichier des unit&eacute;s des attributs provoquait une erreur) 
* Unit&eacute; par d&eacute;faut lors de la cr&eacute;ation d'un attribut 
* Insertion d'un espace avant et apr&eacute;s chaque shortcode ins&eacute;r&eacute; depuis la box dans les page et articles 
* Suppression du caract&egrave;re 'underscore' &agrave; la fin d'un attribut lors de la cr&eacute;ation 
* Lors de l'activation du plugin un message d'erreur apparait (Encodage du fichier de la classe des unit&eacute;s des attributs d&eacute;fini en UTF8) 
* Probl&egrave;me de cr&eacute;ation des tables de base du plugin (V&eacute;rification et cr&eacute;ation lors du chargement du plugin) 
* Affichage des messages d&eacute;cal&eacute;s sur certaines pages 
* Inclusion de certains javascript et de certaines fonctions entrant en conflit suivant les version de wordpress (Inf&eacute;rieure &agrave; 3.1 avant la mise &agrave; jour de Jquery dans wordpress)


= Version 1.2 =

Am&eacute;liorations

* Shortcodes pour afficher des cat&eacute;gories et/ou des produits(Cat&eacute;gories / Sous-cat&eacute;gories / Produits / Gestions de param&egrave;tres / Interface de gestion) 
* Ajout de boxs s&eacute;par&eacute;es pour g&eacute;rer les images et documents associ&eacute;s &agrave; un produit 
* Ajout des options permettant de choisir les types d'affichages pour la page cat&eacute;gorie(&eacute;l&eacute;ments &agrave; afficher (informations principales / sous-cat&eacute;gories / produits) - Affichage des produits et sous-cat&eacute;gories en liste ou grille (nombre de produit si mode grille))
* Possibilit&eacute; de choisir d'afficher ou non les produits dans le menu g&eacute;r&eacute; dans le widget 
* Dupliquer les &eacute;l&eacute;ments personnalisable dans le th&egrave;me courant(Template hml / ccs / js / - Option permettant de r&eacute;&eacute;craser)
* Onglets fiche produit(Descriptif / Attributs)
* Affectation d'un groupe d'unit&eacute; aux attributs (Pour ne pas avoir la liste de toute les unit&eacute;s sur tous les attributs) 
* G&eacute;n&eacute;rer un shortcode pour les attributs et les sections de groupes d'attributs (R&eacute;cup&eacute;rable et pla&ccedil;able n'importe o&ugrave;)
* Ajout d'une option sur les attributs permettant de les historiser 
* Gestion des groupes d'attributs si plusieurs groupes existant (Permet de s&eacute;lectionner le groupe d'attribut &agrave; utiliser par produit) 
* Gestion automatique de la mise &agrave; jour de la base de donn&eacute;e (Lors de l'ajout d'un champs ou d'une table lors du lancement la mise &agrave; jour est effectu&eacute;e automatiquement)

Corrections

* Lors de la d&eacute;sactivation et de la r&eacute;activation certaines donn&eacute;es &eacute;taient ins&eacute;r&eacute;es plusieurs fois dans la base 


= Version 1.1 =

Am&eacute;liorations

* Utilisation du syst&egrave;me de gestion interne &agrave; wordpress pour g&eacute;rer les produits et cat&eacute;gories de produits (permet d'avoir les fonctionnalit&eacute;s par d&eacute;faut de wordpress)
* Gestion des groupes d'attributs
* Affichage de la fiche des produits dans la partie publique du site ( Avec affichage d'une galerie d'image, d'une galerie de documents et de la liste des attributs associ&eacute;s au produit)
* Affichage de la fiche d'une cat&eacute;gorie dans la partie publique du site
* Possibilit&eacute; d'ajouter un widget contenant la liste des cat&eacute;gories et produits
* Possibilit&eacute; d'ajouter une photo &agrave; une cat&eacute;gorie


= Version 1.0 =

* Possibilit&eacute; de g&eacute;rer des produits (R&eacute;f&eacute;rence/Nom/Descriptions/Documents/Images/Cat&eacute;gories)
* Possibilit&eacute; de g&eacute;rer les cat&eacute;gories de produits (Nom/Description)
* Possibilit&eacute; de g&eacute;rer des documents (Nom/Description/Par d&eacute;faut/Ne pas afficher dans la gallerie dans le frontend) (Dans les produits)


== Am&eacute;liorations Futures ==

* Ajout des produits dans le panier
* Moyen de paiement
* Facturation
* Expedition


== Upgrade Notice ==

= Version 1.2 =
Improve attributes management functionnalities. Add possibility to add product or categories shortcode where you want

= Version 1.1 =
Improve product and categories management

= Version 1.0 =
Plugin first delivery

== Contactez l'auteur ==

dev@eoxia.com
