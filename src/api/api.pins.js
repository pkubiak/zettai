var PINSTYLE = 0;

L.IconMarker = L.Icon.extend({
	options: {
    // EDIT THIS TO POINT TO THE FILE AT http://www.charliecroom.com/marker_hole.png (or your own marker)
		iconUrl: '<%= image_path("leaflet/marker_hole.png") %>',
		style: '',
		pinId: 0,
		shadowUrl: null,
		iconSize: new L.Point(32, 37),
		iconAnchor: new L.Point(16, 37),
		popupAnchor: new L.Point(0, -33),
		//className: 'leaflet-div-icon'
	},
 
	createIcon: function () {
		var div = document.createElement('div');
		$(div).css('background-image', 'url(gfx/pins/'+this.options['style']+'.png)');
		$(div).css('background-position', '-'+(this.options['pinId']*32)+'px 0px');
		
		/*var img = this._createImg(this.options['iconUrl']);
		var numdiv = document.createElement('div');
		numdiv.setAttribute ( "class", "number" );
		numdiv.innerHTML = this.options['number'] || '';
		div.appendChild ( img );
		div.appendChild ( numdiv );*/
		
		this._setIconStyles(div, 'icon');
		return div;
	},
 
	//you could change this to add a shadow like in the normal marker if you really wanted
	createShadow: function () {
		return null;
	}
});



function setPinsStyle(style){
	console.debug(style);
	PINSTYLE = style;
	$('#pins-list div').each(function(){$(this).css('background-image','url(gfx/pins/'+style+'.png)');});
}

function setPins(e){
	
}



function readPinsListCallback(rsp){
	if(rsp.status=='SUCCESS'){
		for(i=0;i<rsp.styles.length;i++){
			var d = document.createElement('div');
			//$(d).attr('id','pins-style-'+rsp.styles[i]);
			$(d).addClass('pins-style');
			$(d).css('background-color','#'+rsp.styles[i]);
			$(d).click(function(x){return function(){setPinsStyle(x)}}(rsp.styles[i]));
			console.debug(d);
			$('#pins-style-list').append(d);
			//$('#pins-style-'+rsp.styles[i]).unbind('click').bind('click', function(e){console.debug(e,rsp, i);setPinsStyle(rsp.styles[i])});
			
		}
			
		for(i=0;i<rsp.categories.length;i++){
			$('#pins-list').append('<h3>'+rsp.categories[i].name+'</h3>');
			for(j=0;j<rsp.pins.length;j++)
				if(rsp.pins[j][1]==rsp.categories[i].id){
					$('<div/>', {
						
						click: function(x){return function(){updatePoiPin(x)};}(rsp.pins[j][0])
					}).css('background-position','-'+(32*rsp.pins[j][2])+'px 0px').appendTo('#pins-list');
				}
					//$('#pins-list').append('<div style="background-image:url(gfx/pins/21dba6.png);background-position:-'+(32*rsp.pins[j][2])+'px 0px"></div>');
			$('#pins-list').append('<hr class="clear"/>');
		}
		
		rsp = null;
	}
}
function readPinsList(){
	var query = {
		'method': 'get-pins-list'
	};
	
	sendQuery(query, readPinsListCallback);
}

$(function(){
	//readPinsList();
	readPinsList();
	
	$('#pins-list').slimScroll({
		height: '200px',
		alwaysVisible: true
	});
});
