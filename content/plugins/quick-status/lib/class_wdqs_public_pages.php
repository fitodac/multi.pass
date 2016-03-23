<?php
/**
 * Handles all Admin access functionality.
 */
class Wdqs_PublicPages {

	var $data;
	var $_link_type = 'link';

	function Wdqs_PublicPages () { $this->__construct(); }

	function __construct () {
		$this->data = new Wdqs_Options;
	}

	/**
	 * Main entry point.
	 *
	 * @static
	 */
	function serve () {
		$me = new Wdqs_PublicPages;
		if ('widget' == $me->data->get('placement')) {
			require_once (WDQS_PLUGIN_BASE_DIR . '/lib/class_wdqs_widget_posting.php');
			add_action('widgets_init', create_function('', "register_widget('Wdqs_WidgetPosting');"));
		}
		$me->add_hooks();
	}


	function js_load_scripts () {
		if (!$this->_check_permissions()) return false;
		wp_enqueue_script('jquery');
		wp_enqueue_script( 'media-upload' );
		add_thickbox();

		wp_enqueue_script('wdqs_widget', WDQS_PLUGIN_URL . '/js/widget.js');
		wp_localize_script('wdqs_widget', 'l10nWdqs', array(
			'no_thumbnail' => __('No thumbnail', 'wdqs'),
			'of' => __('of', 'wdqs'),
			'images_found' => __('images found', 'wdqs'),
			'use_default_title' => __('Use default title', 'wdqs'),
			'use_this_title' => __('Use this title', 'wdqs'),
			'post_title' => __('Post title', 'wdqs'),
			'height' => __('Height', 'wdqs'),
			'width' => __('Width', 'wdqs'),
			'leave_empty_for_defaults' => __('Leave these boxes empty for defaults', 'wdqs'),
		));

		$options = apply_filters('wdqs-core-javascript_options', array(
			"ajax_url" => admin_url('admin-ajax.php'),
			"admin_url" => admin_url(),
		));
		printf('<script type="text/javascript">var _wdqs=%s;</script>', json_encode($options));
	}

	function css_load_styles () {
		if (!current_theme_supports('wdqs')) {
			wp_enqueue_style('wdqs', WDQS_PLUGIN_URL . '/css/wdqs.css');
		}
		if (!$this->_check_permissions()) return false;
		wp_enqueue_style('thickbox');
		wp_enqueue_style('wdqs_widget', WDQS_PLUGIN_URL . '/css/widget.css');
		wp_enqueue_style('wdqs_widget-front', WDQS_PLUGIN_URL . '/css/widget-front.css');
	}

	function status_widget () {
		if (!$this->_check_permissions()) return false;
		if (defined('WDQS_BOX_CREATED')) return false; // Already added
		$status = false;
		echo "<div>";
		include(WDQS_PLUGIN_BASE_DIR . '/lib/forms/dashboard_widget.php');
		echo "</div>";
		define ('WDQS_BOX_CREATED', true);
	}

	private function _check_permissions () {
		global $current_user;
		$level = WDQS_PUBLISH_CAPABILITY;//$this->data->get("contributors") ? "edit_posts" : "publish_posts";
		if (!current_user_can($level)) return false;
		if (!$current_user->ID) return false;

		$placement = $this->data->get('placement');
		$placement = $placement ? $placement : 'front_page';

		if ('front_page' == $placement && !is_front_page()) return false;

		return true;
	}

	function html5_video_support_javascript ($options) {
		$html5_video = $this->data->get('html5_video');

		$html5_video_types = explode(
			',',
			(@$html5_video['video_types'] ? $html5_video['video_types'] : 'webm, mp4, ogg, ogv')
		);
		$html5_video_types = is_array($html5_video_types)
			? array_map('trim', $html5_video_types)
			: array()
		;

		$options['html5_video'] = array(
			"allowed" => (int)@$html5_video['use_html5_video'],
			"video_unavailable" => @$html5_video['unavailable'] ? $html5_video['unavailable'] : __('Not supported', 'wdqs'),
			"video_types" => $html5_video_types,
		);
		return $options;
	}

	function oembed_providers_list ($options) {
		if (!class_exists('WP_oEmbed')) require_once(ABSPATH . '/wp-includes/class-oembed.php');
		$wp_oembed = new WP_oEmbed();
		$provider_rx = array();
		foreach(array_keys($wp_oembed->providers) as $rx) $provider_rx[] = preg_replace('/#i?/', '', $rx);
		$options['oembed']['providers'] = $provider_rx;
		return $options;
	}


	function add_hooks () {
		// Step0: Register options and menu
		if (!$this->data->get('show_on_public_pages')) return false;
		add_action('wp_print_scripts', array($this, 'js_load_scripts'));
		add_action('wp_print_styles', array($this, 'css_load_styles'));

		$placement = $this->data->get('placement');
		$placement = $placement ? $placement : 'front_page';

		if (!in_array($placement, array('manual', 'widget'))) {
			$hook = $this->data->get('use_hook');
			$hook = $hook ? $hook : 'loop_start';
			add_action($hook, array($this, 'status_widget'), 100);
		}

		// Internal
		add_filter('wdqs-core-javascript_options', array($this, 'html5_video_support_javascript'), 9);
		add_filter('wdqs-core-javascript_options', array($this, 'oembed_providers_list'), 9);
	}
}

/**
 * Manual placement function.
 * This can be used in theme files, e.g. like this:
 *
 * <code>
 *	if (function_exists('wdqs_quick_status')) wdqs_quick_status();
 * </code>
 */
function wdqs_quick_status () {
	$status = new Wdqs_PublicPages;
	$placement = $status->data->get('placement');
	if ('manual' == $placement) $status->status_widget();
}