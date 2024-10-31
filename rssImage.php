<?php
/*
Plugin Name: RSS Add Images to Post
Plugin URI: http://www.brainphp.com/wordpress-plugins/rss-add-image-post/
Description: This plugin will add the first image from the current post to your RSS feed. This way, your feed should have an image for every post, which will make your RSS easier & more fun to read (Mashable & LifeHacker for example use this technique).
Version: 1.3
Author: Leonid Shalimov
Author URI: http://brainphp.com/
*/

$post_image_options = array(
		'rss_excerpt_length' => 600,
		'rss_add_image_url' => 'http://cdn.iconfinder.net/data/icons/pleasant/JPEG-Image.png',
		'rss_strip_tags' => '<strong><b><p><br><a>',
		'rss_img_align' => 'left',
		'rss_read_more' => 'Read more..'
	    	);

add_option('rss_add_image_setting', $post_image_options);
add_action('admin_menu', 'rss_add_image_menu');

function rss_add_image_menu()
{
    add_submenu_page('options-general.php', 'Edit RSS Post Images', 'RSS Post Images', 'administrator', 'rss-post-edit', 'rss_add_image_options');
    add_action('admin_init', 'register_options_add_image');
}

function register_options_add_image()
{
    register_setting('rss-post-image-setting', $post_image_options);
}

function rss_add_image_options() 
{
    if( isset($_POST['info_update']) ) 
    {
        $new_options = $_POST['options'];
        update_option('rss_add_image_setting', $new_options);
        echo '<div id="message" class="updated fade"><p><strong>' . __('RSS Feed Settings SAVED...') . '</strong></p></div>';
    }
    $def_options = get_option('rss_add_image_setting');
    ?>
	<div class="wrap">
	<h2>RSS Post Image Options</h2>
	<form method='POST'>
 	   <?php settings_fields('rss-post-image-setting'); ?>
 	   <table class='form-table'>
		<tr>
              	  <th scope='row'>Current Default Image <br />(if no image is found in post)</th>
              	  <td><img src="<? esc_attr_e($def_options['rss_add_image_url']) ?>" border="1" /></td>
		</tr>
	        <tr valign='top'>
	          <th scope='row'>Default Image URL</th>
	          <td><input type='text' name='options[rss_add_image_url]' size='50' value='<?php esc_attr_e($def_options['rss_add_image_url']) ?>' /></td>
 	        </tr>
	        <tr valign='top'>
	          <th scope='row'>Article Excerpt Length<br />(default: 600)</th>
	          <td><input type='text' name='options[rss_excerpt_length]' size='50' value='<?php esc_attr_e($def_options['rss_excerpt_length']) ?>' /></td>
 	        </tr>
	        <tr valign='top'>
	          <th scope='row'>Article Tags Allowed<br />(default: &lt;strong&gt;&lt;b&gt;&lt;p&gt;&lt;br&gt;&lt;a&gt;)</th>
	          <td><input type='text' name='options[rss_strip_tags]' size='50' value='<?php esc_attr_e($def_options['rss_strip_tags']) ?>' /></td>
 	        </tr>
	        <tr valign='top'>
	          <th scope='row'>Read More Text<br />(default: Read more...)</th>
	          <td><input type='text' name='options[rss_read_more]' size='50' value='<?php esc_attr_e($def_options['rss_read_more']) ?>' /></td>
 	        </tr>
 	   </table>
           <input type='hidden' name='options[rss_img_align]' value='left' />
       <p class='submit'>
       <input type='submit' class='button-primary' name='info_update' value='<?php _e('Update Options', 'options') ?>' />
       </p>
</form>
</div>
<?
}

if( !function_exists( 'rss_add_image' ) ) {
	function rss_add_image()
	{
		global $post;
		$options   = get_option('rss_add_image_setting');

		// Set output to first image in post (just URL)
		$output    = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches);
		$first_img = $matches [1] [0];

		// Shorten the article
		$article   = substr($post->post_content, 0, $options['rss_excerpt_length']);

		// Trim to deal with unexpected whitespaces & strip tags to deal with first image unaligned output
		$article   = trim($article)."...";
		$article   = strip_tags($article, $options['rss_strip_tags']);

		if(empty($first_img))
		{
			// Sets a default image to display if no image found in post
			$first_img = $options['rss_add_image_url'];
		}

		// Sets image properties for display purposes
		$first_img = '<a href="'.get_permalink($post->id).'" alt="'.$post->post_title.'"><img src="'.$first_img.'" align="'.$options['rss_img_align'].'" alt="'.$post->post_title.'" hspace="5" vspace="5" border="0" /></a>';

		// Must use print() for displaying in feed, substr to cut out a trailing character
		$first_img = print $first_img;

		// Read more link
		$readmore  = ' <a href="'.get_permalink($post->id).'">'.$options['rss_read_more'].'</a>';

		$content = substr($first_img, 0, -1).$article.$readmore;
		return $content;
	}
	//add_filter('the_content', 'rss_add_image', 10);
	add_filter('the_excerpt_rss', 'rss_add_image', 10);
}
?>
