<?php
	
	$options = get_option( 'box-api-options' );

	define('CLIENT_ID', $options['client_id']);
	define('CLIENT_SECRET', $options['client_secret']);
	define('API_KEY', $options['api_key']);

	class Box{
		
		public static function setup_first_time_token($user_login){

			if(isset($_REQUEST['code'])){
			
				echo '<h2>Please wait, TEST SITE is integrating your BOX account ...</h2>';
				Box::get_access_token_from_system('new', $user_login);
				echo '<script> window.location = "http://localhost/wordpress/api/box"; </script>';
				exit;
			}
			echo '<script> window.location = "https://app.box.com/api/oauth2/authorize?response_type=code&client_id='.CLIENT_ID.'&state=security_token%3DKnhMJatFipTAnM0nHlZA"; </script>';
		}
		public static function get_access_token_from_system($request_type, $user_login){
			
			if($request_type == 'new'){

				$request = "grant_type=authorization_code&code=".$_REQUEST['code']."";
			}
			else{

				$request = "grant_type=refresh_token&refresh_token=".$_SESSION['refresh_token']."";
			}

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "https://www.box.com/api/oauth2/token");
			//curl_setopt($ch, CURLOPT_HEADER, false); 
			//curl_setopt($ch, CURLOPT_HTTPGET,true);
			curl_setopt($ch, CURLOPT_POST,true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $request."&client_id=".CLIENT_ID."&client_secret=".CLIENT_SECRET."");
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
			$result = curl_exec($ch);
			if(curl_errno($ch))
			{
				echo 'error:' . curl_error($ch);
			}
			curl_close($ch);
			$response = json_decode($result);
			$_SESSION['access_token'] = $response->access_token;
			$_SESSION['refresh_token'] = $response->refresh_token;

			global $wpdb;
			$table_name = $wpdb->prefix.'box_refresh_tokens';

			if(self::get_refresh_token_from_db($user_login)){

				$wpdb->update('box_refresh_tokens', array('refresh_token' => $response->refresh_token), array('user_login' => $user_login));
			}
			else{
				
				$data_to_be_inserted = array('refresh_token' => $response->refresh_token, 'user_login' => $user_login);
				$wpdb->insert($table_name, $data_to_be_inserted);
			}
		}
		public static function get_refresh_token_from_db($user_login){
			
			global $wpdb;
			$table_name = $wpdb->prefix.'box_refresh_tokens';
			return $wpdb->get_results('SELECT refresh_token FROM '.$table_name.' WHERE user_login = "'.$user_login.'"');
		}
		public static function get($request_type, $resource_id = 0){
			
			if($request_type == 'whole_tree')
				$url = 'https://api.box.com/2.0/folders/0';
			else if($request_type == 'folder')
				$url = 'https://api.box.com/2.0/folders/'.$resource_id.'';
			else if($request_type == 'file')
				$url = 'https://api.box.com/2.0/files/'.$resource_id.'';

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$_SESSION['access_token'].''));
			$result = curl_exec($ch);
			$http_code = curl_getinfo ( $ch, CURLINFO_HTTP_CODE );
			if(curl_errno($ch))
			{
				echo 'error:' . curl_error($ch);
			}
			curl_close($ch);

			$data['result'] = json_decode($result);
			$data['http_code'] = $http_code;
			
			$_SESSION['tree'] = $data['result'];
			return $data;
		}
		public static function render_tree($items){

			echo '<ul>';
			krsort($items);
			foreach($items as $item){
				
				if($item->type == 'file'){

					echo '<li><div class="download_link" onclick="download('.$item->id.')">'.$item->type.'--'.$item->name.'---'.$item->id.'</div></li>';
				}
				else{
					
					echo '<li><div id="'.$item->id.'" class="folder_cont">'.$item->type.'--'.$item->name.'---'.$item->id.'</div>';
					$data = self::get($item->type, $item->id);
					if($data['http_code'] == 401){
						self::get_access_token_from_system('refresh_token');
						$data = self::get($item->type, $item->id);
					}
					self::render_tree($data['result']->item_collection->entries);
					echo'</li>';
				}
			}
			echo '</ul>';
			return;
		}
		public static function download_file($id) {
	
			$download_url = '';
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "https://api.box.com/2.0/files/".$id."/content");
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$_SESSION['access_token'].''));
			$result = curl_exec($ch);
			$http_code = curl_getinfo ( $ch, CURLINFO_HTTP_CODE );
			if(curl_errno($ch))
			{
				echo 'error:' . curl_error($ch);
			}
			if($http_code == 302)
				$download_url = curl_getinfo ( $ch, CURLINFO_REDIRECT_URL );
			curl_close($ch);
			if($download_url)
				echo '<script> window.location = "'.$download_url.'"; </script>';
		}
	}

?>