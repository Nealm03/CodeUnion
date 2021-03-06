<?php

/**
 * LOAD COMMONLY USED TEMPLATE TOOLS
 */
add_action('wp_cta_init', 'inbound_include_template_functions');
if (!function_exists('inbound_include_template_functions'))
{
	function inbound_include_template_functions()
	{
		include_once(WP_CTA_PATH.'shared/functions.templates.php');
	}
}


/**
 * LOAD CORRECT CTA TEMPLATE ON FRONTEND
 */

add_filter('single_template', 'wp_cta_custom_template');

function wp_cta_custom_template($single) {
    global $wp_query, $post, $query_string;

	$template = get_post_meta($post->ID, 'wp-cta-selected-template', true);
	$template = apply_filters('wp_cta_selected_template',$template);

	if (isset($template))
	{
		//echo 2;exit;
		if ($post->post_type == "wp-call-to-action")
		{
			if (strstr($template,'-slash-'))
			{
				$template = str_replace('-slash-','/',$template);
			}

			$my_theme =  wp_get_theme($template);

			if ($my_theme->exists())
			{
				return "";
			}
			else if ($template!='default')
			{
				$template = str_replace('_','-',$template);
				//echo WP_CTA_URLPATH.'templates/'.$template.'/index.php'; exit;
				if (file_exists(WP_CTA_PATH.'templates/'.$template.'/index.php'))
				{
					//query_posts ($query_string . '&showposts=1');
					return WP_CTA_PATH.'templates/'.$template.'/index.php';
				}
				else
				{
					//query_posts ($query_string . '&showposts=1');
					return WP_CTA_UPLOADS_PATH.$template.'/index.php';
				}
			}
		}
	}
    return $single;
}

/**
 * APPLY CTA CUSTOM JS AND CUSTOM CSS TO FRONT END
 */
add_action('wp_head','wp_call_to_actions_insert_custom_head');
function wp_call_to_actions_insert_custom_head() 
{
	global $post;

   if (isset($post)&&'wp-call-to-action'==$post->post_type)
   {
		//$global_js =  htmlspecialchars_decode(get_option( 'wp_cta_global_js', '' ));
		$global_record_admin_actions = get_option( 'wp_cta_global_record_admin_actions', '0' );

		$custom_css_name = apply_filters('wp-cta-custom-css-name','wp-cta-custom-css');
		$custom_js_name = apply_filters('wp-cta-custom-js-name','wp-cta-custom-js');
		//echo $custom_css_name;
		$custom_css = get_post_meta($post->ID, $custom_css_name, true);
		$custom_js = get_post_meta($post->ID, $custom_js_name, true);
		//echo $this_id;exit;

		//Print Cusom CSS
		if (!stristr($custom_css,'<style'))
		{
			echo '<style type="text/css" id="wp_cta_css_custom">'.$custom_css.'</style>';
		}
		else
		{
			echo $custom_css;
		}
		if (!stristr($custom_css,'<script'))
		{
			echo '<script type="text/javascript" id="wp_cta_js_custom">jQuery(document).ready(function($) {
			'.$custom_js.' });</script>';
		}
		else
		{
			echo $custom_js;
		}

		if ($global_record_admin_actions==0&&current_user_can( 'manage_options' ))
		{
		}
		else
		{

			if (!wp_cta_determine_spider())
			{
				//wp_cta_set_page_views(get_the_ID($this_id));
			}
		}

		//rewind_posts();
		//wp_reset_query();
   }
}


/* Import Global CSS Overrides */
add_action('wp_footer', 'wp_cta_load_global_css');
function wp_cta_load_global_css(){
global $post;
if (!isset($post))
	return;
	if ($post->post_type=='wp-call-to-action')
	{
		$global_css = get_option( 'wp-cta-main-global-css');
		if ($global_css != "") {
			echo "<style id='global-cta-styles' type='text/css'>";
			echo $global_css;
			echo "</style>";
		}
	}
}

/* Add support for core Pop-Up Displays */

add_action('wp_footer', 'wp_cta_load_popup');
function wp_cta_load_popup() {
	global $post;
	
	if (!isset($post))
		return;
		
	global $wp_query;
	
	$current_page_id = $wp_query->get_queried_object_id();
	$wp_cta_placement = get_post_meta($current_page_id, 'wp_cta_content_placement', true);
	
	/* Cookie Options
	$popup_delay = get_post_meta($post->ID, 'wp_cta_content_placement');
	$popup_show_number = get_post_meta($post->ID, 'wp_cta_content_placement');
	$global_popup_settings = get_post_meta($post->ID, 'wp_cta_content_placement');
	*/
	
	if (isset($post)&&$post->post_type !=='wp-call-to-action' && $wp_cta_placement == 'slideout')  
	{
		echo '<style type="text/css">
			#wp_cta_box{bottom:5px;width:360px;right:5px;display:block;right:-415px;display:block;}
 			</style>
 			<div id="wp_cta_box" class="simple wp-cta-slideout" style="right: -415px;">
 			<a id="wp_cta_close" href="#" rel="close">Close</a>
 			<iframe id="wp-cta-per-page" class="wp-cta-display wp-cta-slideout" src="" scrolling="no" frameborder="0" style="border:none; overflow:hidden; display:none;" allowtransparency="true"></iframe>
 			</div>
 			';
	}
	
	if (isset($post)&&$post->post_type !=='wp-call-to-action' && $wp_cta_placement == 'popup')  
	{ 
	?>
	<script type="text/javascript">
     jQuery(document).ready(function($) {
     	var the_pop_id = "wp_cta_" + jQuery("#cta-popup-id").text();
     	var global_cookie = jQuery.cookie("wp_cta_global");
     	var local_cookie = jQuery.cookie(the_pop_id);
     	var c_length = parseInt(wp_cta_popup.c_length);
     	var g_length = parseInt(wp_cta_popup.global_c_length);
     	var page_view_theshold = parseInt(wp_cta_popup.page_views);
     	var page_view_count = countProperties(pageviewObj);
     	var show_me = true;
     	if (wp_cta_popup.c_status === 'yes' && local_cookie === 'true') {
     		console.log('Popup halted by local cookie');
     		var show_me = false;
     		return false;
     	}
     	if (page_view_theshold > page_view_count) {
     		console.log('Popup halted by not enough page views');
     		var show_me = false;
     		return false;
     	}
     	// global settings show only once on and cookie exists turn off
     	if (wp_cta_popup.global_c_status == 1 && global_cookie === 'true') {
			console.log('Popup halted by global settings show only once on');
			var show_me = false;
			return false;
     	}

     	// Popup rendering
     	if (show_me === true){
		        $('.popup-modal').magnificPopup({
		          type: 'inline',
		          preloader: false
		          // modal: true // disables close
		        });

		       	setTimeout(function() {
		       			var parent = $('#wp-cta-popup').parent().width();
		       			$('.wp_cta_popup').attr('data-parent', parent);
		       			$(".white-popup-block").addClass("cta_wait_hide");
		                $("#wp-cta-popup").show();
		                $('.popup-modal').magnificPopup('open');
		                jQuery.cookie(the_pop_id, true, { path: '/', expires: c_length });
		                jQuery.cookie("wp_cta_global", true, { path: '/', expires: g_length });

		        }, wp_cta_popup.timeout);
	       }
	        $(document).on('click', '.popup-modal-dismiss', function (e) {
	          e.preventDefault();
	          $.magnificPopup.close();
	        });

      });
    </script>
    <span id="cta-popup-id"></span>
    <style type="text/css">
    #cta-no-show, #the-popup-id, #cta-popup-id {
    	display: none !important;
    }
    #wordpress-cta {
    	text-align: center;
    }
	.white-popup-block {
		background: #FFF;
		padding: 0px 0px;
		text-align: left;
		max-width: 750px;
		margin: 40px auto;
		position: relative;
	}
	.cta_wait_hide {
		display: none !important;
	}
	.mfp-close {
		color:red;
	}
	</style>
	<?php 
	} 
}

/* Add listener to check to render cta in full screen mode */
add_filter('admin_url','wp_cta_add_fullscreen_param');
function wp_cta_add_fullscreen_param( $link )
{
	if (isset($_GET['page']))
		return $link;

	if (  ( isset($post) && 'wp-call-to-action' == $post->post_type ) || ( isset($_REQUEST['post_type']) && $_REQUEST['post_type']=='wp-call-to-action' ) )
	{
		$params['frontend'] = 'false';
		if(isset($_GET['frontend']) && $_GET['frontend'] == 'true') {
	        $params['frontend'] = 'true';
	    }
	    if(isset($_REQUEST['frontend']) && $_REQUEST['frontend'] == 'true') {
	        $params['frontend'] = 'true';
	    }
	    $link = add_query_arg( $params, $link );

	}

	return $link;
}


/* Not sure what this does -  Needs documenting */
add_action('wp_head', 'wp_cta_header_load');
function wp_cta_header_load(){
	global $post;
	if (isset($post)&&$post->post_type=='wp-call-to-action')
	{
		wp_enqueue_style('cta-wordpress-base-css', WP_CTA_URLPATH . 'css/frontend/global-cta-style.css');
		if (isset($_GET['wp-cta-variation-id']) && !isset($_GET['cta-template-customize']) && !isset($_GET['iframe_window']) && !isset($_GET['live-preview-area'])) 
		{ 
		?>
		<script type="text/javascript">
		if (typeof window.history.pushState == 'function') {
		var current=window.location.href;var cleanparams=current.split("?");var clean_url=cleanparams[0];history.replaceState({},"landing page",clean_url);
		//console.log("push state supported.");
		}
		var trackObj = "";
		</script>

		<?php }
	}
}


function wp_cta_discover_important_wrappers($content)
{
	$wrapper_class = "";
	if (strstr($content,'gform_wrapper'))
	{
		$wrapper_class = 'gform_wrapper';
	}
	return $wrapper_class;
}

function wp_cta_rebuild_attributes($content=null, $wrapper_class=null, $standardize_form = 0)
{
	if (strstr($content,'<form'))
	{
		if ($standardize_form)
		{
			$tag_whitelist = trim(get_option( 'wp-cta-main-wp-call-to-action-auto-format-forms-retain-elements' , '<button><script><textarea><style><input><form><select><label><a><p><b><u><strong><i><img><strong><span><font><h1><h2><h3><center><blockquote><embed><object><small>'));
			$content = strip_tags($content, $tag_whitelist);

			if (!strstr($content,'<label')&&strstr($content,'<p'))
			{
				$content = str_replace('<p>','<label >',$content);
				$content = str_replace('</p>','</label>',$content);
				//echo $content; exit;
			}

			if (!strstr($content,'<label')&&strstr($content,'<span'))
			{
				$content = str_replace('<span','<label',$content);
				$content = str_replace('</span>','</label>',$content);
			}

			$form = preg_match_all('/\<form(.*?)\>/s',$content, $matches);
			if (!empty($matches[0]))
			{
				foreach ($matches[0] as $key=> $value)
				{
					$new_value = $value;
					$form_name = preg_match('/ name *= *["\']?([^"\']*)/i',$value, $name); // 1 for true. 0 for false
					$form_id = stristr($value, ' id=');
					$form_class = stristr($value, ' class=');

					($form_name) ? $name = $name[1] : $name = $key;

					/* We are breaking the ids here need to only fix/add classes
					if ($form_id)
					{
						$new_value = preg_replace('/ id=(["\'])(.*?)(["\'])/',' id="lp-form-'.$name.' $2"', $new_value);
					}
					else
					{
						$new_value = str_replace('<form ','<form id="lp-form-'.$name.'" ', $new_value);
					}
					*/
					if ($form_class)
					{
						$new_value = preg_replace('/ class=(["\'])(.*?)(["\'])/',' class="lp-form lp-form-track $2"', $new_value);
					}
					else
					{
						$new_value = str_replace('<form ','<form class="lp-form lp-form-track" ', $new_value);
					}

					$content = str_replace($value,$new_value,$content);
				}
			}

			// Standardize all Labels
			$inputs = preg_match_all('/\<label(.*?)\>/s',$content, $matches);
			if (!empty($matches[0]))
			{
				foreach ($matches[0] as $value)
				{
					$new_value = $value;
					// regex to match text in label /(?<=[>])[^<>]+(?=[<])/g
					(preg_match('/ for *= *["\']?([^"\']*)/i',$value, $for)) ? 	$for = $for[1] : $for = 'input';
					$for = str_replace(' ','-',$for);

					$new_value = preg_replace('/ id=(["\'])(.*?)(["\'])/','', $new_value);

					$new_value = preg_replace('/ class=(["\'])(.*?)(["\'])/','', $new_value);

					$new_value = str_replace('<label ','<label id="lp-label-'.$for.'" ', $new_value);
					$new_value = str_replace('<label ','<label class="lp-input-label" ', $new_value);
					//$new_value = str_replace('<label>','<label class="lp-select-heading"> ', $new_value); // fix select headings


					//$new_value  = "<div id='wp_cta_field_'
					$content = str_replace($value, $new_value, $content);
				}
			}

			/* Fix empty labels (aka select headings)
				$inputs = preg_match_all('/\<label(.*?)\>/s',$content, $matches);
				if (!empty($matches[0]))
				{
					foreach ($matches[0] as $value)
					{
						$new_value = str_replace('<label>','<p class="lp-select-heading">', $value);
						$new_value = str_replace('</label>','</p>', $new_value); // doesn't work
						$content = str_replace($value,$new_value, $content);
					}
				}
			*/
			// Standardize all input fields
			$inputs = preg_match_all('/\<input(.*?)\>/s',$content, $matches);
			if (!empty($matches[0]))
			{
				foreach ($matches[0] as $value)
				{
					$new_value = $value;
					//get input name
					(preg_match( '/ name *= *["\']?([^"\']*)/i', $new_value, $name )) ? $name = $name[1] : $name =	"button";

					// get input type
					(preg_match('/ type *= *["\']?([^"\']*)/i',$new_value, $type)) ? $type = $type[1] : $type = "text";


					// if class exists do this
					if (preg_match('/ class *= *["\']?([^"\']*)/i', $new_value, $class))
					{
						$new_value = preg_replace('/ class=(["\'])(.*?)(["\'])/',' class="lp-input-'.$type.'"', $new_value);
					}
					else
					{
						$new_value = str_replace('<input ','<input class="lp-input-'.$type.'" ', $new_value);
					}

					// if id exists do this
					if (preg_match('/ id *= *["\']?([^"\']*)/i', $new_value, $class))
					{
						$new_value = preg_replace('/ id=(["\'])(.*?)(["\'])/',' id="lp-'.$type.'-'.$name.'"', $new_value);
					}
					else
					{
						$new_value = str_replace('<input ','<input id="lp-'.$type.'-'.$name.'" ', $new_value);
					}

					$content = str_replace($value,$new_value, $content);
				}
			}


			// Standardize All Select Fields
			$selects = preg_match_all('/\<select(.*?)\>/s',$content, $matches);
			if (!empty($matches[0]))
			{
				foreach ($matches[0] as $value)
				{
					preg_match('/ name *= *["\']?([^"\']*)/i',$value, $name);
					$name = $name[1];

					$new_value = preg_replace('/ id=(["\'])(.*?)(["\'])/',' id="lp-select-'.$name.'"', $value);
					$new_value = preg_replace('/ class=(["\'])(.*?)(["\'])/',' class="lp-input-select"', $new_value);
					$content = str_replace($value,$new_value, $content);
				}
			}




			// Standardize All Select Fields
			$fields = preg_match_all("/\<label(.*?)\<input(.*?)\>/si",$content, $matches);
			if (!empty($matches[0]))
			{
				foreach ($matches[0] as $value)
				{
					//echo $value;exit;
					//echo "<hr>";
					(preg_match( '/Email|e-mail|email/i', $value, $email_input)) ? $email_input = "lp-email-value" : $email_input =	"";

					// match name or first name. (minus: name=, last name, last_name,)
					(preg_match( '/(?<!((last |last_)))name(?!\=)/im', $value, $first_name_input)) ? $first_name_input = "lp-first-name-value" : $first_name_input =	"";

					// Match Last Name
					(preg_match( '/(?<!((first)))(last name|last_name|last)(?!\=)/im', $value, $last_name_input)) ? $last_name_input = "lp-last-name-value" : $last_name_input =	"";



					$new_value  = "<div class='wp_cta_form_field $email_input $first_name_input $last_name_input'>".$value."</div>";

					$content = str_replace($value,$new_value, $content);
				}

			}



			// Fix All Span Tags
			$inputs = preg_match_all('/\<span(.*?)\>/s',$content, $matches);
			if (!empty($matches[0]))
			{
				foreach ($matches[0] as $value)
				{
					$new_value = preg_replace('/\<span(.*?)\>/s','<span class="lp-span">', $value);
					$content = str_replace($value,$new_value, $content);
				}
			}

			// Fix All <p> Tags
			$inputs = preg_match_all('/\<p(.*?)\>/s',$content, $matches);
			if (!empty($matches[0]))
			{
				foreach ($matches[0] as $value)
				{
					$new_value = preg_replace('/\<p(.*?)\>/s','<p class="lp-paragraph">', $value);
					$content = str_replace($value,$new_value, $content);
				}
			}

			//handle gform error messages
			if (strstr($content,'There was a problem with your submission. Errors have been highlighted below.'))
			{
				$content = preg_replace('/(There was a problem with your submission. Errors have been highlighted below.)/','<div class="validation_error">$1</div>', $content);
				$content = preg_replace('/(Please enter a valid email address.)/','<div class="gfield_description validation_message">$1</div>', $content);
				$content = preg_replace('/(This field is required.)/','<div class="gfield_description validation_message">$1</div>', $content);
			}
			//echo 1; exit;

			$content = str_replace('name="submit"','name="s"',$content);
			$content = "<div id='wp_cta_container_form'  class='$wrapper_class'>{$content}</div>";
		}
		else
		{
			$form = preg_match_all('/\<form(.*?)\>/s',$content, $matches);
			if (!empty($matches[0]))
			{
				foreach ($matches[0] as $key=>$value)
				{
					$new_value = $value;
					$form_name = preg_match('/ name *= *["\']?([^"\']*)/i',$value, $name); // 1 for true. 0 for false
					$form_id = stristr($value, ' id=');
					$form_class = stristr($value, ' class=');

					($form_name) ? $name = $name[1] : $name = $key;
						/* We are breaking the ids here need to only fix/add classes
					if ($form_id)
					{
						$new_value = preg_replace('/ id=(["\'])(.*?)(["\'])/',' id="lp-form-'.$name.' $2"', $new_value);
					}
					else
					{
						$new_value = str_replace('<form ','<form id="lp-form-'.$name.'" ', $new_value);
					}
						*/
					if ($form_class)
					{
						$new_value = preg_replace('/ class=(["\'])(.*?)(["\'])/',' class="lp-form lp-form-track $2"', $new_value);
					}
					else
					{
						$new_value = str_replace('<form ','<form class="lp-form lp-form-track" ', $new_value);
					}

					$content = str_replace($value,$new_value,$content);
				}
			}

			$check_wrap = preg_match_all('/wp_cta_container_form/s',$content, $check);
			if (empty($check[0]))
			{
				$content = str_replace('name="submit"','name="s"',$content);
				$content = "<div id='wp_cta_container_form' >{$content}</div>";
			}
		}


	}
	else
	{

		// Standardize all Labels
		$inputs = preg_match_all('/\<a(.*?)\>/s',$content, $matches);
		if (!empty($matches[0]))
		{
			foreach ($matches[0] as $key => $value)
			{
				if ($key==0)
				{
					$new_value = $value;
					$new_value = preg_replace('/ class=(["\'])(.*?)(["\'])/','class="$2 lp-track-link"', $new_value);



					$content = str_replace($value, $new_value, $content);
					break;
				}
			}
		}

		$check_wrap = preg_match_all('/wp_cta_container_noform/s',$content, $check);
		if (empty($check[0]))
		{
			$content = "<div id='wp_cta_container_noform'  class='$wrapper_class'>{$content}</div>";
		}
	}

	return $content;
}


function wp_cta_conversion_area($post = null, $content=null,$return=false, $doshortcode = true, $rebuild_attributes = true)
{
	if (!isset($post))
		global $post;

	$wrapper_class = "";

	if (wp_cta_get_value($post, 'lp', 'conversion-area'))
	{
		$content = wp_cta_get_value($post, 'lp', 'conversion-area');
	}


	$content = apply_filters('wp_cta_conversion_area_pre_standardize',$content, $post->ID);

	$standardize_form = get_option( 'wp-cta-main-wp-call-to-action-auto-format-forms' , 1); // conditional to check for options

	$wrapper_class = wp_cta_discover_important_wrappers($content);



	if ($doshortcode)
	{
		$content = do_shortcode($content);
	}

	if ($rebuild_attributes)
	{
		$content = wp_cta_rebuild_attributes($content, $wrapper_class, $standardize_form );
	}

	$content = apply_filters('wp_cta_conversion_area_post',$content, $post);

	//echo "here2";
	//echo $content;exit;
	if(!$return)
	{

		echo $content;
	}
	else
	{
		return $content;
	}

}



function wp_cta_main_headline($post = null, $headline=null,$return=false)
{
	if (!isset($post))
		global $post;

	if (!$headline)
	{
		$main_headline =  wp_cta_get_value($post, 'wp-cta', 'main-headline');
		$main_headline = apply_filters('wp_cta_main_headline',$main_headline);

		if(!$return)
		{
			echo $main_headline;

		}
		else
		{
			return $main_headline;
		}
	}
	else
	{
		if(!$return)
		{
			echo $headline;
		}
		else
		{
			return $headline;
		}
	}
}

function wp_cta_content_area($post = null, $content=null,$return=false)
{
	if (!isset($post))
		global $post;

	if (!$content)
	{
		global $post;

		if (!isset($post)&&isset($_REQUEST['post']))
		{
			$post = get_post($_REQUEST['post']);
		}
		else if (!isset($post)&&isset($_REQUEST['wp_cta_id']))
		{
			$post = get_post($_REQUEST['wp_cta_id']);
		}

		$content_area = get_post_field('post_content', $post->ID);

		$content_area = apply_filters('wp_cta_content_area',$content_area, $post);

		if(!$return)
		{
			echo $content_area;

		}
		else
		{
			return $content_area;
		}
	}
	else
	{
		if(!$return)
		{
			echo $content_area;
		}
		else
		{
			return $content_area;
		}
	}
}



function wp_cta_body_class()
{
	global $post;
	global $wp_cta_data;
	// Need to add in wp_cta_right or wp_cta_left classes based on the meta to float forms
	// like $conversion_layout = wp_cta_get_value($post, $key, 'conversion-area-placement');
	if (get_post_meta($post->ID, 'lp-selected-template', true))
	{
		$wp_cta_body_class = "template-" . get_post_meta($post->ID, 'lp-selected-template', true);
		 $postid = "page-id-" . get_the_ID();
		echo 'class="';
		echo $wp_cta_body_class . " " . $postid . " wordpress-wp-call-to-action";
		echo '"';
	}
	return $wp_cta_body_class;
}

function wp_cta_get_parent_directory($path)
{
	if(stristr($_SERVER['SERVER_SOFTWARE'], 'Win32')){
		$array = explode('\\',$path);
		$count = count($array);
		$key = $count -1;
		$parent = $array[$key];
		return $parent;
    } else if(stristr($_SERVER['SERVER_SOFTWARE'], 'IIS')){
        $array = explode('\\',$path);
		$count = count($array);
		$key = $count -1;
		$parent = $array[$key];
		return $parent;
    }else {
		$array = explode('/',$path);
		$count = count($array);
		$key = $count -1;
		$parent = $array[$key];
		return $parent;
	}
}

function wp_cta_get_value($post, $key, $id)
{
	//echo 1; exit;
	if (isset($post))
	{
		$return = get_post_meta($post->ID, $key.'-'.$id , true);
		$return = apply_filters('wp_cta_get_value',$return,$post,$key,$id);

		return $return;
	}
}

function wp_cta_check_active()
{
	return 1;
}


function wp_cta_remote_connect($url)
{
	$method1 = ini_get('allow_url_fopen') ? "Enabled" : "Disabled";
	if ($method1 == 'Disabled')
	{
		//do curl
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "$url");
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
		curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');
		curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
		$string = curl_exec($ch);
	}
	else
	{
		$string = file_get_contents($url);
	}

	return $string;
}

//***********FUNCTION THAT WILL FIND POST ID FROM URL FOR CUSTOM POST TYPES******************/
function wp_cta_url_to_postid($url)
{
	global $wpdb;

	//first check if URL is homepage
	$wordpress_url = get_bloginfo('url');
	if (substr($wordpress_url, -1, -1)!='/')
	{
		$wordpress_url = $wordpress_url."/";
	}

	if (str_replace('/','',$url)==str_replace('/','',$wordpress_url))
	{
		return get_option('page_on_front');
	}

	$parsed = parse_url($url);
	$url = $parsed['path'];

	$parts = explode('/',$url);

	$count = count($parts);
	$count = $count -1;

	if (empty($parts[$count]))
	{
		$i = $count-1;
		$slug = $parts[$i];
	}
	else
	{
		$slug = $parts[$count];
	}

	$my_id = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_name = '$slug' AND post_type='wp-call-to-action'");

	if ($my_id)
	{
		return $my_id;
	}
	else
	{
		return 0;
	}
}


add_action( 'wp_cta_footer', 'wp_cta_print_dimensions' );
function wp_cta_print_dimensions() {
	global $post;
	if (isset($_GET['wp-cta-variation-id'])) {
		$var = $_GET['wp-cta-variation-id'];
		$width = get_post_meta($post->ID, 'wp_cta_width-'.$var, true);
		$height = get_post_meta($post->ID, 'wp_cta_height-'.$var, true);
		$link_opens = get_post_meta($post->ID, 'link_open_option', true);
		echo "<div id='cpt_cta_width' style='display:none;'>" . $width . "</div>";
		echo "<div id='cpt_cta_height' style='display:none;'>" . $height . "</div>";
		echo "<div id='cpt_cta_link_opens' style='display:none;'>" . $link_opens . "</div>";
		echo "<style type='text/css'>html.fix-admin-view {margin-top:0px !important;}</style>";
	}
}


/**
 * [wp_cta_localize_script localizes variable needed on pages
 * @param  [type] $cta_ids [description]
 * @return [type]          [description]
 */
function wp_cta_localize_script() {
	global $post;
	global $wp_query;
	
	$current_page_id = $wp_query->get_queried_object_id();
	$wp_cta_post_template_ids = get_post_meta($current_page_id, 'cta_display_list'); //print_r($wp_cta_post_template_ids);


	// If ctas toggled on for page, run
	if (!empty($wp_cta_post_template_ids)){

        $wp_cta_placement = get_post_meta($current_page_id, 'wp_cta_content_placement');
         if (!empty($wp_cta_placement)){
            	$placement = $wp_cta_placement[0];
            } else {
            	$placement = 'off';
            }

	$ctas_from_current_page = $wp_cta_post_template_ids[0];
	$cta_obj = array();
        $main_count = 0;
        foreach ($ctas_from_current_page as $cta_id) {
        	$variation_list = get_post_meta( $cta_id, 'wp-cta-ab-variations', true );
        	$behavorial = get_post_meta( $cta_id, 'wp_cta_global_bt_status', true ); // move to ext
        	$var_array = preg_split('/,/', $variation_list);
        	$count_var_array = count($var_array);
        	$url = get_permalink( $cta_id );
        	$cta_obj[$main_count]['id'] = $cta_id;
			$cta_obj[$main_count]['url'] = $url;
			$cta_obj[$main_count]['behavorial'] = $behavorial; // more to ext

        	$loop_count = 0;
	        	foreach ($var_array as $key => $value) {
			        	$id = $cta_id;
			        	$var_width = get_post_meta( $id, 'wp_cta_width-'.$key, true );
			        	$var_height = get_post_meta( $id, 'wp_cta_height-'.$key, true );
			        	$status = get_post_meta( $id, 'wp_cta_ab_variation_status-'.$key, true );
			        	// If variation off
			        	if ($status === "0"){
			        		$on_off = false;
			        		$count_var_array--;
			        	} else {
			        		$on_off = true;
			        	}
			        	$cta_obj[$main_count]['count'] = $count_var_array;
			        	$cta_obj[$main_count]['variation'][$key]['status'] = $on_off;
			        	$cta_obj[$main_count]['variation'][$key]['cta_height'] = $var_height;
			        	$cta_obj[$main_count]['variation'][$key]['cta_width'] = $var_width;

			        	$loop_count++;

			  	}
			$main_count++;
        }
        return $cta_obj;
	}
}



add_filter('the_content', 'wp_cta_placements_add_post_content', 10);
function wp_cta_placements_add_post_content($content)
{
	global $post;
	global $table_prefix;
	if (!isset($post))
		return;
	if (!defined('DONOTCACHEPAGE')) define( 'DONOTCACHEPAGE', true );

	$title = $post->post_title;

	$wp_cta_post_template_ids = get_post_meta($post->ID, 'cta_display_list');

	//print_r($wp_cta_post_template_ids);
	// If ctas toggled on for page, run
	if (!empty($wp_cta_post_template_ids)){

        $wp_cta_placement = get_post_meta($post->ID, 'wp_cta_content_placement');
         if (!empty($wp_cta_placement)){
            $placement = $wp_cta_placement[0];
         } else {
            $placement = 'off';
         }

         $wp_cta_alignment = get_post_meta($post->ID, 'wp_cta_alignment');
         if (!empty($wp_cta_alignment)){
            $cta_alignment = $wp_cta_alignment[0];
         } else {
           	$cta_alignment = "cta-aligncenter"; // default alignment
         }

	if ($placement=='popup') {
		$popclass = "wp_cta_popup";
	} else {
		$popclass = "";
	}

        /* Older PHP rands
        $count = count($wp_cta_post_template_ids[0]);
        $rand_key = array_rand($wp_cta_post_template_ids[0], 1);
        $ctaw_id = $wp_cta_post_template_ids[0][$rand_key];
        $the_link = get_permalink( $ctaw_id );
        $width = get_post_meta( $ctaw_id, 'wp_cta_width', true );
	    	if(!empty($width) && $width != "") {
	    		$final_width = $width;
	    		str_replace("px", "", $final_width);
	    		$width_output = "width:" . $final_width . "px;";
	    	} else {
	    		$width_output = "";
	    	}
	    $height = get_post_meta( $ctaw_id, 'wp_cta_height', true );
	    	if(!empty($height) && $height != "") {
	    		$final_height = $height;
	    		str_replace("px", "", $final_height);
	    		$height_output = "height:" . $final_height . "px;";
	    	} else {
	    		$height_output = "";
	    	}
        */

		$height_output = ""; $width_output = "";
    	$ad_content = '<iframe id="wp-cta-per-page" class="wp-cta-display '.$popclass.'" src="" scrolling="no" frameborder="0" style="border:none; overflow:hidden; '.$width_output.' '.$height_output.' display:none;" allowtransparency="true"></iframe>';

        if ($placement=='above') {
			$content = "<div class='".$cta_alignment."'>" . $ad_content. "</div>" . $content;
		} elseif ($placement=='middle') {
			$count = strlen($content);
			$half =  $count/2;
			$left = substr($content, 0, $half);
			$right = substr($content, $half);
			$right = explode('. ',$right);
			$right[1] = $ad_content.$right[1];
			$right = implode('. ',$right);
			$content =  $left.$right;

		} elseif ($placement=='below') {
			$content = $content . "<div class='".$cta_alignment."'>" . $ad_content . "</div>";
		} elseif ($placement=='popup') {
			$content = $content . "<a id='cta-no-show' class='popup-modal' href='#wp-cta-popup'>Open modal</a><div id='wp-cta-popup' class='mfp-hide white-popup-block' style='display:none;'><button title='Close (Esc)' type='button' class='mfp-close'>�</button>" . $ad_content . "</div>";
		}  elseif ($placement=='widget_1') {
			$content = $content;
		}

	}
	return $content;
}


/* CTA Placement Shortcode */
add_shortcode( 'cta', 'wp_cta_shortcode');
function wp_cta_shortcode( $atts, $content = null ) 
{
	extract(shortcode_atts(array(
		'id' => '',
		'align' => ''
		//'style' => ''
	), $atts));

	if ($id === ""){
		$iframe_class = 'wp-cta-display';
	} else {
		$iframe_class = 'wp-cta-display wp-cta-special';
	}

	$possible_ctas = explode(",", $id);

	$rand_key = array_rand($possible_ctas,1);

	$cta_id = $possible_ctas[$rand_key];

	$variation_list = get_post_meta( $cta_id, 'wp-cta-ab-variations', true );

	$var_array = preg_split('/,/', $variation_list);
	$count_var_array = count($var_array);
	$possible_variation = array();
	foreach ($var_array as $key => $value) {
		$status = get_post_meta( $cta_id, 'wp_cta_ab_variation_status-'.$key, true ); // check status
		if ($status != "0"){
		array_push($possible_variation, $key);
			 }
	}
	// Get Variation width/height
	$height_output = "";
	$rand_var_key = array_rand($possible_variation,1);
	$var_id = $possible_variation[$rand_var_key];
	$var_width = get_post_meta( $cta_id, 'wp_cta_width-'.$var_id, true );
	$var_height = get_post_meta( $cta_id, 'wp_cta_height-'.$var_id, true );
	if(!empty($var_height) && $var_height != "") {
			str_replace("px", "", $var_height);
			$height_output = "height:" . $var_height . "px;";
	}
	if(!empty($var_width) && $var_width != "") {
			str_replace("px", "", $var_width);
			$width_output = "height:" . $var_width . "px;";
	}

	$permalink = get_permalink( $cta_id ) . '?wp-cta-variation-id=' . $var_id;

	if($align === "right") {
		$alignment = ' float:right; padding-left:10px;';
	}
	else if ($align === "left") {
		$alignment = ' float:left; padding-right:10px;';
	} else {
		$alignment = '';
	}

	return '<div><iframe id="wp-cta-selected-id" class="wp-cta-display" src="'.$permalink.'" scrolling="no" frameborder="0" style="border:none; overflow:hidden; '.$width_output.' '.$height_output.' '.$alignment.' display:none;" allowtransparency="true"></iframe></div>';
}

add_shortcode( 'insert_cta', 'wp_cta_insert_shortcode');
function wp_cta_insert_shortcode( $atts, $content = null ) 
{
	return '<div><iframe id="wp-cta-per-page" class="wp-cta-display" src="" scrolling="no" frameborder="0" style="border:none; overflow:hidden; display:none;" allowtransparency="true"></iframe></div>';
}


// old inline
function cta_var_render(){
	echo 	'<script type="text/javascript">
      					var var_array= '.$json.';
    					var cta_link = "'.$the_link.'";
			</script>' ;
}