<?php
class wpshop_dashboard {
	function display_dashboard() {
		global $order_status;
	
	ob_start();
?>
	<div id="wpshop_dashboard">

		<div id="dashboard-widgets" class="metabox-holder">

			<div class="postbox-container" style="width:49%;">

				<div id="wpshop_right_now" class="wpshop_right_now postbox">
					<h3><?php _e('Right Now', 'wpshop') ?></h3>
					<div class="inside">

						<div class="table table_content">
							<p class="sub"><?php _e('Shop Content', 'wpshop'); ?></p>
							<table>
								<tbody>
									<tr class="first">
										<td class="first b"><a href="edit.php?post_type=<?php echo WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT; ?>"><?php
											$num_posts = wp_count_posts(WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT);
											$num = number_format_i18n( $num_posts->publish );
											echo $num;
										?></a></td>
										<td class="t"><a href="edit.php?post_type=<?php echo WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT; ?>"><?php _e('Products', 'wpshop'); ?></a></td>
									</tr>
									<tr>
										<td class="first b"><a href="edit-tags.php?taxonomy=<?php echo WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES; ?>&post_type=<?php echo WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT; ?>"><?php echo wp_count_terms(WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES); ?></a></td>
										<td class="t"><a href="edit-tags.php?taxonomy=<?php echo WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES; ?>&post_type=<?php echo WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT; ?>"><?php _e('Product Categories', 'wpshop'); ?></a></td>
									</tr>
									<tr>
										<td class="first b"><a href="edit.php?post_type=<?php echo WPSHOP_NEWTYPE_IDENTIFIER_ORDER; ?>">
										<?php $num_posts = wp_count_posts(WPSHOP_NEWTYPE_IDENTIFIER_ORDER); echo number_format_i18n($num_posts->publish); ?></a></td>
										<td class="t"><a href="edit.php?post_type=<?php echo WPSHOP_NEWTYPE_IDENTIFIER_ORDER; ?>"><?php _e('Orders', 'wpshop'); ?></a></td>
									</tr>
									<tr>
										<td class="first b"><a href="users.php"><?php $result = count_users(); echo $result['total_users']; ?></a></td>
										<td class="t"><a href="users.php"><?php _e('Customers', 'wpshop'); ?></a></td>
									</tr>
								</tbody>
							</table>
						</div>
						<div class="table table_discussion">
							<p class="sub"><?php _e('Orders', 'wpshop'); ?></p>
							<table>
								<tbody>
									<?php
									$args = array(
										'numberposts'     => -1,
										'orderby'         => 'post_date',
										'order'           => 'DESC',
										'post_type'       => WPSHOP_NEWTYPE_IDENTIFIER_ORDER,
										'post_status'     => 'publish'
									);
									$orders = get_posts($args);
									$order_completed = $order_shipped = $order_awaiting_payment = $order_denied = 0;
									if ($orders) {
										foreach ($orders as $o) {
											$order = get_post_meta($o->ID, '_order_postmeta', true);
											if(!empty($order)){
												switch($order['order_status']) {
													case 'completed': $order_completed++; break;
													case 'shipped': $order_shipped++; break;
													case 'awaiting_payment': $order_awaiting_payment++; break;
													case 'denied': $order_denied++; break;
												}
											}
										}
									}
									?>
									
									<tr>
										<td class="b"><a href="edit.php?post_type=shop_order&shop_order_status=completed"><span class="total-count"><?php echo $order_completed; ?></span></a></td>
										<td class="last t"><a class="completed" href="edit.php?post_type=shop_order&shop_order_status=completed"><?php _e('Completed', 'wpshop'); ?></a></td>
									</tr>
									
									<tr>
										<td class="b"><a href="edit.php?post_type=shop_order&shop_order_status=shipped"><span class="total-count"><?php echo $order_shipped; ?></span></a></td>
										<td class="last t"><a class="shipped" href="edit.php?post_type=shop_order&shop_order_status=completed"><?php _e('Shipped', 'wpshop'); ?></a></td>
									</tr>
									
									<tr class="first">
										<td class="b"><a href="edit.php?post_type=shop_order&shop_order_status=awaiting_payment"><span class="total-count"><?php echo $order_awaiting_payment; ?></span></a></td>
										<td class="last t"><a class="pending" href="edit.php?post_type=shop_order&shop_order_status=awaiting_payment"><?php _e('Awaiting payment', 'wpshop'); ?></a></td>
									</tr>
									
									<tr>
										<td class="b"><a href="edit.php?post_type=shop_order&shop_order_status=denied"><span class="total-count"><?php echo $order_denied; ?></span></a></td>
										<td class="last t"><a class="denied" href="edit.php?post_type=shop_order&shop_order_status=denied"><?php _e('Denied', 'wpshop'); ?></a></td>
									</tr>
									
									
								</tbody>
							</table>
						</div>
						<div class="versions">
							<p id="wp-version-message"><?php _e('You are using', 'wpshop'); ?> <strong>WPShop <?php echo WPSHOP_VERSION; ?></strong></p>
						</div>
						<div class="clear"></div>
					</div>

				</div><!-- postbox end -->

				<div class="postbox">
					<h3 class="hndle" id="poststuff"><span><?php _e('Recent Orders', 'wpshop') ?></span></h3>
					<div class="inside">
						<?php
							$args = array(
								'numberposts'     => 10,
								'orderby'         => 'post_date',
								'order'           => 'DESC',
								'post_type'       => WPSHOP_NEWTYPE_IDENTIFIER_ORDER,
								'post_status'     => 'publish'
							);
							$orders = get_posts( $args );
							if ($orders) {
								echo '<ul class="recent-orders">';
								foreach ($orders as $o) :

									$order = get_post_meta($o->ID, '_order_postmeta', true);
									//echo '<pre>'; print_r($order); echo '</pre>';
									
									$nb_items = !empty($order['order_items']) ? sizeof($order['order_items']) : 0;
									$total = !empty($order['order_grand_total']) ? $order['order_grand_total'] : 0;

									echo '
									<li>
										<span class="order-status '.$order['order_status'].'">'.__($order_status[$order['order_status']],'wpshop').'</span>
										<a href="'.admin_url('post.php?post='.$o->ID).'&action=edit">'.get_the_time('l j F Y, h:i:s A', $o->ID).'</a><br />
										'.$nb_items.' '.__('items', 'wpshop').' &mdash; <span class="order-cost">'.__('Total', 'wpshop').': '.$total.' '.wpshop_tools::wpshop_get_currency().'</span>
									</li>';

								endforeach;
								echo '</ul>';
							}
							else {
								echo __('There is no order yet','wpshop');
							}
						?>
					</div>
				</div><!-- postbox end -->



			</div>
			<div class="postbox-container" style="width:49%; float:right;">

				<?php
					global $current_month_offset;

					$current_month_offset = (int) date('m');

					if (isset($_GET['month'])) $current_month_offset = (int) $_GET['month'];
				?>
				<div class="postbox stats" id="wpshop-stats">
					<h3 class="hndle" id="poststuff">
						<?php if ($current_month_offset!=date('m')) : ?>
							<a href="admin.php?page=wpshop_dashboard&amp;month=<?php echo $current_month_offset+1; ?>" class="next"><?php echo __('Next Month','wpshop'); ?> &rarr;</a>
						<?php endif; ?>
						<a href="admin.php?page=wpshop_dashboard&amp;month=<?php echo $current_month_offset-1; ?>" class="previous">&larr; <?php echo __('Previous Month','wpshop'); ?></a>
						<span><?php _e('Monthly Sales', 'wpshop') ?></span></h3>
					<div class="inside">
						<div id="placeholder" style="width:100%; height:300px; position:relative;"></div>
						<script type="text/javascript">
							/* <![CDATA[ */

							jQuery(function(){

								function weekendAreas(axes) {
									var markings = [];
									var d = new Date(axes.xaxis.min);
									// go to the first Saturday
									d.setUTCDate(d.getUTCDate() - ((d.getUTCDay() + 1) % 7))
									d.setUTCSeconds(0);
									d.setUTCMinutes(0);
									d.setUTCHours(0);
									var i = d.getTime();
									do {
										// when we don't set yaxis, the rectangle automatically
										// extends to infinity upwards and downwards
										markings.push({ xaxis: { from: i, to: i + 2 * 24 * 60 * 60 * 1000 } });
										i += 7 * 24 * 60 * 60 * 1000;
									} while (i < axes.xaxis.max);

									return markings;
								}

								<?php

									function orders_this_month( $where = '' ) {
										global $current_month_offset;

										$month = $current_month_offset;
										$year = (int) date('Y');

										$first_day = strtotime("{$year}-{$month}-01");
										$last_day = strtotime('-1 second', strtotime('+1 month', $first_day));

										$after = date('Y-m-d', $first_day);
										$before = date('Y-m-d', $last_day);

										$where .= " AND post_date > '$after'";
										$where .= " AND post_date < '$before'";

										return $where;
									}
									add_filter( 'posts_where', 'orders_this_month' );

									$args = array(
										'numberposts'      => -1,
										'orderby'          => 'post_date',
										'order'            => 'DESC',
										'post_type'        => WPSHOP_NEWTYPE_IDENTIFIER_ORDER,
										'post_status'      => 'publish' ,
										'suppress_filters' => false
									);
									$orders = get_posts( $args );

									$order_counts = array();
									$order_amounts = array();

									// Blank date ranges to begin
									$month = $current_month_offset;
									$year = (int) date('Y');

									$first_day = strtotime("{$year}-{$month}-01");
									$last_day = strtotime('-1 second', strtotime('+1 month', $first_day));

									if ((date('m') - $current_month_offset)==0) :
										$up_to = date('d', strtotime('NOW'));
									else :
										$up_to = date('d', $last_day);
									endif;
									$count = 0;

									while ($count < $up_to) :

										$time = strtotime(date('Ymd', strtotime('+ '.$count.' DAY', $first_day))).'000';

										$order_counts[$time] = 0;
										$order_amounts[$time] = 0;

										$count++;
									endwhile;

									if ($orders) :
										foreach ($orders as $order) :

											$order_data = get_post_meta($order->ID, '_order_postmeta', true);

											if ($order_data['order_status']=='denied' || $order_data['order_status']=='awaiting_payment') continue;

											$time = strtotime(date('Ymd', strtotime($order_data['order_date']))).'000';
											
											$order_grand_total = !empty($order_data['order_grand_total']) ? $order_data['order_grand_total'] : 0;

											if (isset($order_counts[$time])) : $order_counts[$time]++;
											else : $order_counts[$time] = 1; endif;

											if (isset($order_amounts[$time])) $order_amounts[$time] = $order_amounts[$time] + $order_grand_total;
											else $order_amounts[$time] = (float) $order_grand_total;

										endforeach;
									endif;

									remove_filter( 'posts_where', 'orders_this_month' );
								?>

								var d = [
									<?php
										$values = array();
										foreach ($order_counts as $key => $value) $values[] = "[$key, $value]";
										echo implode(',', $values);
									?>
								];

								for (var i = 0; i < d.length; ++i) d[i][0] += 60 * 60 * 1000;

								var d2 = [
									<?php
										$values = array();
										foreach ($order_amounts as $key => $value) $values[] = "[$key, $value]";
										echo implode(',', $values);
									?>
								];

								for (var i = 0; i < d2.length; ++i) d2[i][0] += 60 * 60 * 1000;

								var plot = jQuery.plot(jQuery("#placeholder"), [ { label: "<?php echo __('Number of sales','wpshop'); ?>", data: d }, { label: "<?php echo __('Sales amount','wpshop'); ?>", data: d2, yaxis: 2 } ], {
									series: {
										lines: { show: true },
										points: { show: true }
									},
									grid: {
										show: true,
										aboveData: false,
										color: '#ccc',
										backgroundColor: '#fff',
										borderWidth: 2,
										borderColor: '#ccc',
										clickable: false,
										hoverable: true,
										markings: weekendAreas
									},
									xaxis: {
										mode: "time",
										timeformat: "%d %b",
										tickLength: 1,
										minTickSize: [1, "day"]
									},
									yaxes: [ { min: 0, tickSize: 1, tickDecimals: 0 }, { position: "right", min: 0, tickDecimals: 2 } ],
									colors: ["#21759B", "#ed8432"]
								});

								function showTooltip(x, y, contents) {
									jQuery('<div id="tooltip">' + contents + '</div>').css( {
										position: 'absolute',
										display: 'none',
										top: y + 5,
										left: x + 5,
										border: '1px solid #fdd',
										padding: '2px',
										'background-color': '#fee',
										opacity: 0.80
									}).appendTo("body").fadeIn(200);
								}

								var previousPoint = null;
								jQuery("#placeholder").bind("plothover", function (event, pos, item) {
									if (item) {
										if (previousPoint != item.dataIndex) {
											previousPoint = item.dataIndex;

											jQuery("#tooltip").remove();

											if (item.series.label=="<?php echo __('Number of sales','wpshop'); ?>") {

												var y = item.datapoint[1];
												showTooltip(item.pageX, item.pageY, y+" <?php echo __('sales','wpshop'); ?>");

											} else {

												var y = item.datapoint[1].toFixed(2);
												showTooltip(item.pageX, item.pageY, /*item.series.label + */y+" <?php echo wpshop_tools::wpshop_get_currency(); ?>");

											}

										}
									}
									else {
										jQuery("#tooltip").remove();
										previousPoint = null;
									}
								});

							});

							/* ]]> */
						</script>
					</div>
				</div><!-- postbox end -->
			</div>
		</div>
	</div>
	<?php
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
}
?>