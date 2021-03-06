<?php
/*
 * Plugin Name: Todayish In History
 * Plugin URI: http://stuporglue.org/todayish-in-history
 * Description: Show links to the post made nearest to today from each previous year
 * Version: 0.2
 * Author: Michael Moore <stuporglue@gmail.com>
 * Author URI: http://stuporglue.org
 * License: GPL2
 */

/*  Copyright 2011 Michael Moore (email : stuporglue@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

wp_enqueue_style('todayish_in_history',plugins_url('todayish_in_history.css',__FILE__)); // We're last so we can override CSS and so we can use JS libs

/**
 * @brief Display the Todayish In History widget or theme block
 *
 * @param $instance (optional) An array of options. 
 *
 * $instance keys available are 'limit','title' and 'class'
 *
 * 'limit' (int/100) How many links to return (at most, one per year). 
 * 'title' (string/Todayish In History) is the title of the displayed widget or theme block. 
 * 'class' (string/horizontal) is a class added to the outermost <div> for styling purposes. In the widget UI it is presented as direction and sets either 'vertical' or 'horizontal' as the values
 * 'width' (string/200px)is how wide to make it
 * 'iswidget' (bool) If you set parameters when not used as a widget, you should also pass FALSE to iswidget
 * 'max_days_off' (int/365) The max number of days away from today's date to search
 * 'range' (bool/FALSE) Use the fixed range_start/range_stop fields?
 * 'range_start' (int/0) Start year in fixed range
 * 'range_stop (int/date('Y')) Stop year in fixed range
 *
 *
 * @param $args (optional) Widget args. Populated by WordPress when called as a widget.
 */
function todayish_in_history($instance = FALSE,$args = FALSE){
    global $wpdb,$options,$table_prefix,$before_title,$after_title;

    $settings = Array(
	'limit' => 100,
	'title' => "Todayish In History",
	'class' => 'horizontal',
	'width' => '200px',
	'iswidget' => FALSE,
	'max_days_off' => 365,
	'range' => FALSE,
	'range_start' => 0,
	'range_stop' => date('Y'),
    );

    if($args){
	extract($args);
    }

    if(is_array($instance)){ // if is_array, then in widget mode
	if(!array_key_exists('iswidget',$instance)){
	    $settings['iswidget'] = TRUE;
	    $settings['class'] = 'vertical'; // default to vertical
	}
	$settings = array_merge($settings,$instance);
	$settings['limit'] = (int)$settings['limit']; // just in case
    }

    if($settings['range']){
	$rangeConditions =  "AND 
	`year` >= '{$settings['range_start']}' AND
	`year` <= '{$settings['range_stop']}'";
    }

    $q = "SELECT * FROM (
	SELECT 
	`ID`, 
	`post_title`, 
	`post_date`, 
	`post_excerpt`, 
	`comment_count`, 
	YEAR(`post_date`) AS `year`,
	ABS(DAYOFYEAR(NOW()) - DAYOFYEAR(`post_date`)) AS `days_off`
	FROM 
	`{$table_prefix}posts` 
	WHERE 
	`post_status`='publish' AND 
	`post_type` <> 'attachment' AND 
	`post_type` <> 'revision' AND 
	`post_type` <> 'page' 
	ORDER BY `days_off`
    ) AS `goodposts`
    WHERE
    `days_off` < {$settings['max_days_off']} 
    $rangeConditions
    GROUP BY `year`
    ORDER BY `year`  DESC
    LIMIT {$settings['limit']}
    ";

    $r = $wpdb->get_results($q, OBJECT);

    if($settings['iswidget']){
	$title = $before_title . apply_filters('widget_title', $settings['title']) . $after_title;
    }else{
	$title = "<h2 id='historylabel' class='notwidgettitle'>{$settings['title']}</h2>";
    }

    print "<div id='todayinhistory' class='{$settings['class']}'>$title<ul style='width:{$settings['width']};'>";

    // check if we got some posts
    if ( $r ) {
	// if we got posts, parse it
	$last = count($r);
	foreach ( $r as $idx => $post )
	{
	    // prepare excerpt
	    if ( $tm_excerpt == TRUE ) {
		$tm_post_excerpt   = $post->post_excerpt;
		if ( $tm_excerpt_cut == TRUE ) {
		    if ( $tm_excerpt_length && mb_strlen($tm_post_excerpt) > ($tm_excerpt_length+1) ) { $tm_post_excerpt = tm_substr_utf8($tm_post_excerpt, 0, $tm_excerpt_length)."&hellip;"; } }
	    }

	    // print formated post line
	    if ( $options['display_commentnum'] == "1" ) { 
		$pcount = ' (<span title="'.__("Number of comments", "tm").'">'.$post->comment_count.'</span>)'; 
	    } else { 
		$pcount = ""; 
	    }

	    if($last == 1){
		$liclass = " class='last'";
	    }else if($idx == 0){
		$liclass = " class='first'";
	    }else{
		$liclass = '';
	    }
	    print "<li$liclass>".date('Y',strtotime($post->post_date)).': ';
	    print '<a href="'.get_permalink($post->ID).'" title="'.$post->post_title.' '.$post->post_date.'">'.$post->post_title.'</a>';
	    print $pcount.' '.$tm_excerpt_before.$tm_post_excerpt.$tm_excerpt_after;
	    print '</li>';
	    $last--;
	}
    } else {
	print_r($wpdb->last_error);
	// if we have no blog posts on current date in past, print that info
	print "<li>Nothing happened in history. Try again tomorrow</li>";
    }
    print "</ul></div>";
}

class todayish_widget extends WP_Widget {
    public function __construct() {
	// widget actual processes
	parent::__construct(
	    'todayish_widget', // Base ID
	    'Todayish', // Name
	    array( 'description' => __( 'Shows post nearest to the current date from each previous year', 'text_domain' ), ) // Args
	);
    }

    public function update( $new_instance, $old_instance ) {
	// processes widget options to be saved
	$instance = array();
	$instance['title'] = strip_tags( $new_instance['title'] );
	$limit = (int)strip_tags($new_instance['limit']);
	$instance['limit'] = ($limit > 0 ? $limit : 100);
	$instance['class'] = $new_instance['class'];
	$instance['width'] = strip_tags( $new_instance['width'] );

	$max_days_off = (int)strip_tags($new_instance['max_days_off']);
	$instance['max_days_off'] = ($max_days_off > 0 ? $max_days_off : 365);
	$instance['range'] = (int)strip_tags($new_instance['range']);
	$instance['range_start'] = (int)$new_instance['range_start'];
	$instance['range_stop'] = (int)$new_instance['range_stop'];

	return $instance;
    }

    public function form($instance){
	// Look and Feel
	$title = (isset($instance['title']) ? $instance['title'] : __('Today-ish In History','text_domain'));
	$class = (isset($instance['class']) ? $instance['class'] : 'vertical');
	$width = (isset($instance['width']) ? $instance['width'] : '200px');
	
	// Search Criteria
	$limit = (isset($instance['limit']) ? $instance['limit'] : 100);
	$max_days_off = (isset($instance['max_days_off']) ? $instance['max_days_off'] : 365);
	$range = (isset($instance['range']) ? $instance['range'] : 0);
	$range_start = (isset($instance['range_start']) ? $instance['range_start'] : 0);
	$range_stop = (isset($instance['range_stop']) ? $instance['range_stop'] : date('Y'));
?>
	<h5>Look and Feel</h5>
	<p>
	<label for="<?php print $this->get_field_id( 'title' ); ?>"><?php _e( 'Widget Title:' ); ?></label> 
	<input class="widefat" id="<?php print $this->get_field_id( 'title' ); ?>" name="<?php print $this->get_field_name( 'title' ); ?>" type="text" value="<?php print esc_attr( $title ); ?>" />
	</p>
	<p>
	<label><?php _e('Display Direction')?></label><br/>
	    <label for="<?php print $this->get_field_id( 'class' ); ?>-v"><?php _e( 'Vertical:' ); ?></label>
	    <input id="<?php print $this->get_field_id( 'class' ); ?>-v" name="<?php print $this->get_field_name( 'class' ); ?>" type="radio" value="vertical" <?php if(esc_attr($class) == 'vertical'){print "checked='checked'";}?> />
	    <br/>
	    <label for="<?php print $this->get_field_id( 'class' ); ?>-h"><?php _e( 'Horizontal:' ); ?></label> 
	    <input id="<?php print $this->get_field_id( 'class' ); ?>-h" name="<?php print $this->get_field_name( 'class' ); ?>" type="radio" value="horizontal" <?php if(esc_attr($class) == 'horizontal'){print "checked='checked'";}?>/>
	</p>
	<p>
	<label for="<?php print $this->get_field_id( 'width' ); ?>"><?php _e( 'Dropdown width (valid CSS):' ); ?></label> 
	<input class="widefat" id="<?php print $this->get_field_id( 'width' ); ?>" name="<?php print $this->get_field_name( 'width' ); ?>" type="text" value="<?php print esc_attr( $width); ?>" />
	</p>

	<h5>Search Criteria</h5>
	<p>
	<label for="<?php print $this->get_field_id( 'limit' ); ?>"><?php _e( 'Max number of links to show:' ); ?></label> 
	<input class="widefat" id="<?php print $this->get_field_id( 'limit' ); ?>" name="<?php print $this->get_field_name( 'limit' ); ?>" type="text" value="<?php print esc_attr( $limit); ?>" />
	</p>
	<p>
	<label for="<?php print $this->get_field_id( 'max_days_off' ); ?>"><?php _e( 'Max days away from today\'s date:' ); ?></label> 
	<input class="widefat" id="<?php print $this->get_field_id( 'max_days_off' ); ?>" name="<?php print $this->get_field_name( 'max_days_off' ); ?>" type="text" value="<?php print esc_attr( $max_days_off); ?>" />
	</p>
	<p>
	<label for="<?php print $this->get_field_id( 'range' ); ?>"><?php _e( 'Use Fixed Date Range:' ); ?></label> 
	<input class="widefat" id="<?php print $this->get_field_id( 'range' ); ?>" name="<?php print $this->get_field_name( 'range' ); ?>" type="checkbox" value="1" <?php print ($range ? 'checked="checked"' : '');  ?> />
	</p>
	<p>
	<label for="<?php print $this->get_field_id( 'range_start' ); ?>"><?php _e( 'Fixed Date Range Start Year:' ); ?></label> 
	<input class="widefat" id="<?php print $this->get_field_id( 'range_start' ); ?>" name="<?php print $this->get_field_name( 'range_start' ); ?>" type="text" value="<?php print esc_attr( $range_start); ?>" />
	</p>
	<p>
	<label for="<?php print $this->get_field_id( 'range_stop' ); ?>"><?php _e( 'Fixed Date Range Stop Year:' ); ?></label> 
	<input class="widefat" id="<?php print $this->get_field_id( 'range_stop' ); ?>" name="<?php print $this->get_field_name( 'range_stop' ); ?>" type="text" value="<?php print esc_attr( $range_stop); ?>" />
	</p>
	<?php 
    }

    public function widget( $args, $instance ) {
	todayish_in_history($instance,$args);
    }
}

function todayish_donate_link($links,$file){
    if ($file == plugin_basename(__FILE__)) {
	$donation_url  = 'https://www.dwolla.com/hub/stuporglue';
	$title         = 'Thanks for the timesaving lifechanger!';
	$links[] = '<a href="' . esc_url( $donation_url ) . '" title="' . esc_attr( $title ) . '">Donate</a>';
    }
    return $links;
}
add_filter( 'plugin_row_meta', 'todayish_donate_link' , 10, 2);
add_action( 'widgets_init', create_function( '', 'register_widget( "todayish_widget" );' ) );
