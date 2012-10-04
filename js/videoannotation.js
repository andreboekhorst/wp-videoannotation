jQuery(document).ready(function() {	

	// Check if there is there is a shortcode for the video in the content
	if( jQuery('.annotated_video_holder').length > 0){
			loadYoutube();
	}
	
});
 
/**
 *	Embedding YouTube using their API - https://developers.google.com/youtube/iframe_api_reference
 */


function loadYoutube(){

	var tag = document.createElement('script');
 	tag.src = "//www.youtube.com/iframe_api";
  	var firstScriptTag = document.getElementsByTagName('script')[0];
  	firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
  	
}
 
  
function onYouTubeIframeAPIReady() { 

 	var players = {};
 
	if( jQuery('.annotated_video_holder').length > 0){	
		jQuery('.annotated_video_holder').each(function(index) {
			
			var video_code 		=	jQuery('.video_code_holder', this ).text();	
			var video_width 	=	parseInt( jQuery('.video_width_holder', this ).text() );
			var video_height	=	parseInt( jQuery('.video_height_holder', this ).text() );
			var playerID 		=	video_code;
		
			players[ video_code ] = new YT.Player( playerID , {
			  height: 275,
			  width: video_width,
			  videoId: video_code,
			  playerVars: { 'modestbranding': 1, 'showinfo': 0, 'rel': 0, 'theme': 'light' },
			  events: {
				'onReady': onYouTubePlayerReady			//The Forms will be initialised.
			  }
			});
		
			initForm( jQuery(this) );
			
		});

	}
}


function onYouTubePlayerReady(event){ 
	
	 var iFrame = event.target.getIframe();			// getIframe() is a Youtube API Function
	 var video_id = jQuery( iFrame ).attr('id'); 	// IFrame ID is similar to the video code.
	 var annotated_video_holder_id = '#holder_' + video_id;
   	 var expire_time =  jQuery( '.expire_time_holder', annotated_video_holder_id ).text();
   
   	jQuery('.annotated_video_comments_holder .loading_msg', annotated_video_holder_id ).fadeOut('200');
	
   	// The Heartbeat of the whole plugin.
	 setInterval(function() {

		var player_time = Math.round( event.target.getCurrentTime() );
		jQuery('.videoannotation_timestamp', annotated_video_holder_id).val( player_time );
		updatecomments( annotated_video_holder_id, player_time, expire_time );
		
	}, 1000);
	
}





function initForm( annotated_video_holder ){
	jQuery('.annotated_video_form_holder', annotated_video_holder).show();
	jQuery('.videoannotation', annotated_video_holder).hide();	
	jQuery('.annotated_video_comments_holder .comment_wrapper', annotated_video_holder).prepend('<span class="loading_msg"><!--Loading Video Annotations...--></span>');
	
	var options = { 
		beforeSubmit:  	ajaxBeforeSubmit,
		success: 		ajaxSucces,
		error:			function(){ ajaxError( annotated_video_holder ) }
	};
	
	jQuery('.annotated_video_comment_field', annotated_video_holder).text('type here...');
	jQuery( '.annotated_video_comment_field', annotated_video_holder ).focus(function(){
		if( jQuery('.annotated_video_comment_field', annotated_video_holder ).text() == 'type here...' ){
        		jQuery('.annotated_video_comment_field', annotated_video_holder).text('');
        }
        });

	
	
	jQuery( 'form', annotated_video_holder ).submit( function() { 
	
		if( jQuery('.annotated_video_comment_field', this).val().length > 0 ){
			if( jQuery('.annotated_video_comment_field', this).val() != 'type here...' ){
        		jQuery(this).ajaxSubmit(options);  // Making use of the Ajax-Form Plugin: http://www.malsup.com/jquery/form/#options-object
        	}
        }
 		return false; 
 		
    }); 
    
    jQuery( '.annotated_video_comment_field', annotated_video_holder ).keydown(function(event) {
    	
		if (event.which == 13) {
			if( jQuery(this).val().length > 0 ){
				jQuery(this).closest('form').submit();
			}
			return false;
		}
    });
    
}


function updatecomments( annotated_video_holder_id, time, expire_time ){

	jQuery('.videoannotation', annotated_video_holder_id ).each(function() {
		
		var classes = jQuery(this).attr('class').split(' ');
		
		for (var i = 0; i < classes.length; i++) {
		  var comment_time = /^timestamp\-(.+)/.exec(classes[i]);
		  
		  if (comment_time != null) {
	
			comment_time[1] = parseInt( comment_time[1] ); //force it to be an integer.
			expire_time = parseInt( expire_time );
			
			if ( jQuery(this).hasClass('comment_pending') ){
				jQuery( this ).hide();
			} else if( time >= comment_time[1] && expire_time == 0 ){ 		//doesn't hide when expire_time is set to 0
				jQuery( this ).show();
			} else if( time >= comment_time[1] && (comment_time[1] + expire_time) > time  ){ //hide comment when outside expire_time margin.
				jQuery( this ).fadeIn(50);
			} else {
				jQuery( this ).slideUp(150);
			}		
			
		  }
		}
	});
}


 
/**
 *	Making use of the Ajax-Form Plugin: http://www.malsup.com/jquery/form/#options-object
 */

function ajaxSucces(responseText, statusText, xhr, form){
	
	var formData = form.formToArray(); 

	// Convert variables to simple array.	
	var commentData = [];	
	for (var i=0; i < formData.length; i++) { 
		var name = formData[i].name;
		commentData[ name ] = formData[i].value;
	}
	
	// Add to page (note that this doesnt really use any Ajax).
	addCommentToList( commentData );	
	
	// Reset Form.
	form.clearForm();				// Clear form
	switchFormEdit( jQuery('.annotated_video_comment_field', form) ); // Make field editable
	console.log('comment posted');

}

function ajaxBeforeSubmit( formData, jqForm, options ){
	switchFormEdit( jQuery('.annotated_video_comment_field', jqForm) ); //Doesnt submit data if disabled.... so doesnt work. 
}

function ajaxError( holder ){

	console.log('Something happened... and the form did not submit');
	jQuery('.comment_wrapper', holder ).prepend('<span class="error_msg">Oops, something went wrong. Please try again.</span>');
	jQuery('.comment_wrapper .error_msg', holder ).delay(2000).fadeOut(400);		
	switchFormEdit( jQuery('.annotated_video_comment_field', holder) );
	
}

function switchFormEdit( element ){

	//Enables / Disables editing the form. Can be used when the Ajax request is pending.	
	if( element.attr('readonly') ){
		element.removeAttr('readonly');
		element.removeClass('readonly');
	} else {
		element.attr('readonly', 'true');
		element.addClass('readonly');
	}
	
}

function addCommentToList( commentData ){
	
	// Add HTML elements to the comment list already. The class 'comment_pending' prevents it from being visible straight away. 
	// On succes this class will be removed.
	
	commentData['comment'] = replaceURLWithHTMLLinks( commentData['comment'] );
	$commentHTML = 	'<div class="videoannotation timestamp-' + commentData['videoannotation_timestamp'] + '">';
	$commentHTML += '<span class="author">' + commentData['authorname'] + '</span>';
	$commentHTML +=	'<span class="comment">' + commentData['comment'] + '</span></div>';
	
	
	
	targetElement = '#holder_' + commentData['video_id'] + ' .annotated_video_comments_holder .comment_wrapper';
	jQuery( targetElement ).prepend( $commentHTML ); 
	
}

function replaceURLWithHTMLLinks(text) {
    var exp = /(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig;
    return text.replace(exp,'<a href="$1" target="_blank">$1</a>'); 
}




	
