<h3><?php CustomMenu::i18n('PLUGIN_NAME'); ?></h3>

<section id="custom-menu-admin">

<table class="highlight edittable">
  <thead>
    <tr>
      <th style="width: 60%;"><?php CustomMenu::i18n('MENU'); ?></th>
      <th style="width: 39%;"><?php CustomMenu::i18n('ITEMS'); ?></th>
      <th style="width: 1%;"></th>
    </tr>
  </thead>
  <tbody>
    <?php
      $menus = CustomMenuData::getMenus();
      foreach ($menus as $name => $menu) {
    ?>
      <tr>
        <td><a href="<?php echo $url; ?>&menu=<?php echo $name; ?>"><?php echo $name; ?></a></td>
        <td><?php echo count($menu); ?></td>
        <td style="text-align: right;"><a href="<?php echo $url; ?>&delete=<?php echo $name; ?>" class="cancel delete">&times;</a></td>
      </tr>
    <?php
      }
      if (empty($menus)) {
    ?>
      <tr>
        <td colspan="100%"><?php CustomMenu::i18n('NO_MENUS'); ?></td>
      </tr>
    <?php
      }
    ?>
  </tbody>
</table>

<a href="<?php echo $url; ?>&create" class="create"><?php CustomMenu::i18n('CREATE'); ?></a>
<input type="submit" class="submit create" value="<?php CustomMenu::i18n('CREATE'); ?>">

</section>

<style>
  input.create { display: none; }
</style>

<script>
/* global jQuery */
jQuery(function($) {
  var $document = $(document);
  var $page     = $document.find("#custom-menu-admin");
  var i18n      = <?php CustomMenu::getI18nHashes(); ?>;

  function deleteMenuCallback(evt) {
    var choice = confirm(i18n.ARE_YOU_SURE_DEL);

    if (!choice) {
      evt.preventDefault();
    }
  }

  function createMenuCallback(evt) {
    window.location.href = $('a.create').attr('href');

    evt.preventDefault();
  }

  function init() {
    // Force the input button to be an anchor to the creation page
    $page.find('a.create').hide();
    $page.find('input.create').show();

    $page.find('input.create').click(createMenuCallback);
    $page.find('.delete').click(deleteMenuCallback);
  }

  init();
});
</script>