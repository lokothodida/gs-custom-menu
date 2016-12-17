<?php
/* Custom Menu */

# thisfile
  $thisfile = basename(__FILE__, '.php');

# language
  i18n_merge($thisfile) || i18n_merge($thisfile, 'en_US');

# requires
  require_once(GSPLUGINPATH . $thisfile . '/php/plugin.class.php');
  require_once(GSPLUGINPATH . $thisfile . '/php/display.class.php');
  require_once(GSPLUGINPATH . $thisfile . '/php/placeholder.class.php');

# class instantiation
  $custommenu = new CustomMenu();

# register plugin
  register_plugin(
    $custommenu->info('id'),
    $custommenu->info('name'),
    $custommenu->info('version'),
    $custommenu->info('author'),
    $custommenu->info('url'),
    $custommenu->info('description'),
    $custommenu->info('page'),
    array($custommenu, 'admin')
  );

# activate actions/filters
  # front-end
    add_action('theme-header', array($custommenu, 'themeHeader'));
  # back-end
    if (isset($_GET['id']) && $_GET['id'] == customMenu::FILE) {
      add_action('header', array($custommenu, 'header'));
    }
    add_action($custommenu->info('page').'-sidebar', 'createSideMenu' , array($custommenu->info('id'), $custommenu->info('sidebar'))); // sidebar link
    add_filter('content', array($custommenu, 'content'));

# functions
  function get_custom_menu($name, $classes = array()) {
    $menu = new CustomMenuDisplay($name, $classes);
    $menu->displayMenu();
  }

?>