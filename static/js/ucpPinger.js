var interval = 5000;
$(document).ready(function() {
	pinger();
	page = location.pathname;
	$(window).on("popstate", function() {
		if(page != location.pathname)
			openSection(location.pathname, true);
	});
});
function pinger() {
	$.ajax({
		type: 'GET',
		url: '/pinger',
		dataType: 'json',
		async: true,
		success: function(data) {
			parseIncomingData(data);
		},
		complete: function(data) {
			setTimeout(pinger, interval);
		}
	});
}
function openMail(id) {
	$.ajax({
		type: "GET",
		url: page + "/" + id + "?ajax",
		async: true,
		success: function(data) {
			window.history.pushState("","", page + "/" + id);
			$('#message-body').html(data);
		}
	});
}
function parseIncomingData(data) {
	for (var k in data) {
		if(data.hasOwnProperty(k)) {  //we got update
			if(k == "new_mail")
				$('#mail_button').addClass("blinkAnimation");
			
			$('#'+k).html(data[k]);
		}
	}
}