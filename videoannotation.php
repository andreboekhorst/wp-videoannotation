<?php

/*
Plugin Name: Live Video Annotation 
Plugin URI: http://andreboekhorst.nl/wordpress/live-video-annotation-plugin/
Description: The Live Video Annotation plugin allows logged-in users to annotate videos when they are watching them. These notes will be visible for later visitors and shown on the same timespot. Great to add notes to lectures, video walkthroughs or translations to a video. Add the following shortcode in your post: [videoannotation video_id='YOUTUBE_CODE_HERE']  
Author: Andre Boekhorst
Version: 1.0
Author URI: http://www.andreboekhorst.nl
License: GPLv2 or later
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

define('VIDEOANNOTATION_VERSION', '0.0.2');
define('VIDEOANNOTATION_PLUGIN_URL', plugin_dir_url( __FILE__ ));

/**  
 * Post Types 
 * All Annotatable video's will get a new post where all the comments are connected to.
 **/

register_post_type('annotated_video', array(
	'label' => __('Annotated_videos','fuseki_theme'),
	'singular_label' => __('Annotated_video','fuseki_theme'),
	'public' => true,
	'show_ui' => true,
	'capability_type' => 'post',
	'hierarchical' => false,
	'rewrite' => false,
	'show_in_menu' => false,
	'query_var' => false,
	'supports' => array('title')
));



/**  
 * Create Shortcode
 **/
 
 function videoannotation( $atts ) {
// [video_annotation video_id="VvcohzJvviQ" expire_time='10']

	extract( shortcode_atts( array(
		'video_id' 		=> 'VvcohzJvviQ', 			//Peaches, by the Presidents of the USA
		'expire_time' 	=> '10',					//comments never expire -> Still to be implemented.
		'width' 		=> '640',
		'height' 		=> '390',
	), $atts ) );

	if( !is_numeric( $width ) ) $width = 640;
    if( !is_numeric( $height ) ) $height = 390;
    
	$videopost = get_posts( array( 'meta_key' => 'video_id', 'meta_value' => $video_id, 'post_type' => 'annotated_video', ) );
	if( empty( $videopost[0]->ID ) ){
		
		//If it doesnt exist add post
		$post = array(
		  'comment_status' => 'open', 				// 'closed' means no comments.
		  'post_name' => $video_id, 				// The name (slug) for your post
		  'post_status' => 'publish', 				//Set the status of the new post. 
		  'post_title' => $video_id, 				//The title of your post.
		  'post_type' => 'annotated_video', 		//You may want to insert a regular post, page, link, a menu item or some custom post type
		); 

		$video_post_id = wp_insert_post( $post ); 		
		add_post_meta( $video_post_id, 'video_id', $video_id);

	} else {
		$video_post_id = $videopost[0]->ID;
	}
	
		

	
	
	
	
	
	
	
	/* Output */
	
	ob_start(); //output bufer
	
	
	$annotated_video_holder = 	'<!-- WP Live Video Annotation Plugin -->';

	$annotated_video_holder .=	'<div class="annotated_video_holder ' . $video_id . '" ID="holder_' . $video_id . '" style="width:'. $width .'px">';	
	$annotated_video_holder .= 	'<div id="' . $video_id . '"></div>';
	$annotated_video_holder .=  '<div class="video_code_holder" style="display:none">' . $video_id . '</div>';
	$annotated_video_holder .=  '<div class="expire_time_holder" style="display:none">' . $expire_time . '</div>';
	$annotated_video_holder .=  '<div class="video_width_holder" style="display:none">' . $width . '</div>';
	$annotated_video_holder .=  '<div class="video_height_holder" style="display:none">' . $height . '</div>';
	
	$annotated_video_holder .= '<div class="annotated_video_form_holder">';
	
	// Theinput field
	if ( is_user_logged_in() ) {
		
		global $current_user;
		get_currentuserinfo();
		
		$comments_args = array(
		'comment_field'        	=> '<textarea class="annotated_video_comment_field" name="comment" cols="45" rows="8" aria-required="true">type here...</textarea>
									<input type="hidden" name="videoannotation_timestamp" class="videoannotation_timestamp" value="0" />
									<input type="hidden" name="authorname" class="authorname" value="' . $current_user->display_name .'" />
									<input type="hidden" name="video_id" class="video_id" value="' . $video_id .'" />',
									//Authorname is not saved in DB, but used by javascript to add comment to the list (without AJAX reload)							
		'title_reply'			=>	'',
		'logged_in_as'			=> '',
		'comment_notes_after'	=>	'',
		'comment_notes_before'	=> '',
		'id_form'              	=> 'annotated_video_form' . $video_id,
		'label_submit'         	=> __('Annotate'),

		);
		
		ob_start(); //output buffer
			comment_form( $comments_args , $video_post_id );
			$comment_form = ob_get_contents();
		ob_end_clean();

		$comment_form = str_replace( 'id="respond"','id="LVA_FORM"' ,  $comment_form); //Remove annoying #respond for CSS
		$annotated_video_holder .= trim($comment_form);
	} 
	
	// The Comments
	$annotated_video_holder .= '<div class="annotated_video_comments_holder">';	 
	$annotated_video_holder .= '<div class="comment_wrapper">';
	$comments = get_comments( array( 'post_id' => $video_post_id ) );
	
	$comments = array_reverse($comments);
	foreach($comments as $comment) :
	
		$timestamp = get_comment_meta( $comment->comment_ID, 'videoannotation_timestamp', true );
		
		$comment_filtered = hyperlink_filter( $comment->comment_content );
		$annotated_video_holder .= '<div class="videoannotation timestamp-' . $timestamp . '"><span class="author">' . $comment->comment_author . '</span><span class="comment">' . $comment_filtered . '</span></div>';
		
	endforeach;
	$annotated_video_holder .= '</div>';
	$annotated_video_holder .= '</div>';
	$annotated_video_holder .= '</div>'; //<!-- #annotated_video_form_holder -->';
	$annotated_video_holder .= '</div>'; //<!-- #annotated_video_holder -->';
	
	wp_reset_query(); //Needed to make sure the comment field of the 'normal' post doesn't refer to the 'annotated video' post.
	
	return $annotated_video_holder;
	
}
add_shortcode( 'videoannotation', 'videoannotation' );
 



/* http://css-tricks.com/snippets/php/find-urls-in-text-make-links/ */
function hyperlink_filter( $post_content ){
	$reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
	
	
	if(preg_match($reg_exUrl, $post_content, $url)){
		   $text = preg_replace($reg_exUrl, '<a href="' . $url[0] . '" target="_blank">' . $url[0] . '</a>', $post_content);
	} else {
		$text = $post_content;
	}
	return $text;

}




add_action('comment_post', 'add_timestamp_field');

function add_timestamp_field($comment_id){
	$comment_timestamp       = ( isset($_POST['videoannotation_timestamp']) )  ? trim(strip_tags($_POST['videoannotation_timestamp'])) : null;
  	add_comment_meta($comment_id, 'videoannotation_timestamp', $comment_timestamp );
}




// comment text

add_filter( 'comment_text', 'modify_commenttext');

function modify_commenttext( $text ){

	$timestamp = get_comment_meta( get_comment_ID(), 'videoannotation_timestamp', true );
	if( is_numeric( $timestamp ) ) {
		$text = '<h4>' . $timestamp . '</h4>' . $text;		
	} 
	
	return $text;

}



// Check if shortcode exists in post...
// http://codex.wordpress.org/Function_Reference/get_shortcode_regex


function your_prefix_detect_shortcode()
{

    global $wp_query;	
    $posts = $wp_query->posts;
    $pattern = get_shortcode_regex();
    
    
    foreach ($posts as $post){
		if (   preg_match_all( '/'. $pattern .'/s', $post->post_content, $matches )
			&& array_key_exists( 2, $matches )
			&& in_array( 'videoannotation', $matches[2] ) )
		{
	
			wp_enqueue_script( 'jquery-forms', VIDEOANNOTATION_PLUGIN_URL.'/js/jquery.form.js', array('jquery'), '0.0.1');
			wp_enqueue_script( 'videoannotation-script', VIDEOANNOTATION_PLUGIN_URL.'/js/videoannotation.js', array('jquery'), '0.0.1');
			
			wp_register_style( 'videoannotation-css', VIDEOANNOTATION_PLUGIN_URL.'/css/videoannotation.css', array(), '20120929', 'all' );  
			wp_enqueue_style( 'videoannotation-css' );
			break;
			
		}    
    }

}
add_action( 'wp', 'your_prefix_detect_shortcode' );


?>