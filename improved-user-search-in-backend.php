<?php/*Plugin Name: Improved User Search in BackendPlugin URI: http://www.blackbam.at/blackbams-blog/2011/06/27/wordpress-improved-user-search-first-name-last-name-email-in-backend/Description:  Improves the search for users in the backend significantly: Search for first name, last, email and more of users instead of only nicename.Version: 1.1.1Author: David St&ouml;cklAuthor URI: http://www.blackbam.at/*//* version check */global $wp_version;$exit_msg='Improved User Search in Backend requires WordPress version 3.0 or higher. <a href="http://codex.wordpress.org/Upgrading_Wordpress">Please update!</a>';if(version_compare($wp_version,"3.0","<")) {	exit ($exit_msg);}// all of this is only for adminsif(is_admin()) {	// add the overwrite actions for the search    add_action('pre_user_query', 'user_search_by_multiple_parameters');	// add the backend menu page	add_action('admin_menu','improved_user_search_in_backend_options');   // the actual improvement of the query    function user_search_by_multiple_parameters($wp_user_query) {        if(false === strpos($wp_user_query -> query_where, '@') && !empty($_GET["s"])) {            global $wpdb;            $uids=array();			// get the custom meta fields to search			$iusib_custom_meta = get_option('iusib_meta_fields');			$iusib_cma = array_map('trim', explode(",",$iusib_custom_meta));			$iusib_add = "";			if(!empty($iusib_cma)) {				$iusib_add = " OR meta_key='".implode("' OR meta_key='",$iusib_cma)."'";			}            $usermeta_affected_ids = $wpdb -> get_results("SELECT DISTINCT user_id FROM " . $wpdb -> prefix . "usermeta WHERE (meta_key='first_name' OR meta_key='last_name'".$iusib_add.") AND meta_value LIKE '%" . mysql_real_escape_string($_GET["s"]) . "%'");            foreach($usermeta_affected_ids as $maf) {                array_push($uids,$maf->user_id);            }            $users_affected_ids = $wpdb -> get_results("SELECT DISTINCT ID FROM " . $wpdb -> prefix . "users WHERE user_nicename LIKE '%" . mysql_real_escape_string($_GET["s"]) . "%' OR user_email LIKE '%" . mysql_real_escape_string($_GET["s"]) . "%'");            foreach($users_affected_ids as $maf) {                if(!in_array($maf->ID,$uids)) {                    array_push($uids,$maf->ID);                }            }            $id_string = implode(",",$uids);            $wp_user_query -> query_where = str_replace("user_nicename LIKE '%" . mysql_real_escape_string($_GET["s"]) . "%'", "ID IN(" . $id_string . ")", $wp_user_query -> query_where);        }        return $wp_user_query;    }    // add the options page    function improved_user_search_in_backend_options() {    	add_options_page('User Search','User Search',    	'manage_options',__FILE__,'improved_user_search_in_backend_page');    }	// add the options page	function improved_user_search_in_backend_page() { ?>		<div class="wrap">			<div><?php screen_icon('options-general'); ?></div>			<h2>Settings: Improved user search in backend</h2>			<?php			if(isset($_POST['improved_user_search_in_backend_update']) && $_POST['improved_user_search_in_backend_update']!="") {				update_option('iusib_meta_fields',$_POST['iusib_meta_fields']);				?>				<div id="message" class="updated">Settings saved successfully</div>			<?php }			?>			<form name="improved_user_search_in_backend_update" method="post" action="">				<div>					<table class="form-table">						<tr valign="top">							<th scope="row">Custom Meta Fields (comma seperated)</th>							<td><textarea name="iusib_meta_fields" rows="6" cols="50"><?php echo get_option('iusib_meta_fields'); ?></textarea></td>							<td class="description">add custom user meta fields from your usermeta table for integration in the user search (e.g. "url", "description", "aim", or custom like "birthday")</td>						</tr>					</table>					<p></p>					<p><input type="hidden" name="improved_user_search_in_backend_update" value="true" />					<input type="submit" name="Save" value="Save Settings" class="button-primary" /></p>				</div>			</form>		</div>	<?php }}register_activation_hook(__FILE__,"improved_user_search_in_backend_activate");function improved_user_search_in_backend_activate() {	register_uninstall_hook(__FILE__,"improved_user_search_in_backend_uninstall");}function improved_user_search_in_backend_uninstall() {	// delete all options, tables, ...	delete_option('iusib_meta_fields');}?>