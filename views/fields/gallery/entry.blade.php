@if ($entry->isImage())
    <a class="{{ $entry_class }}" href="{{ $entry->url($full_mods) }}"><img src="{{ $entry->previewUrl($preview_mods) }}"></a>
@endif