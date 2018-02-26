<?php
/**
 * Admin side handling
 *
 * @package wpmu-dev-seo
 */

/**
 * Admin handling root class
 */
class Smartcrawl_Admin extends Smartcrawl_Renderable {

	/**
	 * Admin page handlers
	 *
	 * @var array
	 */
	private $_handlers = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Initializing method
	 */
	private function init() {
		// Set up dash.
		if ( file_exists( SMARTCRAWL_PLUGIN_DIR . 'external/dash/wpmudev-dash-notification.php' ) ) {
			global $wpmudev_notices;
			if ( ! is_array( $wpmudev_notices ) ) { $wpmudev_notices = array(); }
			$wpmudev_notices[] = array(
				'id'      => 167,
				'name'    => 'SmartCrawl',
				'screens' => array(
					'toplevel_page_wds_wizard-network',
					'toplevel_page_wds_wizard',
					'smartcrawl_page_wds_onpage-network',
					'smartcrawl_page_wds_onpage',
					'smartcrawl_page_wds_sitemap-network',
					'smartcrawl_page_wds_sitemap',
					'smartcrawl_page_wds_settings-network',
					'smartcrawl_page_wds_settings',
					'smartcrawl_page_wds_autolinks-network',
					'smartcrawl_page_wds_autolinks',
					'smartcrawl_page_wds_social-network',
					'smartcrawl_page_wds_social',
				),
			);
			require_once( SMARTCRAWL_PLUGIN_DIR . 'external/dash/wpmudev-dash-notification.php' );
		}

		add_action( 'admin_init', array( $this, 'register_setting' ) );
		add_action( 'admin_init', array( $this, 'admin_master_reset' ) );
		add_filter( 'whitelist_options', array( $this, 'save_options' ), 20 );

		add_action( 'wp_ajax_wds_dismiss_message', array( $this, 'smartcrawl_dismiss_message' ) );
		add_action( 'wp_ajax_wds-user-search', array( $this, 'json_user_search' ) );
		add_action( 'wp_ajax_wds-user-search-add-user', array( $this, 'json_user_search_add_user' ) );

		if ( Smartcrawl_Settings::get_setting( 'extras-admin_bar' ) ) {
			add_action( 'admin_bar_menu', array( $this, 'add_toolbar_items' ), 99 );
		}

		add_filter( 'plugin_action_links_' . SMARTCRAWL_PLUGIN_BASENAME, array( $this, 'add_settings_link' ) );

		require_once( SMARTCRAWL_PLUGIN_DIR . 'admin/settings.php' );
		require_once SMARTCRAWL_PLUGIN_DIR . 'core/class_wds_service.php';

		$smartcrawl_options = Smartcrawl_Settings::get_options();

		// Sanity check first!
		if ( ! get_option( 'blog_public' ) ) {
			add_action( 'admin_notices', array( $this, 'blog_not_public_notice' ) );
		}

		if ( ! empty( $smartcrawl_options['access-id'] ) && ! empty( $smartcrawl_options['secret-key'] ) ) {
			require_once( SMARTCRAWL_PLUGIN_DIR . 'tools/seomoz/api.php' );
			require_once( SMARTCRAWL_PLUGIN_DIR . 'tools/seomoz/results.php' );
			require_once( SMARTCRAWL_PLUGIN_DIR . 'tools/seomoz/dashboard-widget.php' );
		}

		require_once( SMARTCRAWL_PLUGIN_DIR . 'admin/settings/dashboard.php' );
		$this->_handlers['dashboard'] = Smartcrawl_Settings_Dashboard::get_instance();

		if ( Smartcrawl_Settings::get_setting( 'checkup' ) ) {
			require_once( SMARTCRAWL_PLUGIN_DIR . 'admin/settings/checkup.php' );
			$this->_handlers['checkup'] = Smartcrawl_Checkup_Settings::get_instance();
		}

		if ( Smartcrawl_Settings::get_setting( 'onpage' ) ) {
			require_once( SMARTCRAWL_PLUGIN_DIR . 'admin/settings/onpage.php' );
			$this->_handlers['onpage'] = Smartcrawl_Onpage_Settings::get_instance();
		}

		if ( Smartcrawl_Settings::get_setting( 'social' ) ) {
			require_once( SMARTCRAWL_PLUGIN_DIR . 'admin/settings/social.php' );
			$this->_handlers['social'] = Smartcrawl_Social_Settings::get_instance();
		}

		require_once( SMARTCRAWL_PLUGIN_DIR . 'tools/sitemaps.php' );
		require_once( SMARTCRAWL_PLUGIN_DIR . 'admin/settings/sitemap.php' );
		$this->_handlers['sitemap'] = Smartcrawl_Sitemap_Settings::get_instance();
		if ( Smartcrawl_Settings::get_setting( 'sitemap' ) ) {
			require_once( SMARTCRAWL_PLUGIN_DIR . 'tools/sitemaps-dashboard-widget.php' );
		}

		require_once( SMARTCRAWL_PLUGIN_DIR . 'admin/settings/autolinks.php' );
		$this->_handlers['autolinks'] = Smartcrawl_Autolinks_Settings::get_instance();

		require_once( SMARTCRAWL_PLUGIN_DIR . 'admin/settings/settings.php' );
		$this->_handlers['settings'] = Smartcrawl_Settings_Settings::get_instance();

		if (
			! class_exists( 'Smartcrawl_Controller_Onboard' ) &&
			file_exists( SMARTCRAWL_PLUGIN_DIR . '/core/class_wds_controller_onboard.php' )
		) {
			require_once( SMARTCRAWL_PLUGIN_DIR . '/core/class_wds_controller_onboard.php' );
			Smartcrawl_Controller_Onboard::serve();
		}

		if (
			! class_exists( 'Smartcrawl_Controller_Analysis' ) &&
			file_exists( SMARTCRAWL_PLUGIN_DIR . '/core/class_wds_controller_analysis.php' )
		) {
			require_once( SMARTCRAWL_PLUGIN_DIR . '/core/class_wds_controller_analysis.php' );
			Smartcrawl_Controller_Analysis::serve();
		}

		if ( Smartcrawl_Settings::get_setting( 'onpage' ) ) {
			require_once( SMARTCRAWL_PLUGIN_DIR . 'admin/metabox.php' );
			require_once( SMARTCRAWL_PLUGIN_DIR . 'admin/taxonomy.php' );
		}
	}

	/**
	 * Adds settings plugin action link
	 *
	 * @param array $links Action links list.
	 *
	 * @return array Augmented action links
	 */
	public function add_settings_link( $links ) {
		if ( ! is_array( $links ) ) { return $links; }

		$links[] = sprintf(
			'<a href="%s">%s</a>',
			esc_url( add_query_arg( 'page', Smartcrawl_Settings::TAB_DASHBOARD, admin_url( 'admin.php' ) ) ),
			esc_html( __( 'Settings', 'wds' ) )
		);

		return $links;
	}

	/**
	 * Saves the submitted options
	 *
	 * @param mixed $whitelist_options Options.
	 *
	 * @return array
	 */
	public function save_options( $whitelist_options ) {
		global $action;

		$smartcrawl_pages = array(
			'wds_settings_options',
			'wds_autolinks_options',
			'wds_onpage_options',
			'wds_sitemap_options',
			'wds_seomoz_options',
			'wds_social_options',
			'wds_redirections_options',
			'wds_checkup_options',
		);
		if ( is_multisite() && SMARTCRAWL_SITEWIDE == true && 'update' == $action && isset( $_POST['option_page'] ) && in_array( $_POST['option_page'], $smartcrawl_pages ) ) {
			global $option_page;

			check_admin_referer( $option_page . '-options' );

			if ( ! isset( $whitelist_options[ $option_page ] ) ) {
				wp_die( __( 'Error: options page not found.' , 'wds' ) );
			}

			$options = $whitelist_options[ $option_page ];

			if ( $options && is_array( $options ) ) {
				foreach ( $options as $option ) {
					$option = trim( $option );
					$value = null;
					if ( isset( $_POST[ $option ] ) ) {
						$value = $_POST[ $option ];
					}
					if ( ! is_array( $value ) ) {
						$value = trim( $value );
					}
					$value = stripslashes_deep( $value );

					// Sanitized/validated via sanitize_option_<option_page>.
					// See each of the admin classes validate method.
					update_site_option( $option, $value );
				}
			}

			$errors = get_settings_errors();
			set_transient( 'wds-settings-save-errors' , $errors, 30 );

			$goback = add_query_arg( 'updated', 'true', wp_get_referer() );
			wp_safe_redirect( $goback );
			die;
		}

		return $whitelist_options;
	}

	/**
	 * Admin page handler getter
	 *
	 * @param string $hndl Handler to get.
	 *
	 * @return object Handler
	 */
	public function get_handler( $hndl ) {
		return isset( $this->_handlers[ $hndl ] )
			? $this->_handlers[ $hndl ]
			: $this
		;
	}

	/**
	 * Admin reset options switch processing
	 *
	 * @return bool|void
	 */
	public function admin_master_reset() {
		if ( is_multisite() && ! current_user_can( 'manage_network_options' ) ) { return false; }
		if ( ! is_multisite() && ! current_user_can( 'manage_options' ) ) { return false; }

		if ( isset( $_GET['wds-reset'] ) ) { // Simple presence switch, no value needed.
			require_once( SMARTCRAWL_PLUGIN_DIR . '/core/class_wds_reset.php' );
			Smartcrawl_Reset::reset();
			wp_safe_redirect( add_query_arg( 'wds-reset-reload', 'true', remove_query_arg( 'wds-reset' ) ) );
			die;
		}

		if ( isset( $_GET['wds-reset-reload'] ) ) { // Simple presence switch, no value needed.
			wp_safe_redirect( remove_query_arg( 'wds-reset-reload' ) );
			die;
		}

		return false;
	}

	/**
	 * Brute-register all the settings.
	 *
	 * If we got this far, this is a sane thing to do.
	 * This overrides the `Smartcrawl_Core_Admin::register_setting()`.
	 *
	 * In response to "Unable to save options multiple times" bug.
	 */
	public function register_setting() {
		register_setting( 'wds_settings_options', 'wds_settings_options', array( $this->get_handler( 'settings' ), 'validate' ) );
		register_setting( 'wds_sitemap_options', 'wds_sitemap_options', array( $this->get_handler( 'sitemap' ), 'validate' ) );
		register_setting( 'wds_onpage_options', 'wds_onpage_options', array( $this->get_handler( 'onpage' ), 'validate' ) );
		register_setting( 'wds_social_options', 'wds_social_options', array( $this->get_handler( 'social' ), 'validate' ) );
		register_setting( 'wds_autolinks_options', 'wds_autolinks_options', array( $this->get_handler( 'autolinks' ), 'validate' ) );
		register_setting( 'wds_redirections_options', 'wds_redirections_options', array( $this->get_handler( 'redirections' ), 'validate' ) );
		register_setting( 'wds_checkup_options', 'wds_checkup_options', array( $this->get_handler( 'checkup' ), 'validate' ) );
	}

	/**
	 * Adds admin toolbar items
	 *
	 * @param object $bar Admin toolbar object.
	 */
	public function add_toolbar_items( $bar ) {
		if ( empty( $bar ) || ! function_exists( 'is_admin_bar_showing' ) ) { return false; }
		if ( ! is_admin_bar_showing() ) { return false; }

		if ( ! apply_filters( 'wds-admin-ui-show_bar', true ) ) { return false; }

		// Do not show if sitewide and we're not super admin.
		if ( defined( 'SMARTCRAWL_SITEWIDE' ) && SMARTCRAWL_SITEWIDE && ! is_super_admin() ) { return false; }

		$root = array(
			'id' => 'wds-root',
			'title' => __( 'SmartCrawl', 'wds' ),
		);
		$bar->add_node( $root );
		foreach ( $this->_handlers as $handler ) {
			if ( empty( $handler ) || empty( $handler->slug ) ) { continue; }

			if ( ! (defined( 'SMARTCRAWL_SITEWIDE' ) && SMARTCRAWL_SITEWIDE) && ! is_super_admin() ) {
				if ( ! Smartcrawl_Settings_Admin::is_tab_allowed( $handler->slug ) ) { continue; }
			}

			$href = (
				defined( 'SMARTCRAWL_SITEWIDE' ) && SMARTCRAWL_SITEWIDE ? network_admin_url( 'admin.php' ) : admin_url( 'admin.php' )
			) . '?page=' . $handler->slug;
			$bar->add_node(array(
				'id' => $root['id'] . '.' . $handler->slug,
				'parent' => $root['id'],
				'title' => $handler->title,
				'href' => $href,
			));
		}
	}

	/**
	 * Validate user data for some/all of your input fields
	 *
	 * @param mixed $input Raw input.
	 */
	public function validate( $input ) {
		return $input; // return validated input.
	}

	/**
	 * Shows blog not being public notice.
	 */
	public function blog_not_public_notice() {
		if ( ! current_user_can( 'manage_options' ) ) { return false; }

		echo '<div class="notice-error notice is-dismissible"><p>' .
			sprintf( __( 'This site discourages search engines from indexing the pages, which will affect your SEO efforts. <a href="%s">You can fix this here</a>', 'wds' ), admin_url( '/options-reading.php' ) ) .
		'</p></div>';

	}

	/**
	 * Process message dismissal request
	 */
	public function smartcrawl_dismiss_message() {
		$message = sanitize_key( smartcrawl_get_array_value( $_POST, 'message' ) );
		if ( null === $message ) {
			wp_send_json_error();
			return;
		}

		$dismissed_messages = get_user_meta( get_current_user_id(), 'wds_dismissed_messages', true );
		$dismissed_messages = '' === $dismissed_messages ? array() : $dismissed_messages;
		$dismissed_messages[ $message ] = true;
		update_user_meta( get_current_user_id(), 'wds_dismissed_messages', $dismissed_messages );
	}

	/**
	 * Process user search requests
	 */
	public function json_user_search() {
		$result = array( 'success' => false );
		if ( ! current_user_can( 'edit_users' ) ) {
			wp_send_json( $result );
			die;
		}

		$params = stripslashes_deep( $_GET );
		$query = sanitize_text_field( smartcrawl_get_array_value( $params, 'query' ) );

		if ( ! $query ) {
			wp_send_json( $result );
			die();
		}

		$users = get_users(array(
			'search' => '*' . $params['query'] . '*',
			'fields' => 'all_with_meta',
		));

		$return_users = array();
		foreach ( $users as $user ) {
			$return_users[] = array(
				'id'   => $user->get( 'ID' ),
				'text' => $user->get( 'display_name' ),
			);
		}
		$result['items'] = $return_users;

		wp_send_json( $result );
	}

	/**
	 * Handles user search requests
	 */
	public function json_user_search_add_user() {
		$result = array( 'success' => false );
		if ( ! current_user_can( 'edit_users' ) ) {
			wp_send_json( $result );
			die;
		}

		$params = stripslashes_deep( $_POST );

		$option_name = sanitize_key( smartcrawl_get_array_value( $params, 'option_name' ) );
		$users_key = sanitize_key( smartcrawl_get_array_value( $params, 'users_key' ) );
		$new_user_key = sanitize_key( smartcrawl_get_array_value( $params, 'new_user_key' ) );

		$user_search_options = smartcrawl_get_array_value( $params, $option_name );
		$email_recipients = smartcrawl_get_array_value( $user_search_options, $users_key );
		$new_user = sanitize_text_field( smartcrawl_get_array_value( $user_search_options, $new_user_key ) );

		if ( null === $new_user ) {
			wp_send_json( $result );
			return;
		}

		if ( ! is_array( $email_recipients ) ) {
			$email_recipients = array();
		} else {
			$email_recipients = array_filter( array_map( 'sanitize_text_field', $email_recipients ) );
		}

		if ( ! in_array( $new_user, $email_recipients ) ) {
			$email_recipients[] = $new_user;
		}

		$new_markup = $this->_load('user-search', array(
			'users'        => $email_recipients,
			'option_name'  => $option_name,
			'users_key'    => $users_key,
			'new_user_key' => $new_user_key,
		));

		$result['user_search'] = $new_markup;
		$result['success'] = true;

		wp_send_json( $result );
	}

	/**
	 * Gets inherited view defaults
	 */
	protected function _get_view_defaults() {
		return array();
	}
}

$Smartcrawl_Admin = new Smartcrawl_Admin();
