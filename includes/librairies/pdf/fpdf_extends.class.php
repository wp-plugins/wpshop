<?php
DEFINE('EUR', chr(128)); // Sigle €
DEFINE('USD', '$'); // Sigle $

// DEFINITION CLASSE export_pdf
// Classe permettant l'export d'une facture au format pdf, hérite de la classe FPDF
class wpshop_export_pdf extends wpshop_FPDF
{
	// CONSTRUCTEUR
	function eoinvoice_export_pdf()
	{
		// Appel du constructeur parent avant toute redéfinition
		parent::wpshop_FPDF();
	}
	
	// Création récursive de dossiers
	function make_recursiv_dir($path, $rights = 0777) {
		if (!@is_dir($path)) {
			$folder_path = array($path);
		} 
		else {
			return;
		}
		 
		while(!@is_dir(dirname(end($folder_path))) && dirname(end($folder_path)) != '/' && dirname(end($folder_path)) != '.' && dirname(end($folder_path)) != '') {
			array_push($folder_path, dirname(end($folder_path)));
		}
		 
		while($parent_folder_path = array_pop($folder_path)) {
			if(!@mkdir($parent_folder_path, $rights)) {
				user_error("Can't create folder \"$parent_folder_path\".\n");
			}
		}
	}
	function invoice_export($order_id) {
	
		$current_user_id = get_current_user_id();
		$order = get_post_meta($order_id, '_order_postmeta', true);
		
		if($order['customer_id']==$current_user_id) {
		
			if($order['order_status']=='completed') {
		
				/* Si la facture n'a pas de reference */
				/*if(empty($order['order_invoice_ref'])) {
					$number_figures = get_option('wpshop_billing_number_figures', false);
					/* If the number doesn't exist, we create a default one */
					/*if(!$number_figures) {
						$number_figures = 5;
						update_option('wpshop_billing_number_figures', $number_figures);
					}
					
					$billing_current_number = get_option('wpshop_billing_current_number', false);
					/* If the counter doesn't exist, we initiate it */
					/*if(!$billing_current_number) { $billing_current_number = 1; }
					else { $billing_current_number++; }
					update_option('wpshop_billing_current_number', $billing_current_number);
					
					$invoice_ref = WPSHOP_BILLING_REFERENCE_PREFIX.((string)sprintf('%0'.$number_figures.'d', $billing_current_number));
					$order['order_invoice_ref'] = $invoice_ref;
					update_post_meta($order_id, '_order_postmeta', $order);
				}
				else {
					$invoice_ref = $order['order_invoice_ref'];
				}*/
				
				$invoice_ref = $order['order_invoice_ref'];
				
				// Currency management
				$currency = $order['order_currency'];
				if($currency == 'EUR')$currency = EUR;
				else $currency = wpshop_tools::wpshop_get_sigle($currency);
				
				// On définit un alias pour le nombre de pages total
				$this->AliasNbPages();
				
				// On ajoute une page au document
				$this->AddPage();
				// On lui applique une police
				$this->SetFont('Arial','',10);
				
				// Coordonnées magasin
				$this->store_head($order_id);
				// Coordonnées client
				$this->client_head($order_id);
				// Date de facturation et référence facture
				$refdate = $this->invoice_refdate($order_id, $invoice_ref);
				// Tableau des lignes de facture
				$this->rows($order_id, $currency);
				// Ligne de total
				$this->total($order_id, $currency);
				// On affiche le rib du magasin
				//$this->rib($store_selected);
				// On mentionnes les informations obigatoires en bas de page
				$this->pre_footer($order_id);
				// On crée le dossier si celui ci n'existe pas
				$this->make_recursiv_dir(WP_CONTENT_DIR . "/uploads/wpshop_invoices");
				// On enregistre
				$path = WP_CONTENT_DIR . "/uploads/wpshop_invoices/" . $refdate . ".pdf";
				$this->Output($path, "F");
				// On force le téléchargement de la facture
				$Fichier_a_telecharger = $refdate.".pdf";
				$this->forceDownload($Fichier_a_telecharger, $path, filesize($path));
			}
			else echo __('The payment regarding the invoice you requested isn\'t completed','wpshop');
		}
		else echo __('You don\'t have the rights to access this invoice.','wpshop');
	}
	
	/** Force le téléchargement d'un fichier */
	function forceDownload($nom, $path, $poids) {
		header('Content-Type: application/pdf');
		header('Content-Length: '. $poids);
		header('Content-disposition: attachment; filename='. $nom);
		header('Pragma: no-cache');
		header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
		header('Expires: 0');
		ob_clean();
		flush();
		readfile($path);
		exit();
	}
	
	// En-tête magasin
	function store_head($order_id) {
	
		$company = get_option('wpshop_company_info', array());
		$emails = get_option('wpshop_emails', array());
		
		//Positionnement
		$this->SetY(45);$this->SetX(12);	
		// Cadre client destinataire
		$this->rect(10, 42, 80, 40);
			
		if(!empty($company) && !empty($emails)) {
			// Infos
			$store_name = utf8_decode(utf8_encode($company['company_name']));
			$store_email = !empty($emails['contact_email']) ? $emails['contact_email'] : null;
			
			// Infos
			$store_street_adress = utf8_decode(utf8_encode($company['company_name']));
			$store_city = utf8_decode(utf8_encode($company['company_city']));
			$store_postcode = utf8_decode(utf8_encode($company['company_postcode']));
			//$store_state = utf8_decode('store state');
			$store_country = utf8_decode(utf8_encode($company['company_country']));
			
			// Gras pour le titre
			$this->SetFont('','B',10);
			$this->Cell($xsize,5,$store_name,0,1,'L'); $this->SetX(12);
			// Police normale pour le reste
			$this->SetFont('','',9);
			$this->Cell($xsize,4,$store_street_adress,0,1,'L'); $this->SetX(12);
			if ($store_suburb != ''){$this->Cell(80,4,$store_suburb,0,1,'L');} $this->SetX(12);
			$this->Cell($xsize,4,$store_postcode . ' ' . $store_city,0,1,'L'); $this->SetX(12);
			//if ($store_state != ''){$this->Cell(80,4,$store_state,0,1,'L');} $this->SetX(12);
			$this->Cell($xsize,4,$store_country,0,1,'L'); $this->SetX(12);
			$this->Cell($xsize,4,$store_email,0,1,'L'); $this->SetX(12);
		}
		else {
			$this->Cell($xsize,5,_('Nc','wpshop'),0,1,'L');
		}
	}
	
	// En-tête client
	function client_head($order_id) {
		$customer_data = get_post_meta($order_id, '_order_info', true);
		$customer_data = $customer_data['billing'];
		
		// FPDF ne décodant pas l'UTF-8, on le fait via PHP
		$customer_firstname = utf8_decode(utf8_encode($customer_data['first_name']));
		$customer_lastname = utf8_decode(utf8_encode($customer_data['last_name']));
		$customer_company = utf8_decode(utf8_encode($customer_data['company']));
		$customer_street_adress = utf8_decode(utf8_encode($customer_data['address']));
		$customer_city = utf8_decode(utf8_encode($customer_data['city']));
		$customer_postcode = utf8_decode(utf8_encode($customer_data['postcode']));
		$customer_state = utf8_decode(utf8_encode($customer_data['state']));
		$customer_country = utf8_decode(utf8_encode($customer_data['country']));

		$xsize = 80;
		
		//Positionnement
		$this->SetY(45);
		$this->SetX(102);
		// Cadre client destinataire
		$this->rect(100, 42, 100, 40);
		// Et on écris
		// On règle la police d'écriture
		// gras pour le titre
		$this->SetFont('','B',10);
		$this->Cell($xsize,5,$customer_lastname.' '.$customer_firstname.(!empty($customer_company)?', '.$customer_company:null),0,1,'L'); $this->SetX(102);
		// Police normale pour le reste
		$this->SetFont('','',9);
		$this->Cell($xsize,4,$customer_street_adress,0,1,'L'); $this->SetX(102);
		if ($customer_suburb != ''){$this->Cell($xsize,4,$customer_suburb,0,1,'L');} $this->SetX(102);
		$this->Cell($xsize,4,$customer_postcode . ' ' . $customer_city,0,1,'L'); $this->SetX(102);
		if ($customer_state != ''){$this->Cell($xsize,4,$customer_state,0,1,'L');} $this->SetX(102);
		$this->Cell($xsize,4,$customer_country . ' ',0,1,'L');
	}
	
	// Référence et date de facturation
	function invoice_refdate($order_id, $invoice_ref)
	{
		$order = get_post_meta($order_id, '_order_postmeta', true);
		// On récupère la référence
		//$invoice_ref = 'FA'.date('ym').'-0001';
		// On récupère la date de facturation
		$invoice_add_date = substr($order['order_date'],0,10);
		// On récupère la date d'échéance
		//$invoice_max_date = '';
		
		// Positionnement
		$this->SetY(25); $this->SetX(135); $this->SetFont('','B',14);
		$this->Cell(50, 5, utf8_decode(__( 'Ref. : ', 'wpshop' )) . $invoice_ref,0,1,'L');
		// Positionnement
		$this->SetX(135); $this->SetFont('','',9);
		$this->Cell(50, 4, utf8_decode(__( 'Billing date : ', 'wpshop' )) . $invoice_add_date,0,1,'L'); 
		
		//$this->SetX(135);
		//$this->Cell(50, 4, utf8_decode(__( 'Date d\'échéance : ', 'wpshop' )) . $invoice_max_date,0,1,'L');
		
		return $invoice_ref.'_'.$invoice_add_date;
	}
	
	// Affiche le tableau des lignes de la facture
	function rows($order_id, $currency)
	{
		$title_ref = utf8_decode(__( 'Reference', 'wpshop' ));
		$title_name = utf8_decode(__( 'Designation', 'wpshop' ));
		$title_qty = utf8_decode(__( 'Qty', 'wpshop' ));
		$title_baseprice = utf8_decode(__( 'PU HT', 'wpshop' ));
		$title_discount = utf8_decode(__( 'Discount', 'wpshop' ));
		$title_tax = utf8_decode(__( 'TVA (Tax)', 'wpshop' ));
		$title_price = utf8_decode(__( 'Total ET', 'wpshop' ));
		
		// Titre des colonnes
		$header = array($title_ref,$title_name,$title_qty,$title_baseprice,$title_discount,$title_tax,$title_price);
		// Largeur des colonnes
		$w = array(26,75,10,20,15,30,20);
		// On récupère les id des lignes de cette facture
		$order_data = get_post_meta($order_id, '_order_postmeta', true);
		$order_items = $order_data['order_items'];
		
		$this->setXY(10,95);
		for($i=0;$i<count($header);$i++) {
			$this->Cell($w[$i],5,$header[$i],1,0,'C');
		}
		$this->Ln();
		
		// Puis on affiche les lignes
		foreach($order_items as $o) {
			$this->row($o, $w, $currency);
		}
	}
	
	// Affiche un ligne de la facture
	function row($row, $dim_array, $currency) {
	
		// Sécurité
		$product_reference = !empty($row['item_ref']) ? $row['item_ref'] : 'Nc';
		$product_name = !empty($row['item_name']) ? $row['item_name'] : 'Nc';
		$qty_invoiced = !empty($row['item_qty']) ? $row['item_qty'] : 'Nc';
		$item_pu_ht = !empty($row['item_pu_ht']) ? $row['item_pu_ht'] : 'Nc';
		$discount_amount = !empty($row['discount_amount']) ? $row['discount_amount'] : 0;
		$item_tva_total_amount = !empty($row['item_tva_total_amount']) ? $row['item_tva_total_amount'] : 0;
		$tax_rate = !empty($row['item_tax_rate']) ? $row['item_tax_rate'] : 19.6;
		$total_ht = !empty($row['item_total_ht']) ? $row['item_total_ht'] : 'Nc';
		
		// On affiche les valeurs
		$this->Cell($dim_array[0],8,$product_reference,'LRB',0,'C');
		$this->Cell($dim_array[1],8,$product_name,'LRB',0,'L');
		$this->Cell($dim_array[2],8,$qty_invoiced,'LRB',0,'C');
		$this->Cell($dim_array[3],8,number_format($item_pu_ht,2,'.',' ').' '.$currency,'LRB',0,'C');
		$this->Cell($dim_array[4],8,number_format($discount_amount,2,'.',' ').' '.$currency,'LRB',0,'C');
		$this->Cell($dim_array[5],8,number_format($item_tva_total_amount,2,'.',' ').' '.$currency.' (' . round($tax_rate, 2) . '%)','LRB',0,'C');
		$this->Cell($dim_array[6],8,number_format($total_ht,2,'.',' ').' '.$currency,'LRB',0,'C');
		$this->Ln();
	}
	
	function total($order_id, $currency) {
	
		/* Données commande */
		$order = get_post_meta($order_id, '_order_postmeta', true);
		
		// Décalage
		$this->Ln(); 
		
		$this->Cell(130,10);
		$this->Cell(25,8,__('Total ET','wpshop'),1); $this->Cell(35,8,number_format($order['order_total_ht'],2,'.',' ') . ' ' . $currency,1,0,'C'); $this->Ln();
		
		foreach($order['order_tva'] as $k => $v) {
			$this->Cell(130,10); 
			$this->Cell(25,8,__('Tax','wpshop').' '.$k.'%',1); $this->Cell(35,8,number_format($v,2,'.',' ') . ' ' . $currency,1,0,'C'); $this->Ln();
		}
		
		$this->Cell(130,10);
		$this->Cell(25,8,__('Shipping','wpshop'),1); $this->Cell(35,8,number_format($order['order_shipping_cost'],2,'.',' ') . ' ' . $currency,1,0,'C'); $this->Ln();
		
		$this->Cell(130,10);
		$this->Cell(25,8,__('Total ATI','wpshop'),1); $this->SetFont('','B',10); $this->Cell(35,8,number_format($order['order_grand_total'],2,'.',' ') . ' ' . $currency,1,0,'C'); $this->Ln();
	}
	
	function rib($store_number)
	{
		// On récupère les infos du magasin
		/*$store_bic_array = $this->tools_object->eoinvoice_get_store_bic($store_number);
		
		// On trie
		$bank_code = $store_bic_array[0];
		$register_code = $store_bic_array[1];
		$account_number = $store_bic_array[2];
		$rib_key = $store_bic_array[3];
		$iban = $store_bic_array[4];
		$bic = $store_bic_array[5];
		
		// On affiche
		$this->SetFont('','B',10);
		$this->Ln(); $this->Ln();
		$this->Cell(40,8,utf8_decode(__('Indentité bancaire', 'eoinvoice_trdom')));
		$this->SetFont('','',8); $this->Ln();
		$this->Cell(20,8,__('Code banque', 'eoinvoice_trdom'),'LRT',0,'C');
		$this->Cell(20,8,__('Code guichet', 'eoinvoice_trdom'),'LRT',0,'C');
		$this->Cell(20,8,utf8_decode(__('N° Compte', 'eoinvoice_trdom')),'LRT',0,'C');
		$this->Cell(20,8,utf8_decode(__('Clé RIB', 'eoinvoice_trdom')),'LRT',0,'C');
		$this->Cell(40,8,__('IBAN', 'eoinvoice_trdom'),'LRT',0,'C');
		$this->Cell(25,8,__('BIC', 'eoinvoice_trdom'),'LRT',0,'C');
		$this->Ln();
		$this->Cell(20,8,$bank_code,1,0,'C');
		$this->Cell(20,8,$register_code,1,0,'C');
		$this->Cell(20,8,$account_number,1,0,'C');
		$this->Cell(20,8,$rib_key,1,0,'C');
		$this->Cell(40,8,$iban,1,0,'C');
		$this->Cell(25,8,$bic,1,0,'C');*/
	}
	
	function pre_footer($order_id)
	{
		// On récupère les infos du magasin
		//$store_info_array = $this->tools_object->eoinvoice_get_store_info($store_number);
		// On trie
		$store_name = 'storename';
		$check_accept = 'check accept';
		$society_type = 'society type';
		$society_capital = 'society capital';
		
		$this->SetFont('','',10);
		$this->SetXY(10,-50);
		if($check_accept > 0)
		{
			$this->MultiCell(190,4,html_entity_decode(__('Adh&eacute;rent d\'un centre de gestion agr&eacute;&eacute;, acceptant &agrave; ce titre les r&egrave;glements par ch&egrave;que.', 'eoinvoice_trdom'), ENT_QUOTES),0,'L',FALSE);
			$this->Ln();
		}
		$this->MultiCell(190,4,html_entity_decode(__( 'Loi 83-629 du 12/07/83, art. 8 : "L\'autorisation administrative ne conf&egrave;re aucun caract&egrave;re officiel &agrave; l\'entreprise ou aux personnes qui en b&eacute;n&eacute;ficient. Elle n\'engage en aucune mani&egrave;re la responsabilit&eacute; des pouvoirs publics."', 'eoinvoice_trdom'), ENT_QUOTES),0,'L',FALSE);
		$this->Ln();
		$this->MultiCell(190,4,html_entity_decode($store_name . ', ' . $society_type . __(' au capital de ', 'eoinvoice_trdom') . $society_capital . EUR . '.', ENT_QUOTES),0,'L',FALSE);
	}
	
	//En-tête
	function Header()
	{
		$this->SetFont('Arial','B',15);
		//Décalage à droite
		$this->Cell(70);
		//Titre
		$this->Cell(30,10,'FACTURE',0,0,'L');
	}

	//Pied de page
	function Footer()
	{
		//Positionnement à 1,5 cm du bas
		$this->SetY(-15);
		//Police Arial italique 8
		$this->SetFont('Arial','I',8);
		//Numéro de page
		$this->Cell(0,10,$this->PageNo() . '/{nb}',0,0,'C');
	}
}
?>
