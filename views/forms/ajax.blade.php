@script('/tao/scripts/jquery.form.js', ['weight' => 100])
@script('/tao/scripts/form.js', ['weight' => 100])
<script>
  $(function() {
    new taoAjaxForm('{{ $form->htmlId($__data) }}', {!! $ajax_options !!});
  });
</script>