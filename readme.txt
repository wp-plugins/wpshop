=== wpshop ===
Contributors: Eoxia
Tags: shop, boutique, produits, e-commerce, commerce
Donate link: http://www.eoxia.com/
Requires at least: 3.0.4
Tested up to: 3.3.1
Stable tag: 1.3.0.5

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


= Version 1.3.0.5 =

Am&eacute;liorations

* Gestion du widget cat&eacute;gories dans le front
* Gestion du template du "mini" panier

Corrections

* Corrections du nombre d'articles affich&eacute;s dans le "mini" panier
* Appel de jquery form pour les formulaires de connexion et de cr&eacute;ation de compte lors de la commande d'un client dans la partie front



= Version 1.3.0.4 =

Am&eacute;liorations

* ST128 - Possibilit&eacute; de dupliquer un produit 
* ST161 - Changement de la page option : gestion &agrave; la wordpress 
* ST162 - Gestion des frais de livraison (v1) (Gestion d'un prix min/max sur les frais de livraison - Possibilit&eacute; de mettre la gratuit&eacute; &agrave; partir d'une certaine somme) 
* ST166 - Panier : ajout d'un bouton de rechargement (Le bouton permet de recalculer l'ensemble du panier avant de le soumettre) 
* ST167 - Connexion et inscription en ajax (La connexion et l'inscription se fait maintenant en ajax.) 

Corrections 

* ST163 - Centrage de l'alert du panier (Correction de la fonction de centrage de l'alerte concernant l'ajoute d'articles au panier => bug sur certain &eacute;crans) 
* ST164 - Correction du wpshop_mini_cart (Affichage du prix total du panier dans le mini cart) 
* ST165 - Corrections javascript diverses et vari&eacute;es (Correction erreur javascript => &eacute;l&eacute;ment non trouvable dans la page qui entrainait le bugguage de tout le javascript) 


= Version 1.3.0.3 =

Am&eacute;liorations

* ST8 - interface de listing produit dans le front (Tri par nom/date/prix/stock/al&eacute;atoire + Pagination + Affichage en grille ou liste)
* ST69 - Gestion de produits li&eacute;s (Possibilit&eacute; pour chaque produit de le lier avec d'autres (du genre "vous aimerez surement :")) 
* ST147 - Gestion des devises par magasin (G&eacute;rable dans les options) 
* ST150 - Envoie des mails au format HTML 

Corrections

* ST146 - Ajouter de exec('chmod -R 755 lecheminachangerlesdroits'); partout ou il y a des cr&eacute;ations de dossiers (Permet de corriger le fait que php ne donne pas les droits correct aux dossiers cr&eacute;&eacute;s) 
* ST148 - Modification de la g&eacute;n&eacute;ration des num&eacute;ros de facture 
* ST149 - Correction variable "WPSHOP_UPLOAD_DIR" avec slash manquant


= Version 1.3.0.2 =

Am&eacute;liorations 

* ST23 - Possibilit&eacute; de choisir les fichiers &agrave; &eacute;craser dans le th&egrave;mes 
* ST83 - Acc&egrave;s au template des photos du produit (La galerie des documents attach&eacute;s &agrave; un produit est maintenant compl&egrave;tement personnalisable depuis les fichiers de template! Attention il faut r&eacute;initialiser les templates pour que cette modification soit prise en compte correctement) 
* ST136 - Ajout du flag "par d&eacute;faut" sur les groupes d'attributs et les sections des groupes d'attributs pour affecter les attributs cr&eacute;&eacute;s automatiquement &agrave; ces groupes 
* ST139 - On ne peut plus supprimer une option d'une liste d&eacute;roulante dans les attributs si celle ci est d&eacute;j&agrave; utilis&eacute;e 
* ST140 - Lors de la modification des valeurs des options de l'attribut Taxe (tx_tva) les prix sont recalcul&eacute;s automatiquement pour tous les produits utilisant la valeur modifi&eacute;es 
* ST141 - Possiblit&eacute; d'ordonner les options des listes d&eacute;roulantes, de choisir une valeur par d&eacute;faut et g&eacute;rer les labels 
* ST142 - Prise en compte de la balise "more" dans la description des produits dans les shortcodes 
* ST143 - Ajout de la r&eacute;cusivit&eacute; sur l'execution des shortcodes (Un shortcode qui est inclus dans la description d'un produit qui lui m&ecirc;me est appel&eacute; par un shortcode sera ex&eacute;cut&eacute;)
 
Corrections 

* ST127 - Fermeture de l'image agrandie (Les images permettant de naviguer et de fermer la fen&ecirc;tre de visualisation des images associ&eacute;es aux produits n'&eacute;tait plus dans le bon dossier) 
* ST133 - Masquage de deux champs de type hidden dans le formulaire de cr&eacute;ation d'un attribut 
* ST134 - Modification d'un attribut (La modification d'un attribut entrainait une modification syst&eacute;matique du groupe d'attribut attribu&eacute; &agrave; cet attribut et provoquait sa disparition de la page du produit dans certains cas)


= Version 1.3.0.1 =

Am&eacute;liorations 

* ST17 - Gestion des prix de base (Prix de vente HT / Taxe / Prix de vente TTC) 
* ST21 - Taxes (La gestion se fait par les attributs) 
* ST49 - Message alerte &agrave; l'installation (- Ne pas mettre le message pour masquer / - Mettre un lien vers la page de configuration) 
* ST58 - Configuration de la boutique (- Information sur la societe / - Mode de paiement disponible / - Emails de la boutique / - Personnalisation des emails / - Utilisation ou non * des permaliens personnalis&eacute;s (si on d&eacute;coche une confirmation est demand&eacute;e) / - Nombre de chiffre minimum composant les num&eacute;ros de facture et de commande) 
* ST65 - Possibilit&eacute; de modifier son mot de passe (client) 
* ST117 - Modification des scripts de mise &agrave; jour de la base de donn&eacute;es (- Une interface est disponible en changeant une variable de configuration dans les fichiers de config) 
* ST118 - V&eacute;rification de certaines valeurs entr&eacute;es avant enregistrement du produit (R&eacute;f&eacute;rence: si vide on remplit avec un sch&eacute;ma d&eacute;finit (variable de configuration) / Prix: Calcul des diff&eacute;rentes valeurs suivant le type de pilotage (variable de configuration)) 
* ST119 - Possibilit&eacute; de choisir liste d&eacute;roulante pour les attributs (Avec gestion de la liste des &eacute;l&eacute;ments) 
* ST121 - Interface de visualisation des emails envoy&eacute;s par la boutique (Avec possiblit&eacute; de les renvoyer) 
* ST122 - Possibilit&eacute; de facturer (Possibilit&eacute; de facturer les commandes) 
* ST123 - Ajout des frais de livraison (Ajout des frais de livraison) 
* ST125 - Suivi des mails (Possibilit&eacute; de g&eacute;rer/renvoyer les emails envoy&eacute; via le plugin au client.) 

Corrections 

* ST64 - Mettre wp_reset_query(); dans le shortcode 
* ST120 - L'affectation des vignettes pour le produit sont de nouveau en place pour la version 3.3.1 de wordpress 
* ST124 - Redirections en javascript (Les redirections sont maintenant effectu&eacute;es avec javascript) 
	
	
= Version 1.3.0.0 =

Am&eacute;liorations 

* Vendre vos produits est maintenant possible (Ajout du bouton ajouter au panier / Gestion du panier d'achat / Gestion des commandes)
* Ajout des prix sur les fiches produit
* Ajout de plusieurs shortcodes (wpshop_cart, wpshop_checkout, wpshop_myaccount) permettant une gestion plus avanc&eacute;e de votre boutique
* Gestion pr&eacute;cise des commandes
* Configuration &agrave; l'installation
* Possibilit&eacute; de choisir le paiement par ch&egrave;que ou par paypal

Corrections 

* Meilleure gestion des produits



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
