<script src="<?php echo get_template_directory_uri() ; ?>/assets/global/plugins/jquery.min.js" type="text/javascript"></script>
<style>
.download_link{cursor: pointer;color: blue;}
</style>
<script>
jQuery(document).ready(function() {

jQuery('.folder_cont').click(function(){
	
	var current_element = jQuery(this).attr('id');
	jQuery('#'+current_element+'').siblings('ul').slideToggle('fast');
});

});
function download(file_id){
	
	window.open('http://localhost/wordpress/api/box/?file_id='+file_id+'','1420635723925','width=700,height=270,toolbar=0,menubar=0,location=0,status=1,scrollbars=1,resizable=1,left=0,top=0');return false;
}
</script>
<?php

	include 'wp-content/plugins/box/api/api_box.php';
	if(	!array_key_exists('access_token', $_SESSION) ||
		array_key_exists('access_token', $_SESSION) && $_SESSION['access_token'] == ''){

		$current_user = wp_get_current_user();
		$token_in_db = Box::get_refresh_token_from_db($current_user->user_login);
		if(!$token_in_db){
			
			Box::setup_first_time_token($current_user->user_login);
		}
		else{

			$_SESSION['refresh_token'] = $token_in_db[0]->refresh_token;
			Box::get_access_token_from_system('refresh_token', $current_user->user_login);
		}
	}
	//var_dump($_SESSION);
	if(isset($_REQUEST['file_id'])){
		
		echo '<h2>TEST SITE will start downloading your file from BOX automatically...</h2>';
		Box::download_file($_REQUEST['file_id']);
	}
	else{
		$data = Box::get('whole_tree');
		if($data['http_code'] == 401){
			Box::get_access_token_from_system('refresh_token', $current_user->user_login);
			$data = Box::get('whole_tree');
		}
		Box::render_tree($data['result']->item_collection->entries);
		/*
		$data = get_option('box_tree');print_r($data);
		if(!$data){
			$data = Box::get('whole_tree');
			if($data['http_code'] == 401){
				Box::get_access_token_from_system('refresh_token', $current_user->user_login);
				$data = Box::get('whole_tree');
			}
			Box::render_tree($data['result']->item_collection->entries);
		}
		Box::render_tree($data['result']->item_collection->entries);
		*/
	}
?>