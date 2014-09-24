<h3 class="hndle"><span><?php _e('Exportation', 'wpsexport_i18n') ?></span></h3>
	<div class="inside">
		<form method="get" action="">
			<p>
				<input type="hidden" name="page" value="wpshop_dashboard" />
				<label for="chooselist"><?php _e('What do you want to export?', 'wpsexport_i18n'); ?></label><br />
				<select name="userlist" id="whatexportid" style="border:solid 1px black; border-radius:5px; box-shadow:0 0 2px;">
					<option value="export3" id="orderlist"><?php _e('Order list', 'wpsexport_i18n'); ?></option>
					<option value="export2" id="customerlist"><?php _e('Client list', 'wpsexport_i18n'); ?></option>
					<option value="export1" id="buyerslist"><?php _e('Clients who already ordered', 'wpsexport_i18n'); ?></option>
					<option value="export4" id="bestbuylist"><?php _e('Clients who ordered at least to' , 'wpsexport_i18n'); ?></option>
					<option value="export5" id="notcompleteorder"><?php _e('Not completed orders', 'wpsexport_i18n'); ?></option>
				</select>
				<input type="number" name="maxmoney" id="bestbuyerbutton" placeholder="<?php _e('Minimum amount', 'wpsexport_i18n'); ?>" style="width:220px; border:solid 1px black; border-radius:5px; text-align:center; box-shadow:0 0 6px;" /> <br />
				<input type='submit' value='<?php _e('Export', 'wpsexport_i18n'); ?>' style="border:solid 1px black; border-radius:5px; text-align:center; box-shadow:0 0 6px;" />
				</form>

</div>