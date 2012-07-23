<?php
class wpshop_messages
{
	/*	Define the database table used in the current class	*/
	const dbTable = WPSHOP_DBT_MESSAGES;
	/*	Define the url listing slug used in the current class	*/
	const urlSlugListing = WPSHOP_URL_SLUG_MESSAGES;
	/*	Define the url edition slug used in the current class	*/
	const urlSlugEdition = WPSHOP_URL_SLUG_MESSAGES;
	/*	Define the current entity code	*/
	const currentPageCode = 'messages';
	/*	Define the page title	*/
	const pageContentTitle = 'Messages';
	/*	Define the page title when adding an attribute	*/
	const pageAddingTitle = 'Add a message';
	/*	Define the page title when editing an attribute	*/
	const pageEditingTitle = 'Message "%s" edit';
	/*	Define the page title when editing an attribute	*/
	const pageTitle = 'Messages list';

	/*	Define the path to page main icon	*/
	public $pageIcon = '';
	/*	Define the message to output after an action	*/
	public $pageMessage = '';

	/**
	*	Get the url listing slug of the current class
	*
	*	@return string The table of the class
	*/
	function setMessage($message){
		$this->pageMessage = $message;
	}
	/**
	*	Get the url listing slug of the current class
	*
	*	@return string The table of the class
	*/
	function getListingSlug(){
		return self::urlSlugListing;
	}
	/**
	*	Get the url edition slug of the current class
	*
	*	@return string The table of the class
	*/
	function getEditionSlug(){
		return self::urlSlugEdition;
	}
	/**
	*	Get the database table of the current class
	*
	*	@return string The table of the class
	*/
	function getDbTable(){
		return self::dbTable;
	}
	/**
	*	Define the title of the page 
	*
	*	@return string $title The title of the page looking at the environnement
	*/
	function pageTitle(){
		$action = isset($_REQUEST['action']) ? wpshop_tools::varSanitizer($_REQUEST['action']) : '';
		$objectInEdition = isset($_REQUEST['id']) ? wpshop_tools::varSanitizer($_REQUEST['id']) : '';

		$title = __(self::pageTitle, 'wpshop' );
		if($action != ''){
			if(($action == 'edit') || ($action == 'delete')){
				$editedItem = self::getElement($objectInEdition);
				$title = sprintf(__(self::pageEditingTitle, 'wpshop'), str_replace("\\", "", $editedItem->frontend_label) . '&nbsp;(' . $editedItem->code . ')');
			}
			elseif($action == 'add'){
				$title = __(self::pageAddingTitle, 'wpshop');
			}
		}
		elseif((self::getEditionSlug() != self::getListingSlug()) && ($_GET['page'] == self::getEditionSlug())){
			$title = __(self::pageAddingTitle, 'wpshop');
		}
		return $title;
	}

	function elementAction(){
	
	}
	function elementList(){
		self::manage_post();
		$bool_archive = !empty($_GET['hist_visibility']) && $_GET['hist_visibility']=='archived';
		if($bool_archive) {
			$messages = self::get_messages('archived');
		}
		else {
			$messages = self::get_messages();
		}
		$message_count = self::message_count();
		$archived_message_count = self::message_count('archived');

		$element_list = '
				<ul class="subsubsub">
					<li class="all"><a href="?page='.WPSHOP_URL_SLUG_MESSAGES.'"'.(!$bool_archive?' class="current"':null).'>'.__('All','wpshop').' <span class="count">('.$message_count.')</span></a> |</li>
					<li class="archived"><a href="?page='.WPSHOP_URL_SLUG_MESSAGES.'&hist_visibility=archived"'.($bool_archive?' class="current"':null).'>'.__('Archived','wpshop').' <span class="count">('.$archived_message_count.')</span></a></li>
				</ul>
				
				<div class="tablenav top">
					<div class="alignleft actions">
						<form method="post">
						<select name="action">
							<option selected="selected" value="-1">'.__('Grouped actions','wpshop').'</option>
							<option value="archive">'.__('Archive','wpshop').'</option>
						</select>
						<input id="doaction" class="button-secondary action" type="submit" value="Appliquer" name="grouped_action">
					</div>
					<br class="clear">
				</div>
				
				<table class="wp-list-table widefat fixed posts" cellspacing="0">
					<thead>
						<tr>
							<th id="cb" class="manage-column column-cb check-column">
								<input type="checkbox">
							</th>
							<th>'.__('Title','wpshop').'</th>
							<th>'.__('Extract from the message','wpshop').'</th>
							<th>'.__('Recipient','wpshop').'</th>
							<th>'.__('Email address','wpshop').'</th>
							<th>'.__('Creation date','wpshop').'</th>
							<th>'.__('Last dispatch date','wpshop').'</th>
							<th class="manage-column">'.__('Actions','wpshop').'</th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th id="cb" class="manage-column column-cb check-column">
								<input type="checkbox">
							</th>
							<th>'.__('Title','wpshop').'</th>
							<th>'.__('Extract from the message','wpshop').'</th>
							<th>'.__('Recipient','wpshop').'</th>
							<th>'.__('Email address','wpshop').'</th>
							<th>'.__('Creation date','wpshop').'</th>
							<th>'.__('Last dispatch date','wpshop').'</th>
							<th class="manage-column">'.__('Actions','wpshop').'</th>
						</tr>
					</tfoot>
					<tbody id="the-list">
					';
					
				if(!empty($messages)):
					foreach($messages as $m):
						$extract = strlen($m->mess_message<110) ? substr($m->mess_message,0,110).'...' : $m->mess_message;
						$element_list .= '
						<tr id="hist-'.$m->mess_id.'">
							<th class="check-column"><input type="checkbox" name="messages[]" value="'.$m->mess_id.'" /></th>
							<td class=""><a href="?page='.WPSHOP_URL_SLUG_MESSAGES.'&mid='.$m->mess_id.'">'.$m->mess_title.'</a></td>
							<td class="wpshop_extract">'.$extract.'</td>
							<td class="wpshop_recipient">'.(!empty($m->user_login)?$m->user_login:__('Unknown','wpshop')).'</td>
							<td class="wpshop_recipient">'.$m->mess_user_email.'</td>
							<td class="wpshop_creation_date">'.$m->mess_creation_date.'</td>
							<td class="wpshop_last_dispatch_date">'.($m->mess_creation_date==$m->mess_last_dispatch_date?'--':$m->mess_last_dispatch_date).'</td>
							<td class="wpshop_actions"><a class="button" href="?page='.WPSHOP_URL_SLUG_MESSAGES.'&mid='.$m->mess_id.'">'.__('See','wpshop').'</a></td>
						</tr>
						';
					endforeach;
				else:
					$element_list .=  '<tr><td colspan="8">'.__('No message found','wpshop').'</td></tr>';
				endif;
				
				$element_list .=  '
					</tbody>
					</table>
					</form>';

		return $element_list;
	}
	function getPageFormButton(){
	
	}
	function getElement(){
	
	}

	function elementEdition($itemToEdit = ''){
		self::manage_post();
		$element_edition = '';

		if(!empty($_GET['mid'])) {
		
			$message = self::get_message($_GET['mid']);
			$histo = self::get_histo($_GET['mid']);
			
			$element_edition .= '<br />	
				<div id="poststuff" class="metabox-holder has-right-sidebar">
				
					<div id="side-info-column" class="inner-sidebar">
						<div id="submitdiv" class="postbox">
							<h3 class="hndle"><span>'.__('Message','wpshop').'</span></h3>
							<div class="inside">
								<div class="misc-pub-section">
									'.__('Recipient','wpshop').' : '.(!empty($m->user_login)?'<b>'.$m->user_login.'</b>':__('Unknown','wpshop')).'<br />
									'.__('Email address','wpshop').' : <b>'.$message->mess_user_email.'</b>
								</div>
								<div class="misc-pub-section curtime">';
								$loop_first=true;
								foreach($histo as $h):
									if($loop_first)
										$element_edition .= '<span id="timestamp">'.__('Sent','wpshop').' : <b>'.$h->hist_datetime.'</b></span><br />';
									else $element_edition .= '<span id="timestamp">'.__('Re-Sent','wpshop').' : <b>'.$h->hist_datetime.'</b></span><br />';
									$loop_first=false;
								endforeach;
								
			$element_edition .= '					
								</div>
								<div style="padding:7px 10px 8px 10px;" class="misc-pub-section misc-pub-section-last">
									<form method="post">
										<input type="hidden" name="mid" value="'.$_GET['mid'].'" />
										<input id="publish" class="button-primary" type="submit" value="'.__('Re-send the message','wpshop').'" name="resend">
									</form>
								</div>
							</div>
						</div>
					</div>
					
					<div id="post-body">
						<div id="post-body-content">
						
							<div id="titlediv">
								<div id="titlewrap">
									<h1 class="wpshop_message_object">'.__('Object','wpshop').' : '.$message->mess_title.'</h1>
								</div>
							</div>
							
							<div id="maindiv" class="postbox">
								<h3 class="hndle"><span>'.__('Message','wpshop').'</span></h3>
								<div class="inside">
									'.$message->mess_message.'
								</div>
							</div>
							
						</div>
					</div>
					
				</div>
			';
		}

		return $element_edition;
	}
	
	/**
	* $_POST Management
	*/
	function manage_post() {
		global $wpdb;
		
		// Renvoi du message
		if(isset($_POST['resend'])) {
			$mid = $_POST['mid'];
			$date = date('Y-m-d H:i:s');
			$message = self::get_message($_GET['mid']);
			
			//$wpdb->query('INSERT INTO '.WPSHOP_DBT_HISTORIC.' VALUES(NULL, '.$mid.', "'.$date.'");');
			//$wpdb->query('UPDATE '.WPSHOP_DBT_MESSAGES.' SET mess_last_dispatch_date="'.$date.'" WHERE mess_id='.$mid.';');
			
			// On enregistre l'envoi dans l'historique
			$wpdb->insert(WPSHOP_DBT_HISTORIC, array(
				'hist_id' => NULL, 
				'hist_message_id' => $mid,
				'hist_datetime' => $date
			));
			// On met à jour les infos sur le message
			$wpdb->update(WPSHOP_DBT_MESSAGES, array(
				'mess_last_dispatch_date' => $date
			), array(
				'mess_id' => $mid
			));
			
			
			// On renvoi le message
			wpshop_tools::wpshop_email($message->mess_user_email, $message->mess_title, $message->mess_message, $save=false);
		}
		elseif(isset($_POST['grouped_action'])) {
			if(isset($_POST['action']) && $_POST['action']=='archive') {
				foreach($_POST['messages'] as $a) {
					$wpdb->update(WPSHOP_DBT_MESSAGES, array(
						'mess_visibility' => 'archived'
					), array(
						'mess_id' => $a
					));
				}
			}
		}
	}
	
	/** Get a message by id
	* @return array
	*/
	function get_message($mid) {
		global $wpdb;
		
		$message = $wpdb->get_row('
			SELECT * FROM '.WPSHOP_DBT_MESSAGES.' 
			LEFT JOIN '.$wpdb->users.' ON mess_user_id=ID
			WHERE mess_id='.$mid.';
		');
		
		return !empty($message) ? $message : array();
	}
	
	/** Get the messages historic by message id
	* @return array
	*/
	function get_histo($mid) {
		global $wpdb;
		$histo = $wpdb->get_results('SELECT * FROM '.WPSHOP_DBT_HISTORIC.' WHERE hist_message_id='.$mid.';');
		return !empty($histo) ? $histo : array();
	}
	
	/** Get the messages (unique)
	* @return void
	*/
	function get_messages($type='normal') {
		global $wpdb;
		
		if($type=='archived') {
			$messages = $wpdb->get_results('
				SELECT * FROM '.WPSHOP_DBT_MESSAGES.' 
				LEFT JOIN '.$wpdb->users.' ON mess_user_id=ID
				WHERE mess_visibility="archived"
				ORDER BY mess_last_dispatch_date DESC
			');
		}
		else {
			$messages = $wpdb->get_results('
				SELECT * FROM '.WPSHOP_DBT_MESSAGES.' 
				LEFT JOIN '.$wpdb->users.' ON mess_user_id=ID
				ORDER BY mess_last_dispatch_date DESC
			');
		}
		
		return !empty($messages) ? $messages : array();
	}
	
	/** Store a new message
	* @return boolean
	*/
	function add_message($recipient_id=0, $email, $title, $message, $object) {
		global $wpdb;
		
		$object_empty = array('object_type'=>'','object_id'=>0);
		$object = array_merge($object_empty, $object);
		
		$date = date('Y-m-d H:i:s');
		// Insertion message
		$wpdb->insert(WPSHOP_DBT_MESSAGES, array(
			'mess_user_id' => $recipient_id,
			'mess_user_email' => $email,
			
			'mess_object_type' => $object['object_type'],
			'mess_object_id' => $object['object_id'],
			
			'mess_title' => $title,
			'mess_message' => $message,
			'mess_creation_date' => $date,
			'mess_last_dispatch_date' => $date
		));
		$message_id = $wpdb->insert_id;
		// Insertion dans l'historique
		$wpdb->insert(WPSHOP_DBT_HISTORIC, array(
			'hist_message_id' => $message_id,
			'hist_datetime' => $date
		));
		return true;
	}
	
	/** Return the number of messages by type
	* @return void
	*/
	function message_count($type='normal') {
		global $wpdb;
		
		if($type=='archived') {
			$count = $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM '.WPSHOP_DBT_MESSAGES.' WHERE mess_visibility="archived";'));
		}
		else {
			$count = $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM '.WPSHOP_DBT_MESSAGES.';'));
		}
		
		return !empty($count) ? $count : 0;
	}
}
?>