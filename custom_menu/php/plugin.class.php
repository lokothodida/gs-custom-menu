<?php

/*
  Main plugin class (and bootstrapper)
  uses CustomMenuPlaceholder
  */
class CustomMenu {
  /* constants */
  const FILE = 'custom_menu';
  const VERSION = '0.6.1';
  const AUTHOR = 'Lawrence Okoth-Odida';
  const URL = 'http://github.com/lokothodida';
  const PAGE = 'pages';

  /* properties */
  private $plugin = array();

  /* methods */
  # constructor
  public function __construct() {
    // may be used in later iterations
  }

  # string to slug (by Gilbert Pellegrom)
  public function strtoslug($string) {
    return strtolower(trim(preg_replace('/[^A-Za-z0-9-_]+/', '-', $this->transliterate($string))));
  }

  # transliteration
  public function transliterate($string) {
    global $i18n;
    if (isset($i18n['TRANSLITERATION']) && is_array($translit = $i18n['TRANSLITERATION']) && count($translit > 0)) {
      $string =  str_replace(array_keys($translit), array_values($translit), $string);
    }
    return $string;
  }

  # make initial files
  private function makeFiles() {
    $return = array();
    $paths = array(self::FILE);

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
    if (!file_exists(GSDATAOTHERPATH.self::FILE.'/default.xml')) {
      $menu = array(
        'name' => 'default',
        'level' => array(0),
        'slug' => array('index'),
        'title' => array('Home'),
        'url' => array('/'),
        'target' => array('_self'),
      );
      $this->saveMenu($menu);
    }

    return $return;
  }

  # info
  public function info($info) {
    if (empty($this->plugin)) {
      $this->plugin['id'] = self::FILE;
      $this->plugin['name'] = i18n_r(self::FILE.'/PLUGIN_NAME');
      $this->plugin['version'] = self::VERSION;
      $this->plugin['author'] = self::AUTHOR;
      $this->plugin['url'] = self::URL;
      $this->plugin['description'] = i18n_r(self::FILE.'/PLUGIN_DESC');
      $this->plugin['page'] = self::PAGE;
      $this->plugin['sidebar'] = i18n_r(self::FILE.'/PLUGIN_SIDEBAR');
    }

    if (isset($this->plugin[$info])) return $this->plugin[$info];
    else return false;
  }

  private function adminItem($item, $mode = true) {
    if (!isset($item['title'])) $item['title'] = '';
    if (!isset($item['url'])) $item['url'] = '';
    if (!isset($item['slug'])) $item['slug'] = '';
    if (!isset($item['level'])) $item['level'] = 0;
    if (!isset($item['target'])) $item['target'] = '_self';
    if (!isset($item['img'])) $item['img'] = null;


    // prevents array to string conversion problem
    foreach ($item as $node => $val) {
      if (is_array($val)) $item[$node] = '';
    }

    // load pages array
    $pages = glob(GSDATAPAGESPATH.'*.xml');
    $slugs = array();
    foreach ($pages as $page) {
      $slugs[] = trim(str_replace(array(GSDATAPAGESPATH, '.xml'), '', $page));
    }

    ob_start();
    ?>
    <div id="metadata_window" class="item" style="margin-left: <?php echo $item['level'] * 20; ?>px;">
      <p>
        <input type="hidden" class="level" name="level[]" value="<?php echo $item['level']; ?>">
        <label style="overflow: hidden; margin-bottom: 4px;">
          <span><?php echo i18n_r(self::FILE.'/TITLE'); ?></span>

          <span style="float: right; margin-right: 10px;">[
          <a href="" class="cancel open" style="text-decoration: none;">&#x25BC;</a>
          <a href="" class="cancel undent" style="text-decoration: none;">&larr;</a>
          <a href="" class="cancel indent" style="text-decoration: none;">&rarr;</a>
          <a href="" class="cancel delete" style="text-decoration: none;">&times;</a>
          ]
          </span>
        </label>
        <input type="text" class="text" name="title[]" value="<?php echo $item['title']; ?>" required>
      </p>

      <div class="advanced">
        <div class="leftopt">
          <p>
            <label><?php echo i18n_r(self::FILE.'/URL'); ?></label>
            <input type="text" class="text" name="url[]" value="<?php echo $item['url']; ?>">
          </p>
          <p>
            <label><?php echo i18n_r(self::FILE.'/SLUG'); ?></label>
            <select class="text slugDropdown" name="slug[]">
              <option value="">----</option>
              <?php foreach ($slugs as $slug) { ?>
                <option value="<?php echo $slug; ?>" <?php if ($slug && $slug == $item['slug']) echo 'selected="selected"'; ?>><?php echo $slug; ?></option>
              <?php } ?>
            </select>
            <input type="text" class="text slugText" style="margin-top: 5px !important;" value="<?php echo $item['slug']; ?>">
          </p>
        </div>
        <div class="rightopt">
          <p>
            <label><?php echo i18n_r(self::FILE.'/TARGET'); ?></label>
            <select name="target[]" class="text">
              <option value="" <?php if ($item['target'] == '') echo 'selected="selected"'; ?>>---</option>
              <option <?php if ($item['target'] == '_self') echo 'selected="selected"'; ?>>_self</option>
              <option <?php if ($item['target'] == '_blank') echo 'selected="selected"'; ?>>_blank</option>
              <option <?php if ($item['target'] == '_parent') echo 'selected="selected"'; ?>>_parent</option>
              <option <?php if ($item['target'] == '_top') echo 'selected="selected"'; ?>>_top</option>
            </select>
          </p>
          <p>
            <label><?php echo i18n_r(self::FILE.'/IMAGE'); ?></label>
            <input type="text" class="text" name="img[]" value="<?php echo $item['img']; ?>">
          </p>
          <div class="nodes">
          </div>
        </div>
        <div class="clear"></div>
      </div>
    </div>
    <?php
    $content = ob_get_contents();
    ob_end_clean();

    if ($mode == true) {
      echo $content;
      return null;
    }
    else return $content;
  }

  # parses menu structure from POST values
  private function saveMenu($post) {
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
      if (empty($return[$key]['slug'])) $return[$key]['slug'] = $this->strtoslug($return[$key]['title']);

      // final formatting
      $return[$key]['slug'] = $this->strtoslug($return[$key]['slug']);
      $return[$key]['url'] =  $this->transliterate($return[$key]['url']);

      // checks to see slug doesn't already exist
      /*
      if (in_array($return[$key]['slug'], $saved)) {
        $return[$key]['slug'] = $return[$key]['slug'].'-'.rand(0, 100);
      }
      */

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
    $post['name'] = $this->strtoslug($post['name']);
    $newfile = GSDATAOTHERPATH.self::FILE.'/'.$post['name'].'.xml';

    if (isset($post['oldname'])) {
      $oldfile = GSDATAOTHERPATH.self::FILE.'/'.$post['oldname'].'.xml';
      if (file_exists($oldfile) && !file_exists($newfile)) {
        unlink($oldfile);
      }
    }

    return $dom->save($newfile);
  }

  # quickly parses array to an XML structure
  private function array2XMLrecurse($array, $xml) {
    foreach ($array as $key => $value) {
      $val = is_array($value) ? $this->array2XMLrecurse($value, $xml) : $value;
      $node = $xml->addChild($key, $val);
    }
    return $xml;
  }

  # array to xml
  private function array2XML($array, $root='<channel/>') {
    $xml = new SimpleXMLElement($root);
    return $this->array2XMLrecurse($array, $xml);
  }

  # load items from menu (as array)
  public function getItems($menu) {
    $file = GSDATAOTHERPATH.self::FILE.'/'.$menu.'.xml';
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

  # get menus
  public function getMenus() {
    $return = array();
    $menus = glob(GSDATAOTHERPATH.self::FILE.'/*.xml');

    // force $menus to be an array
    if ($menus === false) $menus = array();

    foreach ($menus as $menu) {
      $tmpname = explode('/', $menu);
      $tmpname = trim(str_replace('.xml', '', end($tmpname)));
      $tmpfile = $this->getItems($tmpname);
      $return[$tmpname] = $tmpfile;
    }

    return $return;
  }

  # placeholder evaluator
  public function content($content) {
    return CustomMenuPlaceholder::filter($content);
  }

  # header (for codemirror)
  public function header() {
    global $SITEURL;
    echo '<link href="'.$SITEURL.'admin/template/js/codemirror/lib/codemirror.css?v=screen" rel="stylesheet" media=""><link href="'.$SITEURL.'admin/template/js/codemirror/theme/default.css?v=screen" rel="stylesheet" media="">';
    echo '<script src="'.$SITEURL.'admin/template/js/fancybox/jquery.fancybox.pack.js?v=2.0.4"></script><script src="'.$SITEURL.'admin/template/js/codemirror/lib/codemirror-compressed.js?v=0.2.0"></script>';
  }

  # theme header
  public function themeHeader() {
    global $SITEURL;
    echo '<base href="'.$SITEURL.'">';
  }

  # admin
  public function admin() {
    global $SITEURL;
    $this->makeFiles();
    $msg = false;
    $url = 'load.php?id='.self::FILE;
    $path = GSPLUGINPATH.self::FILE.'/php/';

    if (!empty($_POST['createMenu'])) {
      $create = $this->saveMenu($_POST);
      if ($create) $msg = array('status' => 'updated', 'msg' => i18n_r(self::FILE.'/MENU_CREATE_SUCCESS'));
      else         $msg = array('status' => 'error', 'msg' => i18n_r(self::FILE.'/MENU_CREATE_ERROR'));
    }
    elseif (!empty($_POST['saveMenu'])) {
      $save = $this->saveMenu($_POST);
      if ($save) $msg = array('status' => 'updated', 'msg' => str_replace('%s', '<b>'.$_POST['name'].'</b>', i18n_r(self::FILE.'/MENU_UPDATE_SUCCESS')));
      else       $msg = array('status' => 'error', 'msg' => i18n_r(self::FILE.'/MENU_UPDATE_ERROR'));
    }
    elseif (!empty($_GET['delete'])) {
      $file = GSDATAOTHERPATH.self::FILE.'/'.$_GET['delete'].'.xml';
      if (file_exists($file)) {
        $delete = unlink($file);
        if ($delete) $msg = array('status' => 'updated', 'msg' => str_replace('%s', '<b>'.$_GET['delete'].'</b>', i18n_r(self::FILE.'/MENU_DEL_SUCCESS')));
        else         $msg = array('status' => 'error', 'msg' => i18n_r(self::FILE.'/MENU_DEL_ERROR'));
      }
    }

    // error message
    if ($msg) {
      ?>
      <script>
        $(document).ready(function() {
          $('div.bodycontent').before('<div class="' + <?php echo json_encode($msg['status']); ?> + '" style="display:block;">'+<?php echo json_encode($msg['msg']); ?>+'</div>');
        }); // ready
      </script>
      <?php
    }

    // create new menu
    if (isset($_GET['create'])) {
      include($path.'menu.php');
    }
    // edit a menu
    elseif (isset($_GET['menu'])) {
      include($path.'menu.php');
    }
    // menus
    else {
      include($path.'menus.php');
    }
  }
}
