<h3><?php echo i18n_r(self::FILE.'/MENU'); ?></h3>

<template name="admin-menu-item">
  <?php $this->adminItem(array()); ?>
</template>

<style>
  .advanced { display: none; }
</style>

<script>
  /* global jQuery */
  jQuery(function($) {
    // Get a template (by name attribute)
    function getTemplate(name) {
      return $($("template[name='" + name + "']").html());
    }

    // Cache document and items
    var $document    = $(document);
    var $items       = $("form .items");
    var itemTemplate = getTemplate("admin-menu-item");

    // Make each ".item" element sortable
    $items.sortable();

    // Inject a new item to the items list
    $('.add').click(function(evt) {
      var template = getTemplate("admin-menu-item");
      $items.append(template);

      evt.preventDefault();
    });

    // Remove an item from the items list
    $document.on("click", ".delete", function(evt) {
      var $elem = $(evt.target);
      var $item = $elem.closest(".item");
      $item.remove();

      evt.preventDefault();
    });

    // Open the advanced settings for an item
    $document.on('click', '.open', function(e) {
      $(this).closest('div').find('.advanced').slideToggle();
      return false;
    });

    // Increase an item's indentation level
    $document.on('click', '.indent', function(e) {
      var selector = $(this).closest('div').find('.level');
      var val = parseInt(selector.val()) + 1;
      var prevVal = parseInt($(this).closest('div').prev().find('.level').val());
      if ((val - prevVal) <= 1) {
        selector.val(val);
        $(this).closest('div').css('margin-left', val * 20);
      }
      return false;
    });

    // Decrease an item's indentation level
    $document.on('click', '.undent', function(e) {
      var selector = $(this).closest('div').find('.level');
      var val = parseInt(selector.val()) - 1;
      if (val >= 0) {
        selector.val(val);
        $(this).closest('div').css('margin-left', val * 20);
      }
      return false;
    });

    // Hide the slug dropdown if the value selected is empty
    $document.on('change', '.slugDropdown', function(e) {
      var value = $(this).val();
      var $this = $(this);

      if (value == '') {
        // The .slugText element should have its value sent to the POST slug[] array
        $(this).next('.slugText').attr('name', 'slug[]').show();
        $this.removeAttr('name');
      } else {
        // The dropdown's value show be sent to the POST slug[] array
        $(this).next('.slugText').removeAttr('name').hide();
        $this.attr('name', 'slug[]').show();
      }

      return false;
    });

    // Force all of the empty slug dropdowns to be hidden
    $('.slugDropdown').trigger('change');
  }); // ready
</script>

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