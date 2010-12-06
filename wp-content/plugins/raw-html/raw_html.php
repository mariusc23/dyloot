<?php
/*
Plugin Name: Raw HTML capability
Plugin URI: http://w-shadow.com/blog/2007/12/13/raw-html-in-wordpress/
Description: Lets you enter raw HTML in your posts. You can also enable/disable smart quotes and other automatic formatting on a per-post basis.
Version: 1.3
Author: Janis Elsts
Author URI: http://w-shadow.com/blog/
*/

/*
Created by Janis Elsts (email : whiteshadow@w-shadow.com) 
It's LGPL.
*/

include 'screen-options/screen-options.php';

/**********************************************
	Filter inline blocks of raw HTML
***********************************************/
global $wsh_raw_parts;
$wsh_raw_parts=array();

function wsh_extraction_callback($matches){
	global $wsh_raw_parts;
	$wsh_raw_parts[]=$matches[1];
	return "!RAWBLOCK".(count($wsh_raw_parts)-1)."!";
}

function wsh_extract_exclusions($text){
	global $wsh_raw_parts;
	
	$tags = array(array('<!--start_raw-->', '<!--end_raw-->'), array('[RAW]', '[/RAW]'));

	foreach ($tags as $tag_pair){
		list($start_tag, $end_tag) = $tag_pair;
		
		//Find the start tag
		$start = stripos($text, $start_tag, 0);
		while($start !== false){
			$content_start = $start + strlen($start_tag);
			
			//find the end tag
			$fin = stripos($text, $end_tag, $content_start);
			
			//break if there's no end tag
			if ($fin == false) break;
			
			//extract the content between the tags
			$content = substr($text, $content_start,$fin-$content_start);
			
			//Store the content and replace it with a marker
			$wsh_raw_parts[]=$content;
			$replacement = "!RAWBLOCK".(count($wsh_raw_parts)-1)."!";
			$text = substr_replace($text, $replacement, $start, 
				$fin+strlen($end_tag)-$start
			 );
			
			//Have we reached the end of the string yet?
			if ($start + strlen($replacement) > strlen($text)) break;
			
			//Find the next start tag
			$start = stripos($text, $start_tag, $start + strlen($replacement));
		}
	}
	return $text;
	/*
	//The regexp version is much shorter, but it has problems with big posts : 
	return preg_replace_callback("/(?:<!--\s*start_raw\s*-->|\[RAW\])(.*?)(?:<!--\s*end_raw\s*-->|\[\/RAW\])/is", 
		"wsh_extraction_callback", $text);
	//	*/
}

function wsh_insertion_callback($matches){
	global $wsh_raw_parts;
	return $wsh_raw_parts[intval($matches[1])];
}

function wsh_insert_exclusions($text){
	global $wsh_raw_parts;
	if(!isset($wsh_raw_parts)) return $text;
	return preg_replace_callback("/!RAWBLOCK(\d+?)!/", "wsh_insertion_callback", $text);		
}

add_filter('the_content', 'wsh_extract_exclusions', 2);
add_filter('the_content', 'wsh_insert_exclusions', 1001);

/*****************************************************
	Disable default formatting on a per-post level
******************************************************/

//Apply function $func to $content unless it's been disabled for the current post 
function maybe_use_filter($func, $content){
	global $post;
	if (get_post_meta($post->ID, 'disable_'.$func, true) == '1') {
		return $content;
	} else {
		return $func($content);
	}
}

//Stub filters that replace the WP defaults
function maybe_wptexturize($content){
	return maybe_use_filter('wptexturize', $content);
}

function maybe_wpautop($content){
	return maybe_use_filter('wpautop', $content);
}

function maybe_convert_chars($content){
	return maybe_use_filter('convert_chars', $content);
}

function maybe_convert_smilies($content){
	return maybe_use_filter('convert_smilies', $content);
}

// Disable default filters and add our conditional filters
function rawhtml_add_conditional_filters(){
	$filters = array(
		'the_content' => array(
			'wpautop',
			'wptexturize',
			'convert_chars',
			'convert_smilies',
		),
		'the_excerpt' => array(
			'wpautop',
			'wptexturize',
			'convert_chars',
			'convert_smilies',
		),
	);
	
	foreach ( $filters as $tag => $functions ){
		foreach ( $functions as $func ){
			if ( remove_filter($tag, $func) ){
				add_filter( $tag, 'maybe_'.$func, 3 );
			};
		}
	}
}
add_action('init', 'rawhtml_add_conditional_filters');

// Add a custom meta box for per-post settings 
add_action('admin_menu', 'rawhtml_add_custom_box');
add_action('save_post', 'rawhtml_save_postdata');

/* Adds a custom section to the "advanced" Post and Page edit screens */
function rawhtml_add_custom_box() {
  //WP 2.5+
  if( function_exists( 'add_meta_box' )) { 
  	foreach( array('post', 'page') as $type ) {
	    add_meta_box( 'rawhtml_meta_box', 'Raw HTML', 'rawhtml_meta_box', $type, 'side' );
    }
  }
}

/* Displays the custom box */
function rawhtml_meta_box(){
	global $post;
	// Use nonce for verification
	echo '<input type="hidden" name="rawhtml_nonce" id="rawhtml_nonce" value="' . 
	wp_create_nonce( plugin_basename(__FILE__) ) . '" />';
	
	//Output checkboxes 
	$fields = array(
		'disable_wptexturize' => 'Disable wptexturize',
		'disable_wpautop' => 'Disable automatic paragraphs',
		'disable_convert_chars' => 'Disable convert_chars',
		'disable_convert_smilies' => 'Disable smilies',
	);
	$defaults = rawhtml_get_default_settings();
	foreach($fields as $field => $legend){
		$current_setting = get_post_meta($post->ID, $field, true);
		if ( $current_setting == '' ){
			$current_setting = $defaults[$field];
		} else {
			$current_setting = (bool)intval($current_setting);
		}
?>
<label for="rawhtml_<?php echo $field; ?>">
	<input type="checkbox" name="rawhtml_<?php echo $field; ?>" id="rawhtml_<?php echo $field; ?>" <?php
		if ($current_setting) echo ' checked="checked"';
	?>/>
	<?php echo $legend; ?>
</label>
<br /> 
<?php
	}
}

/* Saves post metadata */
function rawhtml_save_postdata( $post_id ){
  // verify this came from the our screen and with proper authorization,
  // because save_post can be triggered at other times
  
  if ( !isset($_POST['rawhtml_nonce']) ){
	return $post_id;
  }
  
  if ( !wp_verify_nonce( $_POST['rawhtml_nonce'], plugin_basename(__FILE__) )) {
    return $post_id;
  }

  if ( 'page' == $_POST['post_type'] ) {
    if ( !current_user_can( 'edit_page', $post_id ))
      return $post_id;
  } else {
    if ( !current_user_can( 'edit_post', $post_id ))
      return $post_id;
  }
  
  // OK, we're authenticated: we need to find and save the data
  $fields  = array('disable_wpautop', 'disable_wptexturize', 'disable_convert_chars', 'disable_convert_smilies');
  foreach ( $fields as $field ){	
  	  if ( !empty($_POST['rawhtml_'.$field]) ){
		update_post_meta($post_id, $field, '1');
	  } else {
		update_post_meta($post_id, $field, '0');
	  };
  }

  return true;
}

//Add our panel to the "Screen Options" box
add_screen_options_panel(
	'rawhtml-default-settings', 
	'Raw HTML defaults', 
	'rawhtml_default_settings_panel', 
	array('post', 'page'),
	'rawhtml_save_new_defaults',
	true
);

/**
 * Retrieve the default settings for our post/page meta box.
 * Settings are saved in user meta.
 * 
 * @return array
 */
function rawhtml_get_default_settings(){
	//By default, all tweaks are disabled
	$defaults = array(
		'disable_wptexturize' => false,
		'disable_wpautop' => false,
		'disable_convert_chars' => false,
		'disable_convert_smilies' => false,
	);
	
	if ( !function_exists('wp_get_current_user') || !function_exists('get_user_meta') ){
		return $defaults;
	}
	
	//Get current defaults, if any
	$user = wp_get_current_user();
	$user_defaults = get_user_meta($user->ID, 'rawhtml_defaults', true);
	if ( is_array($user_defaults) ){
		$defaults = array_merge($defaults, $user_defaults);
	}
	
	return $defaults;
}

/**
 * Update default settings for our post/page meta box.
 * 
 * @param array $new_defaults
 * @return bool True on success, false on failure.
 */
function rawhtml_set_default_settings($new_defaults){
	if ( !function_exists('wp_get_current_user') || !function_exists('update_user_meta') ){
		return false;
	}
	
	//Get current defaults, if any
	$user = wp_get_current_user();
	if ( isset($user) && $user && isset($user->ID) ){
		return update_user_meta($user->ID, 'rawhtml_defaults', $new_defaults);
	} else {
		return false;
	}
}

/**
 * Generate the "Raw HTML defaults" panel for Screen Options.
 * 
 * @return string
 */
function rawhtml_default_settings_panel(){
	$defaults = rawhtml_get_default_settings();
	
	//Output checkboxes 
	$fields = array(
		'disable_wptexturize' => 'Disable wptexturize',
		'disable_wpautop' => 'Disable automatic paragraphs',
		'disable_convert_chars' => 'Disable convert_chars',
		'disable_convert_smilies' => 'Disable smilies',
	 );
	 
 	$output = '';
	foreach($fields as $field => $legend){
		$esc_field = esc_attr($field);
		$output .= sprintf(
			'<label for="rawhtml_default-%s" style="line-height: 20px;">
				<input type="checkbox" name="rawhtml_default-%s" id="rawhtml_default-%s"%s>
				%s
			</label><br>',
			$esc_field,
			$esc_field,
			$esc_field,
			($defaults[$field]?' checked="checked"':''),
			$legend
		);
	}
	
	return $output;
}

/**
 * Process the "Raw HTML defaults" form fields and save new settings
 * 
 * @param array $params
 * @return void
 */
function rawhtml_save_new_defaults($params){
	//Get current defaults
	$defaults = rawhtml_get_default_settings();
	
	//Read new values from the submitted form
	foreach($defaults as $field => $old_value){
		if ( isset($params['rawhtml_default-'.$field]) && ($params['rawhtml_default-'.$field] == 'on') ){
			$defaults[$field] = true;
		} else {
			$defaults[$field] = false;
		}
	}
	
	//Store the new defaults
	rawhtml_set_default_settings($defaults);
}

?>