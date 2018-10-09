Element.prototype.remove = function() {
    this.parentElement.removeChild(this);
}
NodeList.prototype.remove = HTMLCollection.prototype.remove = function() {
    for(var i = this.length - 1; i >= 0; i--) {
        if(this[i] && this[i].parentElement) {
            this[i].parentElement.removeChild(this[i]);
        }
    }
}
function fadeOut(elem) {
	if(!elem.style.opacity)
		elem.style.opacity = 1;
	var outInterval = setInterval(function() {
		elem.style.opacity -= 0.02;
		if( elem.style.opacity <= 0 ) {
			elem.remove();
			clearInterval(outInterval);
		}
	}, 10);
}
function hidePreloader() {
	fadeOut(document.getElementById("preload"));
}
function setBackground() {
	if (typeof(page) != "undefined") {
		var img = new Image();
		img.onload = function() {
			document.getElementsByTagName("html")[0].style["backgroundImage"] = "url(" + this.src + ")";
			hidePreloader();
		}
		if (page == "home") {
			img.src="/static/images/login.jpg";
		}
	}
}

document.addEventListener("DOMContentLoaded", function() {
	setBackground();
	createEvents();
});
function createEvents() {
	if ( typeof(document.getElementById("loginButton")) == "object" ) {
		document.getElementById("loginButton").addEventListener("click", function() {
			document.location.href = "/login";
		});
	}
}