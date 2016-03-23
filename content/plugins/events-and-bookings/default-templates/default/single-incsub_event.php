<?php
global $blog_id, $wp_query, $booking, $post, $current_user;
$event = new Eab_EventModel($post);

get_header( );
?>
	<div id="primary">
		<div id="content" role="main">
            <div class="event <?php echo Eab_Template::get_status_class($post); ?>" id="wpmudevevents-wrapper">
		<div id="wpmudevents-single">

                    <?php
                    the_post();

                    $start_day = date_i18n('m', strtotime(get_post_meta($post->ID, 'incsub_event_start', true)));
                    ?>

                    <div class="wpmudevevents-header">
                        <h2><?php echo $event->get_title(); ?></h2>
                        <div class="eab-needtomove"><div id="event-bread-crumbs" ><?php echo Eab_Template::get_breadcrumbs($event); ?></div></div>
                        <?php
                        echo Eab_Template::get_rsvp_form($post);
						echo Eab_Template::get_inline_rsvps($post);
                        ?>
                    </div>

                    <hr />
                    <?php

                    if ($event->is_premium() && $event->user_is_coming() && !$event->user_paid()) { ?>
		    <div id="wpmudevevents-payment">
			<?php _e('You haven\'t paid for this event', Eab_EventsHub::TEXT_DOMAIN); ?>
                        <?php echo Eab_Template::get_payment_forms($post); ?>
		    </div>
                    <?php } ?>

                    <?php echo Eab_Template::get_error_notice(); ?>

                    <div class="wpmudevevents-content">
			<div id="wpmudevevents-contentheader">
                            <h3><?php _e('About this event:', Eab_EventsHub::TEXT_DOMAIN); ?></h3>

			    <div id="wpmudevevents-user"><?php _e('Created by ', Eab_EventsHub::TEXT_DOMAIN); ?><?php the_author_link();?></div>
			</div>

                        <hr />
			<div class="wpmudevevents-contentmeta">
                            <?php echo Eab_Template::get_event_details($post); //event_details(); ?>
			</div>
			<div id="wpmudevevents-contentbody">
			    <?php
			    	add_filter('agm_google_maps-options', 'eab_autoshow_map_off', 99);
			    	the_content();
					remove_filter('agm_google_maps-options', 'eab_autoshow_map_off');
			    ?>
			    <?php if ($event->has_venue_map()) { ?>
			    	<div class="wpmudevevents-map"><?php echo $event->get_venue_location(Eab_EventModel::VENUE_AS_MAP); ?></div>
			    <?php } ?>
                        </div>
                        <?php comments_template( '', true ); ?>
                    </div>
                </div>
        </div>
	</div>
</div>
<?php get_footer('event'); ?>