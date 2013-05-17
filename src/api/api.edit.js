var tool = 'hand';

function putPoiOnMap(e){
	console.debug(e);
	createNewPoi(e.latlng, MAPID);
	//alert(e);
}

function setTool(tool){
	if(tool=='hand'){//reka
		map.off('click', putPoiOnMap);
		$('#tools-icon-hand').addClass('selected');
		$('#tools-icon-poi').removeClass('selected');
		$('#map').css('cursor','');
	}else
	if(tool='poi'&&MAPID!=null){
		$('#map').css('cursor','url(gfx/cursor.png) 16 37, pointer');
		map.on('click', putPoiOnMap);
		
		$('#tools-icon-hand').removeClass('selected');
		$('#tools-icon-poi').addClass('selected');
	}
}

$(function(){
	setTool('hand');
	$('#tools-icon-hand').unbind('click').click(function(){setTool('hand');});
	$('#tools-icon-poi').unbind('click').click(function(){setTool('poi');});
	
});
