<?php
/**
 * Cart
 * 
 * The WPShop cart class handles the cart process, collecting products data and calcul total
 *
 * @class 		wpwhop_cart
 * @package		WPShop
 * @category	Class
 * @author		Eoxia
 */

/** Display the cart */
function wpshop_display_cart() {
	global $wpshop_cart;
	$wpshop_cart->display_cart();
}
/** Display the mini cart */
function wpshop_display_mini_cart() {
	global $wpshop_cart;
	$wpshop_cart->display_mini_cart();
}

class wpshop_cart {

	var $cart = array();
	
	/** Constructor of the class */
	function __construct() {
		$this->cart = $this->load_cart_from_db();
	}
	
	/** Reload the cart from the database and return it
	 * @return array
	*/
	function load_cart_from_db() {
		global $wpdb;
		
		$cart = array();
		$cart_id = $this->get_cart_id();
		
		if($cart_id) {
			$order_total_ht = 0;
			$order_total_ttc = 0;
			$order_tva = array();
				
			$data = $wpdb->get_results('SELECT product_id, product_qty FROM '.WPSHOP_DBT_CART_CONTENTS.' WHERE cart_id="'.$cart_id.'" ORDER BY id ASC');
			if(!empty($data)) {
			
				/* Shipping var */
				$total_weight = 0;
				$nb_of_items = 0;
				$order_shipping_cost_by_article = 0;
				
				foreach($data as $d) {
					$product = wpshop_products::get_product_data($d->product_id);
					$cart['items'][] = array_merge(array(
						'product_id'	=> $d->product_id,
						'product_qty' 	=> $d->product_qty
					),$product);
					
					/* Shipping var */
					$total_weight += $d->product_weight;
					$nb_of_items += $d->product_qty;
					$order_shipping_cost_by_article += $product['product_shipping_cost'] * $d->product_qty;
				
					
					/* item */
					$order_total_ht += $product['product_price_ht'] * $d->product_qty;
					$order_total_ttc += $product['product_price_ttc'] * $d->product_qty;
					/* Si le taux n'existe pas, on l'ajoute */
					if(isset($order_tva[(string)$product['product_tax_rate']])) {
						$order_tva[(string)$product['product_tax_rate']] += $product['product_tax_amount']*$d->product_qty;
					}
					else $order_tva[(string)$product['product_tax_rate']] = $product['product_tax_amount']*$d->product_qty;
				}
				ksort($order_tva);
				
				$cart['order_total_ht'] = number_format($order_total_ht, 5, '.', '');
				$cart['order_total_ttc'] = number_format($order_total_ttc, 5, '.', '');
				
				$total_cart_ht_or_ttc_regarding_config = WPSHOP_PRODUCT_PRICE_PILOT=='HT' ? $cart['order_total_ht'] : $cart['order_total_ttc'];
				$cart['order_shipping_cost'] = $this->get_shipping_cost($nb_of_items, $total_cart_ht_or_ttc_regarding_config, $order_shipping_cost_by_article, $total_weight);
				$cart['order_grand_total'] = number_format($order_total_ttc + $cart['order_shipping_cost'], 5, '.', '');
				$cart['order_tva'] = array_map('number_format_hack', $order_tva);
			}
		}
		
		return $cart;
	}
	
	function get_shipping_cost($nb_of_items, $total_cart, $total_shipping_cost, $total_weight) {
	
		$rules = get_option('wpshop_shipping_rules',array());
		$shipping_cost = $total_shipping_cost;
		
		/* Min-Max */
		if($rules['free_from']>=0 && $total_cart>$rules['free_from']) $shipping_cost=0;
		else {
			if($shipping_cost < $rules['min_max']['min']) $shipping_cost = $rules['min_max']['min'];
			elseif($shipping_cost > $rules['min_max']['max']) $shipping_cost = $rules['min_max']['max'];
		}
		
		return number_format($shipping_cost, 5, '.', '');
	}
	
	/** Get the current cart id regarding the session id
	 * @return void
	*/
	function get_cart_id() {
		global $wpdb;
		$user_id = get_current_user_id();
		
		if($user_id) {
			$cart = $wpdb->get_row('SELECT id,user_id FROM '.WPSHOP_DBT_CART.' WHERE session_id="'.session_id().'" OR user_id="'.$user_id.'"', ARRAY_A);
			//$cart = $wpdb->get_row('SELECT id FROM '.WPSHOP_DBT_CART.' WHERE user_id="'.$user_id.'"', ARRAY_A);
			/* Si l'utilisateur est connecté et que son id n'est pas référencé dans le panier */
			if(!$cart['user_id']) {
				$wpdb->update(WPSHOP_DBT_CART, array('user_id' => $user_id), array('id' => $cart['id']));
			}
		}
		else {
			$cart = $wpdb->get_row('SELECT id FROM '.WPSHOP_DBT_CART.' WHERE session_id="'.session_id().'"', ARRAY_A);
		}
		return !empty($cart) ? $cart['id'] : 0;
	}
	
	/** Set the product qty to the qty gived in the argument
	 * @return void
	*/
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
						return sprintf(__('You cannot add that amount to the cart since there is not enough stock. We have %s in stock and you already have %s in your cart.', 'wpshop'), number_format($product_data['product_stock'],0), $this->cart['items'][$found_cart_item_key]['product_qty']);
					endif;
					
					/* Update db */
					$update = $wpdb->update(WPSHOP_DBT_CART_CONTENTS, 
						array('product_qty' => $quantity), 
						array('cart_id' => $cart_id, 'product_id' => $product_id)
					);
				}
				elseif($quantity<=0) {
					/* On supprime le produit du panier */
					$delete = $wpdb->query('DELETE FROM '.WPSHOP_DBT_CART_CONTENTS.' WHERE cart_id='.$cart_id.' AND product_id='.$product_id.'');
				}
				
				/* Update the cart from the db */
				$this->update_cart();
			}	
			else{
				/* Le produit n'est pas present dans le panier */
				return __('This product does not exist in the cart.', 'wpshop');
			}
			
			return 'success';
		}
	}
	
	/* Update the cart from the db */
	function update_cart() {
		$this->cart = $this->load_cart_from_db();
		if($this->is_empty()) { $this->empty_cart(); }
	}
	
	/** Return true if the cart is empty, false otherwise
	* @return boolean
	*/
	function is_empty() {
		$cart = (array)$this->cart;
		return empty($cart);
	}
	
	/** Empty the cart
	* @return void
	*/
	function empty_cart() {
		global $wpdb;
		$cart_id = $this->get_cart_id();
		$wpdb->query('DELETE FROM '.WPSHOP_DBT_CART_CONTENTS.' WHERE cart_id='.$cart_id.'');
		$wpdb->query('DELETE FROM '.WPSHOP_DBT_CART.' WHERE id='.$cart_id.'');
		$this->cart = array();
	}
	
	/** Display the cart content, mini version
	* @return void
	*/
	function display_mini_cart() {
		
		$cart = (array)$this->cart;
		$cpt=0;
		if(!empty($cart['items'])) {
			foreach($cart['items'] as $b) $cpt++;
		}
		$mini_cart = '<div>';
		if($cpt==0) {
			$mini_cart .= __('Your cart is empty','wpshop');
		}
		else {
			// Currency
			$currency = wpshop_tools::wpshop_get_currency();
			$mini_cart .= '<a href="'.get_permalink(get_option('wpshop_cart_page_id')).'">'.sprintf(__('Your have %s item(s) in your cart','wpshop'), $cpt).' - '.number_format($cart['order_grand_total'],2).' '.$currency.'</a>';
		}
		$mini_cart .= '</div>';
		echo $mini_cart;
	}
	
	/** Display the cart content
	* @param boolean $hide_button : cacher le bouton de soumission ou non
	* @return void
	*/
	function display_cart($hide_button=false) {

		$cart = (array)$this->cart;
		
		// Currency
		$currency = wpshop_tools::wpshop_get_currency();
		
		$cartContent = '
		<a href="#" class="recalculate-cart-button">'.__('Recalculate the cart','wpshop').'</a>
		<table id="cartContent">
		<thead>
		<tr>
			<th>'.__('Product name', 'wpshop').'</th>
			<th class="center">'.__('Unit price ET', 'wpshop').'</th>
			<th class="center">'.__('Quantity', 'wpshop').'</th>
			<th>'.__('Total price ET', 'wpshop').'</th>
			<th>'.__('Total price ATI', 'wpshop').'</th>
			<th class="center">'.__('Action', 'wpshop').'</th>
		</tr>
		</thead>
		<tfoot>
		<tr>
			<th>'.__('Product name', 'wpshop').'</th>
			<th class="center">'.__('Unit price ET', 'wpshop').'</th>
			<th class="center">'.__('Quantity', 'wpshop').'</th>
			<th>'.__('Total price ET', 'wpshop').'</th>
			<th>'.__('Total price ATI', 'wpshop').'</th>
			<th class="center">'.__('Action', 'wpshop').'</th>
		</tr>
		</tfoot>
		<tbody>';
		
		if(!$this->is_empty())
		{
			foreach($cart['items'] as $b):
				// On récupère la liste des catégories pour chaque produit
				$cats = get_the_terms($b['product_id'], WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES);
				$cat_id = empty($cats) ? 0 : $cats[0]->term_id;
				if($cat_id==0){
					$product_link = 'catalog/product/' . $b['data']['post_name'];
				}
				else $product_link = get_term_link((int)$cat_id , WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES) . '/' . $b['post_name'];
				
				$cartContent .= '
					<tr id="product_'.$b['product_id'].'">
					
						<input type="hidden" value="'.$b['product_qty'].'" name="currentProductQty" />
						
						<td><a href="'.$product_link.'">'.wpshop_tools::trunk($b['product_name'],30).'</a></td>
						
						<td class="product_price_ht center">'.sprintf('%0.2f', $b['product_price_ht']).' '.$currency.'</td>
						
						<td class="center" style="min-width: 125px;">
							<a href="#" class="productQtyChange">-</a> 
							<input type="text" value="'.$b['product_qty'].'" name="productQty" /> 
							<a href="#" class="productQtyChange">+</a>
						</td>
						
						<td class="total_price_ht center"><span>'.sprintf('%0.2f', $b['product_price_ht']*$b['product_qty']).' '.$currency.'</span></td>
						
						<td class="total_price_ttc center"><span>'.sprintf('%0.2f', $b['product_price_ttc']*$b['product_qty']).' '.$currency.'</span></td>
						
						<td class="center"><a href="#" class="remove" title="Remove">Remove</a></td>
					</tr>';
			endforeach;
			$cartContent .= '</tbody></table>';
			$submit = empty($hide_button) ? '<input type="submit" value="Valider mon panier" name="cartCheckout" />' : null;
			echo empty($hide_button) ? '<form action="'.self::get_checkout_url().'" method="post">' : null;
			
			$tva_string = '';
			foreach($cart['order_tva'] as $k => $v) {
				$tva_string .= '<div id="tax_total_amount_'.str_replace(".","_",$k).'">'.__('Tax','wpshop').' '.$k.'% : <span class="right">'.number_format($v,2,'.',' ').' '.$currency.'</span></div>';
			}
			
			echo '<span id="loading">&nbsp;</span>
					<div class="cart">
						'.$cartContent.'
						<p>
							<div>'.__('Total ET','wpshop').' : <span class="total_ht right">'.number_format($cart['order_total_ht'],2).' '.$currency.'</span></div>
							'.$tva_string.'
							<div id="order_shipping_cost">'.__('Shipping','wpshop').' : <span class="right">'.number_format($cart['order_shipping_cost'],2).' '.$currency.'</span></div>
							<div>'.__('Total ATI','wpshop').' : <span class="total_ttc right bold">'.number_format($cart['order_grand_total'],2).' '.$currency.'</span></div>
						</p>
						'.$submit.'
					</div>
			';
			echo empty($hide_button) ? '</form>' : null;
		}
		else echo '<div class="cart">'.__('Your cart is empty.','wpshop').'</div>';
	}
	
	/**
     * Check if product is in the cart and return cart item key
     * @param int $product_id
     * @return int|null
     */
	function find_product_in_cart($product_id) {
		if(!empty($this->cart['items'])) {
			foreach ($this->cart['items'] as $cart_item_key => $cart_item) :
				if ($cart_item['product_id'] == $product_id) :
					return $cart_item_key;
				endif;
			endforeach;
		}
        return NULL;
    }
	
	/** Remove a item from the cart
	 * @return void
	*/
	function remove_from_cart($product_id) {
		global $wpdb;
		
		$cart_id = $this->get_cart_id();
		if($cart_id) {
			$delete = $wpdb->query('DELETE FROM '.WPSHOP_DBT_CART_CONTENTS.' WHERE cart_id='.$cart_id.' AND product_id='.$product_id.'');
			/* Update the cart from the db */
			$this->update_cart();
			return true;
		}
		
		return false;
	}
	
	/**
	 * Add a product to the cart
	 * @param   string	product_id	contains the id of the product to add to the cart
	 * @param   string	quantity	contains the quantity of the item to add
	 */
	function add_to_cart($product_id, $quantity = 1) {
		global $wpdb;
		
		/* ID du panier */
		$cart_id = $this->get_cart_id();
		
		if ($quantity < 1) $quantity = 1;
		$product = wpshop_products::get_product_data($product_id);
		
		// If product doesn't exist
		if($product===false) :
			return __('This product does not exist', 'wpshop');
		endif;
		// Price set check
		if($product[WPSHOP_PRODUCT_PRICE_TTC] === '') :
			return __('This product cannot be purchased - the price is not yet announced', 'wpshop');
		endif;
		// Price set check
		if($product[WPSHOP_PRODUCT_PRICE_TTC] < 0) :
			return __('This product cannot be purchased - its price is negative', 'wpshop');
		endif;
		
		// Search for the product in the cart
		$cart_content = $wpdb->get_row('SELECT id, product_qty FROM '.WPSHOP_DBT_CART_CONTENTS.' WHERE cart_id="'.$cart_id.'" AND product_id="'.$product_id.'"');
		
		// Add it
		if (!empty($cart_content)) :
			
			$quantity = $quantity + $cart_content->product_qty;
			
			// Stock check - this time accounting for whats already in-cart
			if ($product['product_stock'] > -1 && $product['product_stock'] < $quantity) :
				return sprintf(__('You cannot add that amount to the cart since there is not enough stock. We have %s in stock and you already have %s in your cart.', 'wpshop'), number_format($product['product_stock'],0), $this->cart['items'][$found_cart_item_key]['product_qty']);
			endif;

			/* Update the cart content */
			$update = $wpdb->update(WPSHOP_DBT_CART_CONTENTS, array('product_qty' => $quantity), array('id' => $cart_content->id));
			
		else :
		
			if(!$cart_id):
				$wpdb->insert(WPSHOP_DBT_CART, array(
					'id' => NULL,
					'session_id' => session_id(),
					'user_id' => get_current_user_id()
				));
				$cart_id = $wpdb->insert_id;
			endif;
		
			// Stock check - only check if we're managing stock and backorders are not allowed
			if ($product['product_stock'] > -1 && $product['product_stock'] < $quantity) :
				return sprintf(__('You cannot add that amount to the cart since there is not enough stock. We have %s in stock.', 'wpshop'), number_format($product['product_stock'],0));
			endif;
			
			$insert = $wpdb->insert(WPSHOP_DBT_CART_CONTENTS, array(
				'id' => NULL,
				'cart_id' => $cart_id,
				'product_id' => $product_id,
				'product_qty' => $quantity
			));
			
		endif;
		
		/* Update the cart from the db */
		$this->update_cart();
		
		return 'success';
	}
	
	/** Gets the url to the checkout page
	 * @return void
	*/
	function get_checkout_url() {
		$checkout_page_id = get_option('wpshop_checkout_page_id');
		if ($checkout_page_id) :
			return get_permalink($checkout_page_id);
		endif;
	}
}
?>