<?php

/*
  Handles data queries (getting and saving) for menus
  */
class CustomMenuData {
  // Constants
  const EXT = '.xml';

  // Methods
  # string to slug (by Gilbert Pellegrom)
  static protected function strtoslug($string) {
    return strtolower(trim(preg_replace('/[^A-Za-z0-9-_]+/', '-', self::transliterate($string))));
  }

  # transliteration
  static protected function transliterate($string) {
    global $i18n;
    if (isset($i18n['TRANSLITERATION']) && is_array($translit = $i18n['TRANSLITERATION']) && count($translit > 0)) {
      $string =  str_replace(array_keys($translit), array_values($translit), $string);
    }
    return $string;
  }

  static public function init() {
    $return = array();
    $paths  = array(CustomMenu::FILE);

    // paths
    foreach ($paths as $path) {
      $fullPath = GSDATAOTHERPATH . $path;

      if (!file_exists($fullPath)) {
        $return[$path][] = mkdir($fullPath, 0755);

        // writeable permissions final check
        if (!is_writable ($fullPath)) {
          $return[$path][] = chmod($fullPath, 0755);
        }
      }
    }

    // files
    if (!self::menuExists('default')) {
      self::saveMenu(array(
        'name'   => 'default',
        'level'  => array(0),
        'slug'   => array('index'),
        'title'  => array('Home'),
        'url'    => array('/'),
        'target' => array('_self'),
      ));
    }

    return $return;
  }

  static public function getMenu($menu) {
    $file = self::getMenuFilename($menu);

    if (file_exists($file)) {
      $items = simplexml_load_file($file, 'SimpleXMLElement', LIBXML_NOCDATA); // thanks to http://blog.evandavey.com/2008/04/how-to-fix-simplexml-cdata-problem-in-php.html
      $items = json_decode(json_encode($items), true);

      // move channel node
      if (isset($items['menu'])) {
        $items = $items['menu'];
      }

      // old format
      if (isset($items['item']['title'])) {
        $items = array('item' => array($items['item']));
      }

      // new format
      if (isset($items['channel']['item']['title'])) {
        $items = array('item' => array($items['channel']['item']));
      }

      return $items['item'];
    }
    else return array();
  }

  static public function listMenus() {
    $files = glob(self::getMenuFilename('*'));
    $slugs = array();

    foreach ($files as $file) {
      $slugs[] = basename($file, self::EXT);
    }

    return $slugs;
  }

  static public function getMenus() {
    $slugs = self::listMenus();
    $menus = array();

    foreach ($slugs as $slug) {
      $menus[$slug] = self::getMenu($slug);
    }

    return $menus;
  }

  static public function saveMenu($post) {
    // initialization
    $return = $nodes = $saved = array();

    foreach ($post as $key => $val) {
      if (is_array($val)) $nodes[] = $key;
    }

    // sets up array
    foreach ($post['level'] as $key => $level) {
      foreach ($nodes as $node) {
        $return[$key][$node] = $post[$node][$key];
      }

      // fill empty fields
      if (empty($return[$key]['slug'])) $return[$key]['slug'] = self::strtoslug($return[$key]['title']);

      // final formatting
      $return[$key]['slug'] = self::strtoslug($return[$key]['slug']);
      $return[$key]['url'] =  self::transliterate($return[$key]['url']);

      // add to saved array
      $saved[] = $return[$key]['slug'];
    }

    // build xml file
    $xml = new SimpleXMLExtended('<?xml version="1.0" encoding="UTF-8"?><channel/>');
    $cdata = array('title', 'url');
    // menu items
    $menu = $xml->addChild('menu');
    foreach ($return as $key => $item) {
      $itemxml = $menu->addChild('item');
      foreach ($item as $field => $val) {
        if (in_array($field, $cdata)) {
          $itemxml->{$field} = null;
          $itemxml->{$field}->addCData($val);
        }
        else $itemxml->addChild($field, $val);
      }
    }

    // settings
    $settings = $xml->addChild('settings');

    // format the xml file (beautify)
    $dom = new DOMDocument;
    $dom->preserveWhiteSpace = false;
    $dom->loadXML($xml->saveXML());
    $dom->formatOutput = true;

    // save to file
    $post['name'] = self::strtoslug($post['name']);
    $newfile = self::getMenuFilename($post['name']);

    if (isset($post['oldname'])) {
      $oldfile = self::getMenuFilename($post['oldname']);
      if (file_exists($oldfile) && !file_exists($newfile)) {
        unlink($oldfile);
      }
    }

    return $dom->save($newfile);
  }

  static public function deleteMenu($slug) {
    // Delete a menu
    $file = self::getMenuFilename($slug);

    return file_exists($file) && (bool) unlink($file);
  }

  static public function menuExists($slug) {
    return file_exists(self::getMenuFilename($slug));
  }

  static public function getMenuFilename($slug) {
    return GSDATAOTHERPATH . CustomMenu::FILE . '/' . $slug . self::EXT;
  }
}