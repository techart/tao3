<div class="b-comments" data-token="{{ csrf_token() }}">
    @if ($count == 0)
        @include('comments ~ empty')
    @endif

    <div class="b-comments__branches">
        @foreach($comments as $comment)
            @include('comments ~ comment')
        @endforeach
    </div>
  
    @include("comments ~ {$template_add}")
</div>