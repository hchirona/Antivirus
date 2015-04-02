<?php
if ((isset($_SERVER["SCRIPT_FILENAME"]) && strlen($_SERVER["SCRIPT_FILENAME"]) > strlen(basename(__FILE__)) && substr(__FILE__, -1 * strlen($_SERVER["SCRIPT_FILENAME"])) == substr($_SERVER["SCRIPT_FILENAME"], -1 * strlen(__FILE__))) || !defined("GOTMLS_plugin_path"))
	die("<html><head><title>403 Forbidden</title></head><body><h1>Forbidden</h1><p>You don't have permission to access this directory.</p></body></html>");
		?>