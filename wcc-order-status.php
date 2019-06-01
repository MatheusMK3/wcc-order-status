<?php
/*
 * Plugin Name: WC Correios Order Status
 * Description: Visual way to keep track of your Correios order status on client-end!
 * Author: Matheus Pratta
 * Author URI: https://matheus.io
 * Version: 1.0
 * Requires at least: 4.2
 * Tested up to: 4.9.4
 * WC requires at least: 3.0
 * WC tested up to:      3.3.1
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

require_once ('rastrear.class.php');

function wccos_get_status ($order) {
	// Is the order complete?
	if ($order->status == 'completed')
		return 3;
	// If not, is it paid?
	else if ($order->status == 'processing') {
		// Get the Correios tracking info
		$tracking_id = get_post_meta($order->id, '_correios_tracking_code');

		// If we have a tracking code, try to find out order status
		if (!empty($tracking_id) && count($tracking_id) && !empty($tracking_id[0])) {
			// Correios API init
			Rastrear::init();

			// Get tracking data
			$tracking_data = Rastrear::get($tracking_id[0]);

			// Check if status is delivered (01)
			if ($tracking_data->evento->status == '01')
				return 3;

			return 2;
		}
		// If not, return 0
		else
			return 1;
	}
	// If not paid
	else
		return 0;
}

function wccos_display_status ($order) {
	// Initial status
	$status = wccos_get_status($order);

	// All Statuses
	$statuses = array(
		0 => 'Aguardando Pagamento',
		1 => 'Pagamento Aprovado',
		2 => 'Produto Enviado',
		3 => 'Produto Entregue'
	);

?>
<div class="wccos-order-status">
	<table>
		<tr>
			<?php foreach ($statuses as $n => $text) { ?>
			<td class="<?php if ($n <= $status) { ?>enabled<?php } ?>">
				<div><?php echo $text; ?></div>
			</td>
			<?php } ?>
		</tr>
	</table>
</div>
<?php }

// Enqueue into WooCommerce
add_action ( 'woocommerce_order_details_before_order_table', 'wccos_display_status' );
wp_enqueue_style ( 'wccos-order-status', plugins_url( '/wcc-order-status.css', __FILE__ ) );

// Plugin stuff :)
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'wcc_plugin_links' );

function wcc_plugin_links ( $links ) {
   $links[] = '<a href="https://github.com/MatheusMK3/wcc-order-status" target="_blank" title="Star this plugin on GitHub! &lt;3">GitHub</a>';
   return $links;
}
