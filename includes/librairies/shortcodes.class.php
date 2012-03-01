<?php
class wpshop_shortcodes{

	/** Display the shortcodes page
	 * @return void
	*/
	function wpshop_shortcodes() {
		echo '
			<div class="wrap">
				<div id="icon-edit" class="icon32 icon32-posts-wpshop_product"><br /></div>
				<h2>Listing des Shortcodes</h2>
				<ul class="menutab">
					<li class="active"><a href="#products">'.__('Products', 'wpshop').'</a></li>
					<li><a href="#category">'.__('Categories', 'wpshop').'</a></li>
					<li><a href="#attributs">'.__('Attributs', 'wpshop').'</a></li>
					<li><a href="#widgets">'.__('Widgets', 'wpshop').'</a></li>
					<li><a href="#customs_emails">'.__('Customs emails', 'wpshop').'</a></li>
				</ul>
				<div class="contenttab">
					<div id="products" class="tab active">
						<label>'.__('Simple product shortcode', 'wpshop').'</label> <code>[wpshop_product pid="<b>ID_DU_PRODUIT</b>" type="<b>list|grid</b>"]</code><br />
						<label class="fw-normal">'.__('Basic example', 'wpshop').'</label> <code>[wpshop_product pid="12" type="list"]</code><br />
						<label class="fw-normal">'.__('PHP example', 'wpshop').'</label> <code>&lt;?php echo do_shortcode(\'[wpshop_product pid="12" type="list"]\'); ?></code><br /><br />
						<label>'.__('Several products shortcode', 'wpshop').'</label> <code>[wpshop_product pid="<b>ID_DU_PRODUIT_1,ID_DU_PRODUIT_2,ID_DU_PRODUIT_3..</b>" type="<b>list|grid</b>"]</code><br />
						<label class="fw-normal">'.__('Basic example', 'wpshop').'</label> <code>[wpshop_product pid="12,25,4,98" type="list"]</code><br />
						<label class="fw-normal">'.__('PHP example', 'wpshop').'</label> <code>&lt;?php echo do_shortcode(\'[wpshop_product pid="12,25,4,98" type="list"]\'); ?></code>
					</div>
					<div id="category" class="tab">
						<label>'.__('Category shortcode', 'wpshop').'</label> <code>[wpshop_category cid="<b>ID_DE_LA_CATEGORIE</b>" type="<b>list|grid</b>"]</code><br />
						<label class="fw-normal">'.__('Basic example', 'wpshop').'</label> <code>[wpshop_category cid="12" type="list"]</code><br />
						<label class="fw-normal">'.__('PHP example', 'wpshop').'</label> <code>&lt;?php echo do_shortcode(\'[wpshop_category cid="12" type="list"]\'); ?></code>
					</div>
					<div id="attributs" class="tab">
						<label>'.__('Attribut shortcode', 'wpshop').'</label> <code>[wpshop_att_val type="<b>decimal|varchar</b>" attid="<b>ID_DE_LATTRIBUT</b>" pid="<b>ID_DU_PRODUIT</b>"]</code><br />
						<label class="fw-normal">'.__('Basic example', 'wpshop').'</label> <code>[wpshop_att_val type="decimal" attid="3" pid="98"]</code><br />
						<label class="fw-normal">'.__('PHP example', 'wpshop').'</label> <code>&lt;?php echo do_shortcode(\'[wpshop_att_val type="decimal" attid="3" pid="98"]\'); ?></code><br /><br />
						<label>'.__('Attributs group shortcode', 'wpshop').'</label> <code>[wpshop_att_group pid="<b>ID_DU_PRODUIT</b>" sid="<b>ID_DE_LA_SECTION</b>"]</code><br />
						<label class="fw-normal">'.__('Basic example', 'wpshop').'</label> <code>[wpshop_att_group pid="98" sid="2"]</code><br />
						<label class="fw-normal">'.__('PHP example', 'wpshop').'</label> <code>&lt;?php echo do_shortcode(\'[wpshop_att_group pid="98" sid="2"]\'); ?></code>
					</div>
					
					<div id="widgets" class="tab">
						<h3>Liste de produits</h3>
						
						<label>'.__('Products list shortcode', 'wpshop').'</label> <code>[wpshop_products limit="<b>NOMBRE_MAXIMUM_DARTICLE_A_AFFICHER</b>" order="<b>PARAMETRES_DE_TRI</b>" sorting="<b>ORDRE_DAFFICHAGE</b>" display="<b>TAILLE_DAFFICHAGE</b>" type="<b>TYPE_DAFFICHAGE</b>" pagination="<b>NOMBRE_DE_PRODUIT_PAR_PAGE</b>"]</code><br />
						<label>order</label> <code>title</code>, <code>date</code>, <code>price</code>, <code>random</code>.<br />
						<label>sorting</label> '.__('<code>asc</code> for the smallest to the largest and <code>desc</code> for the largest to smallest','wpshop').'.<br />
						<label>display</label> '.__('<code>normal</code> for a classic display with thumbnail and <code>mini</code> for tiny display without image','wpshop').'.<br />
						<label>type</label> '.__('<code>list</code> to display one item per line and <code>grid</code> to display several items per line','wpshop').'.<br />
						<label>pagination</label> '.__('Number of products displayed per page. Paging system active if the parameter is greater than zero','wpshop').'.<br />
						<label class="fw-normal">'.__('Basic example', 'wpshop').'</label> <code>[wpshop_products limit="20" order="price" sorting="desc" display="normal" type="grid"]</code><br />
						<label class="fw-normal">'.__('PHP example', 'wpshop').'</label> <code>&lt;?php echo do_shortcode(\'[wpshop_products limit="20" order="price" sorting="desc" display="normal" type="grid"]\'); ?></code><br />
						<label class="fw-normal">'.__('Print result', 'wpshop').'</label> '.__('This code will display the list of 20 most expensive products, the most expensive to cheapest in a grid with images','wpshop').'.<br /><br />
						
						<h3>'.__('Cart', 'wpshop').'</h3>
						
						<label>'.__('Attribut shortcode', 'wpshop').'</label> <code>[wpshop_cart]</code><br />
						<label class="fw-normal">'.__('Basic example', 'wpshop').'</label> <code>[wpshop_cart]</code><br />
						<label class="fw-normal">'.__('PHP example', 'wpshop').'</label> <code>&lt;?php echo do_shortcode(\'[wpshop_cart]\'); ?></code><br /><br />
						
						<label>'.__('Attribut shortcode', 'wpshop').'</label> <code>[wpshop_mini_cart]</code><br />
						<label class="fw-normal">'.__('Basic example', 'wpshop').'</label> <code>[wpshop_mini_cart]</code><br />
						<label class="fw-normal">'.__('PHP example', 'wpshop').'</label> <code>&lt;?php echo do_shortcode(\'[wpshop_mini_cart]\'); ?></code><br /><br />
						
						<h3>'.__('Checkout', 'wpshop').'</h3>
						
						<label>'.__('Attribut shortcode', 'wpshop').'</label> <code>[wpshop_checkout]</code><br />
						<label class="fw-normal">'.__('Basic example', 'wpshop').'</label> <code>[wpshop_checkout]</code><br />
						<label class="fw-normal">'.__('PHP example', 'wpshop').'</label> <code>&lt;?php echo do_shortcode(\'[wpshop_checkout]\'); ?></code><br /><br />
						
						<h3>'.__('Account', 'wpshop').'</h3>
						
						<label>'.__('Attribut shortcode', 'wpshop').'</label> <code>[wpshop_myaccount]</code><br />
						<label class="fw-normal">'.__('Basic example', 'wpshop').'</label> <code>[wpshop_myaccount]</code><br />
						<label class="fw-normal">'.__('PHP example', 'wpshop').'</label> <code>&lt;?php echo do_shortcode(\'[wpshop_myaccount]\'); ?></code><br /><br />
						
						<h3>'.__('Shop', 'wpshop').'</h3>
						
						<label>'.__('Attribut shortcode', 'wpshop').'</label> <code>[wpshop_products]</code><br />
						<label class="fw-normal">'.__('Basic example', 'wpshop').'</label> <code>[wpshop_products]</code><br />
						<label class="fw-normal">'.__('PHP example', 'wpshop').'</label> <code>&lt;?php echo do_shortcode(\'[wpshop_products]\'); ?></code><br /><br />
						
						<h3>'.__('Related products', 'wpshop').'</h3>
						
						<label>'.__('Attribut shortcode', 'wpshop').'</label> <code>[wpshop_related_products pid="<b>ID_DU_PRODUIT</b>"<i>{optionel}</i> display_mode="<b>MODE_DAFFICHAGE</b>"<i>{optionel}</i>]</code><br />
						<label class="fw-normal">'.__('Basic example', 'wpshop').'</label> <code>[wpshop_related_products]</code><br />
						<label class="fw-normal">'.__('PHP example', 'wpshop').'</label> <code>&lt;?php echo do_shortcode(\'[wpshop_related_products]\'); ?></code>
					</div>
					
					<div id="customs_emails" class="tab">
						<p style="font-weight:bold;">'.__('Some emails can be customized from the settings page of the plugin. Here is a list of the various tags available','wpshop').' :</p>
						<label class="fw-normal">'.__('Customer first name', 'wpshop').'</label> <code>[customer_first_name]</code><br />
						<label class="fw-normal">'.__('Customer last name', 'wpshop').'</label> <code>[customer_last_name]</code><br />
						<label class="fw-normal">'.__('Order id', 'wpshop').'</label> <code>[order_key]</code><br />
						<label class="fw-normal">'.__('Paypal transaction id', 'wpshop').'</label> <code>[paypal_order_key]</code><br />
					</div>
					
				</div>
			</div>
		';
	}
}
?>