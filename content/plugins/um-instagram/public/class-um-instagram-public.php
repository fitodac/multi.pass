<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://ultimatemember.com/
 * @since      1.0.0
 *
 * @package    Um_Instagram
 * @subpackage Um_Instagram/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Um_Instagram
 * @subpackage Um_Instagram/public
 * @author     Ultimate Member <support@ultimatemember.com>
 */
class Um_Instagram_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		
		

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {


		if( $this->is_enabled()  ){
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/um-instagram-public.css', array(), $this->version, 'all' );
		}

		

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {


		if( $this->is_enabled()  ){
			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/um-instagram-public.js', array( 'jquery' ), $this->version, false );
			
			$translation_array = array(
					'image_loader' => um_url.'/assets/img/loading-dots.gif',
			);

			wp_localize_script( $this->plugin_name, 'um_instagram', $translation_array );
		}

		
		
	}

	/**
	 * Customize instagram photo field in profile edit
	 * filter hook: um_edit_field_profile_instagram_photo
	 * 
	 * @since    1.0.0
	 * @param 		string $output 
	 * @param 		array $data  
	 * @return 		string
	 */
	public function edit_field_profile_instagram_photo( $output, $data ){

		global $ultimatemember, $um_connect_instagram;

		if( ! $this->is_enabled()  ){
			return;
		}

		$output .= '<div class="um-field um-field-'.$data['type'].' um-field-'.$data['type'].'" data-key="'.$data['metakey'].'">';
		$output .= $ultimatemember->fields->field_label( $data['label'], $data['metakey'], $data );


		$has_token = $um_connect_instagram->get_user_token( $data['metakey'] );
		if( ! $has_token ){
			$has_token = get_user_meta( um_user('ID'), $data['metakey'], true );
		}
		
		if( $has_token ){
			$output .= '<a href="javascript:;" class="um-ig-photos_disconnect"><i class="um-faicon-times"></i> Disconnect </a>';
			$output .= '<div class="um-clear"></div>';
			$output .= '<div id="um-ig-content" class="um-ig-content" >';
					$output .= '<div id="um-ig-photo-wrap" class="um-ig-photos"  data-metakey="'.$data['metakey'].'" data-viewing="false">';
					$output .= $this->get_user_photos( $has_token, false );
					$output .= '</div>';
					$output .= '<div class="um-ig-photo-navigation">';
						$output .= '<a href="javascript:;" class="nav-left  nav-show"><i class="um-faicon-arrow-left"></i></a>';
						$output .= '<a href="javascript:;" class="nav-right  nav-show"><i class="um-faicon-arrow-right"></i></a>';
					$output .= '</div>';
				$output .= '<div class="um-clear"></div>';
				$output .= $this->get_user_details( $has_token );
				$output .= '<div class="um-ig-paginate"><span>0/0</span></div>';
			$output .= '</div>';
			$output .= '<div id="um-ig-preload"></div>';
			$output .= '<div class="um-clear"></div>';
			$output .= '<input type="hidden" class="um-ig-photos_metakey" name="'.$data['metakey'].'" value="'.$has_token.'"/>';
			
		}else{

			$output .= '<div class="um-connect-instagram">';
			$output .= '<div class="um-ig-photo-wrap">';
			$output .= '<div class="um-clear"></div>';
			$output .= '<a href="'. $um_connect_instagram->connect_url().'"><i class="um-faicon-instagram"></i>';
			$output .= '<div class="um-clear"></div>';
			$output .= __('Connect to Instagram','um-instagram');
			$output .= '</a>';
			$output .= '</div>';
			$output .= '</div>';

		}

		$output .= '</div>';

		return $output;
	}

	/**
	 * Customize instagram photo in profile view
	 * @param  string $output 
	 * @param  array $data   
	 * @return string
	 */
	public function view_field_profile_instagram_photo( $output, $data ){
		global $um_connect_instagram;


		if( ! $this->is_enabled()  ){

			add_filter( 'um_instagram_photo_form_show_field','instagram_photo_form_show_field', 99 ,2 );
			return;
		}

		$has_token = $um_connect_instagram->get_user_token( $data['metakey'] );
		
		if( $has_token ){
			
			$output  = '<div class="um-clear"></div>';
			$output .= '<div id="um-ig-content" class="um-ig-content">';
				$output .= '<div id="um-ig-photo-wrap" class="um-ig-photos" data-metakey="'.$data['metakey'].'" data-viewing="true"></div>';
				$output .= '<div class="um-ig-photo-navigation">';
				$output .= '<a href="javascript:;" class="nav-left nav-show"><i class="um-faicon-arrow-left"></i></a>';
				$output .= '<a href="javascript:;" class="nav-right nav-show"><i class="um-faicon-arrow-right"></i></a>';
				$output .= '</div>';
				$output .= '<div class="um-clear"></div>';
				$output .= $this->get_user_details( $has_token );
				$output .= '<div class="um-ig-paginate"><span>0/0</span></div>';
			$output .= '</div>';
			$output .= '<div id="um-ig-preload"></div>';
			$output .= '<div class="um-clear"></div>';
			
		}
			
		return $output;
	}

	/**
	 * Get user instagram photos
	 * @param  string $access_token
	 * @return string
	 */
	public function get_user_photos( $access_token, $viewing = true ){

		global $ultimatemember;

		$response = wp_remote_get('https://api.instagram.com/v1/users/self/media/recent/?access_token='.$access_token.'&count=18');
		
		if( is_array( $response ) ){
			if( isset( $response['body'] ) && ! empty( $response['body'] ) ){
				$photos = json_decode( $response['body'] );
				if( isset( $photos->data  ) ){
					$output = '<ul id="um-ig-show_photos" data-viewing="'. $viewing.'" data-photos-count="'.count( $photos->data  ).' ">';
						foreach ( $photos->data as $photo ) {
							$original_resolution_url = str_replace('/s640x640/', '/', $photo->images->standard_resolution->url);
							$thumbnail_resolution_url = str_replace('/s150x150/', '/s320x320/',$photo->images->thumbnail->url);
							$output .= '<li><a href="'.$original_resolution_url.'" data-src="'.$original_resolution_url.'" class="um-photo-modal"><img class="um-lazy" data-original="'.$thumbnail_resolution_url.'" src="'.$thumbnail_resolution_url.'"/></a></li>';
						}

						
						for( $a = 1; $a <= ( 18 - count(  $photos->data ) ); $a++) {
							$output .= '<li class="um-ig-photo-placeholder"></li>';

						
								if( count(  $photos->data ) < 6 && $a == ( 6 - count(  $photos->data ) ) ){
									break;
								}

								if( count(  $photos->data ) < 12 && $a == ( 12 - count(  $photos->data ) ) ){
									break;
								}
							
						}
						

					$output .= '</ul>';
				}
			}
		}
		

		return $output;
	}

	/**
	 * Get instagram user details
	 * @param  string $access_token
	 * @return string
	 * 
	 * @since  1.0.0
	 */
	public function get_user_details( $access_token ){

		global $um_connect_instagram;

		$response = wp_remote_get('https://api.instagram.com/v1/users/self/?access_token='.$access_token);
		
		if( is_array( $response ) ){
			if( isset( $response['body'] ) && ! empty( $response['body'] ) ){
				$user = json_decode( $response['body'] );
				if( isset( $user->data  ) ){
					$output  = '<span class="um-ig-user-details">';
					$output .= '<a href="https://instagram.com/'.$user->data->username.'/">';
					$output .= '<i class="um-faicon-instagram"></i>&nbsp;';
					$output .= '<span>View all photos on Instagram</span>';
					$output .= '</a>';
					$output .= '</span>';
				}
			}
		}
		

		return $output;
	}

	/**
	 * Remove IG code from the url
	 * @param  array $to_update 
	 * @since  1.0.0
	 */
	public function user_after_updating_profile( $args ){

		if ( !isset( $args['is_signup'] ) && um_is_core_page('user') &&  $this->is_enabled() ) {
			$cancel_uri = remove_query_arg('um_ig_code',um_edit_my_profile_cancel_uri() );
			exit( wp_redirect(  $cancel_uri ) );
		}
		
	}

	/**
	 * Get Instagram photos via Ajax
	 * @since  1.0.0
	 */
	public function ajax_get_photos(){
		
		global $um_connect_instagram;
		

		if( ! $this->is_enabled()  ){
			return;
		}


		$data = $_REQUEST;

		$access_token = $um_connect_instagram->get_user_token( $data['metakey'], $data['um_user_id'] );
		$response = array();

		if( $access_token ){
			$photos = $this->get_user_photos( $access_token, $data['viewing'] );
			if( ! empty( $photos ) ){
				$response['photos'] = $photos;
				$response['has_photos'] = true;
				$response['has_error'] = false;
			}else{
				$response['photos'] = '';
				$response['has_photos'] = false;
				$response['has_error'] = true;
				$response['error_code'] = 'no_photos_found';
			}
		}else{
			$response['has_error'] = true;
			$response['photos'] = '';
			$response['error_code'] = 'no_access_token';
		}

		$response['raw_request'] = $_REQUEST;

		return wp_send_json( $response );

		wp_die();
	}

	/**
	 * Add body class
	 * @param  array $classes 
	 * @return array
	 * 
	 * @since  1.0.0
	 */
	public function body_class( $classes ){
		global $ultimatemember;


		if( ! $this->is_enabled()  ){
			return $classes;
		}

		if( um_is_core_page('user') ){
			$classes[] = 'um-profile-id-'.um_get_requested_user();
		}

		return $classes;
	}

	/**
	 * Checks Instagram extension enable
	 * @return boolean 
	 * @since  1.0.1
	 */
	public function is_enabled(){
		
		$enable_instagram_photo = um_get_option('enable_instagram_photo'); 

		if( $enable_instagram_photo ){
			return true;
		}
		
		return false;
	}

	/**
	 * Hide instagram field
	 * @param  string $output    
	 * @param  string $form_mode
	 * @return boolean
	 * @since  1.0.1
	 */
	public function instagram_photo_form_show_field( $output, $form_mode ){
			 return;
	}

}
