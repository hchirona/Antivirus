<?php 

class backup_schedule{

		  private $get_value;

		  private $post_value;

		  function schedule_backup_time() {

				$this->get_value  = $_GET;
				$this->post_value = $_POST;

				if($this->get_value['action']=='settings_update'){
								
								if($this->post_value['backup_time']=='None'){
             						      wp_clear_scheduled_hook('schedule_google_drive_backup');
								}else{
									if (wp_next_scheduled( 'schedule_google_drive_backup' ) ) {
             							   wp_clear_scheduled_hook('schedule_google_drive_backup');
            						}
									wp_schedule_event( current_time( 'timestamp' ), $this->post_value['backup_time'], 'schedule_google_drive_backup');
								}
								 
					}	

			}

}

class google_drive_settings{
	
		  private $get_value;
		  private $post_value;
	
		  public function settings_option(){
				
			   $this->get_value  = $_GET;
			   $this->post_value = $_POST;
				
			   switch($this->get_value['action']){
				
					case 'settings_update':
					
						$success_msg ='';
				
						if ( !is_dir( GBACKUP_PLUGIN_BACKUPFOLDER_PATH ) ){
            				if ( ! @mkdir( GBACKUP_PLUGIN_BACKUPFOLDER_PATH, 0777 ) ){
									$success_msg .= "Please create folder name as backup in ".GBACKUP_CONTENT_URL." with permission 0777"."<br/>";
							}
                        }

						if ( !is_dir( GBACKUP_PLUGIN_DBFOLDER_PATH) ){
            				if ( ! @mkdir( GBACKUP_PLUGIN_DBFOLDER_PATH, 0777 ) ){
									$success_msg .= "Please create folder name as db in ".GBACKUP_CONTENT_URL." with permission 0777";
							}
                        }
						
						if($success_msg!=''){
							$page  = 'backup_settings';
							$obj   = new notify_message($page,$success_msg);
							$obj->Gdrive_error();
						}
				
						$sett_options['gd_backup_name']     = $this->post_value['gd_backup_name'];
						$sett_options['gd_intimate_option'] = $this->post_value['gd_intimate_option'];
						$sett_options['gd_mail']            = $this->post_value['gd_mail'];
						$sett_options['backup_time']        = $this->post_value['backup_time'];
						update_option( 'google_drive_backup_sett', $sett_options );
						// status message
						$obj         = new notify_message("backup_settings","Backup has been successfully updated");
						$obj->Gdrive_message();
					
					break;
				
				
				   case 'gd_mgt_db':
					
						 if($this->get_value['page']=='gd_manage_database'){
							$sett_options = array();
							$sett_options['db_exclude']     = @implode("|",$this->post_value['db_exclude']);
							$sett_options['gd_db_bkp']      = $this->post_value['gd_db_bkp'];
							$sett_options['max_db_bkp']     = $this->post_value['max_db_bkp'];
							update_option( 'google_drive_database_sett', $sett_options );
							// status message 
							$obj         = new notify_message("gd_manage_database","Manage Files has been successfully updated");
							$obj->Gdrive_message();
							
						 }
				   
				   break;
				   
				   case 'gd_mgt_fl':
				   
				       if($this->get_value['page']=='gd_manage_files'){
							$sett_options = array();
							$sett_options['exclude_backup']       = @implode("|",$this->post_value['exclude_backup']);
							$sett_options['exclude_core_backup']  = @implode("|",$this->post_value['exclude_core_backup']);
							$sett_options['max_fl_bkp']           = $this->post_value['max_fl_bkp'];
							update_option( 'google_drive_fl_sett', $sett_options ); 
							// status message 
							$obj         = new notify_message("gd_manage_files","Manage Database has been successfully updated");
							$obj->Gdrive_message();

					   }
				   
				   break;
				
				}
				
		  }
} 
 
class gd_take_backup{

		 private $bkp_type; // backup type 
		 
		 private $fl_manage_array; // manage old backup & new backup files
		 
		 private $db_manage_array; // manage old backup & new backup database
		 
		 private $bkp_sql_name; // database backup name 
		 
		 private $bkp_settings; // backup settings 
		 
		 private $bkp_file_name; // backup file name 
		 
		 private $notify_mail; // notify mail 
		 
		 private $backup_time; // backup time 
		 
		 private $gc_options; // client id and Client secret
		 
		 private $access_token; // access token
		 
		 private $refresh_token; // manage refresh token 


		 function __construct($bkp_type='schedule_time_bkp') {
			$this->bkp_type = $bkp_type;
		 }
	
		 public function schedule_time_backup(){				
			
			    ini_set('memory_limit', '512M'); // memory size
				//set_time_limit(1000); 
			
			    $this->check_create_folder(); /// check create folder 'backup' & 'db'
			
				$this->fl_manage_array  = get_option('google_drive_fl_sett'); // list file manager result
				$this->db_manage_array  = get_option('google_drive_database_sett'); // list database manager result
				$this->bkp_settings     = get_option('google_drive_backup_sett'); // backup settings 

				$filename    = time() . '.zip'; //Backup File Name
				$gd_backup_name = trim($this->bkp_settings['gd_backup_name']);
				if(!empty($gd_backup_name)){
					$filename = $gd_backup_name."_".$filename; // custom backup file name
				}
				$this->bkp_file_name = $filename;
				
				/// database backup 
				
				if($this->db_manage_array['gd_db_bkp']){
					$db_bkp = new take_db_backup($gd_backup_name); 
					$this->bkp_sql_name = $db_bkp->get_database_backup($gd_backup_name); // Take Database Backup
					if($this->bkp_sql_name){
						$this->notify_mail[] = "Database backup has beeen created .";
					}	
	            }
				
				// exclude mention option 		
						
				$exlude_file = array();
				
				$file_path   = GBACKUP_PLUGIN_BACKUPFOLDER_PATH ."/". $filename; // Backup File Path
				$exlude_file = $this->exclude_files(); // Exlude Backup File 
				
				// create zip format
				
				$zip    = new create_zip; // create new zip format
				$result = $zip->addtozip(trim(WP_CONTENT_DIR,"wp-content"), $file_path, $exlude_file );
				if ( !$result ) {
				        $this->notify_mail[]  = "Backup ZIP File is not created";
						$this->error_handle("Backup ZIP File is not created");
				}else {
				        $this->notify_mail[]  = "Backup ZIP File is created ";
						$this->manage_fl_bkp_history($file_path,$filename); // manage backup history  
						$this->gc_options    = get_option( 'google_drive_backup' ); // Google Configure Option 
						$this->refresh_token = $this->gc_options['token']; // assign refresh token 
						if ( $this->access_token = $this->get_access_token()) {
						 	$insert =  $this->file_uploadto_googledrive( $file_path, $filename );
							$this->notify_mail[]  = "Backup Successfully Updated to Google drive .";
							$this->error_handle("Backup Successfully Updated to Google drive .");
						}else{
							$this->notify_mail[]  = "Couldn't Attempt Google drive .";
							$this->error_handle("Backup Successfully Updated to Google drive .");
						}		
				}
		}
			
			
		private function check_create_folder(){
		
			   $success_msg ='';
				if( !is_dir( GBACKUP_PLUGIN_BACKUPFOLDER_PATH) ){
            				if ( ! @mkdir( GBACKUP_PLUGIN_BACKUPFOLDER_PATH, 0777 ) ){
									$success_msg .= "Please create folder name as backup in ".GBACKUP_CONTENT_URL." with permission 0777"."<br/>";
							}
                  }

				if ( !is_dir( GBACKUP_PLUGIN_DBFOLDER_PATH ) ){
            				if ( ! @mkdir( GBACKUP_PLUGIN_DBFOLDER_PATH, 0777 ) ){
									$success_msg .= "Please create folder name as db in ".GBACKUP_CONTENT_URL." with permission 0777";
							}
                 }

				if($success_msg!=''){
				        $this->notify_mail[] = $success_msg;
						$this->error_handle($success_msg);
				}
		
		}	
			
		
		public function movezip_to_googledrive($file_path, $filename){
		
				ini_set('memory_limit', '512M'); // memory size
		
				$this->bkp_file_name = $filename;
				$this->backup_time   = date("Y-m-d H:i:s");
				$this->bkp_settings  = get_option('google_drive_backup_sett'); // backup settings 
				$this->gc_options    = get_option( 'google_drive_backup' ); // Google Configure Option 
				$this->refresh_token = $this->gc_options['token']; // assign refresh token 
				if ( $this->access_token = $this->get_access_token()) {
					 $insert 	     =  $this->file_uploadto_googledrive( $file_path, $filename );
					 $this->notify_mail[] = "Backup Successfully Updated to Google drive .";
					 $this->error_handle("Backup Successfully Updated to Google drive .");
				}		
		}	
		
		private function removedir( $dir ) {
				@unlink( $dir );
		}
		
		private function exclude_files(){
				$exlude = GBACKUP_PLUGIN_BACKUPFOLDER_PATH . '|'.trim($this->fl_manage_array['exclude_backup']);
				if($this->fl_manage_array['exclude_core_backup']){
					$exlude .= $exlude.'|'.trim($this->fl_manage_array['exclude_core_backup']);
				}
				if(!$this->db_manage_array['gd_db_bkp']){
					$exlude .= $exlude.'|'.GBACKUP_PLUGIN_DBFOLDER_PATH;
				}
				return @explode("|",$exlude);
		}
		
		
		private function manage_fl_bkp_history($file,$filename){
				global $wpdb;

				$limit      =  $this->fl_manage_array['max_fl_bkp'];  // user assign store limit
				$rm_lists   = gd_get_total_fl_row($limit);  // manager table rows
				foreach($rm_lists as $rm_list){
					$this->removedir(GBACKUP_PLUGIN_BACKUPFOLDER_PATH.'/'.$rm_list); /// remove dir files 
				}
				
				$size = filesize( $file );
				$data = array();
				$data['file_name'] = $filename;
				$data['file_size'] = gd_format_bytes($size);
				$data['type']      = 'fl';
				$data['date']      = $this->backup_time = date("Y-m-d H:i:s");
				
				$tablename  = $wpdb->prefix."gd_manager";
				$query      = arrayToSQLInsert($tablename,$data); // insert backup history
				$insert     = $wpdb->query($query);
		}
			
		
		
		private function file_uploadto_googledrive( $file, $title) {
		
				$size = filesize( $file );
			
				$url = $this->resumable_link();
				if ( $url ){
					$url .= '?convert=false'; 
				}
				
				$body = '<?xml version=\'1.0\' encoding=\'UTF-8\'?><entry xmlns="http://www.w3.org/2005/Atom" xmlns:docs="http://schemas.google.com/docs/2007"><category scheme="http://schemas.google.com/g/2005#kind" term="http://schemas.google.com/docs/2007#file"/><title>' . $title . '</title></entry>';
				
				$param = array( 
				'method' => 'POST',
				'httpversion' => '1.1',
				'redirection' => 0,
				'headers' => array( 
					'Content-Type' => 'application/atom+xml',
					'Authorization' => 'Bearer ' . $this->access_token,
					'X-Upload-Content-Type' => 'application/zip',
					'X-Upload-Content-Length' => (string)$size,
					'GData-Version' =>3.0
				)
				);
				
				$param['body'] = $body;
				$result = (array)wp_remote_request($url,$param );
				
				   if (!isset($result['errors'])) {
					 if(isset($result['headers']['location'])){
						$response = array();
						$response['Location'] = $result['headers']['location'];
						if ( isset( $response['Location'] ) ) {
							$pointer = 0;
							return $this->upload_google_chunk( $response['Location'], $file, $pointer, $size);
						 }
					}
				}else{
					 $this->notify_mail[] = $result['errors']['http_request_failed'][0];
					 $this->error_handle($result['errors']['http_request_failed'][0]);
				}
		}
		   
		private function resumable_link() {
		
				$url = 'https://docs.google.com/feeds/default/private/full';

				$param = array( 
					'method' => 'GET',
					'httpversion' => '1.1',
					'redirection' => 0,
					'headers' => array( 
						'Authorization' => 'Bearer ' .$this->access_token,
						'GData-Version' => '3.0'
					)
				);
				
				$result = (array)wp_remote_request( $url, $param );
				
				switch($result['response']['code']){
					case 200:

						if (isset($result['body'])) {
							$xml = simplexml_load_string( $result['body'] );
							foreach ( $xml->link as $link ){
								if ( $link['rel'] == 'http://schemas.google.com/g/2005#resumable-create-media' ){
									return $link['href'];
								}
							}		
						}

					break;
					
					default: 
						$this->notify_mail[] = $result['response']['message'];
						$this->error_handle($result['response']['message']);
				}
				
		}
		   
		   
		private function upload_google_chunk( $location, $file, $pointer, $size) {
		
				 $chunk = @file_get_contents($file, false, NULL, $pointer,524288);
				 $chunk_size = strlen( $chunk );
				 $bytes = 'bytes ' . (string)$pointer . '-' . (string)($pointer + $chunk_size - 1) . '/' . (string)$size;
				 $param = array( 
						'method' => 'PUT',
						'httpversion' => '1.1',
						'redirection' => 0,
						'ignore_errors' => true,
						'follow_location' => false,
							'headers' => array( 
								'Authorization'=> 'Bearer '.$this->access_token,
								'Content-Range'=>$bytes,
								'GData-Version' => '3.0'
							)
						);
					
					$param['body'] = $chunk;
					$result 	   = wp_remote_request($location,$param ); 
					$result        = (array)$result; 
					
					 switch($result['response']['code']){
					 
						case 308:
						
							ini_set('max_execution_time', 0);
							set_time_limit(0); 
							flush(); 
							ob_flush(); 
							sleep(1); 
						
							unset($chunk); 
							$pointer += $chunk_size;
							return $this->upload_google_chunk( $location, $file, $pointer, $size);
						break;
					 
						case 201:
							$xml = @simplexml_load_string( $result['body'] );
							return $xml;
						break;
						
						default: 
							$this->notify_mail[] = $result['response']['message'];
							$this->error_handle($result['response']['message']);
					 }
		}
		
		
		private function error_handle($error){
		
		    switch($this->bkp_type){
				  case 'ontime_backup':
				        if($this->bkp_settings['gd_intimate_option']){
						  $this->notify_mails();
                        }						
						echo $error;
						exit;
				  break;
				  
				  case 'schedule_time_bkp':
				  		if($this->bkp_settings['gd_intimate_option']){
						  $this->notify_mails();
                        }						
				  break;
				  
				  default:
			}
		
		}
	
		private function notify_mails(){
		
		     if(trim($this->bkp_settings['gd_mail'])!=''){
			    $send_mail = $this->bkp_settings['gd_mail'];
			 }else{
			 	$send_mail = get_option('admin_email');
			 }
		
			 $headers   = "From: ".get_bloginfo( 'name' )." <".get_option('admin_email').">\r\n\\";
			 $message   = "\r\r";
			 $message  .= "Hello,\r\r";
			 $message  .= @implode("\r",$this->notify_mail);
			 $message  .= "\r\r";
			 $message  .= "Your back-up Details\r";
			 $message  .= "--------------------------------\r";
			 $message  .= "Backup Folder Name :".$this->bkp_file_name." \r";
			 $message  .= "Backup Created on  : ".$this->backup_time."\r\r";
			 $message  .= "\r";
			 $message  .= "Thanks\r";
			 $message  .= "\r\r";
			 wp_mail($send_mail,"Backup", $message, $headers);
		}
		
		
		private function get_access_token(){
 
				$params = array(
					'body' => array(
						'client_id' => $this->gc_options['client_id'],
						'client_secret' => $this->gc_options['client_secret'],
						'refresh_token' => $this->refresh_token,
						'grant_type' => 'refresh_token'
					)
				);
				
				$result = (array)wp_remote_post('https://accounts.google.com/o/oauth2/token', $params );
				
				switch($result['response']['code']){
					case 200:
					
						$result = json_decode( $result['body'], true );
						if(isset($result['access_token'])){
							return $result['access_token'];
						}
					
					break;
					
					default: 
					
						$this->notify_mail[] = $result['response']['message'];
						$this->error_handle($result['response']['message']);
		
		}
 
}

		
			
}



?>