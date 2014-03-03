<?php

function wpdf_editor_ajax_action_function() {

	$module = $_POST["module"];
	$modulerun = $_POST["modulerun"];
	if(get_magic_quotes_gpc()) {
		$keyword = stripslashes($_POST['keyword']);
	} else {
		$keyword = $_POST["keyword"];
	}

	$nonce = $_POST["wpnonce"];
	if (!wp_verify_nonce($nonce, 'wpdf_security_nonce')) {
		echo json_encode(array("error" => "Invalid request."));
		exit;
	}	

	if(empty($module)) {
		echo json_encode(array("error" => "No content source found."));
		exit;	
	}
	
	if(empty($keyword)) {
		echo json_encode(array("error" => "Keyword is empty."));
		exit;	
	}

	global $source_infos, $modulearray;
	@require_once("api.class.php");	
	
	$options = get_option("wpinject_settings");
	$items_per_req = $options["general"]["options"]["items_per_req"]["value"];
	if(empty($items_per_req)) {$items_per_req = 30;}
	
	$start = 1 + (($modulerun - 1) * $items_per_req);
	
	$api = new wpdf_API_request;
	$result = $api->api_content_bulk($keyword, array($module => array("count" => $items_per_req, "start" => $start))); 

	if(is_array($result) && !empty($result[$module]["error"])) {
		echo json_encode(array("error" => $result[$module]["error"]));
		exit;		
	} else {
		$result = $result[$module];
		echo json_encode(array("result" => $result));
		exit;	
	}
}

function wpdf_editor_ajax_set_featured_function() {

	$src = $_POST["src"];
	$post_id = $_POST["post_id"];

	$nonce = $_POST["wpnonce"];
	if (!wp_verify_nonce($nonce, 'wpdf_security_nonce')) {
		echo json_encode(array("error" => "Invalid request."));
		exit;
	}	

	if(empty($src)) {
		echo json_encode(array("error" => "No image source found."));
		exit;	
	}
	
	if(empty($post_id)) {
		echo json_encode(array("error" => "No post found. This feature requires that an auto-save or draft of the current post was saved first."));
		exit;	
	}

	$result = media_sideload_image($src, $post_id);
	$attachments = get_posts(array('numberposts' => '1', 'post_parent' => $post_id, 'post_type' => 'attachment', 'post_mime_type' => 'image', 'orderby' => 'post_date', 'order' => 'DESC'));

	if(sizeof($attachments) > 0){
		set_post_thumbnail($post_id, $attachments[0]->ID);	
	}
	$newsrc = wp_get_attachment_image_src( $attachments[0]->ID, "full" );
	
	if(empty($newsrc)) {
		echo json_encode(array("error" => "Image could not be saved."));
		exit;	
	} else {
		echo json_encode(array("result" => $newsrc[0]));
		exit;		
	}	
}
/*
function wpdf_editor_ajax_save_keys_function() {

	$flickrapi = $_POST["flickrapi"];
	
	$nonce = $_POST["wpnonce"];
	if (!wp_verify_nonce($nonce, 'wpdf_security_nonce')) {
		echo json_encode(array("error" => "Invalid request."));
		exit;
	}

	$options = get_option("wpinject_settings");
	$options["flickr"]["options"]["appid"]["value"] = $flickrapi;
	update_option("wpinject_settings", $options);

	echo json_encode(array("success" => "true"));
	exit;	
}
*/
function wpdf_editor_ajax_save_to_server_function() {

	$src = $_POST["src"];
	$post_id = $_POST["post_id"];

	$nonce = $_POST["wpnonce"];
	if (!wp_verify_nonce($nonce, 'wpdf_security_nonce')) {
		echo json_encode(array("error" => "Invalid request."));
		exit;
	}	

	if(empty($src)) {
		echo json_encode(array("error" => "No image source found."));
		exit;	
	}
	
	if(empty($post_id)) {
		echo json_encode(array("error" => "No post found. This feature requires that an auto-save or draft of the current post was saved first."));
		exit;	
	}

	$result = media_sideload_image($src, $post_id);
	$attachments = get_posts(array('numberposts' => '1', 'post_parent' => $post_id, 'post_type' => 'attachment', 'post_mime_type' => 'image', 'orderby' => 'post_date', 'order' => 'DESC'));

	$newsrc = wp_get_attachment_image_src( $attachments[0]->ID, "full" );
	
	if(empty($newsrc)) {
		echo json_encode(array("error" => "Image could not be saved."));
		exit;	
	} else {
		echo json_encode(array("result" => $newsrc[0]));
		exit;		
	}	
}

?>