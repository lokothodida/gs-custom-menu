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
  // Return i18n hash
  static public function i18n_r($hash) {
    return i18n_r(self::FILE . '/' . $hash);
  }

  // Print i18n hash
  static public function i18n($hash) {
    echo self::i18n_r($hash);
  }

  // Return all custom i18n hashes
  static public function returnI18nHashes() {
    include(GSPLUGINPATH . self::FILE . '/lang/en_US.php');

    $hashes = array();

    foreach ($i18n as $hash => $string) {
      $hashes[$hash] = self::i18n_r($hash);
    }

    return $hashes;
  }

  // Print all custom i18n hashes (used for client-side i18n)
  static public function getI18nHashes() {
    echo json_encode(self::returnI18nHashes());
  }

  // string to slug (by Gilbert Pellegrom)
  static public function strtoslug($string) {
    return strtolower(trim(preg_replace('/[^A-Za-z0-9-_]+/', '-', self::transliterate($string))));
  }

  // string transliteration
  static public function transliterate($string) {
    global $i18n;
    if (isset($i18n['TRANSLITERATION']) && is_array($translit = $i18n['TRANSLITERATION']) && count($translit > 0)) {
      $string =  str_replace(array_keys($translit), array_values($translit), $string);
    }
    return $string;
  }

  // admin panel
  static public function admin() {
    global $SITEURL;
    $init = CustomMenuData::init();
    $url  = 'load.php?id=' . self::FILE;
    $path = GSPLUGINPATH . self::FILE . '/php/';

    // POST query handling
    $msg = self::processPostQuery();

    // Some JavaScript for displaying the error message
    if ($msg) {
      include($path . 'status.php');
    }

    // Display the correct page
    if (isset($_GET['create'])) {
      // Create a menu
      include($path . 'menu.php');
    } elseif (isset($_GET['menu'])) {
      // Edit a menu
      include($path . 'menu.php');
    } else {
      // Show all menus
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
      $delete = CustomMenuData::deleteMenu($_GET['delete']);

      if ($delete) {
        $msg = array('status' => 'updated', 'msg' => str_replace('%s', '<b>' . $_GET['delete'] . '</b>', self::i18n_r('MENU_DEL_SUCCESS')));
      } else {
        $msg = array('status' => 'error', 'msg' => self::i18n_r('MENU_DEL_ERROR'));
      }
    }

    return $msg;
  }

  static public function getMenuItemTemplate($item = array(), $mode = true) {
    // Default data for item
    $item = array_merge(array(
      'title'  => '',
      'url'    => '',
      'slug'   => '',
      'level'  => 0,
      'target' => '_self',
      'img'    => null,
    ), $item);

    // prevents array to string conversion problem
    foreach ($item as $node => $val) {
      if (is_array($val)) $item[$node] = '';
    }

    // load pages array
    $pages = glob(GSDATAPAGESPATH . '*.xml');
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
