<?php

class removeemailverification {

    var $build = 1;
    var $user_blog_url = 'none';

    function __construct() {


        add_action('init', array(&$this, 'output_buffer'), 0);

        // Remove existing filters - we need to do this in the whitelist_options filter because there isn't another action between
        // the built in MU one and the saving, besides which we need to add our new_admin_email field to the list anyway.
        add_filter('whitelist_options', array(&$this, 'remove_mu_option_hooks'));

        //filters for overriding other functions
        add_filter('wpmu_signup_blog_notification', '__return_false');

        add_filter('wpmu_signup_user_notification', '__return_false');

        if( apply_filters( 'removeev_disable_welcome_email', false ) ){ // Remove MS welcome email disabled by default.
            add_filter( 'wpmu_welcome_notification', '__return_false' );
            add_filter( 'wpmu_welcome_user_notification', '__return_false' );
            remove_filter( 'site_option_welcome_user_email', 'welcome_user_msg_filter' );
        }

        // Blog signup - autoactivate
        add_filter('wpmu_signup_blog_notification', array(&$this, 'activate_on_blog_signup'), 10, 7);

        // Use the brand new BP disable notification filter
        add_filter('bp_core_signup_send_activation_key', '__return_false');

        // Lets assume we successfully activated the user account
        add_filter('bp_registration_needs_activation',  '__return_false');

        // User signup - autoactivate
        add_filter('wpmu_signup_user_notification', array(&$this, 'activate_on_user_signup'), 10, 4);


        add_filter('wpmu_signup_user_notification', '__return_false');

        // Change internal confirmation message - user-new.php
        add_filter('gettext', array(&$this, 'activated_newuser_msg'), 10, 3);

        //Remove BP activation emails.
        add_filter('wp_mail', array(&$this, 'remove_bp_activation_emails'));

        add_action('user_register', array(&$this, 'user_auto_login_after_signup'));

        add_action('wpmu_new_user', array(&$this, 'user_auto_login_after_signup'));

        add_action('bp_init', array(&$this, 'bp_suppress_init'));
    }

    function removeemailverification() {
        $this->__construct();
    }

    function bp_suppress_init() {
        if (!is_multisite()) {
            add_action('bp_core_signup_user', array(&$this, 'disable_validation'));
            add_filter('bp_registration_needs_activation', array(&$this, 'fix_signup_form_validation_text'));
            add_filter('bp_core_signup_send_activation_key', array(&$this, 'disable_activation_email'));
        } else {
            add_filter('wpmu_signup_user_notification', array(&$this, 'activate_on_user_signup'), 10, 4);
        }
    }

    function fix_signup_form_validation_text() {
        return false;
    }

    function disable_activation_email() {
        return false;
    }

    function disable_validation($user_id) {
        global $wpdb;

        $wpdb->query($wpdb->prepare("UPDATE $wpdb->users SET user_status = 0 WHERE ID = %d", $user_id));

        //Add note on Activity Stream
        if (function_exists('bp_activity_add')) {
            $userlink = bp_core_get_userlink($user_id);

            bp_activity_add(array(
                'user_id' => $user_id,
                'action' => apply_filters('bp_core_activity_registered_member', sprintf(__('%s became a registered member', 'buddypress'), $userlink), $user_id),
                'component' => 'profile',
                'type' => 'new_member'
            ));
        }

        //Send email to admin
        //wp_new_user_notification($user_id);
        // Remove the activation key meta
        delete_user_meta($user_id, 'activation_key');

        // Delete the total member cache
        wp_cache_delete('bp_total_member_count', 'bp');

        //Automatically log the user in	.
        $user_info = get_userdata($user_id);
        wp_set_auth_cookie($user_id);

        do_action('wp_signon', $user_info->user_login);
    }

    function remove_bp_activation() {
        return false;
    }

    function remove_bp_activation_emails($data) {
        if (strstr($data['message'], __('To activate your user, please click the following link', 'removeev')) || strstr($data['message'], __('To activate your blog, please click the following link', 'removeev')) || preg_match('/Thanks for registering/', $data['message'])) {
            unset($data);
            $data['message'] = '';
            $data['to'] = '';
            $data['subject'] = '';
        }
        return $data;
    }

    function remove_mu_option_hooks($whitelist_options) {
        global $wp_filter;

        if (has_action('update_option_new_admin_email', 'update_option_new_admin_email')) {
            remove_action('update_option_new_admin_email', 'update_option_new_admin_email', 10, 2);
            // Add our own replacement action
            add_action('pre_update_option_new_admin_email', array(&$this, 'custom_update_option_new_admin_email'), 10, 2);
        }

        $whitelist_options['general'][] = 'new_admin_email';

        return $whitelist_options;
    }

    function custom_update_option_new_admin_email($new_value, $old_value) {
        global $current_site;

        // Update the correct fields
        update_option('admin_email', $new_value);
        // Return the old value so that the new_admin_email option isn't set
        return $old_value;
    }

    function activate_on_blog_signup($domain, $path, $title, $user, $user_email, $key, $meta) {

        global $current_site;

        // Rather than recreate the wheel, just activate the blog immediately
        $result = wpmu_activate_signup($key);

        if (is_wp_error($result)) {
            if ('already_active' == $result->get_error_code() || 'blog_taken' == $result->get_error_code()) {
                $signup = $result->get_error_data();
                ?>
                <h2><?php _e('Congratulations! Your new blog is ready!', 'removeev'); ?></h2>

                <?php
                if ($signup->domain . $signup->path != '') {
                    printf(__('<p class="lead-in">Your blog at <a href="%1$s">%2$s</a> is active. You may now login to your blog using your chosen username of "%3$s".  Please check your email inbox at %4$s for your password and login instructions.  If you do not receive an email, please check your junk or spam folder.  If you still do not receive an email within an hour, you can <a href="%5$s">reset your password</a>.</p>', 'removeev'), 'http://' . $signup->domain, $signup->domain, $signup->user_login, $signup->user_email, 'http://' . $current_site->domain . $current_site->path . 'wp-login.php?action=lostpassword');	     	 	   					 
                }
            } else {
                ?>
                <h2><?php _e('An error occurred during the signup', 'removeev'); ?></h2>
                <?php
                echo '<p>' . $result->get_error_message() . '</p>';
            }
        } else {
            extract($result);

            $url = get_blogaddress_by_id($result['blog_id']);
            $newuser = new WP_User($result['user_id']);
            ?>
            <h2><?php _e('Congratulations! Your new blog is ready!', 'removeev'); ?></h2>

            <div id="signup-welcome">
                <p><span class="h3"><?php _e('Username:', 'removeev'); ?></span> <?php echo $newuser->user_login ?></p>
                <p><span class="h3"><?php _e('Password:', 'removeev'); ?></span> <?php echo $password; ?></p>
            </div>

            <?php if (!empty($url)) : ?>
                <p class="view"><?php printf(__('You\'re all set up and ready to go. <a href="%s">View your site</a> or go to the <a href="%s">admin area</a>.', 'removeev'), $url, trailingslashit($url) . 'wp-admin'); ?></p>
            <?php else: ?>
                <p class="view"><?php printf(__('You\'re all set up and ready to go. Why not go back to the <a href="%2$s">homepage</a>.', 'removeev'), 'http://' . $current_site->domain . $current_site->path); ?></p>

            <?php
            endif;

            // automatically login the user so they can see the admin area on the next page load
            $userbylogin = get_user_by('login', $user);
            if (!empty($userbylogin)) {
                @wp_set_auth_cookie($userbylogin->ID);
                wp_set_current_user($userbylogin->ID);
            }
        }

        // Now we need to hijack the sign up message so it isn't displayed
        ob_start();
        // End activation message display
        add_action('signup_finished', array(&$this, 'activated_signup_finished'), 1);

        return false; // Returns false so that the activation email isn't sent out to the user
    }

    function activate_on_user_signup($user, $user_email, $key, $meta) {

        global $current_site, $current_blog;

        // Output buffer in case we need to email instead of output
        $html = '';

        // Rather than recreate the wheel, just activate the user immediately
        if (function_exists('bp_core_activate_signup'))
            $result = bp_core_activate_signup($key);
        else
            $result = wpmu_activate_signup($key);

        if (is_wp_error($result)) {
            if ('already_active' == $result->get_error_code() || 'blog_taken' == $result->get_error_code()) {
                $signup = $result->get_error_data();
                $html .= '<h2>' . __('Hello, your account has been created!', 'removeev') . "</h2>\n";
                if ($signup->domain . $signup->path == '') {
                    $html .= sprintf(__('<p class="lead-in">Your account has been activated. Please check your email inbox at %3$s for your password and login instructions. If you do not receive an email, please check your junk or spam folder. If you still do not receive an email within an hour, you can <a href="%4$s">reset your password</a>.</p>', 'removeev'), $signup->user_login, $signup->user_email, 'http://' . $current_blog->domain . $current_blog->path . 'wp-login.php?action=lostpassword');
                } else {
                    $html .= sprintf(__('<p class="lead-in">Your account at <a href="%1$s">%2$s</a> is active. Please check your email inbox at %4$s for your password and login instructions.  If you do not receive an email, please check your junk or spam folder.  If you still do not receive an email within an hour, you can <a href="%5$s">reset your password</a>.</p>', 'removeev'), 'http://' . $signup->domain, $signup->domain, $signup->user_login, $signup->user_email, 'http://' . $current_blog->domain . $current_blog->path . 'wp-login.php?action=lostpassword');
                }
            } else {

                $html .= '<h2>' . __('An error occurred during the signup', 'removeev') . "</h2>\n";
                $html .= '<p>' . $result->get_error_message() . '</p>';
            }
        } else {
            extract($result);

            if( empty( $user_id ) ){
                $user_id = $result;
            }

            if( empty( $password ) ){
                $password = '********';
            }

            $newuser = new WP_User((int) $user_id);

            $html = '<h2>' . sprintf(__('Hello %s, your account has been created!', 'removeev'), $newuser->user_login) . "</h2>\n";

            $html .= '<div id="signup-welcome">';
            $html .= '<p><span class="h3">' . __('Username:', 'removeev') . '</span> ' . $newuser->user_login . '</p>';
            $html .= '<p><span class="h3">' . __('Password:', 'removeev') . '</span> ' . $password . '</p>';
            $html .= '</div>';

            $html .= '<p class="view">' . sprintf(__('You can now update your details by going to the <a href="%1$s">admin area</a> of your account or go back to the <a href="%2$s">homepage</a>.', 'removeev'), 'http://' . $current_blog->domain . $current_blog->path . 'wp-admin', 'http://' . $current_blog->domain . $current_blog->path) . '</p>';
        }

        // Check if we are passed in an admin area
        if (!is_admin() || !(isset($_POST['_wp_http_referer']) && strstr($_POST['_wp_http_referer'], 'user-new.php'))) {
            echo $html;
        }

        // automatically login the user so they can see the admin area on the next page load
        $userbylogin = get_user_by('login', $user);

        if (!empty($userbylogin)) {
            wp_set_current_user($ucserbylogin->ID);
            wp_set_auth_cookie($userbylogin->ID, false, is_ssl());
        }

        // Now we need to hijack the sign up message so it isn't displayed
        ob_start();
        // End activation message display
        add_action('signup_finished', array(&$this, 'activated_signup_finished'), 1);

        return false; // Returns false so that the activation email isn't sent out to the user
    }

    function activated_newuser_msg($transtext, $normtext, $domain) {

        switch ($normtext) {
            // Plugin page text that we want to remove
            case 'Invitation email sent to new user. A confirmation link must be clicked before their account is created.':
                $transtext = __('The new user has been created and an email containing their account details has been sent to them.', 'removeev');
                break;
            case 'If you change this we will send you an email at your new address to confirm it. <strong>The new address will not become active until confirmed.</strong>':
                $transtext = '';
                break;
        }

        return $transtext;
    }

    function user_auto_login_after_signup($user_id) {
        global $current_blog;

        delete_user_meta( $user_id, 'activation_key' );

        if( is_user_logged_in() ) return;

        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id, false, is_ssl());

    }

    function output_buffer() {
        ob_start();
    }

    function activated_signup_finished() {
        // Flush the activation buffer
        if (ob_get_level() > 0)
            ob_end_clean();
    }

}
?>