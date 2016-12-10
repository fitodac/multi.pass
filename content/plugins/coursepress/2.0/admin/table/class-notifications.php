<?php
/**
 * A sub-class of WP_Posts_List_Table
 *
 * @package WordPress
 * @subpackage CoursePress
 **/
if ( ! class_exists( 'WP_Posts_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-posts-list-table.php';
}

class CoursePress_Admin_Table_Notifications extends WP_Posts_List_Table {
	private $count = array();
	private $post_type;
	private $_categories;
	private $recivers_allowed_options;

	public function __construct() {
		$post_format = CoursePress_Data_Notification::get_format();
		parent::__construct( array(
			'singular' => $post_format['post_args']['labels']['singular_name'],
			'plural' => $post_format['post_args']['labels']['name'],
			'ajax' => false,
		) );

		$this->post_type = CoursePress_Data_Notification::get_post_type_name();
		$this->count = wp_count_posts( $this->post_type );
	}

	public function prepare_items() {
		global $wp_query;
		$screen = get_current_screen();
		/**
		 * Per Page
		 */
		$option = $screen->get_option( 'per_page', 'option' );
		$per_page = (int) get_user_option( $option );
		if ( empty( $per_page ) || $per_page < 1 ) {
			$per_page = $this->get_option( 'per_page', 'default' );
			if ( ! $per_page ) {
				$per_page = 20;
			}
		}
		$per_page = $this->get_items_per_page( 'coursepress_notifications_per_page', $per_page );
		/**
		 * Post statsu
		 */
		$post_status = 'any';
		$current_page = $this->get_pagenum();
		$offset = ( $current_page - 1 ) * $per_page;
		$s = isset( $_POST['s'] )? mb_strtolower( trim( $_POST['s'] ) ):false;

		$post_args = array(
			'post_type' => $this->post_type,
			'post_status' => $post_status,
			'posts_per_page' => $per_page,
			'offset' => $offset,
			's' => $s,
		);

		$course_id = isset( $_GET['course_id'] ) ? sanitize_text_field( $_GET['course_id'] ) : '';

		if ( ! empty( $course_id ) && 'all' !== $course_id ) {
			$post_args['meta_query'] = array(
				array(
					'key' => 'course_id',
					'value' => (int) $course_id,
				),
			);
		} else {
			// Only show notifications where the current user have access with.
			$courses = CoursePress_Data_Notification::get_courses();
			$courses_ids = array_map( array( __CLASS__, 'get_course_id' ), $courses );
			// Include notification for all courses
			$courses_ids[] = 'all';
			$post_args['meta_query'] = array(
				array(
					'key' => 'course_id',
					'value' => (array) $courses_ids,
					'compare' => 'IN',
				),
			);
		}

		// @todo: Add permissions
		$wp_query = new WP_Query( $post_args );
		$this->items = array();
		foreach ( $wp_query->posts as $post ) {
			$post->user_can_edit = CoursePress_Data_Capabilities::can_update_notification( $post->ID );
			$post->user_can_delete  = CoursePress_Data_Capabilities::can_delete_notification( $post->ID );
			$post->user_can_change_status = CoursePress_Data_Capabilities::can_change_status_notification( $post->ID );
			$post->user_can_change = $post->user_can_edit || $post->user_can_delete || $post->user_can_change_status;
			$this->items[] = $post;
		}
		$total_items = $wp_query->found_posts;

		$this->set_pagination_args(
			array(
			'total_items' => $total_items,
			'per_page'	=> $per_page,
			'total_pages' => ceil( $total_items / $per_page ),
			)
		);
	}

	protected function can_update( $item_id ) {
		return CoursePress_Data_Capabilities::can_update_notification( $item_id );
	}

	protected function can_delete( $item_id ) {
		return CoursePress_Data_Capabilities::can_delete_notification( $item_id );
	}

	protected function can_change_status( $item_id ) {
		return CoursePress_Data_Capabilities::can_change_status_notification( $item_id );
	}

	/** No items */
	public function no_items() {
		echo __( 'No notifications found.', 'cp' );
	}

	public function column_cb( $item ) {
		if ( $item->user_can_edit ) {
			return sprintf(
				'<input type="checkbox" name="bulk-actions[]" value="%s" />', $item->ID
			);
		}
		return '';
	}

	public function get_columns() {
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'notification' => __( 'Notification', 'cp' ),
			'course' => __( 'Course', 'cp' ),
			'receivers' => __( 'Receivers', 'cp' ),
			'status' => __( 'Status', 'cp' ),
		);

		return $columns;
	}

	protected function handle_row_actions( $item, $column_name, $primary ) {
		if ( 'notification' !== $column_name ) {
			return '';
		}

		$actions = array();

		/**
		 * check current_user_can update?
		 */
		if ( $this->can_update( $item ) ) {
			$edit_url = add_query_arg(
				array(
					'action' => 'edit',
					'id' => $item->ID,
				)
			);
			$actions['edit'] = sprintf( '<a href="%s">%s</a>', esc_url( $edit_url ), __( 'Edit', 'cp' ) );
		}

		if ( $this->can_delete( $item ) ) {
			$delete_url = add_query_arg(
				array(
					'action' => 'delete2',
					'id' => $item->ID,
				)
			);
			$actions['delete'] = sprintf( '<a href="%s">%s</a>', esc_url( $delete_url ), __( 'Delete', 'cp' ) );
		}

		return $this->row_actions( $actions );
	}

	public function column_notification( $item ) {
		$title = '<strong>' . apply_filters( 'the_title', $item->post_title ) . '</strong>';
		return $title;
	}

	/**
	 * Coulmn Notifications Receivers
	 *
	 * @since 2.0.0
	 */
	public function column_receivers( $item ) {
		$receivers = get_post_meta( $item->ID, 'receivers', true );
		if ( empty( $receivers ) ) {
			$receivers = 'all';
		}
		$attributes = CoursePress_Data_Notification::attributes( $item->ID );
		$course_id = $attributes['course_id'];
		if ( 'all' == $course_id ) {
			return sprintf(
				'<span aria-hidden="true">&#8212;</span><span class="screen-reader-text">%s</span>',
				__( 'Option not available for all courses.', 'cp' )
			);
		}
		$recivers_allowed_options = array();
		if ( isset( $this->recivers_allowed_options[ $course_id ] ) ) {
			$recivers_allowed_options = $this->recivers_allowed_options[ $course_id ];
		} else {
			$recivers_allowed_options = CoursePress_Admin_Notifications::get_allowed_options( $course_id );
			$this->recivers_allowed_options[ $course_id ] = $recivers_allowed_options;
		}
		if ( isset( $recivers_allowed_options[ $receivers ] ) ) {
			return $recivers_allowed_options[ $receivers ]['label'];
		}
		return __( 'Wrong receivers!', 'cp' );
	}

	protected function get_bulk_actions() {
		$actions = array(
			'publish' => __( 'Visible', 'cp' ),
			'unpublish' => __( 'Private', 'cp' ),
			'delete' => __( 'Delete', 'cp' ),
		);
		return $actions;
	}

	public function column_course( $item ) {
		$attributes = CoursePress_Data_Notification::attributes( $item->ID );
		$output = sprintf( '<div data-course="%s">%s</div>',
			$attributes['course_id'],
			$attributes['course_title']
		);

		return $output;
	}

	public function column_status( $item ) {
		/**
		 * check permissions
		 */
		if ( ! $item->user_can_change_status ) {
			return ucfirst( $item->post_status );
		}
		// Publish Course Toggle
		$d_id = $item->ID;
		$status = get_post_status( $d_id );
		$ui = array(
			'label' => '',
			'left' => '<i class="fa fa-key"></i>',
			'left_class' => '',
			'right' => '<i class="fa fa-globe"></i>',
			'right_class' => '',
			'state' => 'publish' === $status ? 'on' : 'off',
			'data' => array(
				'nonce' => wp_create_nonce( 'publish-notification' ),
			),
		);
		$ui['class'] = 'notification-' . $d_id;
		$publish_toggle = ! empty( $d_id ) ? CoursePress_Helper_UI::toggle_switch( 'publish-notification-toggle-' . $d_id, 'publish-notification-toggle-' . $d_id, $ui ) : '';

		return $publish_toggle;
	}

	public static function get_course_id( $course ) {
		return is_object( $course ) ? $course->ID : null;
	}

	protected function course_filter( $which = '' ) {
		if ( 'top' !== $which ) {
			return;
		}

		$course_id = isset( $_GET['course_id'] ) ? sanitize_text_field( $_GET['course_id'] ) : '';

		$options = array();
		$options['value'] = $course_id;
		$options['class'] = 'medium dropdown';
		$options['first_option'] = array(
			'text' => __( 'All courses', 'cp' ),
			'value' => 'all',
		);

		$courses =  CoursePress_Data_Notification::get_courses();
		if ( current_user_can( 'manage_options' ) ) {
			$courses = false;
		} elseif ( CoursePress_Data_Capabilities::can_add_notification_to_all() ) {
			$courses = false;
		}

		echo CoursePress_Helper_UI::get_course_dropdown( 'course_id', 'course_id', $courses, $options );
	}

	protected function pagination( $which ) {
		// Show pagination only at the bottom
		if ( 'top' !== $which ) {
			parent::pagination( $which );
		}
	}

	public function extra_tablenav( $which ) {
		if ( 'top' !== $which ) {
			return;
		}

		?>
		<div class="alignleft actions category-filter">
			<?php $this->course_filter( $which ); ?>
			<input type="submit" class="button" name="action" value="<?php esc_attr_e( 'Filter', 'cp' ); ?>" />
		</div>
		<?php
		$this->search_box( __( 'Search Notifications', 'cp' ), 'search_notifications' );
	}
}