<div class="b-comments__comment" data-id="{{ $comment->id }}">
    <div class="b-comments__header">
        <div class="b-comments__author">{{ $comment->authorName() }}</div>
        <div class="b-comments__date">{{ date($date_format, $comment->time_create) }}</div>
    </div>
    <div class="b-comments__content">
        <div class="b-comments__message">{{ $comment->content}}</div>
        <div class="b-comments__buttons">
            @if ($comment->accessEdit())
                <span class="b-comments__edit_button" data-url-raw="{{ $comment->rawCommentUrl() }}" data-url-update="{{ $comment->editUrl() }}">Редактировать</span>
            @endif
            @if ($comment->accessDelete())
                <span class="b-comments__delete_button" data-url="{{ $comment->deleteUrl() }}">Удалить</span>
            @endif
        </div>
    </div>
</div>