var POIS = [];
var PATHS = [];
var OPENPOI = null;

var MAPID = null;

function readMapsListCallback(rsp){
	$('#maps-list').removeClass('spinner');
	if(rsp.status=='FAIL'){
		
		$('#maps-list').html('Error occured:<br/>'+rsp.error);
	}else{
		var periods = [['last-hour', 'LAST HOUR'], ['today','TODAY'], ['yesterday', 'YESTERDAY'], ['last-week', 'LAST WEEK'], ['last-month', 'LAST MONTH'], ['last-year', 'LAST YEAR'], ['older', 'OLDER']];
		
		var f = function(x){
			var res = document.createElement('ul');
			for(var i=0;i<x.length;i++){
				var item = document.createElement('li');
				///TODO: XSS prevent
				$(item).html('<a href="#maps/'+x[i].id+'"><b>'+x[i].title+'</b> <small>['+(x[i].public?'public':'private')+']</small><br/><small>&nbsp;'+x[i].desc+'</small></a>');
				$(res).append(item);
			}
			return res;
		}
		
		$('#maps-list').html('');
		
		for(var i=0;i<periods.length;i++)
			if(rsp.maps[periods[i][0]].length>0){
				$('#maps-list').append('<h3>'+periods[i][1]+'</h3>');
				$('#maps-list').append(f(rsp.maps[periods[i][0]]));
			}
	}
}
function readMapsList(){
	var query = {
		method: "get-maps-list"	
	}
	
	$('#maps-list').html('');
	$('#maps-list').addClass('spinner');
	sendQuery(query, readMapsListCallback);
}

function createNewMapCallback(rsp){
	if(rsp.status=='FAIL'){
		$('#maps-create-warning').text(rsp.error);
		$('#maps-create-warning').css('display','block');
		alert(rsp.error);
	}else{
		$('#maps-create-warning').css('display','none');
		loadMap(rsp.mapid);
	}
}

function createUpdateMap(){
	var x = [];
	if($('#maps-create-title').val().trim()==''){
		$('#maps-create-warning').text('Map title couldn\'t be empty');
		$('#maps-create-warning').css('display','block');
		return false;
	}else{
		$('#tags-map-tags').children().each(function(){
			x.push($(this).children('a').text());
		});
		
		console.debug(x);
		var query = {
			method: '',
			title: $('#maps-create-title').val(),
			desc: $('#maps-create-desc').val(),
			policy: $('input:radio[name=policy]:checked').val(),
			tags: x
		};
		return query;
		//sendQuery(query, createNewMapCallback);		
	}
}
function createNewMap(){
	var query = createUpdateMap();
	if(query!=false){
		query.method='create-new-map';
		sendQuery(query, createNewMapCallback);		
	}
}

function _mapSetView(view){
	$('#maps-box').removeClass('hidden');
	if(view=='create-map'){
		$('#maps-create-warning').css('display','none');
		$('#maps-create-title').val('');
		$('#maps-create-desc').val('');
		$('input:radio[name=policy]:checked').removeAttr('checked');
		$('input:radio[name=policy][value=private]').attr('checked','checked');
		$('#tags-map-tags').html('');
		
		$('#maps-cancel-button').unbind('click').click(function(){_mapSetView('browse-maps');});
		$('#maps-create-button').unbind('click').click(createNewMap);
	
	
		$('#maps-create').css('display','block');
		$('#maps-browse').css('display','none');
		$('#maps-spotlight').css('display','none');
	}else
	if(view=='browse-maps'){
		$('#maps-create').css('display','none');
		$('#maps-spotlight').css('display','none');
		$('#maps-browse').css('display','block');
		location.hash = '';
		
		readMapsList();
	}else
	if(view=='spotlight'){
		$('#maps-create').css('display','none');
		$('#maps-browse').css('display','none');
		$('#maps-spotlight').css('display','block');
	}else
	if(view=='edit-map'){
		$('#maps-create-warning').css('display','none');
		
		$('#maps-cancel-button').unbind('click').click(function(){_mapSetView('spotlight');});
		//$('#maps-create-button').unbind('click').click(confirmEditMap);
		
		$('#maps-create').css('display','block');
		$('#maps-browse').css('display','none');
		$('#maps-spotlight').css('display','none');
	}
	if(view=='empty'){
		$('#maps-create').css('display','none');
		$('#maps-browse').css('display','none');
		$('#maps-spotlight').css('display','none');
	}
}

function addNewTag(){
	var fo = document.createElement('form');
	var f = function(){
		var x;
	
		if($(this).is('input'))x = $(this).val();
		else x = $(this).children('input').val();

		x = x.trim().replace(/[ _/\\]/g,'-');
		if(x!=''){
			var a = document.createElement('a');
			$(a).attr('href','javascript:void(0)');
			$(a).click(function(){$(this).parent().remove();});
			$(a).text(x);
			var s = document.createElement('span');
			$(s).append(a).append(', ');
			$('#tags-map-tags').append(s);
		}
		if($(this).is('input'))$(this).parent().remove();
		else $(this).remove();
		
		$('#maps-tagadd-button').focus();
	};
	
	$(fo).submit(f);
	$(fo).attr('action','javascript:void(0)');
	$(fo).css('display','inline');
	var obj = document.createElement('input');
	$(obj).attr('size','8');
	$(obj).blur(f);
	$(fo).append(obj);
	$('#tags-map-tags').append(fo);
	$(obj).focus();
}


function deleteMapCallback(rsp){
	if(rsp.status=='FAIL'){
		window.alert('Error during removing map: '+rsp.error);
	}else{
		_mapSetView('browse-maps');
	}
}

function deleteMap(mapid){
	if(window.confirm('Do you really want to delete map #'+mapid)==true){
		var query = {
			method: 'delete-map',
			mapid: mapid
		}
		
		sendQuery(query, deleteMapCallback);
	}
}

function confirmEditMapCallback(rsp){
	if(rsp.status=='FAIL'){
		alert('Error during map edit: '+rsp.error);
	}else{
		loadMap(rsp.mapid);
	}
}

function confirmEditMap(mapid){
	var query = createUpdateMap();
	if(query!=false){
		query.method='update-map';
		query.mapid=mapid;
		sendQuery(query, confirmEditMapCallback);		
	}
}

function editMapCallback(rsp){
	if(rsp.status=='FAIL')
		alert('Map edition failed: '+rsp.error);
	else{
		$('#maps-create-title').val(rsp.title);
		$('#maps-create-desc').val(rsp.description);
		
		$('input:radio[name=policy]:checked').removeAttr('checked');
		if(rsp.public)
			$('input:radio[name=policy][value=public]').attr('checked','checked');
		else
			$('input:radio[name=policy][value=private]').attr('checked','checked');
			
		$('#tags-map-tags').html('');
		if(rsp.tags!=''){
			var tags = rsp.tags.split(',');//$('#tags-map-tags').html('');
			for(var i=0;i<tags.length;i++){
				var a = document.createElement('a');
				$(a).attr('href','javascript:void(0)');
				$(a).click(function(){$(this).parent().remove();});
				$(a).text(tags[i]);
				var s = document.createElement('span');
				$(s).append(a).append(', ');
				$('#tags-map-tags').append(s);
			}
		}
			

		$('#maps-create-button').unbind('click').click(function(){confirmEditMap(rsp.mapid);});
		_mapSetView('edit-map');
	}
}
function editMap(mapid){
	var query = {
		method: 'load-map',
		mapid: mapid
	};
	sendQuery(query, editMapCallback);
}

function showPoi(poi, mapid, id){
	var d = document.getElementById('poi-'+id);
	if(d==null)
		d = document.createElement('div');
	else
		$(d).html('');
	$(d).addClass('map-pois');
	var e = document.createElement('div');
	$(e).css('background-image', 'url(gfx/pins/'+poi.look.style+'.png)');
	$(e).css('background-position', '-'+(32*poi.look.pin)+'px 0px');
	$(d).append(e);
	var b = document.createElement('b');
	$(b).text(poi.title);
	$(d).append(b);
	
	
	//var pos = poi.pos.substring(1,poi.pos.length-1).split(',');
	var mrk = L.marker([parseFloat(poi.lat), parseFloat(poi.lng)], {icon: new L.IconMarker({style: poi.look.style, pinId: poi.look.pin })});
	//console.log('DODAJE: ');
	//console.debug(mrk);
	
	
	POIS.push([mrk,d, id]);
	
	mrk.addTo(map);
	mrk.dragging.enable();
	
	var f = function(e){
		var wsp = e.target.getLatLng();
		var x = map.latLngToLayerPoint(wsp);
		//Wylicz reprezentacje w stopniach
		var t = '';
		var w = Math.abs(parseFloat(wsp.lat));
		//50&deg; 12' 32'' N&emsp;20&deg; 20' 51'' E
		t+= parseInt(w) + '&deg ';w = 60.0*(w-Math.floor(w));
		t+= parseInt(w) + '\' ';w = 60.0*(w-Math.floor(w));
		t+= parseInt(w) + '\'\' ';
		if(wsp.lat>=0.0)t+='N';else t+='S';
		t+='&emsp;';
		w = Math.abs(parseFloat(wsp.lng));
		t+= parseInt(w) + '&deg ';w = 60.0*(w-Math.floor(w));
		t+= parseInt(w) + '\' ';w = 60.0*(w-Math.floor(w));
		t+= parseInt(w) + '\'\' ';
		if(wsp.lng<0.0)t+='E';else t+='W';
		
		map.panTo(wsp);
		
		$('#poi-delete-button').unbind('click').bind('click', function(){deletePoi(id);});
		$('#poi-edit-button').unbind('click').bind('click', function(){editPoi(mapid,id);});
		$('#poi-marker-icon').unbind('click').bind('click', function(){setPinsStyle(poi.look.style);editMarker(mapid, id);});
		popup.setContent({title: poi.title, desc: poi.desc, style:poi.look.style, pin:poi.look.pin, cords: t});
		popup.setLatLng(e.target.getLatLng()).openOn(map);
	}

	mrk.on('click', f);
	mrk.on('dragend', function(e){updatePoiPosition(mapid, id, e.target.getLatLng())});
	
	$(d).click(function(){mrk.fire('click');});
	$('#map-objs').append(d);
}

function getPoisListCallback(rsp){
	if(rsp.status=='FAIL')
		alert('Error during loading POIs: '+rsp.errro);
	else{
		for(i in rsp.pois){
			showPoi(rsp.pois[i],rsp.mapid,parseInt(i));
		}
		
		var minx=10000.0,miny=10000.0, maxx=-10000.0, maxy=-10000.0;
		
		for(i=0;i<POIS.length;i++){
			minx = Math.min(POIS[i][0]._latlng.lat, minx);
			miny = Math.min(POIS[i][0]._latlng.lng, miny);
			maxx = Math.max(POIS[i][0]._latlng.lat, maxx);
			maxy = Math.max(POIS[i][0]._latlng.lng, maxy);
		}					
		
		map.fitBounds([[minx-(maxx-minx)*0.05, miny-(maxy-miny)*0.05],[maxx+(maxx-minx)*0.05,maxy+(maxy-miny)*0.05]]);
	}
}

function getPoisList(mapid,pois){
	var query = {
		method: 'get-pois-list',
		mapid: mapid,
		pois: pois
	};
	
	sendQuery(query, getPoisListCallback);
}

function removePathCallback(rsp){
	if(rsp.status=='FAIL')
		alert('Cant remove path: '+rsp.error);
	else{
		loadMap(MAPID);
	}
}
function removePath(pathid){
	var query = {
		method: 'remove-path',
		pathid: pathid
	};
	
	sendQuery(query, removePathCallback);	
}

function getPathsListCallback(rsp){
	if(rsp.status=='FAIL')
		alert('Error during loading PATHs: '+rsp.error);
	else{
		for(i in rsp.paths){
			var p = rsp.paths[i];
			console.log('PATH');
			var x = p.points.replace(/[()]/g,'','g').split(',');

			var a = []
			for(j=0;j<parseInt(x.length/2);j++){
				a.push([parseFloat(x[2*j]), parseFloat(x[2*j+1])]);
			}
			
			var polyline = L.polyline(a, {color: 'red'});
			polyline.bindPopup('<b>'+p.title+'</b><br/>'+p.desc+'<br/><a href="javascript:void(0)" onclick="javascript:removePath('+i+')">Delete path</a>');
			
			PATHS.push(polyline);
			polyline.addTo(map);
			
			var d = document.getElementById('path-'+i);
			if(d==null)
				d = document.createElement('div');
			else
				$(d).html('');
			$(d).addClass('map-pois');
			var e = document.createElement('div');
			$(e).css('background-image', 'url(gfx/path.png)');
			$(d).append(e);
			var b = document.createElement('b');
			$(b).text(p.title);
			$(d).append(b);
			$(d).click(function(){polyline.openPopup();});
			$('#map-objs').append(d);
	
			console.debug(x);
		}
	}
}

function getPathsList(mapid, paths){
	var query = {
		method: 'get-paths-list',
		mapid: mapid,
		paths: paths
	};
	sendQuery(query, getPathsListCallback);
}

function loadMapCallback(rsp){
	if(rsp.status=='FAIL'){
		if(rsp.error=='Access denied')
			alert('Access denied to map '+rsp.mapid);
		else
			alert('Error during map loading: '+rsp.error);
	}else{
		MAPID = rsp.mapid;
		$('#map-spot-title').text(rsp.title+' ['+(rsp.public?'public':'private')+']');
		$('#map-spot-desc').text(rsp.description);
		$('#map-spot-ctime').text(rsp.ctime);
		$('#map-spot-mtime').text(rsp.mtime);
		$('#map-spot-views').text(rsp.views);
		$('#map-spot-author').text(rsp.author);
		$('#map-spot-tags').text(rsp.tags.split(',').join(', '));
		
		$('#map-delete-button').unbind('click');
		$('#map-delete-button').click(function(){deleteMap(rsp.mapid);});
		
		$('#map-edit-button').unbind('click');
		$('#map-edit-button').click(function(){editMap(rsp.mapid);});
		
		$('#map-objs').html('' );
		var pois = [],paths=[];
		for(i=0;i<rsp.objs.length;i++)
			if(rsp.objs[i].type=='POI')
				pois.push(rsp.objs[i].id);
			else
			if(rsp.objs[i].type=='PATH')
				paths.push(rsp.objs[i].id);
			
		if(pois.length>0)
			getPoisList(rsp.mapid,pois);
			
		if(paths.length>0)
			getPathsList(rsp.mapid, paths);
		
		_mapSetView('spotlight');
	}
}

function updatePoiPositionCallback(rsp){
	if(rsp.status=='FAIL')
		alert(rsp.status);
}

function updatePoiPosition(mapid, poiid, position){
	var query = {
		method: 'update-poi-position',
		mapid: mapid,
		poi: poiid,
		lat: position.lat,
		lng: position.lng
	};
	
	sendQuery(query, updatePoiPositionCallback);
}

function createNewPoiCallback(rsp){
	if(rsp.status=='FAIL')
		alert('Error during creating new POI: '+rsp.error);
	else{
		console.debug(rsp);
		showPoi(rsp.poi,MAPID, rsp.poiid);
		POIS[POIS.length-1][0].fire('click');
	}
}

function createNewPoi(position, mapid){
	var query = {
		method: 'create-new-poi',
		lat: position.lat,
		lng: position.lng,
		mapid: mapid
	};
	
	sendQuery(query, createNewPoiCallback);
}

function deletePoiCallback(rsp){
	if(rsp.status=='FAIL'){
		alert('Deleting POI failed: '+rsp.error);
	}else{
		for(i=0;i<POIS.length;i++)
			if(POIS[i][2]==rsp.poiid){
				map.removeLayer(POIS[i][0]);
				$(POIS[i][1]).remove();
			}
		if(popup)popup._onCloseButtonClick();
	}	
}

function deletePoi(poiid){
	var query = {
		method: 'delete-poi',
		poiid: poiid
	};
	
	sendQuery(query, deletePoiCallback);
}


function loadMap(mapid){
	console.log('POIS:');
	console.debug(POIS);
	
	while(POIS.length>0){
		var x = POIS.pop();
		if(x==null)continue;
		console.debug(x[0]);
		map.removeLayer(x[0]);
	}
	while(PATHS.length>0){
		var x = PATHS.pop();
		if(x==null)continue;
		map.removeLayer(x);
	}
	
	var query = {
		method: 'load-map',
		mapid: mapid
	};
	
	sendQuery(query, loadMapCallback);
}

function loadGpxFailed(x){
	$('#map-import-warning').css('display','block');
	$('#map-import-warning').text(x);
	showGpxSend();
}

function loadGpxSuccess(){
	$('#map-import-iframe').css('display','none');
	loadMap(MAPID);
}

function sendGpx(){
	$('#map-import-iframe iframe').contents().find('form').submit();
}

function showGpxSend(){
	//$('#map-import-warning').css('display','none');
	//$('#map-import-warning').text('');
	
	$('#map-import-iframe iframe').attr('src', 'post.php?method=gpx&id='+MAPID);
	$('#map-import-iframe').css('display','block');
	
}

function sendEditPoiCallback(rsp){
	if(rsp.status=='FAIL')
		alert('Updating POI\' data failed: '+rsp.error);
	else{
		$('#poi-inner').css('display','block');
		$('#poi-edit').css('display','none');
		
		for(i=0;i<POIS.length;i++)
			if(POIS[i][2]==rsp.poiid){
				map.removeLayer(POIS[i][0]);
				$(POIS[i][1]).remove();
			}
		
		showPoi(rsp.poi, MAPID, rsp.poiid);
		//popup._onCloseButtonClick();
		POIS[POIS.length-1][0].fire('click');
	}
}

function sendEditPoi(mapid, poiid){
	var query = {
		method: 'update-poi-data',
		mapid: mapid,
		poiid: poiid,
		title: $('#poi-edit input').val(),
		desc: $('#poi-edit textarea').val()
	};
	
	sendQuery(query, sendEditPoiCallback);
}


function editPoiCallback(rsp){
	if(rsp.status=='FAIL')
		alert('Start POI editing failed: '+rsp.error);
	else{
		var id;
		for(i in rsp.pois){
			id = parseInt(i);
			$('#poi-edit input').val(rsp.pois[i].title);alert
			$('#poi-edit textarea').val(rsp.pois[i].desc);
			break;
		}
		
		$('#poi-inner').css('display','none');
		$('#poi-edit').css('display','block');
		$('#poi-cancel-button').unbind('click').click(
			function(){
				$('#poi-inner').css('display','block');
				$('#poi-edit').css('display','none');
			}
		);
		
		$('#poi-save-button').unbind('click').click(
			function(){sendEditPoi(rsp.mapid, id);}
		);
	}	
}
function editPoi(mapid, poiid){
	var query = {
		method: 'get-pois-list',
		mapid: mapid,
		pois: [poiid]
	};
	sendQuery(query, editPoiCallback);	
}

function editMarker(mapid, poiid){
	$('#poi-inner').css('display','none');
	$('#pins-box').css('display','block');
	OPENPOI = poiid;
}

function updatePoiPinCallback(rsp){
	if(rsp.status=='FAIL')
		alert('Error during POIs pin update: '+rsp.error);
	else{
		$('#poi-inner').css('display','block');
		$('#pins-box').css('display','none');
		
		for(i=0;i<POIS.length;i++)
			if(POIS[i][2]==rsp.poiid){
				map.removeLayer(POIS[i][0]);
				$(POIS[i][1]).remove();
			}
		
		showPoi(rsp.poi, MAPID, rsp.poiid);
		//popup._onCloseButtonClick();
		POIS[POIS.length-1][0].fire('click');
	}
}

function updatePoiPin(v){
	var query = {
		method: 'update-poi-pin',
		poiid: OPENPOI,
		pin: v,
		mapid: MAPID,
		style: PINSTYLE
	};
	sendQuery(query, updatePoiPinCallback);
}


function _mapHashChange(){ 	
	if(location.hash.indexOf('#maps/')==0){
		//alert('Change map to: '+location.hash.substr(6));
		loadMap(parseInt(location.hash.substr(6)));
	}//else alert('unknown hash: '+location.hash);
}

$(function(){
	/* Rozwijanie okienka */
	$('#maps-box h2').click(function(){$('#maps-box').toggleClass('hidden');});
	
	//readMapsList();
	
	$('#maps-list').slimScroll({
		height: '280px',
		alwaysVisible: true
	});
	
	$('#maps-spotlight-content').slimScroll({
		height: '280px',
		alwaysVisible: true
	});
	
	$('#maps-button-create').click(function(){_mapSetView('create-map');});
	
	
	$('#maps-button-my').click(function(){readMapsList();_mapSetView('browse-maps');});
	
	$('#maps-tagadd-button').click(addNewTag);
	
	$('#poi-popup').appendTo('.leaflet-popup-pane');
	/* Zmiana w adresie strony */
	window.addEventListener("hashchange", _mapHashChange, false);
	
	$('#map-import-button').click(showGpxSend);
	$('#pins-list-cancel').click(function(){$('#pins-box').css('display','none');$('#poi-inner').css('display','block');});
	
	
	$('#map-import-submit').click(function(){$('#map-import-warning').css('dsplay','none');sendGpx();});
	$('#map-import-cancel').click(function(){$('#map-import-iframe').css('display','none');});
	
	//loadMap(43);
});
