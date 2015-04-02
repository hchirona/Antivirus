<?php 


class google_oauth{

		private $options     = array();
		private $req_get_val = array(); 
		private $post_val    = array();

		
		public function backup_auth() {
					
					$this->req_get_val = $_GET;
					$this->post_val    = $_POST;
					
					if($this->req_get_val['page']!='configure_google'){
						  return false;
					}
					
					if ($this->req_get_val['action'] == 'auth' ) {
						 $this->google_auth_section();
					}
				
					$this->reset_google_api();
		}
		
		private function reset_google_api(){
		
					if ($this->req_get_val['action'] == 'reset' ) {
							$options['token'] = '';
							update_option( 'google_drive_backup', $options );
							$msg_obj   = new notify_message("configure_google","You have now successfully re-configured the Google Drive settings.");
							$msg_obj->Gdrive_message();
					}		
		
		}
		
		private function google_auth_section(){
					if($this->req_get_val['state'] == 'token' ){
								$this->access_token_method();
					}else {
								$this->auth_google_acc();
					}
		}
		
		private function access_token_method(){
		
					$this->options = get_option( 'google_drive_backup' );
					if( isset( $this->req_get_val['code'] ) ) {
							$this->google_api_configure();
					}else{
							$msg_obj   = new notify_message('configure_google','Configuration to Google API Access has been failed');
							$msg_obj->Gdrive_error();
					}
		
		}
		
		private function google_api_configure(){
		
					$params = array(
						'body' => array(
							'code' => $this->req_get_val['code'],
							'client_id' => $this->options['client_id'],
							'client_secret' => $this->options['client_secret'],
							'redirect_uri' => admin_url('admin.php?page=configure_google&action=auth'),
							'grant_type' => 'authorization_code'
						)
					);
					
					$result = (array)wp_remote_post('https://accounts.google.com/o/oauth2/token', $params );
					
					switch($result['response']['code']){
						case 200:
							$result = json_decode( $result['body'], true );
							
							if (isset( $result['refresh_token'] ) ) {
								$options['client_id']     = $this->options['client_id'];
								$options['client_secret'] = $this->options['client_secret'];
								$options['token'] = $result['refresh_token']; // Save token
								update_option('google_drive_backup', $options);
								$msg_obj   = new notify_message("configure_google","Configuration to Google API Access has been done successfully");
								$msg_obj->Gdrive_message();
							}
						
						break;
						
						default: 
								if(empty($result['response']['message'])){
									$result['response']['message'] = "Configuration to Google API Access has been Failed";
								}
								$msg_obj   = new notify_message("configure_google", $result['response']['message']);
								$msg_obj->Gdrive_message();
					
					}
		
		}
		
		private function auth_google_acc(){
		
							$options['client_id']     = $this->post_val['client_id'];
							$options['client_secret'] = $this->post_val['client_secret'];
							update_option( 'google_drive_backup', $options );
							$params = array(
								'scope' => 'https://www.googleapis.com/auth/drive.file https://docs.google.com/feeds/ https://docs.googleusercontent.com/ https://spreadsheets.google.com/feeds/',
								'state' => 'token',
								'redirect_uri' => admin_url('admin.php?page=configure_google&action=auth'),
								'response_type' => 'code',
								'client_id' => $this->post_val['client_id'],
								'access_type' => 'offline',
								'approval_prompt' => 'force'
							);
							
							header('Location: https://accounts.google.com/o/oauth2/auth?'.http_build_query($params));
		
		}

}

?>