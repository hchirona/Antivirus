<?php

/**
 * Copyright 2012 SendReach.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */
 
// main api class
class SendReachApi {
	
	protected $api_vars = null;

	public function __construct($key, $secret){
		$this->api_vars = array();
		$this->api_vars['key'] = $key;
		$this->api_vars['secret'] = $secret;
	}

	public function validate(){
		$api_vars = $this->api_vars;

		$query = 'http://api.sendreach.com/index.php?key='.$api_vars['key'].'&secret='.$api_vars['secret'].'&action=lists_view';

		$call = file_get_contents($query);
		$json = json_decode($call);

		if(isset($json->code) && isset($json->message) && $json->message == 'app does not exist'){
			return false;
		}

		return true;
	}

	// create new list
	function list_create($list_name,$list_redirect,$list_from_name,$list_from_email,$list_optin = "single"){
		$api_vars = $this->api_vars;
		$query = 'http://api.sendreach.com/index.php?key='.$api_vars['key'].'&secret='.$api_vars['secret'].'&action=list_create&user_id='.$api_vars['userid'].'&list_name='.urlencode($list_name).'&list_redirect='.$list_redirect.'&list_from_name='.urlencode($list_from_name).'&list_from_email='.$list_from_email.'&list_optin='.$list_optin.'';
		$call = file_get_contents($query);
		return $call;
	}
	
	// get lists
	function lists_view(){
		$api_vars = $this->api_vars;;
		$query = 'http://api.sendreach.com/index.php?key='.$api_vars['key'].'&secret='.$api_vars['secret'].'&action=lists_view';
		$call = file_get_contents($query);
		$json = json_decode($call);

		$result = new stdClass;

		if(isset($json->status) && $json->status == 'error'){
			$result->error = true;
		} else {
			$result->error = false;
			$result->lists = $json;
		}

		return $result;
	}
	
	// get list details
	function list_details($lid){
		$api_vars = $this->api_vars;;
		$query = 'http://api.sendreach.com/index.php?key='.$api_vars['key'].'&secret='.$api_vars['secret'].'&action=list_details&user_id='.$api_vars['userid'].'&list_id='.$lid.'';
		$call = file_get_contents($query);
		return $call;
	}
	
	// get list size | number of subscribers
	function list_size($lid){
		$api_vars = $this->api_vars;;
		$query = 'http://api.sendreach.com/index.php?key='.$api_vars['key'].'&secret='.$api_vars['secret'].'&action=list_size&user_id='.$api_vars['userid'].'&list_id='.$lid.'';
		$call = file_get_contents($query);
		return $call;
	}
	
	// get list subscribers
	function list_subscribers($lid){
		$api_vars = $this->api_vars;;
		$query = 'http://api.sendreach.com/index.php?key='.$api_vars['key'].'&secret='.$api_vars['secret'].'&action=list_subscribers&user_id='.$api_vars['userid'].'&list_id='.$lid.'';
		$call = file_get_contents($query);
		return $call;
	}
	
	// add subscriber
    function subscriber_add($lid,$first_name,$last_name,$email,$client_ip, $photo = "", $gender = "", $dob = "", $cell = "", $address_1 = "", $address_2 = "", $city = "", $state = "", $zip = "", $country = "", $custom_1 = "", $custom_2 = "", $custom_3 = "", $custom_4 = "", $custom_5 = "", $custom_6 = "", $custom_7 = "", $custom_8 = "", $custom_9 = "", $custom_10 = ""){
		$api_vars = $this->api_vars;;
		$query = 'http://api.sendreach.com/index.php?key='.$api_vars['key'].'&secret='.$api_vars['secret'].'&action=subscriber_add&user_id='.$api_vars['userid'].'&list_id='.$lid.'&first_name='.$first_name.'&last_name='.$last_name.'&email='.$email.'&client_ip='.$client_ip.'&photo='.urlencode($photo).'&gender='.$gender.'&dob='.urlencode($dob).'&cell='.$cell.'&address_1='.urlencode($address_1).'&address_2='.urlencode($address_2).'&city='.urlencode($city).'&state='.urlencode($state).'&zip='.$zip.'&country='.urlencode($country).'&custom_1='.urlencode($custom_1).'&custom_2='.urlencode($custom_2).'&custom_3='.urlencode($custom_3).'&custom_4='.urlencode($custom_4).'&custom_5='.urlencode($custom_5).'&custom_6='.urlencode($custom_6).'&custom_7='.urlencode($custom_7).'&custom_8='.urlencode($custom_8).'&custom_9='.urlencode($custom_9).'&custom_10='.urlencode($custom_10).'';
        $call = file_get_contents($query);
		return $call;
	}
	
	// get subscriber details
	function subscriber_view($sid){
		$api_vars = $this->api_vars;;
		$query = 'http://api.sendreach.com/index.php?key='.$api_vars['key'].'&secret='.$api_vars['secret'].'&action=subscriber_view&user_id='.$api_vars['userid'].'&subscriber_hash='.$sid.'';
		$call = file_get_contents($query);
		return $call;
	}
	
	// unsubscribe subscriber
	function subscriber_unsubscribe($hash){
		$api_vars = $this->api_vars;;
		$query = 'http://api.sendreach.com/index.php?key='.$api_vars['key'].'&secret='.$api_vars['secret'].'&action=subscriber_unsubscribe&user_id='.$api_vars['userid'].'&subscriber_hash='.$hash;
		$call = file_get_contents($query);
		return $call;
	}
	
	// add broadcast
	function broadcast_add($name,$subject,$message,$sms_message){
		$api_vars = $this->api_vars;;
		$name = base64_encode($name);
		$subject = base64_encode($subject);
		$message = base64_encode($message);
		$sms_message = base64_encode($sms_message);		
		$query = 'http://api.sendreach.com/index.php?key='.$api_vars['key'].'&secret='.$api_vars['secret'].'&action=broadcast_add&user_id='.$api_vars['userid'].'&name='.$name.'&subject='.$subject.'&message='.$message.'&sms_message='.$sms_message.'';
        $call = file_get_contents($query);
		return $call;
	}
	
	// get broadcast details
	function broadcast_view($bid){
		$api_vars = $this->api_vars;;
		$query = 'http://api.sendreach.com/index.php?key='.$api_vars['key'].'&secret='.$api_vars['secret'].'&action=broadcast_view&user_id='.$api_vars['userid'].'&broadcast_id='.$bid.'';
		$call = file_get_contents($query);
		return $call;
	}
	
	// send a broadcast
	function broadcast_send($bid,$btype,$lid){
		$api_vars = $this->api_vars;;
		$query = 'http://api.sendreach.com/index.php?key='.$api_vars['key'].'&secret='.$api_vars['secret'].'&action=broadcast_send&user_id='.$api_vars['userid'].'&broadcast_id='.$bid.'&broadcast_type='.$btype.'&list_id='.$lid.'';
		$call = file_get_contents($query);
		return $call;
	}

    // get broadcasts
    function broadcasts_view(){
    	$api_vars = $this->api_vars;;
    	$query = 'http://api.sendreach.com/index.php?key='.$api_vars['key'].'&secret='.$api_vars['secret'].'&action=broadcasts_view&user_id='.$api_vars['userid'].'';
    	$call = file_get_contents($query);
    	return $call;
    }
}

?>