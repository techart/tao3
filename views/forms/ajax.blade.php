@script('/tao/scripts/jquery.form.js')
@script('/tao/scripts/form.js')
<script>
  $(function() {
    new taoAjaxForm('{{ $form->htmlId($__data) }}', {!! $ajax_options !!});
  });
</script>