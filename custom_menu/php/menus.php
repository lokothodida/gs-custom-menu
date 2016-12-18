<h3><?php CustomMenu::i18n('PLUGIN_NAME'); ?></h3>

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
        <td style="text-align: right;"><a href="<?php echo $url; ?>&delete=<?php echo $name; ?>" class="cancel delete" onclick="return confirm('<?php CustomMenu::i18n('ARE_YOU_SURE_DEL'); ?>');">&times;</a></td>
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

<style>
  input.create { display: none; }
</style>

<script>
/* global jQuery */
jQuery(function($) {
  // Force the input button to be an anchor to the creation page
  $('a.create').hide();
  $('input.create').show();
  $('input.create').click(function(evt) {
    window.location.href = $('a.create').attr('href');

    evt.preventDefault();
  });
});
</script>