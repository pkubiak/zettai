L.CustomPopup2 = L.Class.extend({
	includes: L.Mixin.Events,

	options: {
		minWidth: 50,
		maxWidth: 300,
		maxHeight: null,
		autoPan: false,
		closeButton: true,
		offset: new L.Point(88, -8),
		autoPanPadding: new L.Point(5, 5),
		className: '',
		zoomAnimation: true
	},

	initialize: function (options, source) {
		console.log('initialize');
		L.setOptions(this, options);

		this._source = source;
		this._animated = L.Browser.any3d && this.options.zoomAnimation;
	},

	onAdd: function (map) {
		console.log('onAdd');
		$('#poi-popup').css('display','block');
		
		this._map = map;

		if (!this._container) {
			this._initLayout();
		}
		this._updateContent();

		var animFade = map.options.fadeAnimation;

		if (animFade) {
			L.DomUtil.setOpacity(this._container, 0);
		}
		map._panes.popupPane.appendChild(this._container);

		map.on('viewreset', this._updatePosition, this);

		if (this._animated) {
			console.debug('TAK');
			map.on('zoomanim', this._zoomAnimation, this);
		}

		if (map.options.closePopupOnClick) {
			map.on('preclick', this._close, this);
		}

		this._update();

		if (animFade) {
			L.DomUtil.setOpacity(this._container, 1);
		}
		
		setTool('hand');
		$('#poi-edit').css('display','none');
		$('#poi-inner').css('display','block');
		$('#pins-box').css('display','none');
		if(!ISLOGGED){
			$('#poi-actions').css('display','none');
		}else{
			$('#poi-actions').css('display','block');
		}
	},

	addTo: function (map) {
		map.addLayer(this);
		return this;
	},

	openOn: function (map) {
		
		map.openPopup(this);
		return this;
	},

	onRemove: function (map) {
		console.log('onRemove');
		//map._panes.popupPane.removeChild(this._container);

		L.Util.falseFn(this._container.offsetWidth); // force reflow

		map.off({
			viewreset: this._updatePosition,
			preclick: this._close,
			zoomanim: this._zoomAnimation
		}, this);

		if (map.options.fadeAnimation) {
			L.DomUtil.setOpacity(this._container, 0);
		}

		this._map = null;
	},

	setLatLng: function (latlng) {
		this._latlng = L.latLng(latlng);
		this._update();
		return this;
	},

	setContent: function (content) {
		
		//this._content = content;
		$('#poi-title').text(content.title);
		$('#poi-cords').text('');
		document.getElementById('poi-cords').innerHTML = content.cords;
		//alert(content.cords);
		//console.debug($('#poi-cords').text(),content.cords);
		$('#poi-desc').text(content.desc);
		
		$('#poi-popup').css('background-color','#'+content.style);
		$('#poi-marker-icon').css('background-image', 'url(gfx/pins/'+content.style+'.png)');
		$('#poi-marker-icon').css('background-position', '-'+(parseInt(content.pin*55.35135))+'px 0px');
		$('#poi-anchor').css('background-image', 'url(gfx/pins/'+content.style+'.png)');
		
		this._update();
		return this;
	},

	_close: function () {
		console.log('_close');
		var map = this._map;

		if (map) {
			map._popup = null;
			$('#poi-popup').css('display','none');
			map.removeLayer(this).fire('popupclose', {popup: this});
		}
	},

	_initLayout: function () {
		console.log('_initLayout');
		//var prefix = 'leaflet-popup',
		//	containerClass = prefix + ' ' + this.options.className + ' leaflet-zoom-' +
			//        (this._animated ? 'animated' : 'hide'),
			//container = this._container = L.DomUtil.create('div', containerClass),
			//closeButton;

		/*if (this.options.closeButton) {
			closeButton = this._closeButton =
			        L.DomUtil.create('a', prefix + '-close-button', container);
			closeButton.href = '#close';
			closeButton.innerHTML = '&#215;';

			L.DomEvent.on(closeButton, 'click', this._onCloseButtonClick, this);
		}*/

		//var wrapper = this._wrapper =       L.DomUtil.create('div', prefix + '-content-wrapper', container);
		//L.DomEvent.disableClickPropagation(wrapper);

		//this._contentNode = L.DomUtil.create('div', prefix + '-content', wrapper);
		//L.DomEvent.on(this._contentNode, 'mousewheel', L.DomEvent.stopPropagation);

		//this._tipContainer = L.DomUtil.create('div', prefix + '-tip-container', container);
		//this._tip = L.DomUtil.create('div', prefix + '-tip', this._tipContainer);
		
		this._container = document.getElementById('poi-popup');
		$('#poi-close').unbind('click');//.click(function(){popup.fire('close');});
		L.DomEvent.on(document.getElementById('poi-close'), 'click', this._onCloseButtonClick, this);
		L.DomEvent.disableClickPropagation(this._container);
		L.DomEvent.on(this._container, 'mousewheel', L.DomEvent.stopPropagation);
		
	},

	_update: function () {
		console.log('_update');
		if (!this._map) { return; }

		this._container.style.visibility = 'hidden';

		this._updateContent();
		this._updateLayout();
		this._updatePosition();

		this._container.style.visibility = '';

		this._adjustPan();
	},

	_updateContent: function () {
		console.log('_updateContent');
		if (!this._content) { return; }

		if (typeof this._content === 'string') {
			this._contentNode.innerHTML = this._content;
		} else {
			while (this._contentNode.hasChildNodes()) {
				this._contentNode.removeChild(this._contentNode.firstChild);
			}
			this._contentNode.appendChild(this._content);
		}
		this.fire('contentupdate');
	},

	_updateLayout: function () {
		console.log('_updateLayout');
		/*var container = this._contentNode,
		    style = container.style;

		style.width = '';
		style.whiteSpace = 'nowrap';

		var width = container.offsetWidth;
		width = Math.min(width, this.options.maxWidth);
		width = Math.max(width, this.options.minWidth);

		style.width = (width + 1) + 'px';
		style.whiteSpace = '';

		style.height = '';

		var height = container.offsetHeight,
		    maxHeight = this.options.maxHeight,
		    scrolledClass = 'leaflet-popup-scrolled';

		if (maxHeight && height > maxHeight) {
			style.height = maxHeight + 'px';
			L.DomUtil.addClass(container, scrolledClass);
		} else {
			L.DomUtil.removeClass(container, scrolledClass);
		}
		*/
		this._containerWidth = this._container.offsetWidth;
	},

	_updatePosition: function () {
		console.log('_updatePosition');
		if (!this._map) { return; }

		var pos = this._map.latLngToLayerPoint(this._latlng),
		    animated = this._animated,
		    offset = this.options.offset;

		if (animated) {
			L.DomUtil.setPosition(this._container, pos);
		}

		//this._containerBottom = -offset.y - (animated ? 0 : pos.y);
		this._containerBottom = -offset.y - (animated ? 0 : pos.y);
		//this._containerLeft = -Math.round(this._containerWidth / 2) + offset.x + (animated ? 0 : pos.x);
		this._containerLeft = -Math.round(this._containerWidth / 2) + offset.x + (animated ? 0 : pos.x);
		//Bottom position the popup in case the height of the popup changes (images loading etc)
		this._container.style.bottom = this._containerBottom + 'px';
		this._container.style.left = this._containerLeft + 'px';
		//$('#poi-popup').css('bottom', this._containerBottom + 'px');
		//$('#poi-popup').css('left', this._containerLeft + 'px');
	},

	_zoomAnimation: function (opt) {
		console.log('_zoomAnimation');
		var pos = this._map._latLngToNewLayerPoint(this._latlng, opt.zoom, opt.center);
		console.debug(opt);
		L.DomUtil.setPosition(this._container, pos);
	},

	_adjustPan: function () {
		console.log('_adjustPan');
		/*if (!this.options.autoPan) { return; }

		var map = this._map,
		    containerHeight = this._container.offsetHeight,
		    containerWidth = this._containerWidth,

		    layerPos = new L.Point(this._containerLeft, -containerHeight - this._containerBottom);

		if (this._animated) {
			layerPos._add(L.DomUtil.getPosition(this._container));
		}

		var containerPos = map.layerPointToContainerPoint(layerPos),
		    padding = this.options.autoPanPadding,
		    size = map.getSize(),
		    dx = 0,
		    dy = 0;

		if (containerPos.x < 0) {
			dx = containerPos.x - padding.x;
		}
		if (containerPos.x + containerWidth > size.x) {
			dx = containerPos.x + containerWidth - size.x + padding.x;
		}
		if (containerPos.y < 0) {
			dy = containerPos.y - padding.y;
		}
		if (containerPos.y + containerHeight > size.y) {
			dy = containerPos.y + containerHeight - size.y + padding.y;
		}

		if (dx || dy) {
			map.panBy(new L.Point(dx, dy));
		}*/
	},

	_onCloseButtonClick: function (e) {
		this._close();
		if(e)L.DomEvent.stop(e);
	}
});

L.CustomPopup = L.Popup.extend({});
