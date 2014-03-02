<?php
/**
 Plugin Name: WP Inject
 Plugin URI: http://wpinject.com/
 Version: 0.30
 Description: Insert photos into your posts or set a featured image in less than a minute! WP Inject allows you to search the huge Flickr image database for creative commons photos directly from within your WordPress editor. Find great photos related to any topic and inject them into your post!
 Author: Thomas Hoefter
 Author URI: http://wpinject.com/
*/

include_once("info_sources_options.php");

function wpdf_add_menu_pages() {
	$wpdf_settings = add_options_page('WP Inject', 'WP Inject', 'manage_options', 'wpdf-options', 'wpdf_settings_page');
	add_action( "admin_print_scripts-$wpdf_settings", 'wpdf_settings_page_scripts' );		
}
add_action('admin_menu', 'wpdf_add_menu_pages');

function wpdf_activate() {
	include("info_sources_options.php");

	$wpinject_settings = $modulearray;
	foreach($wpinject_settings as $module => $moduledata) {
		if($moduledata["enabled"] != 2 && $moduledata["enabled"] != 1) {
			unset($wpinject_settings[$module]["options"]);
			unset($wpinject_settings[$module]["templates"]);
		}
	}
	
	update_option('wpinject_settings',$wpinject_settings);			
}
register_activation_hook(__FILE__, 'wpdf_activate');

function wpdf_deactivate() {
	delete_option('wpinject_settings');			
}
register_deactivation_hook( __FILE__, 'wpdf_deactivate' );

///////////////////////// SETTINGS PAGE

function wpdf_settings_page_scripts() {
	wp_enqueue_script('jquery');
	$wpi_url = WP_PLUGIN_URL . '/' . basename(dirname(__FILE__)); //plugins_url( 'WPInject/wpdf-editor-styles.css' )
	
	wp_register_style( 'wpinject-editor-css', $wpi_url . '/wpdf-editor-styles.css' );
	wp_enqueue_style( 'wpinject-editor-css' );		
}

/*
if(isset($_GET['page']) && $_GET['page'] == 'wpdf-options' ) {
	//add_action('admin_head', 'wpdf_settings_page_head');		
}
function wpdf_settings_page_head() {
	?>
    <script type="text/javascript">	
	jQuery(document).ready(function($) {
		var index;
		var modules = ["flickr"];
		for (index = 0; index < modules.length; ++index) {
			toggle("#" + modules[index], "#" + modules[index] + "_enabled");
		}
	});
	
	function toggle(className, obj) {
		var jQueryinput = jQuery(obj);
		if (jQueryinput.prop('checked')) jQuery(className).show();
		else jQuery(className).hide();
	}
	</script>		
	<?php
}*/

function wpdf_settings_page() {
	global $source_infos, $modulearray;
	
	$options = $modulearray;
	$optionsarray = get_option("wpinject_settings");

	if($_POST["save_options"]) {
		foreach($options as $module => $moduledata) {

			if($optionsarray[$module]["enabled"] != 2) {
				$optionsarray[$module]["enabled"] = $_POST[$module."_enabled"];
				if(empty($_POST[$module."_enabled"])) {$optionsarray[$module]["enabled"] = 0;}
				
				if($optionsarray[$module]["enabled"] == 1 && empty($optionsarray[$module]["options"])) {
					$optionsarray[$module] = $options[$module];
					$optionsarray[$module]["enabled"] = 1;
				}
			}
			
			if($optionsarray[$module]["enabled"] == 1 || $optionsarray[$module]["enabled"] == 2) {
				foreach($moduledata["options"] as $option => $data) {	
				
					if($option == "img_template" || $option == "attr_template" || $option == "attr_template_multi") {
					
						$_POST[$module."_".$option] = stripslashes($_POST[$module."_".$option]);
						if($option == "attr_template" && (strpos($_POST[$module."_".$option], "{link}") === false || strpos($_POST[$module."_".$option], "{author}") === false)) {
							echo '<div class="error"><p><strong>WARNING: </strong> The Attribution Template setting has to contain the {link} and {author} tag with a proper link back to the owner or you will be <strong>in violation of the license</strong> of Flickr photos you insert!</p></div>';	
						}
					}
				
					$optionsarray[$module]["options"][$option]["value"] = $_POST[$module."_".$option];				
				}		
			}
		}

		$result = update_option("wpinject_settings", $optionsarray);
		if($result) {
			echo '<div class="updated"><p>Options have been updated.</p></div>';	
		} else {
			echo '<div class="error"><p>Error: Options could not be updated.</p></div>';	
		}			
	}	
	
	if(!empty($_POST) && empty($_POST["save_options"])) {
		// VERIFICATION FUNCTION
		foreach($options as $module => $moduledata) {
			if($_POST[$module."_verify"]) {
				@require_once("api.class.php");
				$api = new wpdf_API_request;
				$result = $api->api_content_bulk("camera",array($module => 1));
				if(empty($result[$module]["error"]) && isset($result[$module][0]["content"])) {
					if($module == "amazon") {$options[$module]["options"]["public_key"]["verified"] = 1;} else {$optionsarray[$module]["options"]["appid"]["verified"] = 1;}
					update_option("wpinject_settings", $optionsarray);
					echo '<div class="updated"><p>'.$moduledata["name"].' has been verified and is working!</p></div>';					
				} else {
					echo '<div class="error"><p>'.$result[$module]["error"].'</p></div>';	
				}
			}
		}	
	}
?>
<div class="wrap">

	<div id="wpdf_settings_box">
		<p style="margin-top: 0;">To <strong>insert images</strong> go to the WordPress "<a href="post-new.php">New Post</a>" or "<a href="post-new.php?post_type=page">New Page</a>" screens where you will find the WP Inject metabox to search for great photos!</p>
		
		<p>Please <a href="http://wpinject.com/tutorial/" target="_blank"><strong>read my short WP Inject tutorial</strong></a> for more details on all the settings on this page and what exactly they do.</p>
	
		<p>Having problems or found a bug? Please <a href="http://wpinject.com/contact" target="_blank">contact me</a> or post in the WordPress support forum.</p>
	
		<p style="margin-bottom: 0;">If you find WP Inject useful <strong>please share!</strong><br/>
			<a title="Share WP Inject on Twitter" target="_blank" class="wpdf_share_twitter" href="https://twitter.com/home?status=I%20am%20using%20WP%20Inject%20to%20insert%20CC%20images%20into%20my%20blog%20fast%20and%20for%20free:%20http://wpinject.com"></a>
			<a title="Share WP Inject on Facebook" target="_blank" class="wpdf_share_fb" href="https://www.facebook.com/sharer/sharer.php?u=http://wpinject.com"></a>
			<a title="Share WP Inject on Google+" target="_blank" class="wpdf_share_google" href="https://plus.google.com/share?url=http://wpinject.com"></a>
		</p>
	</div>

	<h2><?php _e("WP Inject Settings","wpinject") ?></h2>
	
	<form method="post" name="wpdf_options">	
	
	<p class="submit"><input class="button-primary" type="submit" name="save_options" value="<?php _e("Save All Settings","wpinject") ?>" /></p>		

	<?php $num = 0; foreach($options as $module => $moduledata) { $num++; ?>

		<?php if($moduledata["enabled"] == 2) { ?>
		<h3><?php echo $moduledata["name"]; ?></h3>
		<?php } else { ?>
		<h3><input checked style="margin-right: 5px; margin-top: -2px;display:none;" onclick="toggle('#<?php echo $module; ?>', this)" class="button" type="checkbox" id="<?php echo $module."_enabled"; ?>" name="<?php echo $module."_enabled"; ?>" value="1" <?php if(1 == $optionsarray[$module]["enabled"]) {echo "checked";} ?>/><label for="<?php echo $module."_enabled"; ?>"><?php echo $moduledata["name"]; ?> <?php _e("Settings","wpinject") ?></label></h3>
		<?php } ?>
		
		<div id="<?php echo $module; ?>">	

		<?php if(empty($moduledata["options"])) { ?>
			<p><?php _e("No settings required for this content source. To edit its templates go to the Templates page.","wpinject"); ?></p>
		<?php } else { ?>
		<table class="form-table" style="clear: none !important;">
			<tbody>				
		
				<?php foreach($moduledata["options"] as $option => $data) {
					if($option != "title" && $option != "unique" && $option != "error" && $option != "unique_direct" && $option != "title_direct") {
					
						if(!empty($optionsarray[$module]["options"][$option]["value"])) {
							$value = $optionsarray[$module]["options"][$option]["value"];
						} else {
							$value = $data["value"];
						}
						
						if($data["type"] == "checkbox" && empty($optionsarray[$module]["options"][$option]["value"]) && !empty($optionsarray[$module]["options"])) {
							$value = "";
						}
					
						if($data["type"] == "text") { // Text Option 
							if($data["display"] == "none") {$dnon = 'style = "display: none;"';} else {$dnon = "";}
						?> 
							<tr <?php echo $dnon;?>>
								<th scope="row"><label for="<?php echo $module."_".$option;?>"><?php echo $data["name"];?></label></th>
								<td><input class="regular-text" type="text" name="<?php echo $module."_".$option;?>" value="<?php echo $value; ?>" />
									<!-- VERIFICATION BUTTON DISPLAY -->
									<?php if($optionsarray[$module]["options"][$option]["verified"] === 0) {?>
										<input class="button" type="submit" name="<?php echo $module."_verify";?>" value="<?php _e("Verify","wpinject"); ?>" <?php if(empty($value)) {echo "disabled";} ?> />
										<?php if(!empty($source_infos["sources"][$module]["signup"])) {?><a href="<?php echo $source_infos["sources"][$module]["signup"]; ?>" target="_blank">Sign Up</a><?php } ?>
									<?php } elseif($optionsarray[$module]["options"][$option]["verified"] === 1) {?>
										<?php echo '<img style="margin-bottom: -3px;" src="'.WP_PLUGIN_URL . '/' . basename(dirname(__FILE__)).'/images/check.png" /> Verified'; ?>
									<?php } ?>

								</td>	
							</tr>
						<?php } elseif($data["type"] == "select") { // Select Option ?>
							<tr>	
								<th scope="row"><label for="<?php echo $module."_".$option;?>"><?php echo $data["name"];?></label></th>
								<td><select name="<?php echo $module."_".$option;?>">
									<?php foreach($data["values"] as $val => $name) { ?>
									<option value="<?php echo $val;?>" <?php if($val == $value) {echo "selected";} ?>><?php echo $name; ?></option>
									<?php } ?>		
								</select></td>	
							</tr>
						<?php } elseif($data["type"] == "checkbox") { // checkbox Option ?>		
							<tr>	
								<th scope="row"><label for="<?php echo $module."_".$option;?>"><?php echo $data["name"];?></label></th>
								<td><input class="button" type="checkbox" id="<?php echo $module."_".$option; ?>_s" name="<?php echo $module."_".$option; ?>" value="1" <?php if(1 == $value) {echo "checked";} ?>/> <label for="<?php echo $module."_".$option;?>_s" style="padding-top: 7px;"><?php echo $data["info"]; ?><label>

								</td>	
							</tr>									
						<?php } elseif($data["type"] == "textarea") { // textarea Option ?>		
							<tr>	
								<th scope="row"><label for="<?php echo $module."_".$option;?>"><?php echo $data["name"];?></label></th>
								
								<td>
								<textarea cols="60" rows="1" name="<?php echo $module."_".$option; ?>"><?php echo $value; ?></textarea>
								</td>	
							</tr>									
						<?php } ?>	
						
					<?php } ?>
				<?php } ?>
		
			</tbody>
		</table>					
		<?php } ?>
		</div>	
	<?php } ?>
	
	<p class="submit"><input class="button-primary" type="submit" name="save_options" value="<?php _e("Save All Settings","wpinject") ?>" /></p>	

	<h3>Available Template Tags</h3>
	<p>You can use the following tags in the "<strong>Image Template</strong>" setting field:</p>
	<p>
		<strong>{keyword}</strong> - The keyword you searched for with WP Inject.<br/>
		<strong>{yoast-keyword}</strong> - Inserts the "Focus Keyword" as set in the WordPress SEO by Yoast plugin for the post.<br/>	
		<strong>{title}</strong> - The title of the image on Flickr.<br/>
		<strong>{description}</strong> -  The description of the image on Flickr<br/>
		<strong>{author}</strong> - Flickr name or username of the author.<br/>
		<strong>{link}</strong> - Link to the image page on Flickr<br/>
		<strong>{src}</strong> - The image file in the specified size<br/>
	</p>
	<p>The following tags are available in the "<strong>Attribution Template</strong>" field:</p>	
	<p>
		<strong>{keyword}</strong> - The keyword you searched for with WP Inject.<br/>
		<strong>{author}</strong> - Flickr name or username of the author.<br/>
		<strong>{link}</strong> - Link to the image page on Flickr<br/>
	</p>	
<?php
}

/////////////////////// META BOX

add_action( 'add_meta_boxes', 'wpdf_editor_metabox' );
function wpdf_editor_metabox() {
	$screens = array('post', 'page');
	foreach ($screens as $screen) {
		add_meta_box('wpdf_editor_section',__( 'WP Inject', 'wpinject' ), 'wpdf_editor_metabox_content', $screen);
	}
}

function wpdf_editor_metabox_content($post) {

	$options = get_option("wpinject_settings");

	$moduleactive = 0;$modulecontent = "";
	if(is_array($options)) {
		foreach($options as $module => $moduledata) {
			if($moduledata["enabled"] != 2) {
				if(!empty($moduledata["options"]["appid"]["value"])) {$moduleactive = 1;}
				$modulecontent .= '<span id="'.$module .'-load" style="display: none;">
				<img src="'.WP_PLUGIN_URL . '/' . basename(dirname(__FILE__)).'/images/ajax-loader.gif" style="width: 16px; height: 16px;margin-bottom: -2px;" /></span>
				<input style="margin-right: 10px;" type="button" class="button wpdf-module" id="'.$module .'" value="Search">';
			}
		}
	}
	
	if($moduleactive == 0) {
		?>
		<div id="wpdf_save_keys_form">
			<p><?php _e("To start injecting images please enter your Flickr API key below:","wpinject") ?></p>
			<label for="flickr_appid">Flickr API Key: <input type="text" value="" id="flickr_appid" name="flickr_appid" class="regular-text"></label><br/>
			<p><?php _e('For more settings head to the <a href="/wp-admin/options-general.php?page=wpdf-options">WP Inject Options</a> page.',"wpinject") ?></p>
			<p><input type="submit" value="Save" id="wpdf_save_keys" name="wpdf_save_keys" class="button-primary"></p>
		</div>
		<?php
	}
	?>
	
	<div id="wpdf_main" <?php if($moduleactive == 0) { echo 'style="display:none;"';} ?>>
	
		<div id="wpdf_modules">
			<div>
				<input placeholder="<?php _e("Enter search keyword","wpinject") ?>" type="text" value="" size="30" class="newtag form-input-tip" name="wpdf_keyword" id="wpdf_keyword">
				<?php echo $modulecontent; ?>
				
				<a href="#" id="wpdf_get_title"><?php _e("&rarr; Copy Title","wpinject") ?></a>
				<?php if(function_exists("wpseo_init")) { ?>
					<a href="#" id="wpdf_get_seo_keyword"><?php _e("&rarr; Copy SEO Keyword","wpinject") ?></a>
				<?php } ?>
			</div>
		</div>
		
		<div id="wpdf_controls">	
		
			<strong><?php _e("All Selected:","wpinject") ?> </strong>
			<a href="#" title="<?php _e("Insert all selected images","wpinject") ?>" id="wpdf_insert_images_normal"><?php _e("Insert Normal","wpinject") ?></a>
			<a href="#" title="<?php _e("Insert all selected images aligned to the left","wpinject") ?>" id="wpdf_insert_images_left"><?php _e("Align Left","wpinject") ?></a>
			<a href="#" title="<?php _e("Insert all selected images aligned to the right","wpinject") ?>" id="wpdf_insert_images_right"><?php _e("Align Right","wpinject") ?></a>
			<a href="#" title="<?php _e("Insert all selected images aligned to the center","wpinject") ?>" id="wpdf_insert_images_center"><?php _e("Align Center","wpinject") ?></a>
					
			<a href="#" title="<?php _e("Remove all selected images","wpinject") ?>" id="wpdf_remove_selected"><?php _e("Remove","wpinject") ?></a>	

			<div style="float: right;">
				<label for="wpdf_size_sq"><input type="radio" class="wpdf_size_mult" name="wpdf_size" id="wpdf_size_sq" value="square" checked>SQ</label>
				<label for="wpdf_size_s"><input type="radio" class="wpdf_size_mult" name="wpdf_size" id="wpdf_size_s" value="small">S</label>
				<label for="wpdf_size_m"><input type="radio" class="wpdf_size_mult" name="wpdf_size" id="wpdf_size_m" value="medium">M</label>
				<label for="wpdf_size_l"><input type="radio" class="wpdf_size_mult" name="wpdf_size" id="wpdf_size_l" value="large">L</label>
			</div>	
			
		</div>			
		
		<div id="wpdf_message_box">		
		</div>	
		
		<div id="wpdf_results">			
		</div>
		
		<div style="clear: both;"></div>
		
		<div id="wpdf_share_box">Enjoying WP Inject? <strong>Please share!</strong> 
			<a title="WP Inject settings page" target="_blank" class="wpdf_settings_link" href="options-general.php?page=wpdf-options">Settings</a>
			<a title="Share WP Inject on Twitter" target="_blank" class="wpdf_share_twitter" href="https://twitter.com/home?status=I%20am%20using%20WP%20Inject%20to%20insert%20CC%20images%20into%20my%20blog%20fast%20and%20for%20free:%20http://wpinject.com"></a>
			<a title="Share WP Inject on Facebook" target="_blank" class="wpdf_share_fb" href="https://www.facebook.com/sharer/sharer.php?u=http://wpinject.com"></a>
			<a title="Share WP Inject on Google+" target="_blank" class="wpdf_share_google" href="https://plus.google.com/share?url=http://wpinject.com"></a>
		</div>

		<div id="wpdf_ri">	
		
			<div id="wpdf_result_item" class="wpdf_result_item">
			
				<div class="wpdf_result_item_nav">
					<input class="wpdf_select_item_o" type="checkbox" name="wpdf_select_item_o" value="1">
				</div>		
			
				<div class="wpdf_result_item_content">
				</div>	
				
				<div class="wpdf_result_item_save" style="display:none;">
				</div>					

				<div style="clear: both;"></div>
			</div>	

		</div>

	</div>
	<?php
}

// Header
function wpdf_editor_head() {
	global $post;
	
	$options = get_option("wpinject_settings");
?>
    <script type="text/javascript">			
	<?php if($options["general"]["options"]["save_images"]["value"] == 1) { ?>
		var wpdf_save_images = 1;
	<?php } else { ?>
		var wpdf_save_images = 0;
	<?php } ?>

	var wpdf_default_align = '<?php echo $options["general"]["options"]["default_align"]["value"]; ?>'; 	
	var wpdf_img_template = '<?php echo $options["advanced"]["options"]["img_template"]["value"]; ?>'; 	
	var wpdf_attr_template = '<?php echo $options["advanced"]["options"]["attr_template"]["value"]; ?>'; 
	var wpdf_attr_template_multi = '<?php echo $options["advanced"]["options"]["attr_template_multi"]["value"]; ?>'; 
	var wpdf_attr_location = '<?php echo $options["general"]["options"]["attr_location"]["value"]; ?>'; 
	var wpdf_wpi_attr = '<?php echo $options["general"]["options"]["wpi_attr"]["value"]; ?>'; 
	var wpdf_feat_img_size = '<?php echo $options["general"]["options"]["feat_img_size"]["value"]; ?>'; 
	var cur_post_id = <?php echo $post->ID; ?>; 
	var wpdf_plugin_url = '<?php echo WP_PLUGIN_URL . '/' . basename(dirname(__FILE__)); ?>';
	var wpdf_security_nonce = {
		security: '<?php echo wp_create_nonce('wpdf_security_nonce');?>'
	}		
	</script>
<?php
}

function wpdf_editor_scripts() {
	$wpi_url = WP_PLUGIN_URL . '/' . basename(dirname(__FILE__)); //plugins_url( 'WPInject/wpdf-editor-styles.css' )

	wp_register_style( 'wpinject-editor-css', $wpi_url . '/wpdf-editor-styles.css' );
	wp_enqueue_style( 'wpinject-editor-css' );	

	wp_register_script( 'wpinject-js', $wpi_url . '/wpdf-editor-js.js' );
	wp_enqueue_script( 'wpinject-js' );		
}

if(is_admin()){
    if(in_array($GLOBALS['pagenow'], array('post.php', 'post-new.php'))){
		add_action('admin_head', 'wpdf_editor_head');		
		add_action('admin_enqueue_scripts', 'wpdf_editor_scripts');
    }
	
	global $pagenow;
	if($pagenow == 'admin-ajax.php'){
		require_once('wpdf_ajax.php');
		add_action('wp_ajax_wpdf_editor', 'wpdf_editor_ajax_action_function');
		add_action('wp_ajax_wpdf_set_featured', 'wpdf_editor_ajax_set_featured_function');	
		add_action('wp_ajax_wpdf_save_keys', 'wpdf_editor_ajax_save_keys_function');	
		add_action('wp_ajax_wpdf_save_to_server', 'wpdf_editor_ajax_save_to_server_function');	
	}
}
?>