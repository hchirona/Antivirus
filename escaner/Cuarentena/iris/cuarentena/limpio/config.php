<?php

define("ZEM_RP_DEFAULT_CUSTOM_CSS",
".related_post_title {
}
ul.related_post {
}
ul.related_post li {
}
ul.related_post li a {
}
ul.related_post li img {
}");

define('ZEM_RP_THUMBNAILS_NAME', 'zem_rp_thumbnail');
define('ZEM_RP_THUMBNAILS_PROP_NAME', 'zem_rp_thumbnail_prop');
define('ZEM_RP_THUMBNAILS_WIDTH', 150);
define('ZEM_RP_THUMBNAILS_HEIGHT', 150);
define('ZEM_RP_CUSTOM_THUMBNAILS_WIDTH', 150);
define('ZEM_RP_CUSTOM_THUMBNAILS_HEIGHT', 150);
define('ZEM_RP_THUMBNAILS_DEFAULTS_COUNT', 31);

define('ZEM_RP_STATIC_THEMES_PATH', 'themes/');
define('ZEM_RP_STATIC_JSON_PATH', 'json/');

define("ZEM_RP_CTR_DASHBOARD_URL", "https://d.zemanta.com/");
define("ZEM_RP_STATIC_CTR_PAGEVIEW_FILE", "js/pageview.js");

define("ZEM_RP_STATIC_RECOMMENDATIONS_JS_FILE", "js/recommendations.js");
define("ZEM_RP_STATIC_RECOMMENDATIONS_CSS_FILE", "zem-css/recommendations.css");

define("ZEM_RP_STATIC_INFINITE_RECS_JS_FILE", "js/infiniterecs.js");
define("ZEM_RP_STATIC_PINTEREST_JS_FILE", "js/pinterest.js");

define("ZEM_RP_ZEMANTA_DASHBOARD_URL", "http://prefs.zemanta.com/dash/");
define("ZEM_RP_ZEMANTA_SUBSCRIPTION_URL", "http://prefs.zemanta.com/api/");
define("ZEM_RP_ZEMANTA_UPLOAD_URL", "http://prefs.zemanta.com/api/upload-articles/");
define("ZEM_RP_ZEMANTA_ARTICLE_COUNT_URL", "http://prefs.zemanta.com/api/article-count/");
define("ZEM_RP_ZEMANTA_API_URL", "http://api.zemanta.com/services/rest/0.0/");
define('ZEM_RP_ZEMANTA_CONTENT_BASE_URL', 'https://content.zemanta.com/static/');

define("ZEM_RP_RECOMMENDATIONS_AUTO_TAGS_MAX_WORDS", 200);
define("ZEM_RP_RECOMMENDATIONS_AUTO_TAGS_MAX_TAGS", 15);

define("ZEM_RP_RECOMMENDATIONS_AUTO_TAGS_SCORE", 2);
define("ZEM_RP_RECOMMENDATIONS_TAGS_SCORE", 10);
define("ZEM_RP_RECOMMENDATIONS_CATEGORIES_SCORE", 5);

define("ZEM_RP_RECOMMENDATIONS_NUM_PREGENERATED_POSTS", 50);

define("ZEM_RP_THUMBNAILS_NUM_PREGENERATED_POSTS", 50);

define("ZEM_RP_MAX_LABEL_LENGTH", 32);
define("ZEM_RP_EXCERPT_SHORTENED_SYMBOL", " [&hellip;]");

global $zem_rp_options, $zem_rp_meta, $zem_global_notice_pages;
$zem_rp_options = false;
$zem_rp_meta = false;
$zem_global_notice_pages = array('plugins.php', 'index.php', 'update-core.php');

function zem_rp_get_options() {
	global $zem_rp_options, $zem_rp_meta;
	if($zem_rp_options) {
		return $zem_rp_options;
	}

	$zem_rp_meta = get_option('zem_rp_meta', false);
	$zem_rp_options = get_option('zem_rp_options', false);

	if(!$zem_rp_meta || !$zem_rp_options || $zem_rp_meta['version'] !== ZEM_RP_VERSION) {
		zem_rp_upgrade();
		$zem_rp_meta = get_option('zem_rp_meta');
		$zem_rp_options = get_option('zem_rp_options');
	}

	$zem_rp_meta = new ArrayObject($zem_rp_meta);
	$zem_rp_options = new ArrayObject($zem_rp_options);

	return $zem_rp_options;
}

function zem_rp_get_meta() {
	global $zem_rp_meta;

	if (!$zem_rp_meta) {
		zem_rp_get_options();
	}

	return $zem_rp_meta;
}

function zem_rp_update_meta($new_meta) {
	global $zem_rp_meta;

	$new_meta = (array) $new_meta;

	$r = update_option('zem_rp_meta', $new_meta);

	if($r && $zem_rp_meta !== false) {
		$zem_rp_meta->exchangeArray($new_meta);
	}

	return $r;
}

function zem_rp_update_options($new_options) {
	global $zem_rp_options;

	$new_options = (array) $new_options;

	$r = update_option('zem_rp_options', $new_options);

	if($r && $zem_rp_options !== false) {
		$zem_rp_options->exchangeArray($new_options);
	}

	return $r;
}

function zem_rp_set_global_notice() {
	$meta = get_option('zem_rp_meta');
	$meta['global_notice'] = array(
		'title' => 'I\'ve installed Related Posts by Zemanta plugin. Now what?',
		'message' => 'Checkout how you can <a target="_blank" href="http://zem.si/1kGo9V6">create awesome content</a>. Hint: it\'s not all about YOU ;-)'
	);
	update_option('zem_rp_meta', $meta);
}

function zem_rp_activate_hook() {
	zem_rp_get_options();
	zem_rp_schedule_notifications_cron();
}

function zem_rp_deactivate_hook() {
	zem_rp_unschedule_notifications_cron();
}

function zem_rp_upgrade() {
	$zem_rp_meta = get_option('zem_rp_meta', false);
	$version = false;

	if($zem_rp_meta) {
		$version = $zem_rp_meta['version'];
	} else {
		$zem_rp_old_options = get_option('zem_rp', false);
		if($zem_rp_old_options) {
			$version = '1.4';
		}
	}

	if($version) {
		if(version_compare($version, ZEM_RP_VERSION, '<')) {
			$upgrade_call = 'zem_rp_migrate_' . str_replace('.', '_', $version);
			if (is_callable($upgrade_call)) {
				call_user_func($upgrade_call);
				zem_rp_upgrade();
			}
			else {
				zem_rp_install();
			}
		}
	} else {
		zem_rp_install();
	}
}

function zem_rp_related_posts_db_table_uninstall() {
	global $wpdb;

	$tags_table_name = $wpdb->prefix . "zem_rp_tags";
	$wpdb->query("DROP TABLE $tags_table_name;");
}

function zem_rp_related_posts_db_table_install() {
	global $wpdb;

	$tags_table_name = $wpdb->prefix . "zem_rp_tags";
	$sql_tags = "CREATE TABLE $tags_table_name (
	  post_id mediumint(9),
	  post_date datetime NOT NULL,
	  label VARCHAR(" . ZEM_RP_MAX_LABEL_LENGTH . ") NOT NULL,
	  weight float,
	  KEY post_id (post_id),
	  KEY label (label)
	 );";

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql_tags);

	$latest_posts = get_posts(array('numberposts' => ZEM_RP_RECOMMENDATIONS_NUM_PREGENERATED_POSTS));
	foreach ($latest_posts as $post) {
		zem_rp_generate_tags($post);
	}
}

function zem_rp_nrelate_migrate() {
	$nrelate = get_option('nrelate_related_options');
	if (empty($nrelate)) { return; }

	$zemanta = get_option('zem_rp_options');

	$direct_copy = array(
		'related_number_of_posts' => 'max_related_posts',
		'related_title' => 'related_posts_title',
		'related_show_post_excerpt' => 'display_excerpt',
		'related_max_chars_post_excerpt' => 'excerpt_max_length',
		'related_default_image' => 'default_thumbnail_path'
	);
	foreach($direct_copy as $nrelate_name => $zem_name) {
		if (!isset($nrelate[$nrelate_name])) { continue; }
		if (isset($zemanta[$zem_name])) {
			$zemanta[$zem_name] = $nrelate[$nrelate_name];
		} elseif (isset($zemanta['desktop'][$zem_name])) {
			$zemanta['desktop'][$zem_name] = $nrelate[$nrelate_name];
		}
	}
	if (!empty($nrelate['related_thumbnail_size'])) {
		$zemanta['custom_thumbnail_width'] = $nrelate['related_thumbnail_size'];
		$zemanta['custom_thumbnail_height'] = $nrelate['related_thumbnail_size'];
	}
	if (isset($nrelate['related_thumbnail'])) {
		$zemanta['desktop']['display_thumbnail'] = $nrelate['related_thumbnail'] === 'Thumbnails';
	}
	if (isset($nrelate['related_max_age_num'])) {
		$time_range = $nrelate['related_max_age_num']; // we get minutes
		switch($nrelate['related_max_age_frame']) {
		case 'Hour(s)':
			$time_range *= 60;
			break;
		case 'Day(s)':
			$time_range *= 1440;
			break;
		case 'Week(s)':
			$time_range *= 10080;
			break;
		case 'Month(s)':
			$time_range *= 44640;
			break;
		case 'Year(s)':
			$time_range *= 525600;
			break;
		}
		$max_related_post_age_in_days = (int)($time_range / 60 / 24);
		if ($max_related_post_age_in_days > 400) {
			$zemanta['max_related_post_age_in_days'] = 0;
		} else {
			$ages = array(
				30 => abs($max_related_post_age_in_days - 30),
				91 => abs($max_related_post_age_in_days - 91),
				356 => abs($max_related_post_age_in_days - 356),
			);
			$best_fit = min($ages);
			foreach($best_fit as $key => $val) {
				if ($val === $best_fit) {
					$zemanta['max_related_post_age_in_days'] = $key;
					break;
				}
			}
		}
	}
	
	update_option('zem_rp_options', $zemanta);
	
	$close_url = add_query_arg( array(
		'page' => 'zemanta-related-posts',
		'zem_global_notice' => 0,
	), admin_url( 'admin.php' ) );
	$meta = get_option('zem_rp_meta');
	$meta['global_notice'] = array(
		'title' => 'Welcome nRelate user',
		'message' => 'We\'ve copied your compatible nRelate settings for you. Checkout our <a href="' . $close_url . '">plugin settings</a>. Are you interested in how to <a target="_blank" href="http://zem.si/1kGo9V6">create awesome content</a>?. Hint: it\'s not all about YOU ;-)'
	);
	update_option('zem_rp_meta', $meta);
	
}

function zem_rp_install() {
	$zem_rp_meta = array(
		'zemanta_api_key' => false,
		'version' => ZEM_RP_VERSION,
		'first_version' => ZEM_RP_VERSION,
		'new_user' => true,
		'blog_tg' => rand(0, 1),
		'remote_recommendations' => false,
		'name' => '',
		'email' => '',
		'subscribed' => false,
		'registered' => false,
		'global_notice' => null,
		'zemanta_username' => false,
		'classic_user' => strpos(get_bloginfo('language'), 'en') === 0 // Enable only if "any" english is the default language
	);

	$zem_rp_options = array(
		'related_posts_title'			=> __('Related Posts', 'zemanta_related_posts'),
		'max_related_posts'			=> 6,
		'exclude_categories'			=> '',
		'on_single_post'			=> true,
		'on_rss'				=> false,
		'max_related_post_age_in_days' => 0,

		'default_thumbnail_path'		=> false,
		'thumbnail_use_custom' => false,
		'thumbnail_custom_field' => '',
		'custom_size_thumbnail_enabled'	=> false,
		'custom_thumbnail_width' => ZEM_RP_CUSTOM_THUMBNAILS_WIDTH,
		'custom_thumbnail_height' => ZEM_RP_CUSTOM_THUMBNAILS_HEIGHT,
		'display_zemanta_linky' => false,
		'only_admins_can_edit_related_posts' => false,

		'subscription_types' => false,
		
		'desktop' => array(
			'display_comment_count'			=> false,
			'display_publish_date'			=> false,
			'display_thumbnail'			=> true,
			'display_excerpt'			=> false,
			'excerpt_max_length'			=> 200,
			'theme_name' 				=> 'vertical.css',
			'theme_custom_css'			=> ZEM_RP_DEFAULT_CUSTOM_CSS,
			'custom_theme_enabled' => false
		)
	);

	update_option('zem_rp_meta', $zem_rp_meta);
	update_option('zem_rp_options', $zem_rp_options);

	zem_rp_related_posts_db_table_install();

	zem_rp_process_latest_post_thumbnails();
	zem_rp_set_global_notice();
	zem_rp_nrelate_migrate();
}

function zem_is_classic() {
	$meta = zem_rp_get_meta();
	if (isset($meta['classic_user']) && $meta['classic_user']) {
		return true;
	}
	return false;
}

function zem_rp_migrate_1_12() {
	$meta = get_option('zem_rp_meta');
	$meta['version'] = '1.12';
	$meta['new_user'] = false;
	update_option('zem_rp_meta', $meta);
}


function zem_rp_migrate_1_11() {
	$meta = get_option('zem_rp_meta');
	$meta['version'] = '1.11.1';
	$meta['new_user'] = false;
	update_option('zem_rp_meta', $meta);
}


function zem_rp_migrate_1_10() {
	$meta = get_option('zem_rp_meta');
	$meta['version'] = '1.11';
	$meta['new_user'] = false;
	update_option('zem_rp_meta', $meta);
}


function zem_rp_migrate_1_9_3() {
	$meta = get_option('zem_rp_meta');
	$meta['version'] = '1.10';
	$meta['new_user'] = false;
	update_option('zem_rp_meta', $meta);
}


function zem_rp_migrate_1_9_2() {
	$meta = get_option('zem_rp_meta');
	$meta['version'] = '1.9.3';
	$meta['new_user'] = false;
	update_option('zem_rp_meta', $meta);
}

function zem_rp_migrate_1_9_1() {
	$meta = get_option('zem_rp_meta');
	$meta['version'] = '1.9.2';
	$meta['new_user'] = false;
	update_option('zem_rp_meta', $meta);
}

function zem_rp_migrate_1_9() {
	$meta = get_option('zem_rp_meta');
	$meta['version'] = '1.9.1';
	$meta['new_user'] = false;
	update_option('zem_rp_meta', $meta);
}

function zem_rp_migrate_1_8_2() {
	$meta = get_option('zem_rp_meta');
	$meta['version'] = '1.9';
	$meta['new_user'] = false;

	$meta_to_remove = array(
		'remote_notifications', 'show_statistics',
		'show_traffic_exchange', 'blog_id', 'auth_key'
	);
	foreach($meta_to_remove as $setting) {
		if (isset($meta[$settings])) {
			unset($meta[$setting]);
		}
	}

	update_option('zem_rp_meta', $meta);

	$options = get_option('zem_rp_options');
	unset($options['mobile']);
	update_option('zem_rp_options', $options);
}

function zem_rp_migrate_1_8_1() {
	$zem_rp_meta = get_option('zem_rp_meta');
	$zem_rp_meta['version'] = '1.8.2';
	$zem_rp_meta['new_user'] = false;

	$zem_rp_meta['subscribed'] = false;
	$zem_rp_meta['registered'] = false;
	
	update_option('zem_rp_meta', $zem_rp_meta);
	zem_rp_set_global_notice();
}

function zem_rp_migrate_1_8() {
	$zem_rp_meta = get_option('zem_rp_meta');
	$zem_rp_meta['version'] = '1.8.1';
	$zem_rp_meta['new_user'] = false;
	update_option('zem_rp_meta', $zem_rp_meta);

	$zem_rp_options = get_option('zem_rp_options');
	$zem_rp_options['custom_size_thumbnail_enabled'] = false;
	$zem_rp_options['custom_thumbnail_width'] = ZEM_RP_CUSTOM_THUMBNAILS_WIDTH;
	$zem_rp_options['custom_thumbnail_height'] = ZEM_RP_CUSTOM_THUMBNAILS_HEIGHT;
	$zem_rp_options['only_admins_can_edit_related_posts'] = false;
	
	update_option('zem_rp_options', $zem_rp_options);
}

function zem_rp_migrate_1_7() {
	global $wpdb;

	$zem_rp_meta = get_option('zem_rp_meta');
	$zem_rp_meta['version'] = '1.8';
	$zem_rp_meta['new_user'] = false;
	if (strpos(get_bloginfo('language'), 'en') === 0) {
		$zem_rp_meta['classic_user'] = true;
	}

	update_option('zem_rp_meta', $zem_rp_meta);
}

function zem_rp_migrate_1_6() {
	global $wpdb;

	$zem_rp_meta = get_option('zem_rp_meta');
	$zem_rp_meta['version'] = '1.7';
	$zem_rp_meta['new_user'] = false;

	update_option('zem_rp_meta', $zem_rp_meta);
}

function zem_rp_migrate_1_5() {
	global $wpdb;

	$zem_rp_meta = get_option('zem_rp_meta');
	$zem_rp_meta['version'] = '1.6';
	$zem_rp_meta['new_user'] = false;

	$wpdb->query("DELETE FROM $wpdb->postmeta WHERE meta_key IN ('_zem_rp_extracted_image_url', '_zem_rp_extracted_image_url_full')");

	update_option('zem_rp_meta', $zem_rp_meta);
}

function zem_rp_migrate_1_4() {
	$zem_rp_meta = get_option('zem_rp_meta');
	$zem_rp_meta['version'] = '1.5';
	$zem_rp_meta['new_user'] = false;
	update_option('zem_rp_meta', $zem_rp_meta);
}

function zem_rp_migrate_1_3_1() {
	$zem_rp_meta = get_option('zem_rp_meta');
	$zem_rp_meta['version'] = '1.4';
	$zem_rp_meta['new_user'] = false;
	update_option('zem_rp_meta', $zem_rp_meta);
}

function zem_rp_migrate_1_3() {
	$zem_rp_meta = get_option('zem_rp_meta');
	$zem_rp_options = get_option('zem_rp_options');

	$zem_rp_meta['version'] = '1.3.1';

	$zem_rp_options['display_zemanta_linky'] = false;

	update_option('zem_rp_options', $zem_rp_options);
	update_option('zem_rp_meta', $zem_rp_meta);
}

function zem_rp_migrate_1_2() {
	$zem_rp_meta = get_option('zem_rp_meta');
	$zem_rp_options = get_option('zem_rp_options');

	$zem_rp_meta['version'] = '1.3';

	if (!isset($zem_rp_meta['blog_tg'])) {
		$zem_rp_meta['blog_tg'] = rand(0, 1);
	}

	update_option('zem_rp_options', $zem_rp_options);
	update_option('zem_rp_meta', $zem_rp_meta);
}

function zem_rp_migrate_1_1() {
	$zem_rp_meta = get_option('zem_rp_meta');
	$zem_rp_options = get_option('zem_rp_options');

	$zem_rp_meta['version'] = '1.2';

	if (!isset($zem_rp_meta['zemanta_username'])) {
		$zem_rp_meta['zemanta_username'] = false;
	}

	$display_options = array(
		'display_comment_count'			=> $zem_rp_options['display_comment_count'],
		'display_publish_date'			=> $zem_rp_options['display_publish_date'],
		'display_thumbnail'			=> $zem_rp_options['display_thumbnail'],
		'display_excerpt'			=> $zem_rp_options['display_excerpt'],
		'excerpt_max_length'			=> $zem_rp_options['excerpt_max_length'],
		'theme_name' 				=> $zem_rp_options['theme_name'],
		'theme_custom_css'			=> $zem_rp_options['theme_custom_css'],
		'custom_theme_enabled' => $zem_rp_options['custom_theme_enabled']
	);

	$zem_rp_options['desktop'] = $display_options;
	$zem_rp_options['mobile'] = $display_options;

	if ($zem_rp_options['mobile']['theme_name'] !== 'plain.css') {
		$zem_rp_options['mobile']['theme_name'] = 'm-stream.css';
	}

	update_option('zem_rp_options', $zem_rp_options);
	$zem_rp_options = get_option('zem_rp_options');

	unset($zem_rp_options['traffic_exchange_enabled']);
	unset($zem_rp_options['promoted_content_enabled']);
	unset($zem_rp_options['ctr_dashboard_enabled']);

	unset($zem_rp_options['thumbnail_use_attached']);
	unset($zem_rp_options['thumbnail_display_title']);

	unset($zem_rp_options['display_comment_count']);
	unset($zem_rp_options['display_publish_date']);
	unset($zem_rp_options['display_thumbnail']);
	unset($zem_rp_options['display_excerpt']);
	unset($zem_rp_options['excerpt_max_length']);
	unset($zem_rp_options['theme_name']);
	unset($zem_rp_options['theme_custom_css']);
	unset($zem_rp_options['custom_theme_enabled']);

	unset($zem_rp_options['from_around_the_web']);

	update_option('zem_rp_options', $zem_rp_options);
	update_option('zem_rp_meta', $zem_rp_meta);
}

function zem_rp_migrate_1_0() {
	$zem_rp_meta = get_option('zem_rp_meta');
	$zem_rp_options = get_option('zem_rp_options');

	$zem_rp_meta['version'] = '1.1';

	if (!isset($zem_rp_meta['zemanta_username'])) {
		$zem_rp_meta['zemanta_username'] = false;
	}

	$zem_rp_options['max_related_post_age_in_days'] = 0;

	zem_rp_related_posts_db_table_uninstall();
	zem_rp_related_posts_db_table_install();

	update_option('zem_rp_options', $zem_rp_options);
	update_option('zem_rp_meta', $zem_rp_meta);
}
