<?php
/*
 * Google Maps Widget
 * Widget definition, admin GUI and front-end functions
 * (c) Web factory Ltd, 2012 - 2015
 */


// this is an include only file
if (!defined('ABSPATH')) {
  die();
}


// main widget class, extends WP widget interface/class
class GoogleMapsWidget extends WP_Widget {
  static $widgets = array();


  // constructor method
  function GoogleMapsWidget() {
    $widget_ops = array('classname' => 'google-maps-widget', 'description' => __('Displays a map image thumbnail with a larger map available in a lightbox.', 'google-maps-widget'));
    $control_ops = array('width' => 450, 'height' => 350);
    $this->WP_Widget('GoogleMapsWidget', __('Google Maps Widget', 'google-maps-widget'), $widget_ops, $control_ops);
  } // GoogleMapsWidget


  // widget edit form HTML
  function form($instance) {
    $instance = wp_parse_args((array) $instance,
                              array('title' => __('Map', 'google-maps-widget'),
                                    'address' => __('New York, USA', 'google-maps-widget'),
                                    'thumb_pin_color' => 'red',
                                    'thumb_pin_type' => 'predefined',
                                    'thumb_pin_size' => 'default',
                                    'thumb_pin_img' => '',
                                    'thumb_width' => '250',
                                    'thumb_height' => '250',
                                    'thumb_type' => 'roadmap',
                                    'thumb_zoom' => '13',
                                    'thumb_header' => '',
                                    'thumb_footer' => 'Powered by Google Maps Widget',
                                    'thumb_color_scheme' => '',
                                    'thumb_link_type' => 'lightbox',
                                    'thumb_link' => '',
                                    'lightbox_width' => '550',
                                    'lightbox_height' => '550',
                                    'lightbox_type' => 'roadmap',
                                    'lightbox_zoom' => '14',
                                    'lightbox_bubble' => '1',
                                    'lightbox_skin' => 'light',
                                    'lightbox_title' => '1',
                                    'lightbox_header' => '',
                                    'lightbox_footer' => ''));

    extract($instance, EXTR_SKIP);

    // legacy fixes for older versions; it's auto-fixed on first widget save but has to be here
    if(!$thumb_pin_type) {
      $thumb_pin_type = 'predefined';
    }
    if(!$thumb_link_type) {
      $thumb_link_type = 'lightbox';
    }
    if(!$lightbox_skin) {
      $lightbox_skin = 'light';
    }

    $map_types_thumb = array(array('val' => 'roadmap', 'label' => __('Road (default)', 'google-maps-widget')),
                             array('val' => 'satellite', 'label' => __('Satellite', 'google-maps-widget')),
                             array('val' => 'terrain', 'label' => __('Terrain', 'google-maps-widget')),
                             array('val' => 'hybrid', 'label' => __('Hybrid', 'google-maps-widget')));

    $map_types_lightbox = array(array('val' => 'm', 'label' => __('Road (default)', 'google-maps-widget')),
                                array('val' => 'k', 'label' => __('Satellite', 'google-maps-widget')),
                                array('val' => 'p', 'label' => __('Terrain', 'google-maps-widget')),
                                array('val' => 'h', 'label' => __('Hybrid', 'google-maps-widget')));

    $pin_colors = array(array('val' => 'black', 'label' => __('Black', 'google-maps-widget')),
                        array('val' => 'brown', 'label' => __('Brown', 'google-maps-widget')),
                        array('val' => 'green', 'label' => __('Green', 'google-maps-widget')),
                        array('val' => 'purple', 'label' => __('Purple', 'google-maps-widget')),
                        array('val' => 'yellow', 'label' => __('Yellow', 'google-maps-widget')),
                        array('val' => 'blue', 'label' => __('Blue', 'google-maps-widget')),
                        array('val' => 'gray', 'label' => __('Gray', 'google-maps-widget')),
                        array('val' => 'orange', 'label' => __('Orange', 'google-maps-widget')),
                        array('val' => 'red', 'label' => __('Red (default)', 'google-maps-widget')),
                        array('val' => 'white', 'label' => __('White', 'google-maps-widget')));

    $pin_sizes = array(array('val' => 'tiny', 'label' => __('Tiny', 'google-maps-widget')),
                       array('val' => 'small', 'label' => __('Small', 'google-maps-widget')),
                       array('val' => 'mid', 'label' => __('Medium', 'google-maps-widget')),
                       array('val' => 'default', 'label' => __('Large (default)', 'google-maps-widget')));

    $zoom_levels = array(array('val' => '0', 'label' => __('0 - entire world', 'google-maps-widget')));
    for ($tmp = 1; $tmp <= 21; $tmp++) {
      $zoom_levels[] = array('val' => $tmp, 'label' => $tmp);
    }

    $lightbox_skins = array(array('val' => 'light', 'label' => __('Light (default)', 'google-maps-widget')),
                            array('val' => 'dark', 'label' => __('Dark', 'google-maps-widget')));

    $lightbox_bubbles = array(array('val' => '0', 'label' => __('Hide', 'google-maps-widget')),
                            array('val' => '1', 'label' => __('Show (default)', 'google-maps-widget')));

    $lightbox_titles = array(array('val' => '0', 'label' => __('Do not show map title on lightbox', 'google-maps-widget')),
                            array('val' => '1', 'label' => __('Show map title on lightbox (default)', 'google-maps-widget')));

    $thumb_pin_types = array(array('val' => 'predefined', 'label' => __('Predefined (default)', 'google-maps-widget')),
                             array('val' => 'custom', 'label' => __('Custom', 'google-maps-widget')));

    $thumb_link_types = array(array('val' => 'lightbox', 'label' => __('Lightbox (default)', 'google-maps-widget')),
                              array('val' => 'custom', 'label' => __('Custom URL', 'google-maps-widget')),
                              array('val' => 'nolink', 'label' => __('Disable link', 'google-maps-widget')));

    $thumb_color_schemes = array(array('val' => 'default', 'label' => __('Default', 'gmw')),
                                 array('val' => 'new', 'label' => __('Refreshed by Google', 'gmw')));

    if (GMW::is_activated()) {
      array_push($thumb_color_schemes, array('val' => 'apple', 'label' => __('Apple', 'google-maps-widget')),
                                       array('val' => 'gray', 'label' => __('Gray', 'google-maps-widget')),
                                       array('val' => 'paper', 'label' => __('Paper', 'google-maps-widget')));
      array_push($lightbox_skins, array('val' => 'noimage-blue', 'label' => __('Blue', 'google-maps-widget')),
                                  array('val' => 'noimage-rounded', 'label' => __('Rounded', 'google-maps-widget')));
    }

    echo '<p><label for="' . $this->get_field_id('title') . '">' . __('Title', 'google-maps-widget') . ':</label><input class="widefat" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . esc_attr($title) . '" /></p>';
    echo '<p><label for="' . $this->get_field_id('address') . '">' . __('Address', 'google-maps-widget') . ':</label><input class="widefat" id="' . $this->get_field_id('address') . '" name="' . $this->get_field_name('address') . '" type="text" value="' . esc_attr($address) . '" /></p>';

    echo '<div class="gmw-tabs" id="tab-' . $this->id . '"><ul><li><a href="#gmw-thumb">' . __('Thumbnail map', 'google-maps-widget') . '</a></li><li><a href="#gmw-lightbox">' . __('Lightbox map', 'google-maps-widget') . '</a></li><li><a href="#gmw-shortcode">' . __('Shortcode', 'google-maps-widget') . '</a></li><li><a href="#gmw-info">' . __('Info &amp; Support', 'google-maps-widget') . '</a></li></ul>';
    echo '<div id="gmw-thumb">';

    echo '<p><label class="gmw-label" for="' . $this->get_field_id('thumb_width') . '">' . __('Map Size', 'google-maps-widget') . ':</label>';
    echo '<input class="small-text" id="' . $this->get_field_id('thumb_width') . '" name="' . $this->get_field_name('thumb_width') . '" type="text" value="' . esc_attr($thumb_width) . '" /> x ';
    echo '<input class="small-text" id="' . $this->get_field_id('thumb_height') . '" name="' . $this->get_field_name('thumb_height') . '" type="text" value="' . esc_attr($thumb_height) . '" />';
    echo ' px</p>';

    echo '<p><label class="gmw-label" for="' . $this->get_field_id('thumb_type') . '">' . __('Map Type', 'google-maps-widget') . ':</label>';
    echo '<select id="' . $this->get_field_id('thumb_type') . '" name="' . $this->get_field_name('thumb_type') . '">';
    GMW::create_select_options($map_types_thumb, $thumb_type);
    echo '</select></p>';

    echo '<p><label class="gmw-label" for="' . $this->get_field_id('thumb_pin_type') . '">' . __('Pin Type', 'google-maps-widget') . ':</label>';
    echo '<select class="gmw_thumb_pin_type" id="' . $this->get_field_id('thumb_pin_type') . '" name="' . $this->get_field_name('thumb_pin_type') . '">';
    GMW::create_select_options($thumb_pin_types, $thumb_pin_type);
    echo '</select></p>';

    echo '<p class="gmw_thumb_pin_type_predefined_section"><label class="gmw-label" for="' . $this->get_field_id('thumb_pin_color') . '">' . __('Pin Color', 'google-maps-widget') . ':</label>';
    echo '<select id="' . $this->get_field_id('thumb_pin_color') . '" name="' . $this->get_field_name('thumb_pin_color') . '">';
    GMW::create_select_options($pin_colors, $thumb_pin_color);
    echo '</select></p>';

    echo '<p class="gmw_thumb_pin_type_predefined_section"><label class="gmw-label" for="' . $this->get_field_id('thumb_pin_size') . '">' . __('Pin Size', 'google-maps-widget') . ':</label>';
    echo '<select id="' . $this->get_field_id('thumb_pin_size') . '" name="' . $this->get_field_name('thumb_pin_size') . '">';
    GMW::create_select_options($pin_sizes, $thumb_pin_size);
    echo '</select></p>';

    echo '<p class="gmw_thumb_pin_type_custom_section"><label class="gmw-label" for="' . $this->get_field_id('thumb_pin_img') . '">' . __('Pin Image URL', 'google-maps-widget') . ':</label>';
    echo '<input type="text" class="regular-text" id="' . $this->get_field_id('thumb_pin_img') . '" name="' . $this->get_field_name('thumb_pin_img') . '" value="' . esc_attr($thumb_pin_img) . '">';

    echo '<p><label class="gmw-label" for="' . $this->get_field_id('thumb_zoom') . '">' . __('Zoom Level', 'google-maps-widget') . ':</label>';
    echo '<select id="' . $this->get_field_id('thumb_zoom') . '" name="' . $this->get_field_name('thumb_zoom') . '">';
    GMW::create_select_options($zoom_levels, $thumb_zoom);
    echo '</select></p>';

    echo '<p><label class="gmw-label" for="' . $this->get_field_id('thumb_link_type') . '">' . __('Link To', 'google-maps-widget') . ':</label>';
    echo '<select class="gmw_thumb_link_type" id="' . $this->get_field_id('thumb_link_type') . '" name="' . $this->get_field_name('thumb_link_type') . '">';
    GMW::create_select_options($thumb_link_types, $thumb_link_type);
    echo '</select></p>';

    echo '<p class="gmw_thumb_link_section"><label class="gmw-label" for="' . $this->get_field_id('thumb_link') . '">' . __('Custom URL', 'google-maps-widget') . ':</label>';
    echo '<input class="regular-text" id="' . $this->get_field_id('thumb_link') . '" name="' . $this->get_field_name('thumb_link') . '" type="text" value="' . esc_attr($thumb_link) . '" /></p>';

    echo '<p><label class="gmw-label" for="' . $this->get_field_id('thumb_color_scheme') . '">' . __('Color Scheme', 'google-maps-widget') . ':</label>';
    echo '<select class="gmw_thumb_color_scheme" id="' . $this->get_field_id('thumb_color_scheme') . '" name="' . $this->get_field_name('thumb_color_scheme') . '">';
    GMW::create_select_options($thumb_color_schemes, $thumb_color_scheme);
    if (!GMW::is_activated()) {
      echo '<option class="promo" value="-1">' . __('Add more schemes for free', 'google-maps-widget') . '</option>';
    }
    echo '</select></p>';

    echo '<p><label for="' . $this->get_field_id('thumb_header') . '">' . __('Text Above Map', 'google-maps-widget') . ':</label>';
    echo '<textarea class="widefat" rows="3" cols="20" id="' . $this->get_field_id('thumb_header') . '" name="' . $this->get_field_name('thumb_header') . '">'. esc_textarea($thumb_header) . '</textarea></p>';

    echo '<p><label for="' . $this->get_field_id('thumb_footer') . '">' . __('Text Below Map', 'google-maps-widget') . ':</label>';
    echo '<textarea class="widefat" rows="3" cols="20" id="' . $this->get_field_id('thumb_footer') . '" name="' . $this->get_field_name('thumb_footer') . '">'. esc_textarea($thumb_footer) . '</textarea></p>';

    echo '</div>'; // thumbnail tab
    echo '<div id="gmw-lightbox">';

    echo '<p><label class="gmw-label" for="' . $this->get_field_id('lightbox_width') . '">' . __('Map Size', 'google-maps-widget') . ':</label>';
    echo '<input class="small-text" id="' . $this->get_field_id('lightbox_width') . '" name="' . $this->get_field_name('lightbox_width') . '" type="text" value="' . esc_attr($lightbox_width) . '" /> x ';
    echo '<input class="small-text" id="' . $this->get_field_id('lightbox_height') . '" name="' . $this->get_field_name('lightbox_height') . '" type="text" value="' . esc_attr($lightbox_height) . '" />';
    echo ' px</p>';

    echo '<p><label class="gmw-label" for="' . $this->get_field_id('lightbox_type') . '">' . __('Map Type', 'google-maps-widget') . ':</label>';
    echo '<select id="' . $this->get_field_id('lightbox_type') . '" name="' . $this->get_field_name('lightbox_type') . '">';
    GMW::create_select_options($map_types_lightbox, $lightbox_type);
    echo '</select></p>';

    echo '<p><label class="gmw-label" for="' . $this->get_field_id('lightbox_zoom') . '">' . __('Zoom Level', 'google-maps-widget') . ':</label>';
    echo '<select id="' . $this->get_field_id('lightbox_zoom') . '" name="' . $this->get_field_name('lightbox_zoom') . '">';
    GMW::create_select_options($zoom_levels, $lightbox_zoom);
    echo '</select></p>';

    echo '<p><label class="gmw-label" for="' . $this->get_field_id('lightbox_skin') . '">' . __('Lightbox Skin', 'google-maps-widget') . ':</label>';
    echo '<select class="gmw_lightbox_skin" id="' . $this->get_field_id('lightbox_skin') . '" name="' . $this->get_field_name('lightbox_skin') . '">';
    GMW::create_select_options($lightbox_skins, $lightbox_skin);
    if (!GMW::is_activated()) {
      echo '<option class="promo" value="-1">' . __('Add more skins for free', 'google-maps-widget') . '</option>';
    }
    echo '</select></p>';

    echo '<p><label class="gmw-label" for="' . $this->get_field_id('lightbox_bubble') . '">' . __('Address Bubble', 'google-maps-widget') . ':</label>';
    echo '<select id="' . $this->get_field_id('lightbox_bubble') . '" name="' . $this->get_field_name('lightbox_bubble') . '">';
    GMW::create_select_options($lightbox_bubbles, $lightbox_bubble);
    echo '</select></p>';

    echo '<p><label class="gmw-label" for="' . $this->get_field_id('lightbox_title') . '">' . __('Map Title', 'google-maps-widget') . ':&nbsp;</label>';
    echo '<select id="' . $this->get_field_id('lightbox_title') . '" name="' . $this->get_field_name('lightbox_title') . '">';
    GMW::create_select_options($lightbox_titles, $lightbox_title);
    echo '</select></p>';

    echo '<p><label for="' . $this->get_field_id('lightbox_header') . '">' . __('Header Text', 'google-maps-widget') . ':</label>';
    echo '<textarea class="widefat" rows="3" cols="20" id="' . $this->get_field_id('lightbox_header') . '" name="' . $this->get_field_name('lightbox_header') . '">'. esc_textarea($lightbox_header) . '</textarea></p>';

    echo '<p><label for="' . $this->get_field_id('lightbox_footer') . '">' . __('Footer Text', 'google-maps-widget') . ':</label>';
    echo '<textarea class="widefat" rows="3" cols="20" id="' . $this->get_field_id('lightbox_footer') . '" name="' . $this->get_field_name('lightbox_footer') . '">'. esc_textarea($lightbox_footer) . '</textarea></p>';

    echo '</div>'; // lightbox tab

    // shortcode tab
    echo '<div id="gmw-shortcode">';
    if (GMW::is_activated()) {
      $id = str_replace('googlemapswidget-', '', $this->id);

      if (!$id || !is_numeric($id)) {
        echo __('Please save the widget so that the shortcode can be generated.', 'google-maps-widget');
      } else {
        echo '<p><code>[gmw id="' . $id . '"]</code><br></p>';
        echo '<p>' . __('Use the above shortcode to display this Google Maps Widget instance in any page or post. <br>Please note that your theme might style the widget in the post as if it is placed in a sidebar. In that case use the <code>span.gmw-shortcode-widget</code> class to target the shortcode and make  necessary changes via CSS.', 'google-maps-widget') . '</p>';
      }
    } else {
      echo '<p>Shortcode support is an extra feature. You can activate it <b>for free</b> and get more features &amp; options for free as well.<br><br><a class="button open_promo_dialog" href="#">Activate extra features</a></p>';
    }
    echo '</div>'; // shortcode tab

    echo '<div id="gmw-info">';
    echo '<h4>' . __('Support', 'google-maps-widget') . '</h4>';
    echo '<p>If you have any problems, questions or would like a new feature added post it on the <a href="https://wordpress.org/support/plugin/google-maps-widget" target="_blank">official support forum</a>. It\'s the only place to get support. Since it\'s free and community powered please be patient. <a target="_blank" href="https://www.paypal.com/cgi-bin/webscr?business=gordan@webfactoryltd.com&cmd=_xclick&currency_code=USD&amount=19&item_name=Premium%20support%20for%20Google%20Maps%20Widget">Premium support</a> is available for $19.</p>';
    echo '<h4>' . __('Activate extra features &amp; options', 'google-maps-widget') . '</h4>';
    echo '<p>' . __('If you subscribe to our mailing list we\'ll instantly activate additional features in the plugin! At the moment those features are: shortcode support, 3 additional thumbnail map skins and 2 additional lightbox skins. More <i>activate by subscribing</i> features will be available soon!', 'google-maps-widget') . '<br>';
    if (GMW::is_activated()) {
      echo __('You\'ve already subscribed and activated extra features. Thank you!', 'google-maps-widget');
    } else {
      echo __('Subscribe and <a class="open_promo_dialog" href="#">activate extra features</a>.', 'google-maps-widget');
    }
    echo '</p>';
    echo '<h4>' . __('Rate the plugin &amp; spread the word', 'google-maps-widget') . '</h4>';
    echo '<p>It won\'t take you more than a minute but it will help us immensely. So please - <a href="https://wordpress.org/support/view/plugin-reviews/google-maps-widget" target="_blank">rate the plugin</a>. Or spread the word by <a href="https://twitter.com/intent/tweet?via=WebFactoryLtd&amp;text=' . urlencode('I\'m using the #free Google Maps Widget for #wordpress. You can grab it too at http://goo.gl/2qcbbf') . '" target="_blank">tweeting about it</a>. Thank you!</p>';
    echo '</div>'; // info tab

    echo '</div><p></p>'; // tabs

    if (!GMW::is_activated()) {
      echo '<p><i>' . __('Subscribe to our newsletter and <a href="#" class="open_promo_dialog">get extra features</a> <b>for free</b>.', 'google-maps-widget') . '</i></p>';
    }
  } // form


  // update/save widget options
  function update($new_instance, $old_instance) {
    $instance = $old_instance;

    $instance['title'] = $new_instance['title'];
    $instance['address'] = $new_instance['address'];
    $instance['thumb_pin_type'] = $new_instance['thumb_pin_type'];
    $instance['thumb_pin_color'] = $new_instance['thumb_pin_color'];
    $instance['thumb_pin_size'] = $new_instance['thumb_pin_size'];
    $instance['thumb_pin_img'] = trim($new_instance['thumb_pin_img']);
    $instance['thumb_width'] = (int) $new_instance['thumb_width'];
    $instance['thumb_height'] = (int) $new_instance['thumb_height'];
    $instance['thumb_zoom'] = $new_instance['thumb_zoom'];
    $instance['thumb_type'] = $new_instance['thumb_type'];
    $instance['thumb_link_type'] = $new_instance['thumb_link_type'];
    $instance['thumb_link'] = trim($new_instance['thumb_link']);
    $instance['thumb_header'] = trim($new_instance['thumb_header']);
    $instance['thumb_footer'] = trim($new_instance['thumb_footer']);
    $instance['thumb_color_scheme'] = $new_instance['thumb_color_scheme'];
    $instance['lightbox_width'] = (int) $new_instance['lightbox_width'];
    $instance['lightbox_height'] = (int) $new_instance['lightbox_height'];
    $instance['lightbox_type'] = $new_instance['lightbox_type'];
    $instance['lightbox_zoom'] = $new_instance['lightbox_zoom'];
    $instance['lightbox_bubble'] = $new_instance['lightbox_bubble'];
    $instance['lightbox_title'] = $new_instance['lightbox_title'];
    $instance['lightbox_header'] = trim($new_instance['lightbox_header']);
    $instance['lightbox_footer'] = trim($new_instance['lightbox_footer']);
    $instance['lightbox_skin'] = $new_instance['lightbox_skin'];
    $instance['core_ver'] = GMW::$version;

    return $instance;
  } // update


  // echo widget
  function widget($args, $instance) {
    $out = $tmp = '';

    $thumb_styles = array(
    'apple' => 'style=feature:water|element:geometry|color:0xa2daf2|&style=feature:landscape.man_made|element:geometry|color:0xf7f1df|&style=feature:landscape.natural|element:geometry|color:0xd0e3b4|&style=feature:landscape.natural.terrain|element:geometry|visibility:off|&style=feature:poi.park|element:geometry|color:0xbde6ab|&style=feature:poi|element:labels|visibility:off|&style=feature:poi.medical|element:geometry|color:0xfbd3da|&style=feature:poi.business|element:all|visibility:off|&style=feature:road|element:geometry.stroke|visibility:off|&style=feature:road|element:labels|visibility:off|&style=feature:road.highway|element:geometry.fill|color:0xffe15f|&style=feature:road.highway|element:geometry.stroke|color:0xefd151|&style=feature:road.arterial|element:geometry.fill|color:0xffffff|&style=feature:road.local|element:geometry.fill|color:black|&style=feature:transit.station.airport|element:geometry.fill|color:0xcfb2db|',
    'gray' => 'style=feature:landscape|element:all|saturation:-100|lightness:65|visibility:on|&style=feature:poi|element:all|saturation:-100|lightness:51|visibility:simplified|&style=feature:road.highway|element:all|saturation:-100|visibility:simplified|&style=feature:road.arterial|element:all|saturation:-100|lightness:30|visibility:on|&style=feature:road.local|element:all|saturation:-100|lightness:40|visibility:on|&style=feature:transit|element:all|saturation:-100|visibility:simplified|&style=feature:administrative.province|element:all|visibility:off|&style=feature:water|element:labels|visibility:on|lightness:-25|saturation:-100|&style=feature:water|element:geometry|hue:0xffff00|lightness:-25|saturation:-97|',
    'paper' => 'style=feature:landscape|element:all|hue:0xF1FF00|saturation:-27.4|lightness:9.4|gamma:1|&style=feature:road.highway|element:all|hue:0x0099FF|saturation:-20|lightness:36.4|gamma:1|&style=feature:road.arterial|element:all|hue:0x00FF4F|saturation:0|lightness:0|gamma:1|&style=feature:road.local|element:all|hue:0xFFB300|saturation:-38|lightness:11.2|gamma:1|&style=feature:water|element:all|hue:0x00B6FF|saturation:4.2|lightness:-63.4|gamma:1|&style=feature:poi|element:all|hue:0x9FFF00|saturation:0|lightness:0|gamma:1|');

    extract($args, EXTR_SKIP);

    $ll = '';
    if ($instance['lightbox_zoom'] > 14) {
      $coordinates = GMW::get_coordinates($instance['address']);
      if ($coordinates) {
        $ll = $coordinates['lat'] . ',' . $coordinates['lng'];
      }
    }

    $lang = substr(@$_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
    if (!$lang) {
      $lang = 'en';
    }

    // legacy fix for older versions; it's auto-fixed on first widget save but has to be here
    if(!$instance['lightbox_skin']) {
      $instance['lightbox_skin'] = 'light';
    }

    self::$widgets[] = array('title' => ($instance['lightbox_title']? $instance['title']: ''),
                             'width' => $instance['lightbox_width'],
                             'height' => $instance['lightbox_height'],
                             'footer' => $instance['lightbox_footer'],
                             'header' => $instance['lightbox_header'],
                             'address' => $instance['address'],
                             'zoom' => $instance['lightbox_zoom'],
                             'type' => $instance['lightbox_type'],
                             'skin' => $instance['lightbox_skin'],
                             'bubble' => $instance['lightbox_bubble'],
                             'll' => $ll,
                             'id' => $widget_id);

    $out .= $before_widget;

    if (!isset($instance['thumb_pin_type']) || empty($instance['thumb_pin_type'])) {
      $instance['thumb_pin_type'] = 'predefined';
    }

    if (!isset($instance['thumb_link_type']) || empty($instance['thumb_link_type'])) {
      $instance['thumb_link_type'] = 'lightbox';
    }

    $title = empty($instance['title'])? ' ' : apply_filters('widget_title', $instance['title']);
    if (!empty($title)) {
      $out .= $before_title . $title . $after_title;
    }

    if (isset($instance['thumb_header']) && $instance['thumb_header']) {
      $tmp .= wpautop(do_shortcode($instance['thumb_header']));
    }
    $tmp .= '<p>';

    if ($instance['thumb_link_type'] == 'lightbox') {
      $alt = __('Click to open larger map', 'google-maps-widget');
    } else {
      $alt = esc_attr($instance['address']);
    }

    if ($instance['thumb_link_type'] == 'lightbox') {
      $tmp .= '<a class="gmw-thumbnail-map gmw-lightbox-enabled" href="#gmw-dialog-' . $widget_id . '" title="' . __('Click to open larger map', 'google-maps-widget') . '">';
    } elseif ($instance['thumb_link_type'] == 'custom') {
      $tmp .= '<a class="gmw-thumbnail-map" title="' . esc_attr($instance['address']) . '" href="' . $instance['thumb_link'] . '">';
    }
    $tmp .= '<img alt="' . $alt . '" title="' . $alt . '" src="//maps.googleapis.com/maps/api/staticmap?center=' .
         urlencode($instance['address']) . '&amp;zoom=' . $instance['thumb_zoom'] .
         '&amp;size=' . $instance['thumb_width'] . 'x' . $instance['thumb_height'] . '&amp;maptype=' . $instance['thumb_type'] .
         '&amp;scale=1&amp;';
    if ($instance['thumb_pin_type'] != 'custom') {
      $tmp .= 'markers=size:' . $instance['thumb_pin_size'] . '%7Ccolor:' . $instance['thumb_pin_color'];
    } else {
      $tmp .= 'markers=icon:' . urlencode($instance['thumb_pin_img']);
    }
    $tmp .= '%7Clabel:A%7C' . urlencode($instance['address']) . '&amp;language=' . $lang;
    if (!isset($instance['thumb_color_scheme']) || $instance['thumb_color_scheme'] == 'default') {
      $tmp .= '&amp;visual_refresh=false';
    } elseif ($instance['thumb_color_scheme'] == 'new') {
      $tmp .= '&amp;visual_refresh=true';
    } elseif (GMW::is_activated()) {
      $tmp .= '&amp;' . str_replace('&', '&amp;', $thumb_styles[$instance['thumb_color_scheme']]);
    }
    $tmp .= '">';
    if ($instance['thumb_link_type'] == 'lightbox' || $instance['thumb_link_type'] == 'custom') {
      $tmp .= '</a>';
    }
    $tmp .= '</p>';
    if (isset($instance['thumb_footer']) && $instance['thumb_footer']) {
      if ($instance['thumb_footer'] == 'Powered by Google Maps Widget') {
        $tmp .= '<span class="gmw-powered-by">Powered by <a title="Powered by free Google Maps Widget plugin for WordPress" href="http://www.googlemapswidget.com" target="_blank">Google Maps Widget</a></span>';
      } else {
        $tmp .= wpautop(do_shortcode($instance['thumb_footer']));
      }
    }
    $out .= apply_filters('google_maps_widget_content', $tmp);

    $out .= $after_widget;

    echo $out;
  } // widget
} // class GoogleMapsWidget