@php
	$uid = 'i'.uniqid();
	$url = $field->buildUrl();
@endphp
<iframe src="{{ $url }}" frameborder=0 class="input-iframe {{ $field->classForInput() }} {{ $uid  }}" style="{!! trim($field->styleForInput(), ';') !!}" {!! $field->renderAttrs() !!}>!</iframe>

<script>
	$(function () {
		var $iframe = $('.{{ $uid }}');

		function resize{{ $uid  }}() {
			var func = $iframe.get(0).contentWindow.getWindowHeight;
			if (typeof func === 'function') {
				var height = func();
				if (height > 0) {
					$iframe.height(height);
				}
			}
		}
		
		resize{{ $uid  }}();

		$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
			resize{{ $uid  }}();
		})
		
		$iframe.load(function () {
			resize{{ $uid  }}();
		});
	});
</script>
