===Wpshop - simple eCommerce===
Contributors: Eoxia
Tags: shop, boutique, produits, e-commerce, commerce, m-commerce, mcommerce, shopping cart, ecommerce, catalog, responsive
Donate link: http://www.eoxia.com/
Requires at least: 3.0.4
Tested up to: 3.3.1
Stable tag: 1.3.0.6

Wpshop a free eCommerce plugin for wordpress. Simple and easy to use, Responsive design for tablets and smartphones included.

== Description ==

Wpshop is a simple and free Shopping cart plugin. With short codes, the development of the site is flexible and easy. Its theme suited for mobile e commerce propels your site to mCommerce.

<h3>Wpshop a free ecommerce extension, 100% open source, web design responsive</h3>
<p style="text-align: center;"><img class="aligncenter" title="wpshop extension ecommerce responsive pour wordpress" src="http://www.wpshop.fr/wp-content/themes/WpshopCommunication/images/wpshop_logo.png" alt="extension wordpress ecommerce" width="284" height="59" /></p>
<p><a title="extension wordpress e-commerce" href="http://www.wpshop.fr">wpshop.fr ecommerce for wordpress</a></p>


== Installation ==

L'installation du plugin peut se faire de 2 façons :

* Méthode 1

1. Téléchargez le fichier zip depuis le site de wordpress
2. Envoyez le dossier `wpshop` dans le répertoire `/wp-content/plugins/`
3. Activer le plugin dans le menu `Extensions` de Wordpress

* Méthode 2

1. Rechercher le plugin "WPSHOP" à partir du menu "Extension" de Wordpress
2. Lancer l'installation du plugin


== Frequently Asked Questions ==

Question 1 : Comment ajouter un menu avec mon catalogue dans la partie visible du site ?

Pour le moment vous ne pouvez ajouter le contenu de votre catalogue sous forme de menu qu'à travers un widget. Pour cela rendez-vous dans la partie administration des widgets puis ajoutez le widget correspondant aux catégories de produit à l'endroit désiré. Vous pouvez lui donner un titre, si aucun titre n'est défini alors le titre par défaut sera "Catalogue"

Question 2 : Mes produits et catégories ne sont pas accessible dans la partie visible du site ?

Il faut vérifier que le réglage des permaliens pour votre site est bien réglé sur "/%postname%"


== Screenshots ==

1. Interface de gestion des catégories (Aucune catégorie)
2. Interface de gestion des catégories (Avec une catégorie)
3. Interface d'édition d'une catégorie
4. Fiche d'une catégorie sans sous-catégorie ni produit
5. Fiche d'une catégorie avec ses sous-éléments
6. Interface de listing des produits
7. Interface d'édition des produits
8. Fiche d'un produit dans la partie publique
9. Liste des attributs
10. Interface d'édition d'un attribut
11. Interface de gestion des attributs dans les groupes (drag and drop) . Permet d'organiser l'ordre et les attributs présents.


== Changelog ==

= Version 1.3.0.6 =

Améliorations

* ST72 - Gestion des nouveautés / produits en vedettes(Possibilité de choisir des dates  pour définir l'intervalle pendant lequel le produit est marqué comme nouveau ou à la une) 
* ST170 - Gestion du listing des produits par attributs avec un shortcode ([wpshop_products att_name="CODE_ATTRIBUT" att_value="VALEUR_DE_L_ATTRIBUT"]) 
* ST171 - Simplification de la gestion des templates (Les templates sont inclus directement depuis le dossier du plugin. Pour modifier le comportement de l'affichage dans le front, il faut copier le fichier désiré dans le dossier du thème utilisé actuellement) 
* ST173 - Mise en place des templates conforme aux normes HTML 
* ST174 - Possiblités d'ajouter une commande depuis l'administration pour un client donné (V1 - L'utilisateur et les produits doivent être déjà existant) 
* ST175 - Factorisation du code pour une meilleure maintenance et une meilleure fiabilité 
* ST183 - Possibilité de choisir le début de la réécriture d'url pour les produits et catégories dans les options 
* ST184 - Possibilité de choisir les fonctionnalités wordpress associées aux post sur les produits de wpshop depuis les options 
* ST185 - Possibilité de choisir l'url depuis les options lorsqu'un produit n'est affecté a aucune catégorie 
* ST189 - Gestion des coupons de réductions sur une commande 

Corrections

* ST172 - Lors de la duplication d'un produit tous les nouveaux produits avaient la même url 
* ST176 - Configurations des tarifs de livraison généraux non pris en compte 



= Version 1.3.0.5 =

Améliorations

* Gestion du widget catégories dans le front
* Gestion du template du "mini" panier

Corrections

* Corrections du nombre d'articles affichés dans le "mini" panier
* Appel de jquery form pour les formulaires de connexion et de création de compte lors de la commande d'un client dans la partie front



= Version 1.3.0.4 =

Améliorations

* ST128 - Possibilité de dupliquer un produit 
* ST161 - Changement de la page option : gestion à la wordpress 
* ST162 - Gestion des frais de livraison (v1) (Gestion d'un prix min/max sur les frais de livraison - Possibilité de mettre la gratuité à partir d'une certaine somme) 
* ST166 - Panier : ajout d'un bouton de rechargement (Le bouton permet de recalculer l'ensemble du panier avant de le soumettre) 
* ST167 - Connexion et inscription en ajax (La connexion et l'inscription se fait maintenant en ajax.) 

Corrections 

* ST163 - Centrage de l'alert du panier (Correction de la fonction de centrage de l'alerte concernant l'ajoute d'articles au panier => bug sur certain écrans) 
* ST164 - Correction du wpshop_mini_cart (Affichage du prix total du panier dans le mini cart) 
* ST165 - Corrections javascript diverses et variées (Correction erreur javascript => élément non trouvable dans la page qui entrainait le bugguage de tout le javascript) 


= Version 1.3.0.3 =

Améliorations

* ST8 - interface de listing produit dans le front (Tri par nom/date/prix/stock/aléatoire + Pagination + Affichage en grille ou liste)
* ST69 - Gestion de produits liés (Possibilité pour chaque produit de le lier avec d'autres (du genre "vous aimerez surement :")) 
* ST147 - Gestion des devises par magasin (Gérable dans les options) 
* ST150 - Envoie des mails au format HTML 

Corrections

* ST146 - Ajouter de exec('chmod -R 755 lecheminachangerlesdroits'); partout ou il y a des créations de dossiers (Permet de corriger le fait que php ne donne pas les droits correct aux dossiers créés) 
* ST148 - Modification de la génération des numéros de facture 
* ST149 - Correction variable "WPSHOP_UPLOAD_DIR" avec slash manquant


= Version 1.3.0.2 =

Améliorations 

* ST23 - Possibilité de choisir les fichiers à écraser dans le thèmes 
* ST83 - Accès au template des photos du produit (La galerie des documents attachés à un produit est maintenant complètement personnalisable depuis les fichiers de template! Attention il faut réinitialiser les templates pour que cette modification soit prise en compte correctement) 
* ST136 - Ajout du flag "par défaut" sur les groupes d'attributs et les sections des groupes d'attributs pour affecter les attributs créés automatiquement à ces groupes 
* ST139 - On ne peut plus supprimer une option d'une liste déroulante dans les attributs si celle ci est déjà utilisée 
* ST140 - Lors de la modification des valeurs des options de l'attribut Taxe (tx_tva) les prix sont recalculés automatiquement pour tous les produits utilisant la valeur modifiées 
* ST141 - Possiblité d'ordonner les options des listes déroulantes, de choisir une valeur par défaut et gérer les labels 
* ST142 - Prise en compte de la balise "more" dans la description des produits dans les shortcodes 
* ST143 - Ajout de la récusivité sur l'execution des shortcodes (Un shortcode qui est inclus dans la description d'un produit qui lui même est appelé par un shortcode sera exécuté)
 
Corrections 

* ST127 - Fermeture de l'image agrandie (Les images permettant de naviguer et de fermer la fenêtre de visualisation des images associées aux produits n'était plus dans le bon dossier) 
* ST133 - Masquage de deux champs de type hidden dans le formulaire de création d'un attribut 
* ST134 - Modification d'un attribut (La modification d'un attribut entrainait une modification systématique du groupe d'attribut attribué à cet attribut et provoquait sa disparition de la page du produit dans certains cas)


= Version 1.3.0.1 =

Améliorations 

* ST17 - Gestion des prix de base (Prix de vente HT / Taxe / Prix de vente TTC) 
* ST21 - Taxes (La gestion se fait par les attributs) 
* ST49 - Message alerte à l'installation (- Ne pas mettre le message pour masquer / - Mettre un lien vers la page de configuration) 
* ST58 - Configuration de la boutique (- Information sur la societe / - Mode de paiement disponible / - Emails de la boutique / - Personnalisation des emails / - Utilisation ou non * des permaliens personnalisés (si on décoche une confirmation est demandée) / - Nombre de chiffre minimum composant les numéros de facture et de commande) 
* ST65 - Possibilité de modifier son mot de passe (client) 
* ST117 - Modification des scripts de mise à jour de la base de données (- Une interface est disponible en changeant une variable de configuration dans les fichiers de config) 
* ST118 - Vérification de certaines valeurs entrées avant enregistrement du produit (Référence: si vide on remplit avec un schéma définit (variable de configuration) / Prix: Calcul des différentes valeurs suivant le type de pilotage (variable de configuration)) 
* ST119 - Possibilité de choisir liste déroulante pour les attributs (Avec gestion de la liste des éléments) 
* ST121 - Interface de visualisation des emails envoyés par la boutique (Avec possiblité de les renvoyer) 
* ST122 - Possibilité de facturer (Possibilité de facturer les commandes) 
* ST123 - Ajout des frais de livraison (Ajout des frais de livraison) 
* ST125 - Suivi des mails (Possibilité de gérer/renvoyer les emails envoyé via le plugin au client.) 

Corrections 

* ST64 - Mettre wp_reset_query(); dans le shortcode 
* ST120 - L'affectation des vignettes pour le produit sont de nouveau en place pour la version 3.3.1 de wordpress 
* ST124 - Redirections en javascript (Les redirections sont maintenant effectuées avec javascript) 
	
	
= Version 1.3.0.0 =

Améliorations 

* Vendre vos produits est maintenant possible (Ajout du bouton ajouter au panier / Gestion du panier d'achat / Gestion des commandes)
* Ajout des prix sur les fiches produit
* Ajout de plusieurs shortcodes (wpshop_cart, wpshop_checkout, wpshop_myaccount) permettant une gestion plus avancée de votre boutique
* Gestion précise des commandes
* Configuration à l'installation
* Possibilité de choisir le paiement par chèque ou par paypal

Corrections 

* Meilleure gestion des produits



= Version 1.2.1.1 =

Améliorations 

* Ajout de la box permettant l'insertion d'un shortcode dans les articles 
* Affichage d'un bloc indiquant que le produit est inexistant si insertion d'un shortcode erroné 

Corrections 

* Le formulaire permettant de modifier les informations concernant les photos envoyées ne s'affichait plus (L'encodage du fichier des unités des attributs provoquait une erreur) 
* Unité par défaut lors de la création d'un attribut 
* Insertion d'un espace avant et aprés chaque shortcode inséré depuis la box dans les page et articles 
* Suppression du caractère 'underscore' à la fin d'un attribut lors de la création 
* Lors de l'activation du plugin un message d'erreur apparait (Encodage du fichier de la classe des unités des attributs défini en UTF8) 
* Problème de création des tables de base du plugin (Vérification et création lors du chargement du plugin) 
* Affichage des messages décalés sur certaines pages 
* Inclusion de certains javascript et de certaines fonctions entrant en conflit suivant les version de wordpress (Inférieure à 3.1 avant la mise à jour de Jquery dans wordpress)


= Version 1.2 =

Améliorations

* Shortcodes pour afficher des catégories et/ou des produits(Catégories / Sous-catégories / Produits / Gestions de paramètres / Interface de gestion) 
* Ajout de boxs séparées pour gérer les images et documents associés à un produit 
* Ajout des options permettant de choisir les types d'affichages pour la page catégorie(éléments à afficher (informations principales / sous-catégories / produits) - Affichage des produits et sous-catégories en liste ou grille (nombre de produit si mode grille))
* Possibilité de choisir d'afficher ou non les produits dans le menu géré dans le widget 
* Dupliquer les éléments personnalisable dans le thème courant(Template hml / ccs / js / - Option permettant de réécraser)
* Onglets fiche produit(Descriptif / Attributs)
* Affectation d'un groupe d'unité aux attributs (Pour ne pas avoir la liste de toute les unités sur tous les attributs) 
* Générer un shortcode pour les attributs et les sections de groupes d'attributs (Récupérable et plaçable n'importe où)
* Ajout d'une option sur les attributs permettant de les historiser 
* Gestion des groupes d'attributs si plusieurs groupes existant (Permet de sélectionner le groupe d'attribut à utiliser par produit) 
* Gestion automatique de la mise à jour de la base de donnée (Lors de l'ajout d'un champs ou d'une table lors du lancement la mise à jour est effectuée automatiquement)

Corrections

* Lors de la désactivation et de la réactivation certaines données étaient insérées plusieurs fois dans la base 


= Version 1.1 =

Améliorations

* Utilisation du système de gestion interne à wordpress pour gérer les produits et catégories de produits (permet d'avoir les fonctionnalités par défaut de wordpress)
* Gestion des groupes d'attributs
* Affichage de la fiche des produits dans la partie publique du site ( Avec affichage d'une galerie d'image, d'une galerie de documents et de la liste des attributs associés au produit)
* Affichage de la fiche d'une catégorie dans la partie publique du site
* Possibilité d'ajouter un widget contenant la liste des catégories et produits
* Possibilité d'ajouter une photo à une catégorie


= Version 1.0 =

* Possibilité de gérer des produits (Référence/Nom/Descriptions/Documents/Images/Catégories)
* Possibilité de gérer les catégories de produits (Nom/Description)
* Possibilité de gérer des documents (Nom/Description/Par défaut/Ne pas afficher dans la gallerie dans le frontend) (Dans les produits)


== Améliorations Futures ==

* Ajout des produits dans le panier
* Moyen de paiement
* Facturation
* Expedition


== Upgrade Notice ==

= 1.3.0.4 =
Major improvements were realized on the files of themes of the plugin, we recommend you to make savings of the modifications which you brought to the theme (wpshop directory in your running theme as well as the file taxonomy-wpshop_product_category.php) then to delete the contents of the directoyre as well as the file taxonomy-wpshop_product_category.php

= Version 1.2 =
Improve attributes management functionnalities. Add possibility to add product or categories shortcode where you want

= Version 1.1 =
Improve product and categories management

= Version 1.0 =
Plugin first delivery

== Contactez l'auteur ==

dev@eoxia.com
