<?php

class CoursePress_Helper_Extension_MarketPress {

	private static $installed = false;

	private static $activated = false;

	private static $base_path = array(
		'pro' => 'marketpress/marketpress.php',
		'free' => 'wordpress-ecommerce/marketpress.php',
	);

	public static function init() {

		if ( CP_IS_CAMPUS ) {
			return false;
		}

		add_filter( 'coursepress_extensions_plugins', array( __CLASS__, 'add_to_extensions_list' ) );
	}

	public static function add_to_extensions_list( $plugins ) {

		if ( CP_IS_PREMIUM ) {

			$plugins[] = array(
				'name' => 'MarketPress',
				'slug' => 'marketpress',
				'base_path' => self::$base_path['pro'],
				'source' => CoursePress::$path . 'asset/file/marketpress-pro.zip',
				'source_message' => __( 'Included in the CoursePress Plugin', 'cp' ),
				'external_url' => '', /* http://premium.wpmudev.org/project/e-commerce/ */
				'external' => 'no',
				'protocol' => '',
			);

		} else {

			$plugins[] = array(
				'name' => 'MarketPress - WordPress eCommerce',
				'slug' => 'wordpress-ecommerce',
				'base_path' => self::$base_path['free'],
				'source' => 'downloads.wordpress.org/plugin/wordpress-ecommerce.zip',
				'source_message' => __( 'WordPress.org Repository', 'cp' ),
				'external_url' => '', /* https://wordpress.org/plugins/wordpress-ecommerce/ */
				'external' => 'yes',
				'protocol' => 'https',
			);

		}

		return $plugins;
	}


	public static function installed_scope() {
		$scope = '';

		foreach ( self::$base_path as $key => $path ) {
			$plugin_dir = WP_PLUGIN_DIR . '/' . $path;
			$plugin_mu_dir = WP_CONTENT_DIR . '/mu-plugins/' . $path;
			$location = file_exists( $plugin_dir ) ? trailingslashit( WP_PLUGIN_DIR ) : ( file_exists( $plugin_mu_dir ) ?  WP_CONTENT_DIR . '/mu-plugins/' : '' ) ;
			$scope = ! empty( $location ) ? $key : $scope;
		}

		return $scope;
	}

	public static function installed() {

		$scope = self::installed_scope();
		return ! empty( $scope );

	}

	public static function activated() {

		$scope = self::installed_scope();

		require_once ABSPATH . 'wp-admin/includes/plugin.php'; // Need for plugins_api.

		return ! empty( $scope ) ? is_plugin_active( self::$base_path[ $scope ] ) : false;
	}

	/**
	 * Show MP install/activation notice
	 **/
	public static function mp_notice() {
		$post_type = CoursePress_Data_Course::get_post_type_name();
		$message = '';

		if ( ! self::installed() ) {
			$mp_settings_url = add_query_arg( array(
				'post_type' => $post_type,
				'page' => 'coursepress_settings',
				'tab' => 'extensions',
				),
				admin_url( 'edit.php' )
			);
			$message = sprintf( '<strong>%s</strong> ', __( 'Install MarketPress plugin in order to sell courses.', 'cp' ) );
			$message .= sprintf( '<a href="%s">%s</a>', $mp_settings_url, __( 'Install MarketPress', 'cp' ) );
		} elseif ( ! self::activated() ) {
			$mp_link = sprintf( '<a href="%s">%s</a>', admin_url( 'plugins.php' ), __( 'MarketPress', 'cp' ) );
			$message = sprintf( __( 'Activate %s to start selling courses.', 'cp' ), $mp_link );
		}

		if ( ! empty( $message ) ) {
			echo CoursePress_Helper_UI::admin_notice( $message, 'warning' );
		}
	}
}