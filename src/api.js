
/**
 * Send object as a query
 * 
 * @return {object} object representing result of query
 * 
 */
function sendQuery(query, callback){
	console.log('Query: ');
	console.debug(query);
	
	var xmlRequest = $.ajax({
		url: "query.php",
		type: "POST",
		data: query,
	}).done(function(result){
		console.debug(result);
		
		callback(JSON.parse(result));
	}).fail(function(){
		callback({status: "FAIL", error: "Request Failed"});
	});
}
