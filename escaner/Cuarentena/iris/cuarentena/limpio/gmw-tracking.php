<?php
/*
 * Google Maps Widget
 * Plugin usage tracking
 * (c) Web factory Ltd, 2012 - 2015
 */


// include only file
if (!defined('ABSPATH')) {
  die();
}


class GMW_tracking {
  // set things up
  public static function init() {
    $options = get_option(GMW_OPTIONS);

    self::check_opt_in_out();

    // ask user if he wants to allow tracking
    if (is_admin() && !isset($options['allow_tracking'])) {
      add_action('admin_notices', array(__CLASS__, 'tracking_notice'));
    }

    add_action(GMW_CRON, array(__CLASS__, 'send_data'));
    // todo - write this properly, so it doesn't run each time, $force ...
    GMW_tracking::setup_cron();
  } // init


  // register additional cron interval
  public static function register_cron_intervals($schedules) {
    $schedules['gmw_weekly'] = array(
      'interval' => DAY_IN_SECONDS * 7,
      'display' => 'Once a Week');

    return $schedules;
  } // cron_intervals


  // clear cron scheadule
  public static function clear_cron() {
    wp_clear_scheduled_hook(GMW_CRON);
  } // clear_cron


  // setup cron job when user allows tracking
  public static function setup_cron() {
    $options = get_option(GMW_OPTIONS);

    if (isset($options['allow_tracking']) && $options['allow_tracking'] === true) {
      if (!wp_next_scheduled(GMW_CRON)) {
        wp_schedule_event(time() + 300, 'gmw_weekly', GMW_CRON);
      }
    } else {
      self::clear_cron();
    }
  } // setup_cron


  // save user's choice for (not) allowing tracking
  public static function check_opt_in_out() {
    $options = get_option(GMW_OPTIONS);

    if (isset($_GET['gmw_tracking']) && $_GET['gmw_tracking'] == 'opt_in') {
      $options['allow_tracking'] = true;
      update_option(GMW_OPTIONS, $options);
      self::send_data(true);
      wp_redirect(remove_query_arg('gmw_tracking'));
      die();
    } else if (isset($_GET['gmw_tracking']) && $_GET['gmw_tracking'] == 'opt_out') {
      $options['allow_tracking'] = false;
      update_option(GMW_OPTIONS, $options);
      wp_redirect(remove_query_arg('gmw_tracking'));
      die();
    }
  } // check_opt_in_out


  // display tracking notice
  public static function tracking_notice() {
    $optin_url = add_query_arg('gmw_tracking', 'opt_in');
    $optout_url = add_query_arg('gmw_tracking', 'opt_out');

    echo '<div class="updated"><p>';
    echo __('Please help us improve <strong>Google Maps Widget</strong> by allowing us to track anonymous usage data. Absolutely <strong>no sensitive data is tracked</strong> (<a href="http://www.googlemapswidget.com/plugin-tracking-info/" target="_blank">complete disclosure &amp; details of our tracking policy</a>).', 'google-maps-widget');
    echo '<br /><a href="' . esc_url($optin_url) . '" style="vertical-align: baseline;" class="button-primary">' . __('Allow', 'google-maps-widget') . '</a>';
    echo '&nbsp;&nbsp;<a href="' . esc_url($optout_url) . '" class="">' . __('Do not allow tracking', 'google-maps-widget') . '</a>';
    echo '</p></div>';
  } // tracking_notice


  // send usage data once a week to our server
  public static function send_data($force = false) {
    $options = get_option(GMW_OPTIONS);

    if ($force == false && (!isset($options['allow_tracking']) || $options['allow_tracking'] !== true)) {
      return;
    }
    if ($force == false && ($options['last_tracking'] && $options['last_tracking'] > strtotime( '-6 days'))) {
      return;
    }

    $data = self::prepare_data();
    $request = wp_remote_post('http://www.googlemapswidget.com/tracking.php', array(
                              'method' => 'POST',
                              'timeout' => 10,
                              'redirection' => 3,
                              'httpversion' => '1.0',
                              'body' => $data,
                              'user-agent' => 'GMW/' . GMW::$version));

    $options['last_tracking'] = current_time('timestamp');
    update_option(GMW_OPTIONS, $options);
  } // send_data


  // get and prepare data that will be sent out
  public static function prepare_data() {
    $options = get_option(GMW_OPTIONS);
    $data = array();
    $current_user = wp_get_current_user();

    $data['url'] = home_url();
    if ($current_user && isset($current_user->user_email) && !empty($current_user->user_email)) {
      $data['admin_email'] = $current_user->user_email;
    } else {
      $data['admin_email'] = get_bloginfo('admin_email');
    }
    $data['wp_version'] = get_bloginfo('version');
    $data['gmw_version'] = GMW::$version;
    $data['gmw_first_version'] = $options['first_version'];
    $data['gmw_first_install'] = $options['first_install'];
    $data['gmw_activated'] = GMW::is_activated();
    $data['ioncube'] = extension_loaded('IonCube Loader');

    $data['gmw_count'] = 0;
    $sidebars = get_option('sidebars_widgets', array());
    foreach ($sidebars as $sidebar_name => $widgets) {
      if (strpos($sidebar_name, 'inactive') !== false || strpos($sidebar_name, 'orphaned') !== false) {
        continue;
      }
      if (is_array($widgets)) {
        foreach ($widgets as $widget_name) {
          if (strpos($widget_name, 'googlemapswidget') !== false) {
            $data['gmw_count']++;
          }
        }
      }
    } // foreach sidebar

    if (get_bloginfo('version') < '3.4') {
      $theme = get_theme_data(get_stylesheet_directory() . '/style.css');
      $data['theme_name'] = $theme['Name'];
      $data['theme_version'] = $theme['Version'];
    } else {
      $theme = wp_get_theme();
      $data['theme_name'] = $theme->Name;
      $data['theme_version'] = $theme->Version;
    }

    // get current plugin information
    if (!function_exists('get_plugins')) {
      include ABSPATH . '/wp-admin/includes/plugin.php';
    }

    $plugins = get_plugins();
    $active_plugins = get_option('active_plugins', array());

    foreach ($active_plugins as $plugin) {
      $data['plugins'][$plugin] = @$plugins[$plugin];
    }

    return $data;
  } // prepare_data
} // class GMW_tracking