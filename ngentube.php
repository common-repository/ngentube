<?php
/*
Plugin Name: NgenTube
Plugin URI: http://nartzco.com/blog/2009/10/03/ngentube/
Description: This is a simple plugin for download and display youtube video on your wordpress blog.
Version: 1.0
Author: Cornelius Sunarko
Author URI: http://nartzco.com/

Copyright 2009, Cornelius Sunarko

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

register_activation_hook( __FILE__, array('ngentube', 'activate'));
register_deactivation_hook( __FILE__, array('ngentube', 'deactivate'));
add_action('wp_head', array('ngentube', 'style'));
add_action('wp_head', array('ngentube', 'script'));
add_action('admin_menu', array('ngentube','admin_menu'));
add_filter( 'plugin_action_links', array('ngentube', 'link'), 10, 2 );

if(!class_exists('ngentube')){
	class ngentube {
	
		function link( $links, $file ){
			static $this_plugin;
			if ( ! $this_plugin ) $this_plugin = plugin_basename(__FILE__);
			
			if ( $file == $this_plugin ){
				$settings_link = '<a href="options-general.php?page=ngentube">' . __('Settings') . '</a>';
				array_unshift( $links, $settings_link ); // before other links
			}
			return $links;
		}
		
		function admin_menu(){
			add_options_page('NgenTube', 'NgenTube', 8, 'ngentube', array('ngentube','control'));
		}
		
		function control() {
			$options = $newoptions = get_option('ngentube');
			if($_POST["ngentube_action"]) {
				//print_r($_POST);
				$newoptions['width'] 	= strip_tags(stripslashes($_POST["ngentube_width"]));
				$newoptions['height'] 	= strip_tags(stripslashes($_POST["ngentube_height"]));
				$newoptions['link'] 	= strip_tags(stripslashes($_POST["ngentube_link"]));
				$newoptions['related'] 	= strip_tags(stripslashes($_POST["ngentube_related"]));
				if(empty($newoptions['width'])) $newoptions['width'] = 300;
				if(empty($newoptions['height'])) $newoptions['height'] = 200;
				if(trim($newoptions['link'])=="") $newoptions['link'] = 0;
				if(trim($newoptions['related'])=="") $newoptions['related'] = 0;
			}
			if ($options != $newoptions) {
				$options = $newoptions;
				//print_r($options);
				update_option('ngentube', $options);
			}
			
			$width	= htmlspecialchars($options['width'], ENT_QUOTES);
			$height	= htmlspecialchars($options['height'], ENT_QUOTES);
			$link	= htmlspecialchars($options['link'], ENT_QUOTES);
			$related= htmlspecialchars($options['related'], ENT_QUOTES);
?>
			<h2>Ngentube is a simple plugin for download and display youtube video on your wordpress blog</h2>
			<em>WordPress plugin written by <a href="http://nartzco.com">Cornelius Sunarko</a> </em><br /><br />
			<form method="post" action="options-general.php?page=ngentube.php">
			<table>
			<tr>
			<td><?php _e('Video Width:'); ?></td><td><input	style="width: 100px;" id="ngentube_width" name="ngentube_width"	type="text" value="<?php echo $width; ?>" /></td>
			</tr>
			<tr>
			<td><?php _e('Video Height:'); ?></td><td><input	style="width: 100px;" id="ngentube_height" name="ngentube_height"	type="text" value="<?php echo $height; ?>" /></td>
			</tr>
			<tr>
			<td><?php _e('Download Link:'); ?></td>
			<td>
				<select name="ngentube_link">
					<option value="1"<?php echo($link=="1"?" selected":"")?>>Yes</option>
					<option value="0"<?php echo($link=="1"?"":" selected")?>>No</option>
				</select>
			</td>
			</tr>
			<tr>
			<td><?php _e('Include Related Videos:'); ?></td>
			<td>
				<select name="ngentube_related">
					<option value="1"<?php echo($related=="1"?" selected":"")?>>Yes</option>
					<option value="0"<?php echo($related=="1"?"":" selected")?>>No</option>
				</select>
			</td>
			</tr>
			</table>
			<input type="hidden" id="ngentube_action" name="ngentube_action" value="1" /><br />
			<input type="submit" id="ngentube_submit" name="ngentube_submit" value="Save Settings" />
			</form>
<?php
		}
			
		function style() {
?>
<style type="text/css">
.ngentube {
	display: none;
}
.angentube{
	margin-top: 5px;
	margin-bottom: 5px;
	color:#96D500;
	cursor: pointer;
	width: 100px;
}
</style>
<?php
		}
		
		function script(){
			$opt = get_option('ngentube');
?>
<script type='text/javascript' src='http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js'></script>
<script language="javascript">
/*
Plugin Name: NgenTube
Plugin URI: http://nartzco.com/blog/2009/10/03/ngentube/
Description: This is a simple plugin for download and display youtube video on your wordpress blog.
Version: 1.0
Author: Cornelius Sunarko
Author URI: http://nartzco.com/

Copyright 2009, Cornelius Sunarko
*/
var getId = function(id){
	var re = "";
	if(id.search(/www.youtube.com/) != -1){
		var x1 = id.split("?");
		if(x1.length>1){
			var x2 = x1[1].split("&");
			for(var i=0;i<x2.length;i++){
				if(x2[i].match(/^v=/)){
					var x3 = x2[i].split("=");
					if(x3.length>1){
						re = x3[1];
					}
				}
			}
		}
	}
	else 
		re = id;
	return re;
};
$(document).ready(function() {
	$(document.body).before("<iframe id=\"ifngentube\" style=\"display:none\" />");
    $(".ngentube").each(function(i){
		this.className = "";
		var w = <?php echo $opt['width'];?>;
		var h = <?php echo $opt['height'];?>;
		var r = <?php echo $opt['related'];?>;
		var id= getId($.trim($(this).html()));
		var em 	= "<object width=\""+w+"\" height=\""+h+"\">"
				+ "<param name=\"movie\" value=\"http://www.youtube.com/v/"+id+"&hl=en&fs=1&rel="+r+"\"></param>"
				+ "<param name=\"allowFullScreen\" value=\"true\"></param>"
				+ "<param name=\"allowscriptaccess\" value=\"always\"></param>"
				+ "<embed "
				+ "src=\"http://www.youtube.com/v/"+id+"&hl=en&fs=1&rel="+r+"\" "
				+ "type=\"application/x-shockwave-flash\" "
				+ "allowscriptaccess=\"always\" "
				+ "allowfullscreen=\"true\" "
				+ "width=\""+w+"\" "
				+ "height=\""+h+"\""
				+ ">"
				+ "</embed>"
				+ "</object>";
		$(this).html(em);
		<?php if($opt['link']=="1"): ?>
		var div = document.createElement("div");
		$(div).html("download").click(function(){
			var src = "http://nartzco.com/ytube/?wp=yes&v=http://www.youtube.com/watch?v="+id;
			$("#ifngentube").attr("src",src);
		});
		$(div).attr("class","angentube");
		$(this).append(div);
		<?php endif;?>
    });
});
</script>
<?php
		}
		
		function activate(){
			$data = array(
				'link' => 0,
				'related' => 0,
				'width' => 300,
				'height'=> 200
			);
	    	if (!get_option('ngentube')){
	      		add_option('ngentube', $data);
	    	} else {
	      		update_option('ngentube', $data);
	    	}		
		}
		
		function deactivate(){
			delete_option('ngentube');
		}
	}
}
?>