<?php
/* Custom Menu */

# thisfile
  $thisfile = basename(__FILE__, '.php');
 
# language
  i18n_merge($thisfile) || i18n_merge($thisfile, 'en_US');
 
# requires
  require_once(GSPLUGINPATH.$thisfile.'/php/class.php');
  
# class instantiation
  $custommenu = new customMenu; // instantiate class

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
  # back-end
    add_action($custommenu->info('page').'-sidebar', 'createSideMenu' , array($custommenu->info('id'), $custommenu->info('sidebar'))); // sidebar link
    add_filter('content', array($custommenu, 'content'));

# functions
  function get_custom_menu($name, $min = 0, $max = 0, $template = null) {
    $menu = new CustomMenuDisplay($name);
  }
 
?>