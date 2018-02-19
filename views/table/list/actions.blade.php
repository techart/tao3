<?php
/**
 * @var \TAO\Fields\Model $row
 * @var \TAO\Fields\Model\User $user
 * @var string $title
 * @var array $fields
 * @var int $count
 * @var int $per_page
 * @var int $numPages
 * @var \TAO\Fields\Model[] $rows
 * @var bool $can_add
 * @var bool $can_edit
 * @var bool $can_delete
 * @var bool $can_copy
 * @var string $add_text
 * @var array $filter
 * @var bool $with_filter
 * @var bool $with_filter
 * @var string $filter_url
 * @var string $reset_filter_url
 * @var bool $sidebar_visible
 * @var bool $filter_empty
 * @var bool $with_row_actions
 * @var array $pager_callback
 * @var int $page
 */
?>
@if ($with_row_actions)
    <td class="actions">
    
        @if ($can_edit && $row->accessEdit($user))
            <a class="btn btn-primary" href="{{ url($controller->actionUrl('edit', ['id' => $row->getKey() ])) }}"><i class="icon-pencil icon-white"></i></a>
        @endif
        
        @if ($can_delete && $row->accessDelete($user))
            <a onClick="return confirm('Вы уверены?')" class="btn btn-danger" href="{{ url($controller->actionUrl('delete', ['id' => $row->getKey() ])) }}"><i class="icon-remove icon-white"></i></a>
        @endif
    </td>
@endif
