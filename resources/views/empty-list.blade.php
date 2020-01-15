@if (!isset($empty_text) || $empty_text)
    <div class="selector-empty-rows">
    @isset($empty_text)
        {{ $empty_text }}
    @else
        Список пуст.
    @endisset
    </div>
@endif