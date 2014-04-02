
var resultCount = (function (module) {
	var state = {};
	var pub = {};

	pub.changeState = function (newstate,module) {
		state[module] = newstate;
		state[module + "run"] = state[module + "run"] + 1;
	};

	pub.getState = function(module) {
		if(state[module] == undefined) {state[module] = 0;}
		return state[module];
	}
	
	pub.getRun = function(module) {
		if(state[module + "run"] == undefined) {state[module + "run"] = 1;}
		return state[module + "run"];
	}		

	return pub;
}());	

function wpdf_set_message(message, error, show_load, replace_load) {

	if(replace_load == 0) {
		var randomnumber = Math.floor(Math.random() * 99) + 1;
		if(error == 1) {var eclass = "wpdf_error";} else {var eclass = "wpdf_msg";}
		jQuery('#wpdf_message_box').append('<div id="wpdf_m' + randomnumber + '" class="' + eclass + '">' + message + '</div>');
		jQuery('#wpdf_message_box #wpdf_m' + randomnumber).slideDown(400);
	} else {
		var randomnumber = replace_load;
		if(error == 1) {
			jQuery('#wpdf_message_box #wpdf_m' + randomnumber).removeClass("wpdf_msg");
			jQuery('#wpdf_message_box #wpdf_m' + randomnumber).addClass("wpdf_error");
		}
		jQuery('#wpdf_message_box #wpdf_m' + randomnumber).html(message);	
	}
	if(show_load == 0) {
		setTimeout(function() {jQuery("#wpdf_m" + randomnumber).slideUp(400, function() {jQuery(this).remove();});}, 5600);
	}
	
	return randomnumber;
}

function wpdf_parse_content(content, attribution, feat_end) {

	//var win = window.dialogArguments || opener || parent || top;
	//win.send_to_editor(content);
	
	if(jQuery("#content").is(":visible")) {
		// HTML editor: always place at the end
		document.getElementById('content').value += content;
		document.getElementById('content').value += attribution;
	} else {

		if(wpdf_attr_location == "image" && feat_end != 1) { // determine attribution placement
			content = content + attribution;
			tinyMCE.execCommand('mceInsertContent',false,content);
		} else {
			tinyMCE.execCommand('mceInsertContent',false,content);

			var curcont = tinyMCE.activeEditor.getContent(); // tinymce.editors.content.getContent();
			tinyMCE.execCommand('mceSetContent',false,curcont + attribution);
		}
	}
}

function wpdf_get_image_size_url(imgurl, imgsize) {

	if(imgsize == "large") {
		imgurl = imgurl.replace('_m.jpg','_b.jpg');	
	} else if(imgsize == "medium") {
		imgurl = imgurl.replace('_m.jpg','.jpg');	
	} else if(imgsize == "orig") {
		imgurl = imgurl.replace('_m.jpg','_o.jpg');	
	} else if(imgsize == "square") {
		imgurl = imgurl.replace('_m.jpg','_q.jpg');	
	}	
	return imgurl;
}

function wpdf_parse_attribution_multi(content) {

	var template = wpdf_attr_template_multi;	
	template = template.replace('{linklist}', content);

	if(wpdf_wpi_attr == "1") {	
		if (template.indexOf('Photos') > -1) {
			template = template.replace('Photos', '<a style="text-decoration: none;" href="http://wpinject.com/" title="Photo inserted by the WP Inject WordPress plugin">Photos</a>');
		} else {
			template = template + '<small> via <a style="text-decoration: none;" href="http://wpinject.com/" title="Free WordPress plugin to insert images into posts">WP Inject</a></small>';
		}		
	}
	return template;
}

function wpdf_parse_attribution(item) {

	var template = wpdf_attr_template;

	var owner_name = jQuery('#' + item).find(".wpdf_result_item_save .name").text(); 
	var owner_link = jQuery('#' + item).find(".wpdf_result_item_save .link").text(); 
	
	var license = jQuery('#' + item).find(".wpdf_result_item_save .license").text(); 
	if(license == "0") {
		var license_name = "All Rights Reserved";
		var license_link = "";
	} else if(license == "1") {
		var license_name = "Attribution-NonCommercial-ShareAlike License";
		var license_link = "http://creativecommons.org/licenses/by-nc-sa/2.0/";
	} else if(license == "2") {
		var license_name = "Attribution-NonCommercial License";
		var license_link = "http://creativecommons.org/licenses/by-nc/2.0/";
	} else if(license == "3") {
		var license_name = "Attribution-NonCommercial-NoDerivs License";
		var license_link = "http://creativecommons.org/licenses/by-nc-nd/2.0/";
	} else if(license == "4") {
		var license_name = "Attribution License";
		var license_link = "http://creativecommons.org/licenses/by/2.0/";
	} else if(license == "5") {
		var license_name = "Attribution-ShareAlike License";
		var license_link = "http://creativecommons.org/licenses/by-sa/2.0/";
	} else if(license == "6") {
		var license_name = "Attribution-NoDerivs License";
		var license_link = "http://creativecommons.org/licenses/by-nd/2.0/";
	} else {
		var license_name = "";
		var license_link = "";	
	}

	if(license_name != "" && license_link != "") {
		var cc_icon = '<a rel="nofollow" href="' + license_link + '" target="_blank" title="' + license_name + '"><img src="' + wpdf_plugin_url + '/images/cc.png" /></a>';
	} else {
		var cc_icon = '';
	}
	
	template = template.replace('{keyword}', jQuery('#wpdf_keyword').val());		
	template = template.replace('{author}', owner_name);
	template = template.replace('{link}', owner_link);
	template = template.replace('{cc_icon}', cc_icon);
	template = template.replace('{license_name}', license_name);
	template = template.replace('{license_link}', license_link);
	
	if(wpdf_wpi_attr == "1") {
		template = template.replace('Photo', '<a rel="nofollow" style="text-decoration: none;" href="http://wpinject.com/" title="Image inserted by the WP Inject WordPress plugin">Photo</a>');
	}

	return template;
}

function wpdf_parse_template(item, imgsize, img, orientation) {

	var template = wpdf_img_template;

	if(img != "" && img != undefined) {
		var imgurl = img;
	} else {
		var imgurl = jQuery('#' + item).find(".wpdf_result_item_save .img").text();
		
		imgurl = wpdf_get_image_size_url(imgurl, imgsize);
	}
	
	template = template.replace('{src}', imgurl);
	template = template.replace('{keyword}', jQuery('#wpdf_keyword').val());
	
	var title = jQuery('#' + item).find(".wpdf_result_item_save .title").text(); 
	var description = jQuery('#' + item).find(".wpdf_result_item_save .description").text(); 
	var owner_name = jQuery('#' + item).find(".wpdf_result_item_save .name").text(); 
	var owner_link = jQuery('#' + item).find(".wpdf_result_item_save .link").text(); 

	template = template.replace(/\{title\}/g, title);
	template = template.replace('{description}', description);
	template = template.replace('{author}', owner_name);
	template = template.replace('{link}', owner_link);
	template = template.replace('{yoast-keyword}', jQuery('#yoast_wpseo_focuskw').val());

	if(orientation == "left") {
		template = template.replace('<img', '<img class="alignleft"');
	} else if(orientation == "right") {
		template = template.replace('<img', '<img class="alignright"');
	} else if(orientation == "center") {
		template = template.replace('<img', '<img class="aligncenter"');
	}
	
	return template;
}

jQuery(document).ready(function($) {

	jQuery('.wpdf_select_all').live("click", function(e) {
		var checkedStatus = this.checked;
		var pid = jQuery(this).parent().parent().attr("id");

		jQuery('#' + pid + ' .wpdf_select_item').each(function () {
			jQuery(this).prop('checked', checkedStatus);
		});
		if(checkedStatus == true) {
			jQuery('#wpdf_controls').slideDown(300);
			jQuery('#' + pid + ' .wpdf_result_item' + " img.wpdf_thumb").css('border-color', '#0074A2');
		} else {
			jQuery('#' + pid + ' .wpdf_result_item' + " img.wpdf_thumb").css("border-color", "");
			if(!jQuery('.wpdf_select_item:checked').length) {jQuery('#wpdf_controls').slideUp(300);}
		}	
	});		

	jQuery('img.wpdf_thumb').live("click", function(e) {
		jQuery(this).parent().parent().find(".wpdf_select_item").click();
	});	

	jQuery('.wpdf_result_item div input').click(function(e) {
		var toselect = jQuery(this).parent().parent().attr('id');
		var cid = jQuery(this).attr('id');
		var checkedStatus = jQuery("#" + cid).is(':checked'); 
		if(checkedStatus == true) {
			jQuery('#wpdf_controls').slideDown(300);
			jQuery('#' + toselect + " img.wpdf_thumb").css('border-color', '#0074A2');
		} else {
			jQuery('#' + toselect + " img.wpdf_thumb").css("border-color", "");
			if(!jQuery('.wpdf_select_item:checked').length) {jQuery('#wpdf_controls').slideUp(300);}
		}			
	});		
	
	jQuery('a#wpdf_get_seo_keyword').click(function(e) {
		e.preventDefault();	
		jQuery('#wpdf_keyword').val(jQuery('#yoast_wpseo_focuskw').val());
	});		

	jQuery('a#wpdf_get_title').click(function(e) {
		e.preventDefault();	
		jQuery('#wpdf_keyword').val(jQuery('#title').val());
	});	

	jQuery('.wpdf_remove_results').live("click", function(e) {
		e.preventDefault();	
		jQuery(this).parent().parent().remove();
	});	
	
	jQuery('#wpdf_remove_selected').click(function(e) {
		e.preventDefault();	

		jQuery("input:checkbox[name=wpdf_select_item]:checked").each(function(i) {
			var item = jQuery(this).parent().parent().attr('id');
			jQuery('#' + item).animate({width: 0}, 450, function() {
				jQuery('#' + item).remove();
			});

			/*if(i == count) {
				jQuery("#wpdf_results div").each(function() {
					
					if(!jQuery(this).jQuery('.wpdf_result_item').length) {
						jQuery(this).remove();
					}
				});	
				
				if ( jQuery('.wpdf_result_item:visible').length < 1) {
					jQuery('#wpdf_controls').hide();
				}			
			}*/
			
		});	

		return false;		
	});			

	/*jQuery('#wpdf_save_keys').click(function(e) {
		e.preventDefault();	
		
		if( !jQuery('#flickr_appid').val()) {return false;}	

		var flickrapikey = jQuery('#flickr_appid').val();

		jQuery('#wpdf_save_keys_form').html('<img src="' + wpdf_plugin_url + '/images/ajax-loader.gif" style="width: 16px; height: 16px;margin-bottom: -2px;" /></span>');			
		
		var data = {
			action: 'wpdf_save_keys',
			wpnonce: wpdf_security_nonce.security,
			flickrapi: flickrapikey
		};

		jQuery.ajax ({
			type: 'POST',
			url: ajaxurl,
			data: data,
			dataType: 'json',
			success: function(response) {
				if(response.error != undefined && response.error != "") {
					jQuery('#wpdf_save_keys_form').html(response.error);
				} else {
					jQuery('#wpdf_save_keys_form').remove();
					jQuery('#wpdf_main').show();
				}
			}
		});			

		return false;			
	});	*/	
	
	jQuery('#wpdf_insert_images_normal, #wpdf_insert_images_left, #wpdf_insert_images_right, #wpdf_insert_images_center').click(function(e) {
		e.preventDefault();	

		var orientation = jQuery(this).attr('id').replace('wpdf_insert_images_','');
		var imgsize = jQuery('input:radio[name=wpdf_size]:checked').val();
		var imgcontent = "";
		var attrcontentall = "";
		
		if(wpdf_save_images == 1) { // display loading graphic

			var loader = wpdf_set_message('<img src="' + wpdf_plugin_url + '/images/ajax-loader.gif" style="width: 16px; height: 16px;margin-bottom: -2px;" /> Saving all images to your server.', 0, 1, 0);
			var all_images = new Array();
			
			jQuery("input:checkbox[name=wpdf_select_item]:checked").each(function() {
				var item = jQuery(this).parent().parent().attr('id');
				
				var imgurl = jQuery('#' + item).find(".wpdf_result_item_save .img").text(); 
				imgurl = wpdf_get_image_size_url(imgurl, imgsize);	
				all_images.push(imgurl);
			});	

			var keyword = jQuery('#wpdf_keyword').val();
			var data = {
				action: 'wpdf_save_multiple_to_server',
				wpnonce: wpdf_security_nonce.security,
				images: all_images,
				post_id: cur_post_id,
				filename: wpdf_filename_template,
				keyword: keyword				
			};

			jQuery.ajax ({
				type: 'POST',
				url: ajaxurl,
				data: data,
				dataType: 'json',
				success: function(response) {
					if(response.error != undefined && response.error != "") {
						wpdf_set_message(response.error, 1, 0, loader);
					} else {
						if(response.result != "" && response.result != undefined) {
							var o = 0;
							jQuery("input:checkbox[name=wpdf_select_item]:checked").each(function() {
								var item = jQuery(this).parent().parent().attr('id');	
								imgurl = response.result[o];
								o = o + 1;
								
								var attrcontent = wpdf_parse_attribution(item);
								var addcontent = wpdf_parse_template(item, imgsize, imgurl, orientation);
								imgcontent += addcontent;	

								var owner_name = jQuery('#' + item).find(".wpdf_result_item_save .name").text(); 
								var owner_link = jQuery('#' + item).find(".wpdf_result_item_save .link").text(); 			
										
								attrcontentall += '<a href="' + owner_link + '">' + owner_name + '</a>, ';							
							});	
							
							var attr = wpdf_parse_attribution_multi(attrcontentall);
							
							wpdf_parse_content(imgcontent, attr, 0);
							
							wpdf_set_message("<strong>Selected images have been inserted into the editor.</strong>", 0, 0, loader);								
						} else {
							wpdf_set_message("Error saving images to server:", 1, 0, loader);
						}
					}
				}
			});					

		} else {
			var loader = 0;
			
			jQuery("input:checkbox[name=wpdf_select_item]:checked").each(function() {

				var item = jQuery(this).parent().parent().attr('id');
				
				var attrcontent = wpdf_parse_attribution(item);
				var addcontent = wpdf_parse_template(item, imgsize, "", orientation);
				imgcontent += addcontent;			

				var owner_name = jQuery('#' + item).find(".wpdf_result_item_save .name").text(); 
				var owner_link = jQuery('#' + item).find(".wpdf_result_item_save .link").text(); 			
						
				attrcontentall += '<a href="' + owner_link + '">' + owner_name + '</a>, ';
			});	

			var attr = wpdf_parse_attribution_multi(attrcontentall);
			
			wpdf_parse_content(imgcontent, attr, 0);
			
			wpdf_set_message("<strong>Selected images have been inserted into the editor.</strong>", 0, 0, loader);			
		}
	});				
	
	jQuery('a.wpdf_set_featured').live("click", function(e) {
		e.preventDefault();	

		var jthis = jQuery(this);
		var item = jQuery(this).parents().eq(4).attr('id');
		var src = jQuery('#' + item).find(".wpdf_result_item_save .img").text(); 
		var keyword = jQuery('#wpdf_keyword').val();
		
		src = wpdf_get_image_size_url(src, wpdf_feat_img_size)

		var loader = wpdf_set_message('<img src="' + wpdf_plugin_url + '/images/ajax-loader.gif" style="width: 16px; height: 16px;margin-bottom: -2px;" /> Loading...', 0, 1, 0);
		jQuery(this).hide();		
				
		/*var data = {
			action: 'wpdf_set_featured',
			wpnonce: wpdf_security_nonce.security,
			src: src,
			post_id: cur_post_id			
		};*/
		
		var data = {
			action: 'wpdf_save_to_server',
			wpnonce: wpdf_security_nonce.security,
			src: src,
			post_id: cur_post_id,
			feat_img: 1,
			filename: wpdf_filename_template,
			keyword: keyword
		};		
			
		jQuery.ajax ({
			type: 'POST',
			url: ajaxurl,
			data: data,
			dataType: 'json',
			success: function(response) {
				if(response.error != undefined && response.error != "") {
					jthis.show();
					wpdf_set_message(response.error, 1, 0, loader);
				} else {
					var attribution = wpdf_parse_attribution(item); 

					wpdf_parse_content("", attribution, 1);

					jthis.remove();

					var msg = "<strong>Featured image has been set!</strong> The required attribution has been added to the end of your article.";					
					wpdf_set_message(msg, 0, 0, loader);
				}
			}
		});			

		return false;			
	});	

	jQuery('a.wpdf_insert_small, a.wpdf_insert_medium, a.wpdf_insert_large, a.wpdf_insert_square, a.wpdf_insert_orig').live("click", function(e) {
		e.preventDefault();	
		
		var loader = wpdf_set_message('<img src="' + wpdf_plugin_url + '/images/ajax-loader.gif" style="width: 16px; height: 16px;margin-bottom: -2px;" /> Loading...', 0, 1, 0);
		
		var imgsize = jQuery(this).attr('class').replace('wpdf_insert_','');	
		var item = jQuery(this).parents().eq(4).attr('id');
		var keyword = jQuery('#wpdf_keyword').val();
		var attrcontent = wpdf_parse_attribution(item);
		
		if(wpdf_save_images == 1) {
		
			var imgurl = jQuery('#' + item).find(".wpdf_result_item_save .img").text(); 
			imgurl = wpdf_get_image_size_url(imgurl, imgsize);

			var data = {
				action: 'wpdf_save_to_server',
				wpnonce: wpdf_security_nonce.security,
				src: imgurl,
				post_id: cur_post_id,
				filename: wpdf_filename_template,
				keyword: keyword				
			};

			jQuery.ajax ({
				type: 'POST',
				url: ajaxurl,
				data: data,
				dataType: 'json',
				success: function(response) {
					if(response.error != undefined && response.error != "") {
						wpdf_set_message(response.error, 1, 0, loader);
					} else {
						imgurl = response.result;

						var addcontent = wpdf_parse_template(item, imgsize, imgurl, wpdf_default_align);
						wpdf_parse_content(addcontent, attrcontent, 0);	

						wpdf_set_message("<strong>Image has been saved to your server and inserted into the editor.</strong>", 0, 0, loader);
					}
				}
			});			
			return false;
		} else {

			var addcontent = wpdf_parse_template(item, imgsize, "", wpdf_default_align);
			wpdf_parse_content(addcontent, attrcontent, 0);

			wpdf_set_message("<strong>Image has been inserted into the editor.</strong>", 0, 0, loader);
		}
	});	
	
	jQuery('.wpdf-module').click(function(e) {

		e.preventDefault();

		var keyword = jQuery("#wpdf_keyword").val();
		var id_kw = keyword.replace(/ /g, "_"); //encodeURIComponent(keyword);//
		var module = jQuery(this).attr('id');	
		var result_num = resultCount.getState(module);

		jQuery(this).attr("disabled", "disabled");
		jQuery("#" + module + "-load").show();		
			
		if( !jQuery('#wpdfr-' + id_kw).length ) {
			module_run = 1;
		} else {
			module_run = parseInt(jQuery('#wpdf-run-' + id_kw).attr('class')) + 1;
		}
	
		var data = {
			action: 'wpdf_editor',
			wpnonce: wpdf_security_nonce.security,
			module: module,
			modulerun: module_run,
			keyword: keyword,
			ajax: 1,
			};
			
		jQuery.ajax ({
			type: 'POST',
			url: ajaxurl,
			data: data,
			dataType: 'json',
			success: function(response) {
				if(response.error != undefined && response.error != "") {
					wpdf_set_message(response.error, 1, 0, 0);

					jQuery("#" + module).removeAttr("disabled"); 
					jQuery('#' + module + '-load').hide();
				} else {
					if( !jQuery('#wpdfr-' + id_kw).length ) {
						jQuery('#wpdf_results').prepend('<div id="wpdfr-' + id_kw + '"><div id="wpdf-run-' + id_kw + '" class="' + module_run + '" style="display:none;"></div><div class="wpdf-search-title"><input type="checkbox" value="1" class="wpdf_select_all"><span>' + module + '</span> search for "<strong>' + keyword + '</strong>" - <a href="#" class="wpdf_remove_results">Remove</a></div></div>'); // show all results
					} else {
						jQuery('#wpdf-run-' + id_kw).attr('class', module_run);
					}

					for (i in response.result) {

						var clone = jQuery("#wpdf_result_item").clone(true, true);
						clone.attr("id", "wpdf_result_item_" + module + "_" + result_num);
						clone.find(".wpdf_result_item_save").html(response.result[i].content);

						// hide everything except image
						var elem = jQuery('<div>').html(response.result[i].content);
						var imgcheck = elem.find('img').attr('src');							
						if(imgcheck == undefined || imgcheck == "") {clone.find(".wpdf_set_featured").remove();clone.find(".wpdf_insert_image").remove();clone.find(".wpdf_insert_image_link").remove();}
						var linkcheck = elem.find('a').attr('href');							
						if(linkcheck == undefined || linkcheck == "") {clone.find(".wpdf_insert_link").remove();clone.find(".wpdf_insert_image_link").remove();}
						var imgurl = elem.find('.img').text();	
						var imgurl_s = imgurl.replace('_m.jpg','_s.jpg');	
						var imgurl_m = imgurl.replace('_m.jpg','.jpg');	
						
						var img_link = elem.find('.link').text();
						
						var sizelink = "";						
						elem.find('.sizes div').each(function () {
							var size_text = jQuery(this).html();
							var size_class = jQuery(this).attr('class');
							
							sizelink += '<a title="Click to insert ' + size_class + ' image" class="wpdf_insert_' + size_class + '" href="#">' + size_text + '</a>';
						});		
							
						sizelink += '<a title="Click to set featured image" class="wpdf_set_featured" href="#" >Featured Image</a>';

						clone.find(".wpdf_result_item_content").html('<img class="wpdf_thumb" src="'+ imgurl_s +'" /><div class="wpdf_big_container"><div class="wpdf_bigger"><div class="wpdf_insert_links">' + sizelink + '</div><a href="' + img_link + '" target="_blank"><img class="wpdf_bigger_img" src="'+ imgurl_m +'" /></a></div></div>');
						
						clone.find(".wpdf_select_item_o").attr('name', "wpdf_select_item");
						clone.find(".wpdf_select_item_o").attr('id', "wpdf_select_" + result_num);							
						clone.find(".wpdf_select_item_o").attr("class", "wpdf_select_item");


						clone.appendTo("#wpdfr-" + id_kw);							
						result_num = result_num + 1;
					}
					
					// Show and Update Module button
					jQuery("#" + module + "-load").hide(); 
					jQuery("#" + module).removeAttr("disabled"); 	
					resultCount.changeState(result_num, module);
					
					if(!jQuery('#wpdf_share_box').is(":visible")) {
						jQuery('#wpdf_share_box').slideDown(400);
					}
				}
			}
		});			

		return false;
	});		
});