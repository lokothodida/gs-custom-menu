<?php
// Set up variables
$menu       = (isset($_GET['menu'])) ? $_GET['menu'] : null;
$buttonType = $menu ? 'saveMenu' : 'createMenu';
?>

<h3><?php CustomMenu::i18n('MENU'); ?></h3>

<section id="custom-menu-admin">

<template name="admin-menu-item">
  <?php CustomMenu::getMenuItemTemplate(); ?>
</template>

<form method="post" action="<?php echo $url; ?>">
  <div>
    <p>
      <input type="hidden" name="oldname" value="<?php echo $menu; ?>">
      <input type="text" class="text" style="width: 150px;" name="name" placeholder="<?php CustomMenu::i18n('NAME'); ?>" required value="<?php if (isset($_GET['menu'])) echo $_GET['menu']; ?>">
      <a href="#" class="cancel add">+ <?php CustomMenu::i18n('ITEM'); ?></a>
    </p>
    <div class="items">
      <?php
        $items = !empty($menu) ? CustomMenuData::getMenu($menu) : array();

        foreach ($items as $item) {
          CustomMenu::getMenuItemTemplate($item);
        }

        if (empty($items)) {
          // Show an empty item by default
          CustomMenu::getMenuItemTemplate();
        }
      ?>
    </div>
    <p>
      <a href="#" class="cancel add">+ <?php CustomMenu::i18n('ITEM'); ?></a>
    </p>
  </div>

  <div style="margin-top: 10px;">
    <input type="submit" class="submit" name="<?php echo $buttonType; ?>" value="<?php i18n('BTN_SAVECHANGES'); ?>">&nbsp;&nbsp;/
    <a href="<?php echo $url; ?>" class="cancel"><?php CustomMenu::i18n('BACK'); ?></a>
  </div>
</form>
</section>

<style>
  .advanced { display: none; }
</style>

<script>
/* global jQuery */
jQuery(function($) {
  // Cache document and items
  var $document = $(document);
  var $page     = $document.find("#custom-menu-admin");
  var $form     = $page.find("form");
  var $items    = $page.find(".items");
  var i18n      = <?php CustomMenu::getI18nHashes(); ?>;

  // Encapsulate item traversal logic in a class
  // This will be assigned to the "data" attribute of the given elem
  class Item {
    constructor(elem) {
      this.$elem = $(elem);
    }

    // Get the next adjacent item
    next() {
      return this.$elem.next();
    }

    // Get the previous adjacent item
    prev() {
      return this.$elem.prev();
    }

    // Get the current level
    getLevel() {
      var level = this.$elem.find(".level").val();
      return parseInt(level, 10);
    }

    // Set the current level
    setLevel(level = 0) {
      this.$elem.find(".level").val(level);
      this.$elem.css("margin-left", level * 20);
    }

    // Get the dropdown
    getSlugDropdown() {
      return this.$elem.find(".slugDropdown");
    }

    // Get slug dropdown
    getSlug() {
      return this.getSlugDropdown().val();
    }

    // Set slug dropdown
    setSlug(value) {
      var $dropdown = this.getSlugDropdown();

      if (value == '') {
        // The .slugText element should have its value sent to the POST slug[] array
        $dropdown.next('.slugText').attr('name', 'slug[]').show();
        $dropdown.removeAttr('name');
      } else {
        // The dropdown's value show be sent to the POST slug[] array
        $dropdown.next('.slugText').removeAttr('name').hide();
        $dropdown.attr('name', 'slug[]').show();
      }
    }

    // Toggle the settings/advanced panel
    toggleSettings() {
      this.$elem.find('.advanced').slideToggle();
    }

    // Get the closest item to the current elemtn
    static closest(elem) {
      return $(elem).closest(".item");
    }
  }

  // Get a template (by name attribute)
  function getTemplate(name) {
    return $($page.find("template[name='" + name + "']").html());
  }

  function initializeItem(elem) {
    var $elem = $(elem);
    $elem.data("item", new Item($elem));
  }

  function addItemCallback(evt) {
    var template = getTemplate("admin-menu-item");
    initializeItem(template);
    $items.append(template);

    evt.preventDefault();
  }

  function deleteItemCallback(evt) {
    var $item = Item.closest(evt.target);
    $item.remove();

    evt.preventDefault();
  }

  function saveMenuConfirmationCallback(evt) {
    var choice = confirm(i18n.ARE_YOU_SURE);

    if (!choice) {
      evt.preventDefault();
    }
  }

  function openItemSettingsCallback(evt) {
    var $item = Item.closest(evt.target);
    var item  = $item.data("item");
    item.toggleSettings();

    evt.preventDefault();
  }

  function increaseItemIndentCallback(evt) {
    // Get the current and previous item's level
    var $item     = Item.closest(evt.target);
    var item      = $item.data("item");
    var $prev     = item.prev();
    var prev      = $prev.data("item");
    var level     = item.getLevel() + 1;
    var prevLevel = prev.getLevel();

    // Don't allow increases more than 1 level beyond prev item
    if ((level - prevLevel) <= 1) {
      item.setLevel(level);
    }

    evt.preventDefault();
  }

  function decreaseItemIndentCallback(evt) {
    // Get th ecurrent item's level
    var $item = Item.closest(evt.target);
    var item  = $item.data("item");
    var level = item.getLevel() - 1;

    // Don't allow decreases below level 0
    if (level >= 0) {
      item.setLevel(level);
    }

    evt.preventDefault();
  }

  function toggleItemSlugDropdown(evt) {
    var $item = Item.closest(evt.target);
    var item  = $item.data("item");
    var value = item.getSlug();

    item.setSlug(value);

    evt.preventDefault();
  }

  // Initialize
  function init() {
    // Make each ".item" element sortable
    $items.sortable();

    // Initialize the existing items first
    $items.find(".item").each((idx, elem) => initializeItem(elem));

    // Inject a new item to the items list
    $form.on("click", ".add", addItemCallback);

    // Remove an item from the items list
    $form.on("click", ".delete", deleteItemCallback);

    // Confirmation before deleting a menu
    $form.on("click", ".submit", saveMenuConfirmationCallback);

    // Open the advanced settings for an item
    $form.on("click", ".open", openItemSettingsCallback);

    // Increase an item's indentation level
    $form.on("click", ".indent", increaseItemIndentCallback);

    // Decrease an item's indentation level
    $form.on("click", ".undent", decreaseItemIndentCallback);

    // Hide the slug dropdown if the value selected is empty
    $form.on("change", ".slugDropdown", toggleItemSlugDropdown);

    // Force all of the empty slug dropdowns to be hidden
    $form.find(".slugDropdown").trigger("change");
  }

  init();
});
</script>