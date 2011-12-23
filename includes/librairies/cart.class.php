<?php

class wpshop_cart {

	var $cart_contents_total;
	var $cart_contents_total_ex_tax;
	//var $cart_contents_weight;
	//var $cart_contents_count;
	var $cart_contents_tax;
	var $cart = array();
	//var $total;
	//var $subtotal;
	//var $subtotal_ex_tax;
	//var $tax_total;
	//var $discount_total;
	var $shipping_total;
	//var $shipping_tax_total;
	//var $applied_coupons;
	
	function __construct() {
		$_SESSION['cart'] = $this->load_cart_from_db();
		$this->cart = $_SESSION['cart'];
	}
	
	function load_cart_from_db() {
		global $wpdb;
		$cart_id = $this->get_cart_id();
		$data = $wpdb->get_results('SELECT product_id, product_qty FROM wp_wpshop__cart_contents WHERE cart_id="'.$cart_id.'"');
		$cart = array();
		foreach($data as $d) {
			$product_data = wpshop_products::get_product_data($d->product_id);
			$cart['content'][] = array(
				'product_id'	=> $d->product_id,
				'quantity' 		=> $d->product_qty,
				'data'			=> $product_data
			);
		}
		$cart['subtotal'] = $this->calculTotal($cart);
		$cart['total'] = $cart['subtotal'];
		return $cart;
	}
	
	function get_cart_id() {
		global $wpdb;
		$data = $wpdb->get_results('SELECT id FROM wp_wpshop__cart WHERE session_id="'.session_id().'"');
		if(!empty($data))
			return $data[0]->id;
		else return 0;
	}
	
	function set_product_qty($product_id, $quantity) {
		global $wpdb;
		
		/* ID du panier courant */
		$cart_id = $this->get_cart_id();
		
		/* Si le panier existe */
		if($cart_id!==0)
		{
			$found_cart_item_key = $this->find_product_in_cart($product_id);
			$product_data = wpshop_products::get_product_data($product_id);
			if($product_data===false)
				return __('This product doesn\'t exist', 'wpshop');
			// Add it
			if (is_numeric($found_cart_item_key)) {
				if($quantity>0) {
					// Stock check - this time accounting for whats already in-cart
					if ($product_data['product_stock'] > -1 && $product_data['product_stock'] < $quantity) :
						return sprintf(__('You cannot add that amount to the cart since there is not enough stock. We have %s in stock and you already have %s in your cart.', 'wpshop'), number_format($product_data['product_stock'],0), $this->cart['content'][$found_cart_item_key]['quantity']);
					endif;

					$this->cart['content'][$found_cart_item_key]['quantity'] = $quantity;
					$update = $wpdb->query('UPDATE wp_wpshop__cart_contents SET product_qty="'.$quantity.'" WHERE cart_id='.$cart_id.' AND product_id='.$product_id.'');
				}
				elseif($quantity<=0) {
					/* On supprime le produit du panier */
					unset($this->cart['content'][$found_cart_item_key]);
					$delete = $wpdb->query('DELETE FROM wp_wpshop__cart_contents WHERE cart_id='.$cart_id.' AND product_id='.$product_id.'');
				}
			}	
			else{
				/* Le produit n'est pas present dans le panier */
				return __('This product does not exist in the cart.', 'wpshop');
			}
			
			$this->set_session();
			return 'success';
		}
	}
	
	/** Retourne vrai si le panier est vide, faux sinon
	* @return boolean
	*/
	function is_empty() {
		return empty($this->cart['content']);
	}
	
	/** Vide le panier
	* @return void
	*/
	function empty_cart() {
		global $wpdb;
		$cart_id = $this->get_cart_id();
		$delete = $wpdb->query('DELETE FROM wp_wpshop__cart_contents WHERE cart_id='.$cart_id.'');
		$this->cart = array();
		$this->set_session();
	}
	
	/** Affiche le contenu du panier à l'écran
	* @param boolean $hide_button : cacher le bouton de soumission ou non
	* @return void
	*/
	function display_cart($hide_button=false) {
		
		$cart = $_SESSION['cart'];
		$cartContent='
		<input type="hidden" value="'.__('Your cart is empty.','wpshop').'" name="emptyCartSentence" />
		<table id="cartContent">
		<thead>
		<tr>
			<th>'.__('Product name', 'wpshop').'</th>
			<th class="center">'.__('Unit price', 'wpshop').'</th>
			<th class="center">'.__('Quantity', 'wpshop').'</th>
			<th>'.__('Subtotal', 'wpshop').'</th>
			<th class="center">'.__('Action', 'wpshop').'</th>
		</tr>
		</thead>
		<tfoot>
		<tr>
			<th>'.__('Product name', 'wpshop').'</th>
			<th class="center">'.__('Unit price', 'wpshop').'</th>
			<th class="center">'.__('Quantity', 'wpshop').'</th>
			<th>'.__('Subtotal', 'wpshop').'</th>
			<th class="center">'.__('Action', 'wpshop').'</th>
		</tr>
		</tfoot>
		<tbody>';
		if(!empty($cart['content']))
		{
			foreach($cart['content'] as $b):
				// On récupère la liste des catégories pour chaque produit
				$cats = get_the_terms($b['product_id'], WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES);
				$cat_id = empty($cats) ? 0 : $cats[0]->term_id;
				if($cat_id==0){
					$product_link = 'catalog/product/' . $b['data']['post_name'];
				}
				else $product_link = get_term_link((int)$cat_id , WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES) . '/' . $b['data']['post_name'];
				
				$cartContent .= '
					<tr id="product_'.$b['product_id'].'">
						<td><a href="'.$product_link.'">'.wpshop_tools::trunk($b['data']['product_name'],30).'</a></td>
						<td class="pu center">'.sprintf('%0.2f', $b['data']['product_price']).' EUR</td>
						<td class="center" style="min-width: 125px;">
							<a href="#" class="productQtyChange">-</a> 
							<input type="hidden" value="'.$b['quantity'].'" name="currentProductQty" />
							<input type="text" value="'.$b['quantity'].'" name="productQty" /> 
							<a href="#" class="productQtyChange">+</a>
						</td>
						<td class="subtotal"><span>'.sprintf('%0.2f', $b['data']['product_price']*$b['quantity']).' EUR</span></td>
						<td class="center"><a href="#" class="remove" title="Remove">Remove</a></td>
					</tr>';
			endforeach;
			$cartContent .= '</tbody></table>';
			$submit = empty($hide_button) ? '<input type="submit" value="Valider mon panier" name="cartCheckout" />' : null;
			echo empty($hide_button) ? '<form action="'.self::get_checkout_url().'" method="post">' : null;
			echo '
					<div class="cart">
						'.$cartContent.'
						<p>
							'.__('Cart subtotal','wpshop').' : <span class="subtotal_right">'.number_format($cart['subtotal'],2).' EUR</span><br />
							'.__('Shipping','wpshop').' : <span class="shipping_right">Offert</span><br />
							'.__('Total','wpshop').' : <span class="total_right">'.number_format($cart['subtotal'],2).' EUR</span>
						</p>
						'.$submit.'
					</div>
			';
			echo empty($hide_button) ? '</form>' : null;
		}
		else echo '<div class="cart">'.__('Your cart is empty.','wpshop').'</div>';
	}
	
	function calculTotal($cart) {
		$total=0;
		if(!empty($cart['content'])) {
			foreach($cart['content'] as $c) {
				$total += $c['data']['product_price']*$c['quantity'];
			}
		}
		return $total;
	}
	
	/**
     * Check if product is in the cart and return cart item key
     * @param int $product_id
     * @return int|null
     */
	function find_product_in_cart($product_id) {
		if(!empty($this->cart['content'])) {
			foreach ($this->cart['content'] as $cart_item_key => $cart_item) :
				if ($cart_item['product_id'] == $product_id) :
					return $cart_item_key;
				endif;
			endforeach;
		}
        return NULL;
    }
	
	function remove_from_cart($product_id) {
		global $wpdb;
		
		$key = $this->find_product_in_cart($product_id);
		unset($this->cart['content'][$key]);
		
		$cart_id = $this->get_cart_id();
		$delete = $wpdb->query('DELETE FROM wp_wpshop__cart_contents WHERE cart_id='.$cart_id.' AND product_id='.$product_id.'');
		
		$this->set_session();
		
		return true;
	}
	
	/**
	 * Add a product to the cart
	 * @param   string	product_id	contains the id of the product to add to the cart
	 * @param   string	quantity	contains the quantity of the item to add
	 */
	function add_to_cart($product_id, $quantity = 1) {
		global $wpdb;
		
		$cart_id = $this->get_cart_id();
		if($cart_id===0):
			$insert = $wpdb->query('INSERT INTO wp_wpshop__cart VALUES(NULL,"'.session_id().'",NULL)');
			$cart_id = $wpdb->insert_id;
		endif;
		
		if ($quantity < 1) $quantity = 1;
		$found_cart_item_key = $this->find_product_in_cart($product_id);
		$product_data = wpshop_products::get_product_data($product_id);
		if($product_data===false)
			return __('This product does not exist', 'wpshop');
		
		// Price set check
		if($product_data['product_price'] === '') :
			return __('This product cannot be purchased - the price is not yet announced', 'wpshop');
		endif;
		// Price set check
		if($product_data['product_price'] < 0) :
			return __('This product cannot be purchased - its price is negative', 'wpshop');
		endif;
		
		// Add it
		if (is_numeric($found_cart_item_key)) :
			
			$quantity = $quantity + $this->cart['content'][$found_cart_item_key]['quantity'];
			
			// Stock check - this time accounting for whats already in-cart
			if ($product_data['product_stock'] > -1 && $product_data['product_stock'] < $quantity) :
				return sprintf(__('You cannot add that amount to the cart since there is not enough stock. We have %s in stock and you already have %s in your cart.', 'wpshop'), number_format($product_data['product_stock'],0), $this->cart['content'][$found_cart_item_key]['quantity']);
			endif;

			$this->cart['content'][$found_cart_item_key]['quantity'] = $quantity;
			$update = $wpdb->query('UPDATE wp_wpshop__cart_contents SET product_qty="'.$quantity.'" WHERE cart_id='.$cart_id.' AND product_id='.$product_id.'');
			
		else :
		
			// Stock check - only check if we're managing stock and backorders are not allowed
			if ($product_data['product_stock'] > -1 && $product_data['product_stock'] < $quantity) :
				return sprintf(__('You cannot add that amount to the cart since there is not enough stock. We have %s in stock.', 'wpshop'), number_format($product_data['product_stock'],0));
			endif;
				
			$this->cart['content'][] = array(
				'product_id'	=> $product_id,
				'quantity' 		=> $quantity,
				'data'			=> $product_data
			);
			
			$insert = $wpdb->query('INSERT INTO wp_wpshop__cart_contents VALUES(NULL,'.$cart_id.','.$product_id.','.$quantity.')');
			
		endif;
		
		$this->set_session();
		return 'success';
	}
	
	/* Sets the php session data for the cart and coupon */
	function set_session() {
		if(!$this->is_empty()) {
			$this->cart['subtotal'] = $this->calculTotal($this->cart);
			$this->cart['total'] = $this->cart['subtotal']; // + shipping
			$_SESSION['cart'] = $this->cart;
		}
		else {
			$_SESSION['cart'] = array();
		}
		//$_SESSION['coupons'] = $this->applied_coupons;
		//$this->calculate_totals();
	}
	
	/** gets the url to the checkout page */
	function get_checkout_url() {
		$checkout_page_id = get_option('wpshop_checkout_page_id');
		if ($checkout_page_id) :
			return get_permalink($checkout_page_id);
		endif;
	}
}
?>