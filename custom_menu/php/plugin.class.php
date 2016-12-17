<?php

/*
  Main plugin class (and bootstrapper)
  uses CustomMenuPlaceholder
  */
class CustomMenu {
  /* constants */
  const FILE    = 'custom_menu';
  const VERSION = '0.6.1';
  const AUTHOR  = 'Lawrence Okoth-Odida';
  const URL     = 'http://github.com/lokothodida';
  const PAGE    = 'pages';

  /* methods */
  static public function i18n_r($hash) {
    return i18n_r(self::FILE . '/' . $hash);
  }

  static public function i18n($hash) {
    echo self::i18n_r($hash);
  }

  # string to slug (by Gilbert Pellegrom)
  static public function strtoslug($string) {
    return strtolower(trim(preg_replace('/[^A-Za-z0-9-_]+/', '-', self::transliterate($string))));
  }

  # transliteration
  static public function transliterate($string) {
    global $i18n;
    if (isset($i18n['TRANSLITERATION']) && is_array($translit = $i18n['TRANSLITERATION']) && count($translit > 0)) {
      $string =  str_replace(array_keys($translit), array_values($translit), $string);
    }
    return $string;
  }

  # load items from menu (as array)
  public function getItems($menu) {
    return CustomMenuData::getMenu($menu);
  }

  # get menus
  public function getMenus() {
    return CustomMenuData::getMenus();
  }

  # placeholder evaluator
  public function content($content) {
    return CustomMenuPlaceholder::filter($content);
  }

  # header (for codemirror)
  static public function header() {
    global $SITEURL;
    echo '<link href="'.$SITEURL.'admin/template/js/codemirror/lib/codemirror.css?v=screen" rel="stylesheet" media=""><link href="'.$SITEURL.'admin/template/js/codemirror/theme/default.css?v=screen" rel="stylesheet" media="">';
    echo '<script src="'.$SITEURL.'admin/template/js/fancybox/jquery.fancybox.pack.js?v=2.0.4"></script><script src="'.$SITEURL.'admin/template/js/codemirror/lib/codemirror-compressed.js?v=0.2.0"></script>';
  }

  # theme header
  static public function themeHeader() {
    global $SITEURL;
    echo '<base href="'.$SITEURL.'">';
  }

  # admin
  static public function admin() {
    global $SITEURL;
    $init = CustomMenuData::init();
    $url  = 'load.php?id=' . self::FILE;
    $path = GSPLUGINPATH . self::FILE . '/php/';

    // POST query handling
    $msg = self::processPostQuery();

    // Some JavaScript for displaying the error message
    if ($msg) {
      ?>
      <script>
        jQuery(function($) {
          var msg = <?php echo json_encode($msg); ?>;
          $('div.bodycontent').before('<div class="' + msg.status + '" style="display:block;">' + msg.msg + '</div>');
        });
      </script>
      <?php
    }

    // Display the correct page
    if (isset($_GET['create'])) {
      // Create a menu
      include($path . 'menu.php');
    } elseif (isset($_GET['menu'])) {
      // Edit a menu
      include($path . 'menu.php');
    } else {
      // Show menus
      include($path . 'menus.php');
    }
  }

  static protected function processPostQuery() {
    $msg = null;

    if (!empty($_POST['createMenu'])) {
      // Create a menu
      $create = CustomMenuData::saveMenu($_POST);
      if ($create) {
        $msg = array('status' => 'updated', 'msg' => self::i18n_r('MENU_CREATE_SUCCESS'));
      } else {
        $msg = array('status' => 'error', 'msg' => self::i18n_r('MENU_CREATE_ERROR'));
      }
    } elseif (!empty($_POST['saveMenu'])) {
      // Update a menu
      $save = CustomMenuData::saveMenu($_POST);
      if ($save) {
        $msg = array('status' => 'updated', 'msg' => str_replace('%s', '<b>'.$_POST['name'].'</b>', self::i18n_r('MENU_UPDATE_SUCCESS')));
      } else {
        $msg = array('status' => 'error', 'msg' => self::i18n_r('MENU_UPDATE_ERROR'));
      }
    } elseif (!empty($_GET['delete'])) {
      // Delete a menu
      $file = GSDATAOTHERPATH.self::FILE.'/'.$_GET['delete'].'.xml';

      if (file_exists($file)) {
        $delete = unlink($file);
        if ($delete) {
          $msg = array('status' => 'updated', 'msg' => str_replace('%s', '<b>'.$_GET['delete'].'</b>', self::i18n_r('MENU_DEL_SUCCESS')));
        } else {
          $msg = array('status' => 'error', 'msg' => self::i18n_r('MENU_DEL_ERROR'));
        }
      }
    }

    return $msg;
  }

  static public function getMenuItemTemplate($item, $mode = true) {
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
      $slugs[] = basename($page, '.xml');
    }

    ob_start();
    ?>
    <div id="metadata_window" class="item" style="margin-left: <?php echo $item['level'] * 20; ?>px;">
      <p>
        <input type="hidden" class="level" name="level[]" value="<?php echo $item['level']; ?>">
        <label style="overflow: hidden; margin-bottom: 4px;">
          <span><?php CustomMenu::i18n('TITLE'); ?></span>

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
            <label><?php CustomMenu::i18n('URL'); ?></label>
            <input type="text" class="text" name="url[]" value="<?php echo $item['url']; ?>">
          </p>
          <p>
            <label><?php CustomMenu::i18n('SLUG'); ?></label>
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
            <label><?php CustomMenu::i18n('TARGET'); ?></label>
            <select name="target[]" class="text">
              <option value="" <?php if ($item['target'] == '') echo 'selected="selected"'; ?>>---</option>
              <option <?php if ($item['target'] == '_self') echo 'selected="selected"'; ?>>_self</option>
              <option <?php if ($item['target'] == '_blank') echo 'selected="selected"'; ?>>_blank</option>
              <option <?php if ($item['target'] == '_parent') echo 'selected="selected"'; ?>>_parent</option>
              <option <?php if ($item['target'] == '_top') echo 'selected="selected"'; ?>>_top</option>
            </select>
          </p>
          <p>
            <label><?php CustomMenu::i18n('IMAGE'); ?></label>
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
    } else {
      return $content;
    }
  }
}
