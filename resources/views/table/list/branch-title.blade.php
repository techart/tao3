<td class="admin-tree-title">
  <div class="bullet bullet-level-{{ $level }}">
    <div class="bullet-text">
      {!! $row->titleForTreeAdmin($level) !!}
    </div>
    @if ($row->checkIfSortable())
      <div class="controls">
        @if (!$row->isFirstBranch)
          <a class="control" href="{{ url($controller->actionUrl('weight', ['id' => $row->getKey(), 'with' => $row->prevBranch->getKey() ])) }}"><i class="up">&nbsp;</i></a>
        @endif
        @if (!$row->isLastBranch)
          <a class="control" href="{{ url($controller->actionUrl('weight', ['id' => $row->getKey(), 'with' => $row->nextBranch->getKey() ])) }}"><i class="down">&nbsp;</i></a>
        @endif
        &nbsp;
      </div>
    @endif
  </div>
</td>
