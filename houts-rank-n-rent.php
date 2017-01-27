<?php
/*
Plugin Name: Houts Rank n Rent Lightbox
Plugin URI: http://alanleenhouts.com/plugins/rank-n-rent-lightbox/
Description: Allows you to lightbox sites over site. Works on all operating systmes and display sizes.
Version: 0.1
Author: Alan Leenhouts
Author URI: http://alanleenhouts.com
License: GPLv2
Text Domain: houts_rnr
*/
if ( ! defined ( 'ABSPATH' ) ) {
	exit;	
}
/* Settings page */
add_action('admin_menu','houts_lightbox_admin');
function houts_lightbox_admin () {
	add_options_page('Houts RnR Lightbox', 'Houts Lightbox', 'manage_options', __file__, 'houts_rnr_admin');
}

/* include files */


/* Add the meta box */

function houts_url_input() {
		$houtsrnr = __('Houts RnR Lightbox', 'houts-rnr');
		$screens = get_option('ppath_types_allowed', array('post', 'page'));
		foreach ( $screens as $screen ) {
			add_meta_box( 
					'per-page-houts',
					$houtsrnr,
					'houtscallback',
					$screen,
					'side',
					'high',
					null );
		}
}
add_action ('add_meta_boxes', 'houts_url_input');


/* Callback */
function houtscallback($post){
	wp_nonce_field( 'houtscallback', 'houtscontent' );
	$value = get_post_meta( $post->ID, 'houts-content', true );
	echo '<label for="per-page-houts">';
		_e( "Insert URL to lightbox", 'per-page-houts' );
	echo '</label><br/> ';
	echo '<input id="houts_urlinput" style="width:100%; min-height:20px; white-space: pre-wrap;" name="per-page-houts">'.str_replace('%BREAK%', "\n",stripslashes_deep(esc_attr($value))).'</input>';
}


// save meta data


function houtsurl_save_postdata( $post_id ) {
  if ( ! isset( $_POST['houtscontent'] ) )
    return $post_id;
  $nonce = $_POST['houtscontent'];
  if ( ! wp_verify_nonce( $nonce, 'houtscallback' ) )
      return $post_id;
  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
      return $post_id;
  if ( 'page' == $_POST['post_type'] ) {
    if ( ! current_user_can( 'edit_page', $post_id ) )
        return $post_id; 
  } else {
    if ( ! current_user_can( 'edit_post', $post_id ) )
        return $post_id;
  }
  
  /* OK, its safe for us to save the data now. */

  // Sanitize user input.
  //esc_sql is doing funky things with line breaks, so taking them out and putting them back in
  $mydata = str_replace('%BREAK%', "\r\n", esc_sql(str_replace(array("\r\n", "\r", "\n"), '%BREAK%',$_POST['per-page-houts']) ));
  // Update the meta field in the database.
  update_post_meta( $post_id, 'houts-content', $mydata );
}
add_action( 'save_post', 'houtsurl_save_postdata' );

function houtslightbox_display(){
	$pageid = get_queried_object_id();
	$posthoutsboxcontent = get_post_meta( $pageid, 'houts-content', true );
	if(!empty($posthoutsboxcontent)){
		wp_enqueue_style( 'lightboxload', plugins_url( '/css/lightboxload.css', __FILE__ ) );
		echo "<div class=\"is-ios box-cover\"><iframe class=\"cover-frame\" src=\"$posthoutsboxcontent\"></iframe></div>\n";
	}
}

add_action('wp_head', 'houtslightbox_display', 1000);
?>