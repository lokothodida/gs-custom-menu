<h3><?php echo i18n_r(self::FILE.'/PLUGIN_NAME'); ?></h3>

<style>
  .warning {
    background: #222;
    padding: 10px;
    position: relative;
    color: #ffffff;
    font-size: 90%;
    width: 80%;
    margin: 0 auto;
  }
  .advanced { display: none; }
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
  }); // ready
</script>

<form method="post" action="<?php echo $url; ?>">
  <p>
    <input type="text" class="text" style="width: 150px;" name="name" placeholder="<?php echo i18n_r(self::FILE.'/NAME'); ?>" required>
    <a href="#" class="cancel add">+ <?php echo i18n_r(self::FILE.'/ITEM'); ?></a>
  </p>
  <div class="items">
    <?php $this->adminItem(array()); ?>
  </div>
  <div style="overflow: hidden;">
    <input type="submit" class="submit" name="createMenu" value="<?php echo i18n_r('BTN_SAVECHANGES'); ?>" onclick="return confirm('<?php echo i18n_r(self::FILE.'/ARE_YOU_SURE'); ?>');">&nbsp;&nbsp;/
    <a href="<?php echo $url; ?>" class="cancel"><?php echo i18n_r(self::FILE.'/BACK'); ?></a>
    <a href="#" class="cancel add" style="float: right;">+ <?php echo i18n_r(self::FILE.'/ITEM'); ?></a>
  </div>
</form>