<?php
/*
Plugin Name: Invelity Products Feeds
Plugin URI: https://www.invelity.com
Description: Plugin Invelity Products Feeds is designed for Wordpress (WooCommerce) online stores and generates xml or csv files used for google/facebook or heureka feed.
Author: Invelity
Author URI: https://www.invelity.com
Version: 1.2.6
 */

require_once __DIR__ . '/invelity-products-feeds-dependencies.php';

if (!defined('ABSPATH')) {
  exit;
}


class InvelityProductsFeeds
{



  public function __construct()
  {

    $this->settings['plugin-slug'] = 'invelity-products-feeds';
    $this->settings['plugin-path'] = plugin_dir_path(__FILE__);
    $this->settings['plugin-url'] = plugin_dir_url(__FILE__);
    $this->settings['plugin-name'] = 'Invelity Products Feeds';
    $this->settings['plugin-license-version'] = '1.x.x';

    $this->initialize();

  }

  private function initialize()
  {
    new InvelityPluginsGeneral($this);
    new InvelityFeedPluginSettings($this);
    new PluginReadingPageSettings($this);


  }

  public function getPluginSlug()
  {
    return $this->settings['plugin-slug'];
  }

  public function getPluginPath()
  {
    return $this->settings['plugin-path'];
  }

  public function getPluginUrl()
  {
    return $this->settings['plugin-url'];
  }

  public function getPluginName()
  {
    return $this->settings['plugin-name'];
  }

  public function getPluginLicenseVersion()
  {
    return $this->settings['plugin-license-version'];
  }


}

new InvelityProductsFeeds();


$plugin = plugin_basename(__FILE__);

function plugin_add_settings_link($links)
{

  $links = array_merge(array(
    '<a href="' . esc_url(admin_url('/options-reading.php')) . '">' . __('Settings', 'textdomain') . '</a>'
  ), $links);
  return $links;
}
add_action("plugin_action_links_$plugin", 'plugin_add_settings_link');


function clear_description($description)
{
  $descriptionText = trim(preg_replace('/\t+/', '', strip_tags($description)));
  $descriptionText = str_replace("&nbsp;", " ", $descriptionText);
  $descriptionText = str_replace("\t", " ", $descriptionText);
  $descriptionText = str_replace("\r\n", " ", $descriptionText);
  $descriptionText = str_replace("\r", " ", $descriptionText);
  $descriptionText = str_replace("\n", " ", $descriptionText);
  return $descriptionText;
}


register_activation_hook(__FILE__, 'invelity_product_feed_activate');


function invelity_product_feed_activate()
{
  if (!wp_next_scheduled('invelity_product_feed_update')) {
    wp_schedule_event(time(), 'daily', 'invelity_product_feed_update');
  }
}


add_action('invelity_product_feed_update', 'invelity_product_feed_do_cron');

function invelity_product_feed_do_cron()
{

  $MSG = '----------------------------------------------------------------WF-CRON----------------------------------------------------------------------';

  if (WP_DEBUG_LOG === true) {
    if (is_array($MSG) || is_object($MSG)) {
      error_log(print_r($MSG, true));
    } else {
      error_log($MSG);

    }
  }

  global $wpdb;
  $var = "wf_config_";
  $query = $wpdb->prepare("SELECT * FROM $wpdb->options WHERE option_name LIKE %s;", $var . "%");
  $result = $wpdb->get_results($query, 'ARRAY_A');
  foreach ($result as $key => $value) {

    $data = unserialize($value['option_value']);

    $date = new DateTime();
    $date = $date->format("d.m.y h:i:s");
    $feedLang = 'default';
    if (function_exists('icl_object_id')) {
      $currentLanguage = apply_filters('wpml_current_language', null);
      global $sitepress;

      if (isset($data['language'])) {
        $feedLang = $data['language'];
      } else {
        $feedLang = $currentLanguage;
      }
      //date_default_timezone_set('Europe/Bratislava');

      $sitepress->switch_lang($feedLang, true);

    }

    $data['language'] = $feedLang;
    $data['last_cron'] = $date;

    if ($data['provider'] == 'heureka' || $data['provider'] == 'Heureka') {
      new InvelityFeedHeureka($data);

    }
    if ($data['provider'] == 'facebook' || $data['provider'] == 'Facebook') {
      new InvelityFeedFacebook($data);

    }
    if ($data['provider'] == 'google merchant center' || $data['provider'] == 'Google Merchant Center') {
      new InvelityFeedGoogle($data);

    }
    if ($data['provider'] == 'custom feed' || $data['provider'] == 'Custom Feed') {
      new InvelityFeedCustomData($data);

    }
    if ($data['provider'] == 'dynamic search feed' || $data['provider'] == 'Dynamic Search Ads') {
      new InvelityFeedDynamicSearchAds($data);

    }

    $filename = 'wf_config_' . $data['filename'];
    update_option($filename, $data);

    error_log($filename);
    error_log(print_r($data, true));

    if (function_exists('icl_object_id')) {
      $sitepress->switch_lang($currentLanguage, true);
    }

  }

}

register_deactivation_hook(__FILE__, 'invelity_product_feed_cron_deactivation');

function invelity_product_feed_cron_deactivation()
{
  wp_clear_scheduled_hook('invelity_product_feed_update');
}