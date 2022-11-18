<?php
/**
 * @var \TAO\Fields\Type\Yecaptcha $field
 */
?>
{{--Скрипт-подключения--}}
<script src="{!! $field::API_URL !!}" defer></script>
{{--Блок-капчи--}}
<div
    id="captcha-container"
    class="smart-captcha"
    {{--Ключ-клиента--}}
    data-sitekey="{!! $field->data['api_key'] !!}"
    {{--Язык--}}
    data-hl="{!! $field->getLanguage() !!}"
></div>
