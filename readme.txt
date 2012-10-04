=== Plugin Name ===
Contributors: andrex84
Tags: comments, comment, video, annotation, youtube, shortcode, bookmarking, commenting, annotating, annotate, note
Requires at least: 3.4.2
Tested up to: 3.4.2
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The Live Video Annotation plugin allows you to add timed footnotes to a YouTube video. Visitors can see these notes later while watching the video.

== Description ==

The Live Video Annotation plugin allows you to add timed footnotes to a YouTube video while you are watching the video. Visitors can see these notes later while watching the video. It's really easy to use and the interface is designed as if you were chatting away.

It can be used for:

*	Add notes to video lectures.
*	Give translations to a video.
*	Add bookmarks to a video.
*	Easily add links to external resources mentioned in a video.
*	Create your own Pop-Up Video.
*	Whatever you can think of….

== Installation ==

1. Upload `videoannotations` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Place the shortcode `[videoannotation video_id='VvcohzJvviQ']` in your posts or page. The video_id can be changed to the YouTube ID of the video of your choice.

= Options =

You can adjust the time the note will be visible by adding the expire_time variable to the shortcode. 

I you want the note to be visible for 5 seconds, you can use the following shortcode:

`[videoannotation video_id='VvcohzJvviQ' expire_time='5']`

If you don't want the note to disappear you can set expite_time to 0 (zero):

`[videoannotation video_id='VvcohzJvviQ' expire_time='0']`

It is also possible to set the dimensions of your video by adding a width and height variable:

`[videoannotation video_id='VvcohzJvviQ' expire_time='0' width='500' height='250']`

= Annotating a video =

Make sure you are logged in to WordPress and visit the post where you added the video. Start playing the video and type your notes in the field below the video. To submit your note you can press [enter] or click on the annotate button.

== Frequently Asked Questions ==

= Can I add links? =

Yes you can! Just copy the whole hyperlink from your browser into the comment field. Be sure to include the http:// part, so we know it’s a link. We’ll take care of the rest.

= How can I delete a video annotation =

To delete comments you need to go into the the Admin area of your WordPress site and go to the Comments page. There you can see all the comments, which you can delete. 

It's not the most convinient way, but this will be corrected in later versions of the plugin.

== Screenshots ==

1. Live Video Annotation Interface

== Changelog ==

= 1.0 =

Hello World