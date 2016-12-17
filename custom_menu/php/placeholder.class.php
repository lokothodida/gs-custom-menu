<?php

class CustomMenuPlaceholder {
  static public function filter($content) {
    $match = preg_match_all('/(<p>\s*)?\(%( )*'. CustomMenu::FILE . '(.*?)( )*%\)(\s*<\/p>)?/', $content, $matches);

    if (isset($matches[3])) {
      foreach ($matches[3] as $key => $params) {
        $params = explode(',', $params);
        $params = str_replace(array('\'', '"'), '', $params);
        $params = array_map('trim', $params);

        // evaluate boolean parameters
        foreach ($params as $k => $par) {
          if (strtolower($par) === 'true')  $params[$k] = true;
          if (strtolower($par) === 'false') $params[$k] = false;
        }
        if (empty($params[0])) $params[0] = 'default';

        ob_start();
          $menu = new CustomMenuDisplay($params[0]);
          $output = '<ul class="'. CustomMenu::FILE . ' ' . $params[0] . '">' . ob_get_contents() . '</ul>';
        ob_end_clean();

        $content = str_replace($matches[0][$key], $output, $content);
      }
    }

    return $content;
  }
}