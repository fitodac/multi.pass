<?php

/**
 * @author: Hoang Ngo
 */
class WD_Logging_Controller extends WD_Controller {
	public function __construct() {
		if ( is_multisite() ) {
			$this->add_action( 'network_admin_menu', 'admin_menu', 12 );
		} else {
			$this->add_action( 'admin_menu', 'admin_menu', 12 );
		}
		$this->add_action( 'admin_enqueue_scripts', 'load_scripts' );
	}

	public function admin_menu() {
		$cap = is_multisite() ? 'manage_network_options' : 'manage_options';
		add_submenu_page( 'wp-defender', __( "Audit Logging", wp_defender()->domain ), __( "Audit Log", wp_defender()->domain ), $cap, 'wdf-logging', array(
			$this,
			'display_main'
		) );
	}

	public function display_main() {
		$this->render( 'logging/soon', array(), true );
	}

	/**
	 * Check if in right page, then load assets
	 */
	public function load_scripts() {
		if ( $this->is_in_page() ) {
			WDEV_Plugin_Ui::load( wp_defender()->get_plugin_url() . 'shared-ui/', false );
			wp_enqueue_style( 'wp-defender' );
			wp_enqueue_script( 'wp-defender' );
		}
	}

	/**
	 * check if this page is page of the plugin
	 * @return bool
	 */
	private function is_in_page() {
		$screen = get_current_screen();
		if ( is_object( $screen ) && in_array( $screen->id, array(
				'defender_page_wdf-logging',
				'defender_page_wdf-logging-network'
			) )
		) {
			return true;
		}

		return false;
	}
}