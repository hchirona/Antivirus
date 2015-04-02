<?php
// START Security Patch by GOTMLS.NET
//if(!session_save_path())	session_save_path(dirname(__FILE__).'/images/');
if (!session_id())
	@session_start();
if (!(isset($_SESSION["GOTMLS_login_attempts"]) && strlen($_SESSION["GOTMLS_login_attempts"]."") > 0 && is_numeric($_SESSION["GOTMLS_login_attempts"])))
	$_SESSION["GOTMLS_login_attempts"] = 0;
if (!(isset($_SESSION["GOTMLS_login_ok"]) && $_SESSION["GOTMLS_login_ok"] === true))
	$_SESSION["GOTMLS_login_ok"] = false;
if ($_SESSION["GOTMLS_login_ok"] && $_SESSION["GOTMLS_login_attempts"] == 0)
	$_SESSION["GOTMLS_login_attempts"] = 1;
@date_default_timezone_set(@date_default_timezone_get());
$GOTMLS_time = @date("mdHm");
if (file_exists(dirname(__FILE__).'/../../../.GOTMLS.failed.login.attempt.from.'.$_SERVER["REMOTE_ADDR"].'.php'))
	include(dirname(__FILE__).'/../../../.GOTMLS.failed.login.attempt.from.'.$_SERVER["REMOTE_ADDR"].'.php');
elseif (isset($_GET["GOTMLS_SESSION_check"]) && is_numeric($_GET["GOTMLS_SESSION_check"])) {
	if ($_SESSION["GOTMLS_login_attempts"] == 0) {
		$_SESSION["GOTMLS_login_attempts"] = 1;
		if ('IP'.str_replace('.','',$_SERVER["REMOTE_ADDR"]) == 'IP'.$_GET["GOTMLS_SESSION_check"])
			die("<script>window.location.replace('wp-login.php?GOTMLS_SESSION_check=$GOTMLS_time');</script>");
		elseif ($_GET["GOTMLS_SESSION_check"] == $GOTMLS_time || ($_GET["GOTMLS_SESSION_check"] + 1) == $GOTMLS_time) {
			if (@file_put_contents(dirname(__FILE__).'/../../../.GOTMLS.failed.login.attempt.from.'.$_SERVER["REMOTE_ADDR"].'.php', '<?php $_SESSION["GOTMLS_login_attempts"] = 1; //set this value to 0 to block all login attempts from this IP '.$_SERVER["REMOTE_ADDR"]))
				die('SESSION FAILURE: Your IP address has been logged.');
			else
				die('SESSION FAILURE: No way to login.');
		}
	} else
		die('SESSION TEST PASSED! You should be able to login now.');
}
if ($_SERVER["REQUEST_METHOD"] == "POST" || isset($_POST["user_login"])) {
	$_SESSION["GOTMLS_login_attempts"]++;
	if ($_SESSION["GOTMLS_login_attempts"] < 2 || $_SESSION["GOTMLS_login_attempts"] > 6)
		die("<html><head><title>Login Error</title></head><body style='margin-top: 0;'><!-- ".$_SESSION["GOTMLS_login_attempts"]." -->\n".'<div id="help-meta" style="background-color: #CCCCCC; display: none; margin: 0 15px; padding: 10px; border-bottom-left-radius: 5px; border-bottom-right-radius: 5px;">This message is shown whenever a possible brute-force attack is detected. Click the link below to have another shot at logging in.<br><iframe src="wp-login.php?GOTMLS_SESSION_check='.str_replace('.','',$_SERVER["REMOTE_ADDR"]).'" style="width: 100%; height: 35px; margin: 10px 0;"></iframe></div><div style="background-color: #CCCCCC; margin: 0 25px; float: right; padding: 10px; border-bottom-left-radius: 5px; border-bottom-right-radius: 5px;"><a onclick="hbox=document.getElementById(\'help-meta\');if (hbox.style.display==\'block\') hbox.style.display=\'none\'; else hbox.style.display=\'block\';" href="#help-meta">Help</a></div><br>'."\n<p>Just what do you think you are doing?</p><p><a href='wp-login.php'>Open the login page to try again</a></p></body></html>");
} else {
	$_SESSION["GOTMLS_login_ok"] = true;
	$_SESSION["GOTMLS_login_attempts"] = 1;
}
$save_GOTMLS_login_attempts = $_SESSION['GOTMLS_login_attempts'];
$save_GOTMLS_login_ok = $_SESSION['GOTMLS_login_ok'];
// END Security Patch by GOTMLS.NET