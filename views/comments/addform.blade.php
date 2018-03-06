@bottomScript('/tao/scripts/comments.js')
<form class="b-comments__form" data-url="{{ url($add_url) }}">
    {{ csrf_field() }}
    <textarea></textarea>
    <span>Добавить комментарий</span>
</form>