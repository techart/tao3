{{ \Assets::useFile('/tao/scripts/jquery.form.js') }}
{{ \Assets::useFile('/tao/scripts/form.js') }}
<script>
  $(function() {
    taoAjaxForm('{{ $form->htmlId($__data) }}', {!! $ajax_options !!});
  });
</script>