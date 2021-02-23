<?php
/*
 Plugin Name: Export Assist
 Plugin URI: https://www.shoaiybsysa.ga/export-assist
 Description: The only Plugin that help you to easily Export Wordpress Posts and Pages to Blogspot in XML format.
 Version: 1.0
 Author: shoaiyb sysa
 Author URI: https://www.shoaiybsysa.ga
 Text Domain: export-assist
 Domain Path: /languages/
*/
const PLUGIN_NAME = 'Export Assist';

load_plugin_textdomain( 'export-assist', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );

//Add export and option
add_action( 'wp_loaded', 'ew2bc_download' );
add_action( 'admin_menu', 'ew2bc_main' );

function ew2bc_main(){
	if ( is_admin() && current_user_can('export') ) {
		add_options_page( PLUGIN_NAME, PLUGIN_NAME, 'administrator', __FILE__, 'ew2bc_mainpage' );
		add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'ew2bc_plugin_action_links' );
	}
}

function ew2bc_download(){
	if ( isset($_GET["ew2bc_download"]) && ctype_xdigit($_GET["ew2bc_download"]) && is_admin() && isset($_GET["ew2bc_nonce"]) && current_user_can('export') ) {
		require_once dirname(__FILE__) . '/go.php';
		die();
	}
}

function ew2bc_admin_notices() {
	$noticenum = 0;
?>
	<?php if ( $errors = get_transient( 'ew2bc-errors' ) ): ?>
	<div class="notice notice-error is-dismissible">
		<ul>
			<?php foreach( $errors as $message ): ?>
				<li><span class="dashicons dashicons-warning"></span> <?php echo esc_html($message); ?></li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php delete_transient( 'ew2bc-errors' ); ?>
	<?php $noticenum++; ?>
	<?php endif; ?>
	
	<?php if ( $updated = get_transient( 'ew2bc-updated' ) ): ?>
	<div class="notice notice-success is-dismissible">
		<ul>
			<?php foreach( $updated as $message ): ?>
				<li><span class="dashicons dashicons-yes"></span> <?php echo esc_html($message); ?></li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php $noticenum++; ?>
	<?php delete_transient( 'ew2bc-updated' ); ?>
	<?php endif; ?>
	
	<?php if ( $noticenum == 0 ): ?>
		<div class="notice notice-warning is-dismissible">
			<ul>
				<li><span class="dashicons dashicons-warning"></span> <?php _e('Please make sure to backup your Website data before using this plugin.', 'export-assist' ); ?></li>
			</ul>
		</div>
	<?php endif; ?>
<?php
}

function ew2bc_mainpage() {
	$cats = get_terms(array (
		'taxonomy' => 'category',
		'hide_empty' => false,
		'fields' => 'ids'
	));
	if (!$cats) {
		echo "<p align='center'><strong><?php _e('Cannot find categories.', 'export-assist' ); ?></strong></p>";
	}
?>
	<style>
		.ew2bc_form tr{	height:1.5em; line-height:1.5; border-bottom: 1px solid #ccc; }
		.ew2bc_form th{ width:auto; font-weight:normal;	}
		.ew2bc_form .alternate th{ font-weight:bold; }
		.ew2bc_form td{ width:30px; }
		.ew2bc_form th, .ew2bc_form td{ padding:4px 0 4px 0; }
		.ew2bc_box{ margin:14px 0 14px 0; }
		.ew2bc_option{ width:75%; float:left; }
		.ew2bc_other{ position:relative; width:calc( 25% - 14px - 28px); padding:7px; float:right; background-color:#fff; border-radius:4px; }
		.ew2bc_other a{ position:relative; text-decoration:none; display:block;	padding:7px; }
		.other_icon{ width:20px; height:20px; font-size:1.2em; vertical-align: middle; display:inline-block; float:left; padding:0; margin:0 4px 0 0; text-decoration:none; }
		.rssicon{ color:#ee802f; }
		.youtubeicon{ color:#ff0000; }
		.other_title{ font-weight:bold;	font-size:1.2em; text-decoration:underline; display:block; line-height:1; min-height: 20px; }
		.other_desc{ font-size:0.9rem; color:#666; float:none; }
		.ew2bc_other a:hover { opacity:0.5; }
		.ew2bc_other hr{ border-top:1px solid #ccc; }
		.hide{ display:none; }
		.show{ display:block; }
	</style>
	<div class="wrap">
	<h2><?php echo PLUGIN_NAME; ?></h2>
	<?php ew2bc_admin_notices(); ?>
	<div id="ew2bc_message"></div>
		<div class="ew2bc_option">
			<form name="ew2bc_form">
				<div class="ew2bc_box">
					<span onclick="ew2bc_export();" class="button button-primary ew2bc_butoon"><?php _e('Export XML', 'export-assist' ); ?></span>
				</div>
				<h3><?php _e('Options', 'export-assist' ); ?></h3>
				<div class="ew2bc_box">
					<?php _e('Type:', 'export-assist' ); ?>
					<select name="type" id="type" onClick="toggle_type();">
					<option value="post"><?php _e('Post', 'export-assist' ); ?></option>
					<option value="page"><?php _e('Page', 'export-assist' ); ?></option>
					</select>
				</div>
					
				<div class="ew2bc_box">
					<?php _e('Status:', 'export-assist' ); ?>
					<select name="status" id="status">
					<option value="any"><?php _e('Any', 'export-assist' ); ?></option>
					<option value="publish"><?php _e('Publish', 'export-assist' ); ?></option>
					<option value="draft"><?php _e('Draft', 'export-assist' ); ?></option>
					<option value="future"><?php _e('Future', 'export-assist' ); ?></option>
					<option value="private"><?php _e('Private', 'export-assist' ); ?></option>
					<option value="trash"><?php _e('Trash', 'export-assist' ); ?></option>
					</select>
				</div>
					
				<div class="ew2bc_box" id="ew2bc_option_label">
					<?php _e('Convert to Blogger label:', 'export-assist' ); ?>
					<input class='labelin' type='checkbox' id='cat2label' name='cat2label' value='on' checked><label for='cat2label'><?php _e('Categories', 'export-assist' ); ?></label>
					<input class='labelin' type='checkbox' id='tag2label' name='tag2label' value='on' checked><label for='tag2label'><?php _e('Tags', 'export-assist' ); ?></label>
				</div>	
					
				<div class="ew2bc_box" id="ew2bc_option_category">
					<table class="form-table ew2bc_form">
						<tbody>
							<tr class="alternate"><th><input type='checkbox' id='toggle' name='toggle' value='on' onClick="toggle_category_checkboxes();"></th><th><?php _e('Select category to export', 'export-assist' ); ?></th></tr>
							<?php
							foreach($cats as $i => $value) {
								echo "<tr>";
								$catid = (int)$cats[$i];
								$catname = esc_html( get_cat_name($cats[$i]) );
								echo "<td>";
								echo "<input class='categorycheck' type='checkbox' id='cat".$catid."' name='category[]' value='".$catid."'></td>";
								echo "</td>";
								echo "<th scope='row'><label for='cat".$catid."'>".$catname."</label></th>";
								echo "</tr>";
							}
							?>
						</tbody>
					</table>
				</div>
			</form>
		</div>
		<div class="ew2bc_other">
			<a href="<?php _e('https://www.shoaiybsysa.ga/get-started-with-export-assist', 'export-assist' ); ?>" target="_blank" rel="noopener">
				<span class="other_title"><span class="dashicons dashicons-editor-help other_icon helpicon"></span><?php _e('How to use this plugin', 'export-assist' ); ?></span>
			</a>
			
	<script type="text/javascript">
		//cookie check
		var arr = new Array();
		if(document.cookie != ''){
			var tmp = document.cookie.split('; ');
			for(var i=0;i<tmp.length;i++){
				var data = tmp[i].split('=');
				arr[data[0]] = decodeURIComponent(data[1]);
			}
		}
		document.ew2bc_form.type.options[parseFloat(arr["ew2bc_type"])].selected = true;
		document.ew2bc_form.status.options[parseFloat(arr["ew2bc_status"])].selected = true;
		if( arr["ew2bc_cat2label"] == "false" ){
			document.ew2bc_form.cat2label.checked = false;
		}
		if( arr["ew2bc_tag2label"] == "false" ){
			document.ew2bc_form.tag2label.checked = false;
		}
		var tmpcat = arr["ew2bc_cats"].split('/');
		for(var i=0;i<tmpcat.length;i++){		
			if ( parseFloat(tmpcat[i]) === parseFloat(tmpcat[i]) ){
				document.getElementById('cat' + parseFloat(tmpcat[i])).checked = true;
			}
		}
		
		//if page selected, hide options
		if(document.ew2bc_form.type.options[parseFloat(document.ew2bc_form.type.selectedIndex)].value == "page"){
			document.getElementById('ew2bc_option_category').classList.add('hide');
			document.getElementById('ew2bc_option_label').classList.add('hide');
		}
		
		//when export button submitted
		function ew2bc_export() {
			//set cookie
			document.cookie = 'ew2bc_type=' + parseFloat(document.ew2bc_form.type.selectedIndex);
			document.cookie = 'ew2bc_status=' + parseFloat(document.ew2bc_form.status.selectedIndex);
			document.cookie = 'ew2bc_cat2label=' + Boolean(document.ew2bc_form.cat2label.checked);
			document.cookie = 'ew2bc_tag2label=' + Boolean(document.ew2bc_form.tag2label.checked);

			var inputlist = document.getElementsByTagName("input");
			var cats = new Array();
			for (i = 0; i < inputlist.length; i++) {
				if ( inputlist[i].getAttribute("type") == 'checkbox' && inputlist[i].getAttribute("class") == 'categorycheck' ) { 
					if (inputlist[i].checked) {
						cats.push( parseFloat(inputlist[i].getAttribute("value")) );
					}
				}
			}
			document.cookie = 'ew2bc_cats=' + cats.join("/");
			
			//get download uuid
			var uuid = new Date().getTime().toString(16) + Math.floor(1000*Math.random()).toString(16);
			//get nonce
			var nounce = '<?php echo wp_create_nonce( 'ew2bc_get_download' ); ?>';
			//get type
			var type = '';
			switch( document.ew2bc_form.type.options[parseFloat(document.ew2bc_form.type.selectedIndex)].value ) {
				case 'post':
					type = 'post';
					break;
				case 'page':
					type = 'page';
					break;
			}
			//get status;
			var status = '';
			switch( document.ew2bc_form.status.options[parseFloat(document.ew2bc_form.status.selectedIndex)].value ) {
				case 'any':
					status = 'any';
					break;
				case 'publish':
					status = 'publish';
					break;
				case 'draft':
					status = 'draft';
					break;
				case 'future':
					status = 'future';
					break;
				case 'private':
					status = 'private';
					break;
				case 'trash':
					status = 'trash';
					break;
			}
			var set = "&ew2bc_download=" + uuid + "&ew2bc_nonce=" + nounce + "&type=" + type + "&status=" + status;
			//get categories to label option
			if(document.ew2bc_form.cat2label.checked){
				set += "&cat2label=1";
			}
			//get tags to label option
			if(document.ew2bc_form.tag2label.checked){
				set += "&tag2label=1";
			}
			//get categories option
			set += "&category=" + cats.join(",");
			//get download page
			location.href = 'options-general.php?page=export-assist%2Fplugin.php' + set;
			//download complete check;
			var reload = 0;
			var onComplete = function(){
				if (get_cookie_value('ew2bc_downloaded') == uuid) {
					//reload after download;
					location.href = 'options-general.php?page=export-assist%2Fplugin.php';
				}
				else{
					if(reload < 15){
						reload++;
						setTimeout(onComplete, 1000);
					}else{
						//timeout message
						const e = document.createElement('div'); 
						e.innerHTML = "<ul><li><span class='dashicons dashicons-warning'></span> <?php _e('Too long processing time.', 'export-assist' ); ?></li><li><?php _e('Please check your export settings if the file is not exported.', 'export-assist' ); ?></li></ul>";
						e.classList.add('notice');
						e.classList.add('notice-error');
						e.classList.add('is-dismissible');
						document.getElementById('ew2bc_message').appendChild(e);
					}
				}
			}
			onComplete();

		}
		//post or page toggle, hide category and label options
		function toggle_type() {
			if(document.ew2bc_form.type.options[parseFloat(document.ew2bc_form.type.selectedIndex)].value == "page"){
				document.getElementById('ew2bc_option_category').classList.remove('show');
				document.getElementById('ew2bc_option_label').classList.remove('show');
				document.getElementById('ew2bc_option_category').classList.add('hide');
				document.getElementById('ew2bc_option_label').classList.add('hide');
			}else{
				document.getElementById('ew2bc_option_category').classList.remove('hide');
				document.getElementById('ew2bc_option_label').classList.remove('hide');
				document.getElementById('ew2bc_option_category').classList.add('show');
				document.getElementById('ew2bc_option_label').classList.add('show');
			}
		}
		//toggle categories when check all button changed
		function toggle_category_checkboxes() {
			var inputlist = document.getElementsByTagName("input");
		
			for (i = 0; i < inputlist.length; i++) {
				if ( inputlist[i].getAttribute("type") == 'checkbox' && inputlist[i].getAttribute("class") == 'categorycheck' ) { 
					if (document.ew2bc_form.toggle.checked) {
						inputlist[i].checked = true
					}else{
						inputlist[i].checked = false;
					}
				}
			}
		}
		//just check one cookie value
		function get_cookie_value(name){
			var result = '',
				key = name+'=',
				_cookie = document.cookie,
				_s = _cookie.indexOf(key),
				_e = _cookie.indexOf(';',_s);

			_e = _e === -1 ? _cookie.length : _e;
			result = decodeURIComponent(_cookie.substring(_s,_e)).replace(key,'');
			return result;
		}
	</script>
<?php
}

function ew2bc_plugin_action_links( $links ) {
	$links[] = '<a href="'. esc_url( get_admin_url(null, 'options-general.php?page=export-assist%2Fplugin.php') ) .'">'. __('Setting', 'export-assist' ).'</a>';
	return $links;
}

?>
