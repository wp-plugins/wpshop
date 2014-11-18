<?php 
$company = get_option('wpshop_company_info');
$emails = get_option('wpshop_emails');
?>

<strong><?php echo ( !empty($company['company_legal_statut']) ) ? $company['company_legal_statut'] : ''; ?> <?php echo ( !empty($company['company_name']) ) ? $company['company_name'] : ''; ?></strong><br/>
<?php echo ( !empty($company['company_street']) ) ? $company['company_legal_statut'] : ''; ?><br/>
<?php echo ( !empty($company['company_postcode']) ) ? $company['company_postcode'] : ''; ?> <?php echo ( !empty($company['company_city']) ) ? $company['company_city'] : ''; ?><br/>
<?php echo ( !empty($company['company_country']) ) ? $company['company_country'] : ''; ?><br/><br/>

<?php _e('Phone', 'wpshop'); ?> : {WPSHOP_COMPANY_PHONE}<br/>
<?php _e('Fax', 'wpshop'); ?> : {WPSHOP_COMPANY_FAX}<br/>
<?php _e('Email', 'wpshop'); ?> : <?php echo ( !empty($emails) && !empty($emails['contact_email']) ) ? $emails['contact_email'] : ''; ?><br/>
<?php _e('Website', 'wpshop'); ?> : <?php echo str_replace( 'http://', '', site_url() ); ?><br/><br/>

<?php echo ( !empty($company['company_rcs']) ) ? __('RCS', 'wpshop').' : '.$company['company_rcs'] : '' ?>
<?php echo ( !empty($company['company_capital']) ) ? __('Capital', 'wpshop').' : '.$company['company_capital'] : '' ?>
<?php echo ( !empty($company['company_siren']) ) ? __('SIREN', 'wpshop').' : '.$company['company_siren'] : '' ?>
<?php echo ( !empty($company['company_siret']) ) ? __('SIRET', 'wpshop').' : '.$company['company_siret'] : '' ?>
<?php echo ( !empty($company['company_tva_intra']) ) ? __('Intra community VAT number', 'wpshop').' : '.$company['company_tva_intra'] : '' ?>
