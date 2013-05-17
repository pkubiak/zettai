/**
 * 
 * Zettai api part providing searching functions
 * 
 * @author: Paweł Kubiak
 * @date: 2013-01-17
 * 
 * @todo: 
 * 		+ skrolowanie na górę listy przy nowych wynikach
 * 		+ zablokwanie przycisku search w czasie szukania
 */
 
 
var querySearchLast = ['', 0, 6];//Last performed query

function querySearchCallback(rsp){
	var maxPerPage = 10;
	console.log('querySearchCallback');
	
	///TODO: skrolowanie na górę listy
	//$('#search-results').slimScroll({ scrollTo: '10px' });
	
	$('#search-results').removeClass('spinner');
	if(rsp.status=='FAIL'){
		$('#search-results').html('Error Ocurred:<br/><i>'+rsp.error+'</i><br/><a href="javascript:void()" onclick="javascript:querySearchRetry()">Try Again</a>');
	}else
	if(rsp.status=='SUCCESS'){
		if(rsp.page>0){//pokaz przycisk wstecz
			$('#search-button-prev').unbind('click').click(function(){querySearchNth(querySearchLast[0], rsp.page-1,querySearchLast[2])});
			$('#search-button-prev').css('visibility','visible');
		}
		if(rsp.results.length>=maxPerPage){//pokaz przycisk następny
			$('#search-button-next').unbind('click').click(function(){querySearchNth(querySearchLast[0], rsp.page+1,querySearchLast[2])});
			$('#search-button-next').css('visibility','visible');
		}
		
		$('#search-button-curr').html('- '+(rsp.page+1)+' -');
		
		var newul = document.createElement('ul');
		
		for(i=0;i<rsp.results.length;i++){
			///TODO: XSS prevent
			var newli = document.createElement('li');
			$(newli).html('<a href="#maps/'+rsp.results[i].id+'"><b>'+rsp.results[i].title+'</b><br/><small>&nbsp;'+rsp.results[i].desc+'</small></a>');
			$(newul).append(newli);
		}
		$('#search-results').append(newul);
		
		if(rsp.results.length<maxPerPage){
			$('#search-results').append('<p>No more results!</p>');
		}
	}
}


function querySearchNth(search, page, mask){
	querySearchLast = [search, page, mask];
	
	console.log('querySearchNth');
	
	$('#search-results').html('');//wyczyść pole
	$('#search-results').addClass('spinner');//Dodaj wiatraczek
	$('#search-button-prev').css('visibility','hidden');//ukryj przycisk wstecz
	$('#search-button-next').css('visibility','hidden');//ukryj przycisk naprzod
	$('#search-button-curr').val('');
	
	var query = {
		method: 'search-maps',
		keyword: search,
		page: page,
		mask: mask
	};

	sendQuery(query, querySearchCallback);
}

function querySearchRetry(){
	querySearchNth(querySearchLast[0], querySearchLast[1], querySearchLast[2]);
}

function querySearch(){
	$('#search-query').text($('#search-input').val());//Ustaw tekst wyszukiwania
	$('#search-box').removeClass('hidden');
	$('#maps-box').addClass('hidden');
	var mask = ($('#search-titles').is(':checked')?4:0)+($('#search-descriptions').is(':checked')?2:0)+($('#search-tags').is(':checked')?1:0);
	querySearchNth($('#search-input').val(), 0, mask);
}


$(function(){//Initialize
	//Dodaj ładny pasek przewijania
	$('#search-results').slimScroll({
		height: '240px',
		alwaysVisible: true
	});
	$('#search-button').click(querySearch);
	$('#search-input').submit(function(){alert('hh');});
	$('#search-box form').submit(function(){querySearch();return false;});
	
	$('#search-box h2').click(function(){$('#search-box').toggleClass('hidden');});
	
});
