// TODO: move tiles, center and other settings to php
// TODO: redefined field class
var coordinatesFields = [];

$(function () {
	$('.b-field-coordinates').each(function () {
		coordinatesFields.push(new CoordinatesField($(this)));
	});

	function CoordinatesField($block) {
		this.$mapContainer = $('.b-field-coordinates__map', $block);
		this.$latInput = $('.b-field-coordinates__input-lat', $block);
		this.$lngInput = $('.b-field-coordinates__input-lng', $block);
		this.$inputCollection = this.$latInput.add(this.$lngInput);
		this.params = $block.data('field-params');

		this.initMap = function () {
			this.map = L.map(this.$mapContainer.attr('id')).setView([55.72, 37.65], 9);
			L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
				attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
				subdomains: ['a', 'b', 'c']
			}).addTo(this.map);
		}

		this.addPoint = function () {
			if (typeof this.marker == 'undefined') {
				this.marker = L.marker(this.map.getCenter(), {draggable: 'true'}).addTo(this.map);
				this.marker.on('dragend', this.onMarkerDrag.bind(this));
			}
		}

		this.refreshPoint = function () {
			this.marker.setLatLng(new L.LatLng(this.point.lat, this.point.lng));
			this.map.setView([this.point.lat, this.point.lng]);
		}

		this.refreshInputValues = function () {
			this.$latInput.val(this.roundCoord(this.point.lat));
			this.$lngInput.val(this.roundCoord(this.point.lng));
		}

		this.refresh = function () {
			this.point = {
				'lng': this.$lngInput.val(),
				'lat': this.$latInput.val(),
			};
			this.refreshPoint();
		}

		this.checkValue = function () {
			var reg = /\d{1,3}(\.\d+)?/;
			return this.$lngInput.val().match(reg) && this.$latInput.val().match(reg);
		}

		var refreshPointTimeoutId;
		this.onInputValueChange = function () {
			if (typeof refreshPointTimeoutId !== 'undefined') {
				clearTimeout(refreshPointTimeoutId);
			}
			if (this.checkValue()) {
				var field = this;
				refreshPointTimeoutId = setTimeout(function () {
					field.refresh();
				}, 1000);
			}
		}

		this.onMarkerDrag = function () {
			this.point = this.marker.getLatLng();
			this.refreshInputValues();
		}

		this.roundCoord = function (coord) {
			var precision = 6;
			return Math.ceil(coord * Math.pow(10, precision)) / Math.pow(10, precision);
		}

		if (this.$mapContainer.length) {
			this.$inputCollection.on('input change', this.onInputValueChange.bind(this));

			this.initMap();
			this.addPoint();
			if (this.params.value !== null) {
				this.point = this.params.value;
				this.refreshPoint();
			}
		}
	}
});
