<?php defined('_JEXEC') or die('Restricted access');

// Program: Fox Contact for Joomla
// Copyright (C): 2011 Demis Palma
// Documentation: http://www.fox.ra.it/forum/2-documentation.html
// License: Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html

// Avoid multiple instances of the same module when called by both template and content (using loadposition)
if (isset($GLOBALS["foxcontact_mid_" . $module->id])) return;
else $GLOBALS["foxcontact_mid_" . $module->id] = true;

// Turns off the cache when rendered as a module
$cache = JFactory::getCache("com_modules", "");
$cache->setCaching(false);

// Turns off the cache when rendered within an article using {loadposition}
$cache = @JFactory::getCache("com_content", "view");
// Muted due to msg: PHP Strict Standards:  Declaration of JCacheControllerView::get() should be compatible with that of JCacheController::get() in /var/www/fc20/libraries/joomla/cache/controller/view.php on line 137
$cache->setCaching(false);

$GLOBALS["ext_name"] = basename(__FILE__);
$GLOBALS["com_name"] = realpath(dirname(__FILE__) . "/../../components");
$GLOBALS["mod_name"] = dirname(__FILE__);
$GLOBALS["EXT_NAME"] = strtoupper($GLOBALS["ext_name"]);
$GLOBALS["COM_NAME"] = strtoupper($GLOBALS["com_name"]);
$GLOBALS["MOD_NAME"] = strtoupper($GLOBALS["mod_name"]);
$GLOBALS["left"] = false;
$GLOBALS["right"] = true;
$app->owner = "module";
$app->oid = $module->id;
$app->cid = 0;
$app->mid = $module->id;
$app->submitted = (bool)count($_POST) && isset($_POST["mid_$app->mid"]);
$me = basename(__FILE__);
$name = substr($me, 0, strrpos($me, '.'));
include(realpath(dirname(__FILE__) . "/" . $name . ".inc"));

$helpdir = JPATH_BASE . '/components/' . $GLOBALS["com_name"] . '/helpers/';
$libsdir = JPATH_BASE . '/components/' . $GLOBALS["com_name"] . '/lib/';
@require_once($helpdir . 'fieldsbuilder.php');
@include_once($helpdir . 'fsubmitter.php');
@include_once($helpdir . 'fajaxuploader.php');
@include_once($helpdir . 'fuploader.php');
@include_once($helpdir . 'fcaptcha.php');
@include_once($helpdir . 'fsession.php');
@include_once($helpdir . 'fantispam.php');
@require_once($helpdir . "fadminmailer.php");
@require_once($helpdir . "fsubmittermailer.php");
@require_once($helpdir . "fjmessenger.php");
@require_once($helpdir . "fnewsletter.php");
@require_once($helpdir . "acymailing.php");
@require_once($helpdir . "jnews.php");
@include_once($libsdir . 'functions.php');
@require_once($helpdir . "messageboard.php");

// Avoids email cloak bug http://www.fox.ra.it/forum/3-bugs/1363-e-mail-cloak-in-textfield.html
// From 2.0.8 the code for disabling plugin is <!--commented-->, since from Joomla 1.7.0 this fix is no longer required, and it produces a emailcloak=off output on the form.
// See http://www.fox.ra.it/forum/5-support/2349-emailcloakoff-in-content.html
if ($scope == "com_content") echo("<!--{emailcloak=off}-->");

$document = JFactory::getDocument();
$prefix = "index.php?option=" . $GLOBALS["com_name"] .
	"&view=loader" .
	"&owner=" . JFactory::getApplication()->owner .
	"&id=" . JFactory::getApplication()->oid;

// User interface stylesheet
$document->addStyleSheet(JRoute::_($prefix . "&root=media&filename=chosen&type=css"));
$document->addStyleSheet(JRoute::_($prefix . "&root=media&filename=bootstrap&type=css"));

// Selected stylesheet
$stylesheet = $params->get("css", "bootstrap.css");
$css_name = preg_replace("/\\.[^.\\s]{3,4}$/", "", $stylesheet);
$document->addStyleSheet($prefix . "&amp;root=components&amp;type=css&amp;filename=" . $css_name);

//$link = FGetLink(NULL, "#mid_" . $module->id);
// FGetLink doesn't work for blog view -> article, because active page is always blog view even if you are into an article
$action = "#mid_" . $module->id;

// Load component language in addition
$language = JFactory::getLanguage();
// Reload the default language (en-GB)
$language->load($GLOBALS["com_name"], JPATH_SITE, $language->getDefault(), true);
// Reload current language, overwriting nearly all the strings, but keeping the english version for untranslated strings
$language->load($GLOBALS["com_name"], JPATH_SITE, null, true);

// Fields properties
$page_subheading = $params->get("page_subheading", "");

// Module xml
$xml = JFactory::getXML(JPATH_SITE . '/modules/' . $app->scope . "/" . $app->scope . '.xml');

$messageboard = new FoxMessageBoard();
$submitter = new FSubmitter($params, $messageboard);
$fieldsBuilder = new FieldsBuilder($params, $messageboard);
$ajax_uploader = new FAjaxUploader($params, $messageboard);
$uploader = new FUploader($params, $messageboard);
$fcaptcha = new FCaptcha($params, $messageboard);
$antispam = new FAntispam($params, $messageboard, $fieldsBuilder);
$jMessenger = new FJMessenger($params, $messageboard, $fieldsBuilder);
$newsletter = new FNewsletter($params, $messageboard, $fieldsBuilder);
$acymailing = new FAcyMailing($params, $messageboard, $fieldsBuilder);
$jnews = new FJNewsSubscriber($params, $messageboard, $fieldsBuilder);

$adminMailer = new FAdminMailer($params, $messageboard, $fieldsBuilder);
$submitterMailer = new FSubmitterMailer($params, $messageboard, $fieldsBuilder);

// Build $FormText
$form_text = "";
$form_text .= $fieldsBuilder->Show();
$form_text .= $ajax_uploader->Show();
$form_text .= $acymailing->Show();
$form_text .= $jnews->Show();
$form_text .= $fcaptcha->Show();
$form_text .= $antispam->Show();
// Usually we want the submit button at the bottom
$form_text .= $submitter->Show();

// Build $TopText and $BottomText
switch (0)
{
	case $submitter->IsValid():
		break;
	case $fieldsBuilder->IsValid():
		break;
	case $ajax_uploader->IsValid():
		break;
	case $uploader->IsValid():
		break;
	case $fcaptcha->IsValid():
		break;
	case $antispam->IsValid():
		break;
	// Spam check passed or disabled
	case $jMessenger->Process():
		break;
	case $newsletter->Process():
		break;
	case $acymailing->Process():
		break;
	case $jnews->Process():
		break;

	case $adminMailer->Process():
		break;
	case $submitterMailer->Process():
		break;
	default: // None of the previous checks are failed
		// Avoid to show the Form and the button again
		$form_text = "";

		// Reset the solution of the captcha in the session after read,
		// avoiding further uses (abuses) of the same valid session,
		// in order to send tons of email
		$jsession = JFactory::getSession();
		$fsession = new FSession($jsession->getId(), 0, $module->id);
		$fsession->PurgeValue("captcha_answer");

		HeaderRedirect($params);
}

require(JModuleHelper::getLayoutPath($app->scope, $params->get('layout', 'default')));
