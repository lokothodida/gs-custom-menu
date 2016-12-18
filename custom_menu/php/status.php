<script>
  jQuery(function($) {
    var msg = <?php echo json_encode($msg); ?>;
    $('div.bodycontent').before('<div class="' + msg.status + '" style="display:block;">' + msg.msg + '</div>');
  });
</script>