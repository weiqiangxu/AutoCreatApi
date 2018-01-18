function searchtxt()
{
	var keyword = $.trim($("#keyword").val());
	var html = '';
	if(keyword != '')
	{
		for (var i = message.length - 1; i >= 0; i--) {
			var up_m = message[i]['class'].toUpperCase();
			var up_k = keyword.toUpperCase();
			if(up_m.search(up_k)!=-1){
				html+='<div class="search_row"><a href="'+message[i]['class']+'">'+message[i]['class_name']+'</a></div>';
			}
		};
	}
	$("#search_res").html(html);
}

$("#keyword").keyup(function(){
	searchtxt();
});