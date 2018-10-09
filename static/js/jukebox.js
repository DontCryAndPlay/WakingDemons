var jukebox;
var clientId = "fDoItMDbsbZz8dY16ZzARCZmzgHBPotA";
var playlist = "1996089";
function showJukebox() {
	if($("#jukebox-main").length == 0) return false;
	if(typeof jukebox == "undefined") {
		$.ajax({
			type: "GET",
			url: "https://api.soundcloud.com/playlists/" + playlist +"?client_id=" + clientId + "&limit=100&offset=0",
			async: true,
			success: function(data) {
				jukebox = data;
				addJukebox();
			}
		});
	} else 
		addJukebox();
}

function addJukebox() {
	for(var i=0;i<jukebox.track_count;i++) {
		var title = jukebox.tracks[i].title.replace("EVE Online - ", "");
		var id = jukebox.tracks[i].id;
		var seconds = parseInt((jukebox.tracks[i].duration / 1000) % 60);
		var minutes = parseInt(((jukebox.tracks[i].duration / (1000 * 60)) % 60));
		if(seconds < 10)
			seconds = "0" + seconds;
		var duration = minutes + ":" + seconds;
		var row = '<tr class="mouseOverEffect" onclick="jukeboxPlay(\'' + id + '\')"><td>' + (i+1) + '</td><td>' + title + '</td><td>' + duration + '</td></tr>';
		$('#jukebox-main table tbody').append(row);
	}
	return true;
}
function jukeboxPlay(id=0) {
	if(id == 0) return false;
	var trackuri = "https://api.soundcloud.com/tracks/" + id + ".json?client_id=" + clientId;
	$.get(trackuri).then(function(result) {
		$("#jukebox").attr('src', result.stream_url + "?client_id=" + clientId);
		$("#jukebox")[0].play();
	});
}