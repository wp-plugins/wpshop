<table style="width:800px; border : 1px solid #A4A4A4; clear : both;">
	<tr>
		<td width="800" valign="middle" align="left" bgcolor="#1D7DC1" height="40" width="800" style="color : #FFFFFF"><?php echo $comment_title; ?></td>
	</tr>
	<tr>
		<td width="800"><?php echo !empty( $comment ) ? $comment : __( 'No comment for this order', 'wpshop' ); ?></td>
	</tr>
</table>
<div style="clear:both; width : 100%; height : 15px; display : block;"></div>
