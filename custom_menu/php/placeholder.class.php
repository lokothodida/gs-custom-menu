<?php

class CustomMenuPlaceholder {
  static public function filter($content) {
    $regex = '/\(% ' . CustomMenu::FILE . '(.*?)%\)/i';

    return preg_replace_callback($regex, 'self::callback', $content);
  }

  static protected function callback($matches) {
    // Get the parameters
    $params = self::parseParameters($matches[1]);

    // Get the menu and wrap it in an unordered list
    $menu = new CustomMenuDisplay($params[0]);
    $output = '<ul class="'. CustomMenu::FILE . ' ' . $params[0] . '">' . $menu->getMenu(). '</ul>';

    return $output;
  }

  static protected function parseParameters($string) {
    $params = explode(',', $string);
    $params = str_replace(array('\'', '"'), '', $params);
    $params = array_map('trim', $params);

    // evaluate boolean parameters
    foreach ($params as $k => $par) {
      if (strtolower($par) === 'true')  $params[$k] = true;
      if (strtolower($par) === 'false') $params[$k] = false;
    }

    // Set a default
    if (empty($params[0])) {
      $params[0] = 'default';
    }

    return $params;
  }
}