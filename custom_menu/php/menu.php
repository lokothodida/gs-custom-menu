<h3><?php echo i18n_r(self::FILE.'/MENU'); ?></h3>

<style>
  .advanced { display: none; }
  #tabs .edit-nav a:hover { color: <?php global $secondary_1; echo $secondary_1; ?>; }
  #tabs ul, #about li { margin: 0; padding: 0; list-style-type: none; }
  #tabs > div { margin: -20px 0 0 0; }
  #tabs .clear { height: 15px; }
  .CodeMirror, .CodeMirror-scroll { height: 200px; }
</style>

<script>
  $(document).ready(function() {
    // sortable
    $('form .items').sortable();
   
    // add item
    $('.add').click(function() {
      $('form .items').append(<?php echo json_encode($this->adminItem(array(), false)); ?>);
      return false;
    }); // click
    
    // delete item
    $(document).on('click', '.delete', function(e){
      $(this).closest('div').remove();
      return false;
    });
    
    // open advanced
    $(document).on('click', '.open',function(e){
      $(this).closest('div').find('.advanced').slideToggle();
      return false;
    });
    
    // add level
    $(document).on('click', '.indent',function(e){
      var selector = $(this).closest('div').find('.level');
      var val = parseInt(selector.val()) + 1;
      var prevVal = parseInt($(this).closest('div').prev().find('.level').val());
      if ((val - prevVal) <= 1) {
        selector.val(val);
        $(this).closest('div').css('margin-left', val * 20);
      }
      return false;
    });
    
    // decrease level
    $(document).on('click', '.undent',function(e){
      var selector = $(this).closest('div').find('.level');
      var val = parseInt(selector.val()) - 1;
      if (val >= 0) {
        selector.val(val);
        $(this).closest('div').css('margin-left', val * 20);
      }
      return false;
    });
    
    // show/hide slug dropdown
    $(document).on('change', '.slugDropdown',function(e){
      var value = $(this).val();
      var $this = $(this);
      if (value == '') {
        $(this).next('.slugText').attr('name', 'slug[]').show();
        $this.removeAttr('name');
      }
      else {
        $(this).next('.slugText').removeAttr('name').hide();
        $this.attr('name', 'slug[]').show();
      }
      return false;
    });
    $('.slugDropdown').trigger('change');
    
    // tabs
    $('#tabs').easytabs();
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