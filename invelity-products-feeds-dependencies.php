<?php

//general settings page of Invelity Plugins
if (!class_exists('InvelityPluginsGeneral')) {
  require_once __DIR__ . '/classes/admin/classInvelityPluginsGeneral.php';
}

//plugin settings
if (!class_exists('InvelityFeedPluginSettings')) {
  require_once __DIR__ . '/classes/admin/classInvelityFeedPluginSettings.php';
}

//settings in reading
if (!class_exists('PLUGIN_READING_PAGE_SETTINGS')) {
  require_once __DIR__ . '/classes/admin/classPluginReadingPageSettings.php';
}
//generate feed page
if (!class_exists('PluginSettingsGenerateFeedPage')) {
  require_once __DIR__ . '/classes/admin/classPluginSettingsGenerateFeedPage.php';
}
//manage feeds page
if (!class_exists('PluginSettingsManageFeedPage')) {
  require_once __DIR__ . '/classes/admin/classPluginSettingsManageFeedPage.php';
}
//ajax products and categories
if (!class_exists('AjaxFeedsProcess')) {
  require_once __DIR__ . '/classes/process/classAjaxFeedsProcess.php';

}


//looping products for feed
if (!class_exists('InvelityGenerateProductLoop')) {
  require_once __DIR__ . '/classes/feeds/classInvelityGenerateProductLoop.php';
}

//feeds
if (!class_exists('InvelityFeedHeureka')) {
  require_once __DIR__ . '/classes/feeds/classInvelityFeedHeureka.php';
}

if (!class_exists('InvelityFeedGoogle')) {
  require_once __DIR__ . '/classes/feeds/classInvelityFeedGoogle.php';
}

if (!class_exists('InvelityFeedCustomData')) {
  require_once __DIR__ . '/classes/feeds/classInvelityFeedCustomData.php';
}

if (!class_exists('InvelityFeedFacebook')) {
  require_once __DIR__ . '/classes/feeds/classInvelityFeedFacebook.php';
}

if (!class_exists('InvelityFeedDynamicSearchAds')) {
  require_once __DIR__ . '/classes/feeds/classInvelityFeedDynamicSearchAds.php';
}


//taxonomies ,obviously..
require_once __DIR__ . '/taxonomy/brand-taxonomy.php';
require_once __DIR__ . '/taxonomy/google-taxonomy.php';
require_once __DIR__ . '/taxonomy/heureka-taxonomy.php';
require_once __DIR__ . '/taxonomy/general-settings-taxonomy.php';
