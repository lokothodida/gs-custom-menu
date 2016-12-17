<?php
/* Custom Menu */

// Plugin ID
$thisfile = basename(__FILE__, '.php');

// language
i18n_merge($thisfile) || i18n_merge($thisfile, 'en_US');

// requires
require_once(GSPLUGINPATH . $thisfile . '/php/plugin.class.php');
require_once(GSPLUGINPATH . $thisfile . '/php/data.class.php');
require_once(GSPLUGINPATH . $thisfile . '/php/display.class.php');
require_once(GSPLUGINPATH . $thisfile . '/php/placeholder.class.php');

// Register plugin
register_plugin(
  CustomMenu::FILE,
  CustomMenu::i18n_r('PLUGIN_NAME'),
  CustomMenu::VERSION,
  CustomMenu::AUTHOR,
  CustomMenu::URL,
  CustomMenu::i18n_r('PLUGIN_DESC'),
  CustomMenu::PAGE,
  'CustomMenu::admin'
);

// Activate actions/filters
// front-end
  // Theme header
  add_action('theme-header', 'CustomMenu::themeHeader');

  // Placeholder content filter
  add_filter('content', 'CustomMenuPlaceholder::filter');

// back-end
  if (isset($_GET['id']) && $_GET['id'] == CustomMenu::FILE) {
    // Admin header
    add_action('header', 'CustomMenu::header');
  }

  // Plugin sidebar
  add_action(CustomMenu::PAGE . '-sidebar', 'createSideMenu' , array(CustomMenu::FILE, CustomMenu::i18n_r('PLUGIN_SIDEBAR'))); // sidebar link

// functions
function get_custom_menu($name, $classes = array()) {
  $menu = new CustomMenuDisplay($name, $classes);
  $menu->displayMenu();
}