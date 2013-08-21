<h3><?php echo i18n_r(self::FILE.'/PLUGIN_NAME'); ?></h3>

<style>
  input.create { display: none; }
</style>

<script>
  $(document).ready(function() {
    $('a.create').hide();
    $('input.create').show();
    $('input.create').click(function() {
      window.location.href = $('a.create').attr('href');
      return false;
    }); // click
  }); // ready
</script>

<table class="highlight edittable">
  <thead>
    <tr>
      <th style="width: 60%;"><?php echo i18n_r(self::FILE.'/MENU'); ?></th>
      <th style="width: 39%;"><?php echo i18n_r(self::FILE.'/ITEMS'); ?></th>
      <th style="width: 1%;"></th>
    </tr>
  </thead>
  <tbody>
    <?php
      $menus = $this->getMenus();
      foreach ($menus as $name => $menu) {
    ?>
      <tr>
        <td><a href="<?php echo $url; ?>&menu=<?php echo $name; ?>"><?php echo $name; ?></a></td>
        <td><?php echo count($menu); ?></td>
        <td style="text-align: right;"><a href="<?php echo $url; ?>&delete=<?php echo $name; ?>" class="cancel delete" onclick="return confirm('<?php echo i18n_r(self::FILE.'/ARE_YOU_SURE_DEL'); ?>');">&times;</a></td>
      </tr>
    <?php
      }
      if (empty($menus)) {
    ?>
      <tr>
        <td colspan="100%"><?php echo i18n_r(self::FILE.'/NO_MENUS'); ?></td>
      </tr>
    <?php
      }
    ?>
  </tbody>
</table>

<a href="<?php echo $url; ?>&create" class="create"><?php echo i18n_r(self::FILE.'/CREATE'); ?></a>
<input type="submit" class="submit create" value="<?php echo i18n_r(self::FILE.'/CREATE'); ?>">