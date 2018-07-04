$(function () {
	ymaps.ready(function () {
		$('.b-coords-output').each(function () {
			new Map($(this));
		});
	});

	function Map($block) {
		this.point = $block.data('coords');
		this.zoom = $block.data('zoom');

		console.log(this.point);

		this.map = new ymaps.Map($block.attr('id'), {
			center: [this.point.lat, this.point.lng],
			zoom: this.zoom
		});

		this.placemark = new ymaps.Placemark([this.point.lat, this.point.lng]);
		this.map.geoObjects.add(this.placemark);
	}
});