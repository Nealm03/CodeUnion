<?php

function wp_cta_templates_upload()
{
	wp_cta_templates_upload_execute();
	wp_cta_display_upload();
	wp_cta_templates_search();
}

function wp_cta_display_upload()
{
?>
	<div class="wrap templates_upload">
		<div class="icon32" id="icon-plugins"><br></div><h2>Install Templates</h2>
		
		<ul class="subsubsub">
			<li class="plugin-install-dashboard"><a href="#search" id='menu_search'>Search</a> |</li>
			<li class="plugin-install-upload"><a class="current" href="#upload" id='menu_upload'>Upload</a> </li>
		</ul>
	
		<br class="clear">
			<h4>Install Landing Pages template by uploading them here in .zip format</h4>
			
			 <p class="install-help">Warning: Do not upload landing page extensions here or you will break the plugin! <br>Extensions are uploaded in the WordPress plugins section.
			</p>
			<form action="" class="wp-upload-form" enctype="multipart/form-data" method="post">
				<input type="hidden" value="<?php echo wp_create_nonce('wp-cta-nonce'); ?>" name="wp_cta_wpnonce" id="_wpnonce">
				<input type="hidden" value="/wp-admin/plugin-install.php?tab=upload" name="_wp_http_referer">
				<label for="pluginzip" class="screen-reader-text">Template zip file</label>
				<input type="file" name="templatezip" id="templatezip">
				<input type="submit" value="Install Now" class="button" id="install-template-submit" name="install-template-submit" disabled="">	
			</form>
	</div>
<?php
}

function wp_cta_templates_search()
{
	//echo 2; exit;
	?>
	
	<div class="wrap templates_search" style='display:none'>
		<div class="icon32" id="icon-plugins"><br></div><h2>Search Templates</h2>

		<ul class="subsubsub">
				<li class="plugin-install-dashboard"><a href="#search" id='menu_search'>Search</a> |</li>
				<li class="plugin-install-upload"><a class="current" href="#upload" id='menu_upload'>Upload</a> </li>
		</ul>
		
		<br class="clear">
			<p class="install-help">Search the Inboundnow marketplace for free and premium templates.</p>
			<form action="edit.php?post_type=wp-call-to-action&page=wp_cta_store" method="POST" id="">
				<input type="search" autofocus="autofocus" value="" name="search">
				<label for="plugin-search-input" class="screen-reader-text">Search Templates</label>
				<input type="submit" value="Search Templates" class="button" id="plugin-search-input" name="plugin-search-input">	
			</form>
	</div>
	
	<?php
}

function wp_cta_templates_upload_execute()
{
	// verify nonce
	//print_r($_POST);
	//print_r($_FILES);exit;
	if ($_FILES)
	{		
		$name = $_FILES['templatezip']['name'];
		$name = preg_replace('/\((.*?)\)/','',$name);
		$name = str_replace(array(' ','.zip'),'',$name);
		$name = trim($name);
		//echo $name;exit;
		//echo $_FILES['templatezip']["tmp_name"];exit;
		if (!wp_verify_nonce($_POST["wp_cta_wpnonce"], 'wp-cta-nonce'))
		{
			return NULL;
		}
		
		include_once(ABSPATH . 'wp-admin/includes/class-pclzip.php');
		
		$zip = new PclZip( $_FILES['templatezip']["tmp_name"]);
		
		$uploads = wp_upload_dir();
		$uploads_path = $uploads['basedir'];
		$extended_path = $uploads_path.'/wp-call-to-actions/templates/';	
		if (!is_dir($extended_path))
		{
			wp_mkdir_p( $extended_path );
		}
		
		if (($list = $zip->listContent()) == 0) 
		{
			die("There was a problem. Please try again!");
		}
		 
		$is_template = false;
		foreach ($list as $key=>$val)
		{
			foreach ($val as $k=>$val)
			{
				if (strstr($val,'/config.php'))
				{
					$is_template = true;
					break;
				}
				else if($is_template==true)
				{
					break;
				}
			}
		}
		
		if (!$is_template)
		{
			echo "<br><br><br><br>";
			die("WARNING! This zip file does not seem to be a template file! If you are trying to install a Landing Page extension please use the Plugin's upload section! Please press the back button and try again!");
		}
		//exit;
		//$result = $zip->extract(PCLZIP_OPT_PATH, $extended_path );
		
		if ($result = $zip->extract(PCLZIP_OPT_PATH, $extended_path ,  PCLZIP_OPT_REPLACE_NEWER  ) == 0) 
		{
			die("There was a problem. Please try again!");
		} else 
		{
			unlink( $_FILES['templatezip']["tmp_name"]);
			echo '<div class="updated"><p>Template uploaded successfully!</div>';
		}
	}
}

function wp_cta_templates_update()
{
	//echo 'hello Update!';
}