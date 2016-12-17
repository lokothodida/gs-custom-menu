<?php

/*
  Prints out HTML for a custom menu
  uses CustomMenu
  */
class CustomMenuDisplay {
  # huge credit to http://www.jongales.com/blog/2009/01/27/php-class-for-threaded-comments/, which this script is based on
  private $menu;
  private $parents;
  private $children;
  private $url = array();
  private $classes = array();

  public function __construct($menu, $classes = array()) {
    $classes = array_merge($classes, array(
      'currentpath' => 'currentpath',
      'current'     => 'current',
      'parent'      => 'parent',
      'child'       => 'child'
    ));

    $this->classes = $classes;
    $this->menu = CustomMenuData::getMenu($menu);
    $this->parse();
  }

  # get the full current url (http://www.phpro.org/examples/Get-Full-URL.html)
  public function currentURL($path = true) {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https' : 'http';
    return trim($protocol . '://' . $_SERVER['HTTP_HOST'] . ($path ? $_SERVER['REQUEST_URI'] : ''));
  }

  # parse the url
  public function url() {
    $url = $this->currentURL();
    $url = explode('/', $url);
    $url = array_map('trim', $url);

    return $url;
  }

  # parses structure into nested list
  private function parse() {
    global $id, $SITEURL;
    $return = array();
    $parent = null;
    $level = 0;
    $prev = null;
    $current = array();
    $currentItem = null;
    $currentURL = rtrim($this->currentURL(), '/').'/';

    foreach ($this->menu as $key => $item) {
      $current[$item['level']] = $item['slug'];

      // set parent id
      if (isset($current[$item['level'] - 1])) {
        $item['parent'] = $current[$item['level'] - 1];
      }
      else {
        $item['parent'] = null;
      }

      // check if site url is already part of url
      $fullurl = null;
      if (is_string($item['url'])) {
        if (strpos($item['url'], $SITEURL === 0)) {
          $fullurl = $item['url'];
        }
        else $fullurl = $SITEURL.$item['url'];
      }

      $fullurl = rtrim($fullurl, '/').'/';

      // now check if this item is currently active
      if ((strpos($currentURL, $fullurl) === 0) && trim($item['slug']) == $id) {
        $currentItem = $item;
      }

      $this->menu[$key] = $item;
    }

    // pull apart array into children/parents arrays
    foreach ($this->menu as $menu)  {
      if (empty($menu['parent']))  {
        $this->parents[$menu['slug']][] = $menu;
      }
      else {
        $this->children[$menu['parent']][] = $menu;
      }
    }

    // current item
    if ($currentItem) {
      $this->addClasses($currentItem['parent']);
    }
  }

  // add currentpath classes
  private function addClasses($slug) {
    // load correct array
    if (!empty($this->parents[$slug])) {
      $title = 'parents';
      $array = $this->parents;
    }
    elseif (!empty($this->children[$slug])) {
      $title = 'children';
      $array = $this->children;
    }
    else {
      $title = null;
      $array = array();
      return false;
    }

    // add the class(es)
    foreach ($array as $name => $child) {
      foreach ($child as $key => $item) {
        if ($item['slug'] == $slug) {
          $this->{$title}[$name][$key]['classes'][] = $this->classes['currentpath'];
          $this->addClasses($item['parent']);
          break;
        }
      }
    }
  }

  private function formatItem($item, $depth) {
    global $SITEURL;
    if ($item['url'] == 'index') $item['url'] = '';
    $item = json_decode(json_encode($item), false);
    $url = '';

    if (!empty($item->target)) {
      // If a valid target is provided:
      if (!empty($item->url)) {
        // Set the URL to the full one given (if it exists)
        $url = $item->url;
      } elseif (!empty($item->slug)) {
        // Or get canonical URL from the page slug
        if (function_exists('generate_url')) {
          // Use the better GS 3.4 generate_url() method (if it exists)
          $url = generate_url($item->slug);
        } else {
          $url = find_url($item->slug, null);
        }
      }
    }

    ?>
    <?php if ($url) { ?><a href="<?php echo $url; ?>" title="<?php echo $item->title; ?>" target="<?php echo $item->target; ?>"><?php } ?>
      <?php if ($item->img) { ?>
      <img alt="<?php echo $item->title; ?>" src="<?php echo (strpos($item->img, 'http') === false ? $SITEURL.'data/uploads/' : '').$item->img; ?>">
      <?php } else { ?>
      <?php echo $item->title; ?>
      <?php } ?>
    <?php if ($item->url) { ?></a><?php } ?>
    <?php
  }

  # recursively output each item
  private function displayItem($items, $depth = 0) {
    global $id;
    foreach ($items as $item)   {
      if (empty($item['classes'])) $item['classes'] = array();
      $classes = $item['classes'];
      $classes[] = is_string($item['slug']) ? $item['slug'] : '';
      if ($id == $item['slug']) $classes[] = $this->classes['current'];
      if (isset($this->children[$item['slug']])) {
        $classes[] = $this->classes['parent'];
      }
      else {
        $classes[] = $this->classes['child'];
      }

      $classes = implode(' ', $classes);

      echo '<li class="'.$classes.'">';
      $this->formatItem($item, $depth);

      if ($item['slug'] && isset($this->children[$item['slug']]))  {
        echo '<ul>';
        $this->displayItem($this->children[$item['slug']], $depth + 1);
        echo '</ul>';
      }
      echo '</li>';
    }
  }

  # final output
  public function displayMenu() {
    echo $this->getMenu();
  }

  # get output
  public function getMenu() {
    // Buffer all of the output
    ob_start();

    $this->url = $this->url();
    if (is_array($this->parents)) {
      foreach ($this->parents as $parent) {
        $this->displayItem($parent);
      }
    }

    $content = ob_get_contents();
    ob_end_clean();

    return $content;
  }
}