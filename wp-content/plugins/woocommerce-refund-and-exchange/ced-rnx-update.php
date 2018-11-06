<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if( ! class_exists( 'Ced_Rnx_Update' ) ) 
{
	class Ced_Rnx_Update 
	{
		public function __construct() {
			register_activation_hook(CED_REFUND_N_EXCHANGE_FILE, array($this, 'ced_rnx_check_activation'));
			add_action('ced_rnx_check_event', array($this, 'ced_rnx_check_update'));
			add_filter( 'http_request_args', array($this, 'ced_rnx_updates_exclude'), 5, 2 );
			register_deactivation_hook(CED_REFUND_N_EXCHANGE_FILE, array($this, 'ced_rnx_check_deactivation'));
		}	

		public function ced_rnx_check_deactivation() 
		{
			wp_clear_scheduled_hook('ced_rnx_check_event');
		}

		public function ced_rnx_check_activation() 
		{
			wp_schedule_event(time(), 'daily', 'ced_rnx_check_event');
		}

		public function ced_rnx_check_update() 
		{
			global $wp_version;
			global $ced_rnx_update_check;
			$plugin_folder = plugin_basename( dirname( CED_REFUND_N_EXCHANGE_FILE ) );
			$plugin_file = basename( ( CED_REFUND_N_EXCHANGE_FILE ) );
			if ( defined( 'WP_INSTALLING' ) )
			{
				return false;
			} 
			$postdata = array(
				'action' => 'check_update',
				'purchase_code' => CED_RNX_LICENSE_KEY
			);
			
			$args = array(
				'method' => 'POST',
				'body' => $postdata,
			);
			
			$response = wp_remote_post( $ced_rnx_update_check, $args );
			if(!isset($response['body'])) 
			{
				return false;
			}
			list($version, $url) = explode('~', $response['body']);

			if($this->ced_rnx_plugin_get("Version") == $version) 
			{
				return false;
			}
			
			$plugin_transient = get_site_transient('update_plugins');
			$a = array(
				'slug' => $plugin_folder,
				'new_version' => $version,
				'url' => $this->ced_rnx_plugin_get("AuthorURI"),
				'package' => $url
			);
			$o = (object) $a;
			$plugin_transient->response[$plugin_folder.'/'.$plugin_file] = $o;
			set_site_transient('update_plugins', $plugin_transient);
		}

		public function ced_rnx_updates_exclude( $r, $url ) 
		{
			if ( 0 !== strpos( $url, 'http://api.wordpress.org/plugins/update-check' ) )
			{
				return $r; 
			}	
			$plugins = unserialize( $r['body']['plugins'] );
			unset( $plugins->plugins[ plugin_basename( __FILE__ ) ] );
			unset( $plugins->active[ array_search( plugin_basename( __FILE__ ), $plugins->active ) ] );
			$r['body']['plugins'] = serialize( $plugins );
			return $r;
		}

		//Returns current plugin info.
		public function ced_rnx_plugin_get($i) 
		{
			if ( ! function_exists( 'get_plugins' ) )
			{
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			}	
			$plugin_folder = get_plugins( '/' . plugin_basename( dirname( CED_REFUND_N_EXCHANGE_FILE ) ) );
			$plugin_file = basename( ( CED_REFUND_N_EXCHANGE_FILE ) );
			return $plugin_folder[$plugin_file][$i];
		}
	}
	new Ced_Rnx_Update();
}		
?>