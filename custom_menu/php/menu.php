<h3><?php echo i18n_r(self::FILE.'/MENU'); ?></h3>

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

    // Encapsulate item traversal logic in a class
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
        return parseInt(level);
      }

      // Set the current level
      setLevel(level = 0) {
        this.$elem.find(".level").val(level);
        this.$elem.css("margin-left", level * 20);
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

    function openItemSettingsCallback(evt) {
      var $item = Item.closest(evt.target);
      $item.find('.advanced').slideToggle();

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
      var $dropdown = $(evt.target);
      var value = $dropdown.val();

      if (value == '') {
        // The .slugText element should have its value sent to the POST slug[] array
        $dropdown.next('.slugText').attr('name', 'slug[]').show();
        $dropdown.removeAttr('name');
      } else {
        // The dropdown's value show be sent to the POST slug[] array
        $dropdown.next('.slugText').removeAttr('name').hide();
        $dropdown.attr('name', 'slug[]').show();
      }

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
  }); // ready
</script>

<section id="custom-menu-admin">

<template name="admin-menu-item">
  <?php $this->adminItem(array()); ?>
</template>

<form method="post" action="<?php echo $url; ?>">
  <div>
    <p>
      <input type="hidden" name="oldname" value="<?php if (isset($_GET['menu'])) echo $_GET['menu']; ?>">
      <input type="text" class="text" style="width: 150px;" name="name" placeholder="<?php echo i18n_r(self::FILE.'/NAME'); ?>" required value="<?php if (isset($_GET['menu'])) echo $_GET['menu']; ?>">
      <a href="#" class="cancel add">+ <?php echo i18n_r(self::FILE.'/ITEM'); ?></a>
    </p>
    <div class="items">
      <?php
        $items = isset($_GET['menu']) ? $this->getItems($_GET['menu']) : array();
        foreach ($items as $item) {
          $this->adminItem($item);
        }
        if (empty($items)) {
          $this->adminItem(array());
        }
      ?>
    </div>
    <p>
      <a href="#" class="cancel add">+ <?php echo i18n_r(self::FILE.'/ITEM'); ?></a>
    </p>
  </div>

  <div style="margin-top: 10px;">
    <input type="submit" class="submit" <?php echo (isset($_GET['menu'])) ? 'name="saveMenu"' : 'name="createMenu"'; ?> value="<?php echo i18n_r('BTN_SAVECHANGES'); ?>" onclick="return confirm('<?php echo i18n_r(self::FILE.'/ARE_YOU_SURE'); ?>');">&nbsp;&nbsp;/
    <a href="<?php echo $url; ?>" class="cancel"><?php echo i18n_r(self::FILE.'/BACK'); ?></a>
  </div>
</form>
</section>