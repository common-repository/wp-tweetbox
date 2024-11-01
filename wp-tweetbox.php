<?php
/*
Plugin Name: WP Tweetbox
Plugin URI: http://www.riyaz.net/wp-tweetbox/
Description: WP Tweetbox is a WordPress plugin that adds a highly customizable Tweetbox at the end of blog posts and pages. Tweets are branded with your own website URL. If desired, the default Tweet text can be manually specified for individual posts/pages.
Author: Riyaz
Version: 0.1
Author URI: http://www.riyaz.net
License: GPL2
*/

/*  Copyright 2010  Riyaz Sayyad  (email : riyaz@riyaz.net)

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
?>
<?php
function wptb_showbox(){
global $rbtnValue;
if ( $rbtnValue == 'wptb_hide') {$hide_box_value = true;}
else {$hide_box_value = false;}

if (is_home() || is_front_page()) return false;
else if (is_single() && get_option('wptb_show_on_posts') == "true" && $hide_box_value == false ) return true;
else if (is_page() && get_option('wptb_show_on_pages') == "true" && $hide_box_value == false ) return true;
else return false;
}
?>
<?php
function wptb_add_tweetbox_to_content($content) {
wptb_read_metadata();
if (wptb_showbox()){
	$content = ''.$content . wptb_tweetbox();
	}
return $content;
}
add_filter('the_content', 'wptb_add_tweetbox_to_content', 9);
?>
<?php
function wptb_add_head(){
		$api = get_option('wptb_twitterapp_apikey');
		?>
		<script type='text/javascript' src='http://platform.twitter.com/anywhere.js?id=<?php echo $api; ?>&v=1'></script>	
<?php
}
add_action('wp_head','wptb_add_head');
?>
<?php
function wptb_read_metadata(){
	global $post; 
	global $defaultContent;
	global $customContent;
	global $rbtnValue;
	$wptb_shortlink;
	$twitter_username = get_option('wptb_twitter_username');
	$shortener = get_option('wptb_shortener_service');
	$shortener_user = get_option('wptb_shortener_user');
	$shortener_api = get_option('wptb_shortener_apikey');
	
	$rbtnValue = get_post_meta($post->ID, 'wptb_radiobuttons_value', true);
	$customContent = get_post_meta($post->ID, 'wptb_customtweettext_value', true);
	
	$wptb_longlink = get_permalink($post->ID);
	
	switch($shortener){
		case "none":
			$wptb_shortlink = $wptb_longlink;
		break;
		
		case "wppost":
			$wptb_shortlink = ''.get_bloginfo('url').'?p='.$post->ID;
		break;
		
		case "wpme":
			if (function_exists('wp_get_shortlink')) {$wptb_shortlink = wp_get_shortlink($post->ID); }
			else if (function_exists('wpme_get_shortlink')) {$wptb_shortlink = wpme_get_shortlink($post->ID); }
			else { $wptb_shortlink = $wptb_longlink;}
		break;
		
		case "bitlypro":
			$wptb_shortlink = "";
			if ($shortener_user != '' && $shortener_api != '') { $fh1 = fopen( 'http://api.bit.ly/v3/shorten?login='.$shortener_user.'&apiKey='.$shortener_api.'&longUrl='.$wptb_longlink.'&format=txt', "r" ); 
				while( $data = fread( $fh1, 4096 ) ){ $wptb_shortlink .= $data; }
				fclose( $fh1 );
			}else { $wptb_shortlink = $wptb_longlink; }
		break;
		case "bitly":
			$wptb_shortlink = "";
			if ($shortener_user != '' && $shortener_api != '') { $fh1 = fopen( 'http://api.bit.ly/v3/shorten?login='.$shortener_user.'&apiKey='.$shortener_api.'&longUrl='.$wptb_longlink.'&format=txt', "r" );}
			else { $fh1 = fopen( 'http://api.bit.ly/v3/shorten?login=wptweetbox&apiKey=R_aa066eca78a8794b15cb45b6e94444ae&longUrl='.$wptb_longlink.'&format=txt', "r" ); }
			while( $data = fread( $fh1, 4096 ) ){ $wptb_shortlink .= $data; }
			fclose( $fh1 );
		break;
		
		case "supr":
	    default:
			$wptb_shortlink = "";
			if ($shortener_user != '' && $shortener_api != '') { $fh = fopen( 'http://su.pr/api/simpleshorten?url='.$wptb_longlink.'&login='.$shortener_user.'&apiKey='.$shortener_api, "r" ); }
			else {$fh = fopen( 'http://su.pr/api/simpleshorten?url='.$wptb_longlink, "r" ); }
			
			while( $data = fread( $fh, 4096 ) ){$wptb_shortlink .= $data;}
			fclose( $fh );
		break;
	}
	$defaultContent = ''. trim(wp_title('',false,'')) .' - '. trim($wptb_shortlink);
	if ($twitter_username != '') $defaultContent .= ' via @'.$twitter_username;
}
?>
<?php
function wptb_tweetbox() {
global $customContent;
global $defaultContent;
global $rbtnValue;

$label = get_option('wptb_label');
if ($label == '') $label = 'Tweet this';
$height = get_option('wptb_height');
if ($height == 0) $height = 65;
$width = get_option('wptb_width');
if ($width == 0) $width = 515;

if ($rbtnValue  == 'wptb_custom')
	$tweetText = trim($customContent);
else if ($rbtnValue == 'wptb_default')
	$tweetText = $defaultContent;
else
	$tweetText = $defaultContent;
	
	$wptb_html = '<br /><div id="wptb-tweetbox"></div>';
	$wptb_html .='<script type="text/javascript">'
				.'twttr.anywhere(WPTB);'
				.'function WPTB(twitter) {'
				.'twitter("#wptb-tweetbox").tweetBox({'
				.'label: "'	.$label. '",'
				.'defaultContent: "'. $tweetText .'",'
				.'height: '.$height.','
				.'width: '.$width.','
				.'});'
				.'};'
				.'</script>';
	return $wptb_html; 
}
?>
<?php
function wp_tweetbox() { //Manual mode
	$manual_tweetbox = wptb_tweetbox();
	echo $manual_tweetbox;
}
?>
<?php
// create custom plugin settings menu
add_action('admin_menu', 'wptb_create_menu');

function wptb_create_menu() {
	//create new top-level menu
	add_menu_page('WP Tweetbox Settings', 'WP Tweetbox', 'administrator', __FILE__, 'wptb_settings_page',plugins_url('/images/riyaznet.ico', __FILE__));

	//call register settings function
	add_action( 'admin_init', 'register_wptb_settings' );
}

function register_wptb_settings() {
	//register the settings
	register_setting( 'wptb-settings-group', 'wptb_twitter_username' );
	
	register_setting( 'wptb-settings-group', 'wptb_label' );
	register_setting( 'wptb-settings-group', 'wptb_height', 'intval' );
	register_setting( 'wptb-settings-group', 'wptb_width' , 'intval' );
	register_setting( 'wptb-settings-group', 'wptb_defaultContent' );
	
	register_setting( 'wptb-settings-group', 'wptb_shortener_service' );
	register_setting( 'wptb-settings-group', 'wptb_shortener_user' );
	register_setting( 'wptb-settings-group', 'wptb_shortener_apikey' );
	register_setting( 'wptb-settings-group', 'wptb_twitterapp_apikey' );
	
	register_setting( 'wptb-settings-group', 'wptb_show_on_posts' );
	register_setting( 'wptb-settings-group', 'wptb_show_on_pages' );
}

function wptb_settings_page() {
?>
<div class="wrap">
<h2>WP Tweetbox Settings</h2>
<?php
global $wp_query;
if( $_GET['updated'] ) { ?>
	<div id="message" class="updated">Settings Saved Successfully.</div>
<?php if (get_option('wptb_twitterapp_apikey') == ''){	?>
	<div id="message" class="error">Please specify a Twitter App API Key. If you havent already registered for a Twitter App, you can easily create one. It takes less than a minute and is free. <a href="http://www.riyaz.net/blog/create-twitter-app/social-media/2187/" target="_blank">Learn more</a>.</div>
<?php } ?>	
<?php } ?>
<div style="float:right; margin:50px 10px 10px 10px; padding:5px; width:25%; border:1px solid grey;">
<h3>Resources</h3>
<ul>
<li><a href="http://www.riyaz.net/blog/create-twitter-app/social-media/2187/" target="_blank">How to Get Your Twitter App API Key</a></li>
<li><a href="http://www.riyaz.net/blog/short-url-own-domain/technology/software/tips-and-tricks/2142/" target="_blank">Brand Your Tweets with Your Own Short URLs</a></li>
<li>Get latest news and updates via <a href="http://feeds.riyaz.net/RiyaznetBlog" title="Subscribe to riyaz.net RSS feed" target="_blank">RSS</a> or <a href="http://feedburner.google.com/fb/a/mailverify?uri=RiyaznetBlog&loc=en_US" title="Get riyaz.net Newsletter by email" target="_blank">Email</a></li>
<li>Follow us on <a href="http://twitter.com/riyaznet" title="Follow @riyaznet on Twitter" target="_blank">Twitter</a></li>
<li>Become a fan on <a href="http://www.facebook.com/riyaznet" title="Like us on Facebook" target="_blank">Facebook</a></li>
</ul>
<h3>Help</h3>
<ul>
<li>For Help and Support use <a href="http://www.riyaz.net/forums/forum.php?id=11" title="riyaz.net Forums" target="_blank">Forums</a> or Ask on <a href="http://twitter.com/riyaznet" title="@riyaznet" target="_blank">Twitter</a>.
</li>
</ul>
<h3>Our other plugins you may like</h3>
<ul>
<li>
<a href="http://www.riyaz.net/getsocial/" target="_blank">GetSocial</a> - GetSocial adds a lightweight and intelligent floating social media sharing box on your blog posts.
</li>
</ul>
Thank you for using <a href="http://www.riyaz.net/wp-tweetbox/" title="WP Tweetbox Homepage" target="_blank">WP Tweetbox</a>.
</div>
<div style="float:left;width:70%;">
<h3>Tweetbox Configuration</h3>
<form method="post" action="options.php">
    <?php settings_fields( 'wptb-settings-group' ); ?>
    <table class="form-table">
        <tr valign="top">
        <th scope="row">Your Twitter Username</th>
        <td><input type="text" name="wptb_twitter_username" value="<?php echo get_option('wptb_twitter_username'); ?>" style="width:200px;"/> (Specify the Twitter username to be used for @ mention)</td>
        </tr>
        <tr valign="top">
        <th scope="row">Tweetbox Label</th>
        <td><input type="text" name="wptb_label" value="<?php echo get_option('wptb_label', 'If you liked this post, Tweet it!'); ?>" style="width:200px;"/> (Specify the Tweetbox Label)</td>
        </tr>
		<tr valign="top">
        <th scope="row">Tweetbox Height</th>
        <td><input type="text" name="wptb_height" value="<?php echo get_option('wptb_height',65); ?>" style="width:50px;"/> (Specify the Tweetbox Height)</td>
        </tr>		
		<tr valign="top">
        <th scope="row">Tweetbox Width</th>
        <td><input type="text" name="wptb_width" value="<?php echo get_option('wptb_width',515); ?>" style="width:50px;"/> (Specify the Tweetbox Width)</td>
        </tr>
		<tr valign="top">
        <th scope="row">URL Shortener to Use</th>
		<td>
			
			<select name="wptb_shortener_service">
			  <option <?php if (get_option('wptb_shortener_service') == "supr") echo 'selected="selected" '; ?> label="Su.pr (StumbleUpon)">supr</option>
			  <option <?php if (get_option('wptb_shortener_service') == "bitly") echo 'selected="selected" '; ?> label="Bit.ly">bitly</option>
			  <option <?php if (get_option('wptb_shortener_service') == "bitlypro") echo 'selected="selected" '; ?> label="Bitly.Pro">bitlypro</option>
			  <option <?php if (get_option('wptb_shortener_service') == "wpme") echo 'selected="selected" '; ?> label="Wp.Me (WordPress.com Stats plugin needed)">wpme</option>
			  <option <?php if (get_option('wptb_shortener_service') == "wppost") echo 'selected="selected" '; ?> label="Use WordPress Post ID">wppost</option>
			  <option <?php if (get_option('wptb_shortener_service') == "none") echo 'selected="selected" '; ?> label="None (Use Long link)">none</option>
			</select>
		</td>
        </tr>
		<tr valign="top">
        <th scope="row">Service Username</th>
        <td><input type="text" name="wptb_shortener_user" value="<?php echo get_option('wptb_shortener_user'); ?>" style="width:200px;"/> (Specify the Username for URL shortening service)</td>
        </tr>
		<tr valign="top">
        <th scope="row">Service API Key</th>
        <td><input type="text" name="wptb_shortener_apikey" value="<?php echo get_option('wptb_shortener_apikey'); ?>" style="width:300px;"/> (Specify the API key for URL shortening service)</td>
        </tr>
		<tr valign="top">
        <th scope="row">Your Twitter App API Key</th>
        <td><input type="text" name="wptb_twitterapp_apikey" value="<?php echo get_option('wptb_twitterapp_apikey'); ?>" style="width:300px;"/> (Required)<br />Specify the API key for Your Twitter App. If you havent already registered for a Twitter App, you can easily create one. It takes less than a minute and is free. <a href="http://www.riyaz.net/blog/create-twitter-app/social-media/2187/" target="_blank">Learn more</a>.</td>
        </tr>
    </table> 
<h3>Display Options</h3>
	<table class="form-table">
        <tr valign="top">
        <th scope="row">Display on Posts</th>
        <td><input type="checkbox" name="wptb_show_on_posts" value="true" <?php if (get_option('wptb_show_on_posts', true) == "true") { _e('checked="checked"', "wptb_show_on_posts"); }?> /></td>
        </tr>
		
		<tr valign="top">
        <th scope="row">Display on Pages</th>
        <td><input type="checkbox" name="wptb_show_on_pages" value="true" <?php if (get_option('wptb_show_on_pages', false) == "true") { _e('checked="checked"', "wptb_show_on_pages"); }?> /></td>
        </tr>
		
		<tr valign="top">
        <td colspan="2">(The display settings can be overridden on individual posts/pages by changing the WP Tweetbox Settings on the Edit Page/Post screen)</td>
        </tr>		
    </table>
    <p class="submit">
    <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
    </p>
</form>
</div>
</div>
<?php } ?>
<?php 
$wptb_meta_boxes =  
array(  
"wptb_hide" => array(  
"name" => "wptb_hide",  
"std" => "",  
"title" => "Hide WP Tweetbox on this post/page",  
"description" => "Hide WP Tweetbox on this post/page")
,
"wptb_radiobuttons" => array(  
"name" => "wptb_radiobuttons",  
"std" => "",  
"title" => "Radiobuttons",  
"description" => "Radiobuttons")
,
"wptb_customtweettext" => array(  
"name" => "wptb_customtweettext",  
"std" => "",  
"title" => "Specify a Custom Tweet Text",  
"description" => "Specify a Custom Tweet Text")  
);  

function wptb_meta_boxes() {  
	global $post, $wptb_meta_boxes;  
			?>
<script type="text/javascript">
function wptb_textCounter() {
var t_area = document.post.wptb_customtweettext_value;
var wptb_ctr = document.post.wptb_ctr;
if (t_area.value.length > 140)
t_area.value = t_area.value.substring(0, 140);
else
wptb_ctr.value = 140 - t_area.value.length;
}

function wptb_chooseRBTN(){
  document.post.wptb_radiobuttons_value[2].checked = 'checked';
}
</script>
		<?php
		$customtweet = '';
		$hidetweetbox = '';
		$defaulttweet = 'checked';
	
	foreach($wptb_meta_boxes as $meta_box) {  
		$meta_box_value = get_post_meta($post->ID, $meta_box['name'].'_value', true);  
		if($meta_box_value == "")  
		$meta_box_value = $meta_box['std'];  
		echo'<input type="hidden" name="'.$meta_box['name'].'_noncename" id="'.$meta_box['name'].'_noncename" value="'.wp_create_nonce( plugin_basename(__FILE__) ).'" />';  
			
		switch($meta_box['name']) {
			case "wptb_radiobuttons":
				if ($meta_box_value == "wptb_hide") $hidetweetbox = 'checked'; else $hidetweetbox = '';
				if ($meta_box_value == "wptb_default") $defaulttweet = 'checked'; else $defaulttweet = '';
				if ($meta_box_value == "wptb_custom") $customtweet = 'checked'; else $customtweet = '';
				
				echo '<input type="radio" name="'.$meta_box['name'].'_value" value="wptb_hide" '.$hidetweetbox.' /> Hide WP Tweetbox on this post/page<br />';
				echo '<input type="radio" name="'.$meta_box['name'].'_value" value="wptb_default" '.$defaulttweet.' /> Use the Default Tweet Text<br />';
				echo '<input type="radio" name="'.$meta_box['name'].'_value" value="wptb_custom" '.$customtweet.' /> Specify a Custom Tweet Text<br />';
			break;	
			
			case "wptb_customtweettext":
				echo'<textarea name="'.$meta_box['name'].'_value" id="'.$meta_box['name'].'_value" value="true" cols="60" rows="3"
					onKeyDown="wptb_textCounter()" onKeyUp="wptb_textCounter()" onclick="wptb_chooseRBTN();" />'; 
				echo $meta_box_value;
				echo'</textarea><br />';
				echo '<input readonly type="text" name="wptb_ctr" size="3" maxlength="3" value="'.(140 - strlen($meta_box_value)).'"> characters left <br>';
			break;		
		}
	} 
}
function create_wptb_meta_box() {  
if ( function_exists('add_meta_box') ) {  
	add_meta_box( 'wptb-meta-boxes', 'WP Tweetbox Settings', 'wptb_meta_boxes', 'page', 'normal', 'high' );  
	add_meta_box( 'wptb-meta-boxes', 'WP Tweetbox Settings', 'wptb_meta_boxes', 'post', 'normal', 'high' );  
	}  
}  

function save_wptb_postdata( $post_id ) {  
global $post, $wptb_meta_boxes;  
  
foreach($wptb_meta_boxes as $meta_box) {  
if ( !wp_verify_nonce( $_POST[$meta_box['name'].'_noncename'], plugin_basename(__FILE__) )) {  
return $post_id;  
}  
  
if ( 'page' == $_POST['post_type'] ) {  
if ( !current_user_can( 'edit_page', $post_id ))  
return $post_id;  
} else {  
if ( !current_user_can( 'edit_post', $post_id ))  
return $post_id;  
}  
  
$data = $_POST[$meta_box['name'].'_value'];  
  
if(get_post_meta($post_id, $meta_box['name'].'_value') == "")  
add_post_meta($post_id, $meta_box['name'].'_value', $data, true);  
elseif($data != get_post_meta($post_id, $meta_box['name'].'_value', true))  
update_post_meta($post_id, $meta_box['name'].'_value', $data);  
elseif($data == "")  
delete_post_meta($post_id, $meta_box['name'].'_value', get_post_meta($post_id, $meta_box['name'].'_value', true));  
}  
}  
add_action('admin_menu', 'create_wptb_meta_box');  
add_action('save_post', 'save_wptb_postdata');   
?>