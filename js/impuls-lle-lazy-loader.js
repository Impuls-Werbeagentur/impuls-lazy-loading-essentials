let lazyObjectObserver = new IntersectionObserver(function(entries, observer) {
		entries.forEach(function(entry) {
			if (entry.isIntersecting) {
				let lazyObject = entry.target;
				console.log(lazyObject.tagName.toLowerCase());
				switch (lazyObject.tagName.toLowerCase()){
					case "img":
					case "iframe":
						if(!(lazyObject.dataset.lazysrc == '')){
							lazyObject.src = lazyObject.dataset.lazysrc;
							lazyObject.classList.remove("impuls-lle-lazy");
							lazyObject.dataset.lazysrc = '';
							lazyObjectObserver.unobserve(lazyObject);
						}
						break;
					default:
						if(!(lazyObject.dataset.lazysrc == '')){
							bgsrc = lazyObject.dataset.lazysrc;
							lazyObject.style.backgroundImage = 'url('+bgsrc+')';
							lazyObject.classList.remove("impuls-lle-lazy");
							lazyObject.dataset.lazysrc = '';
							lazyObjectObserver.unobserve(lazyObject);
						}
						break;
				}
			}
		});
	},{ rootMargin: "0px 0px 0px 0px" });

let lazyGMapsObserver = new IntersectionObserver(function(entries, observer) {
	entries.forEach(function(entry) {
		if (entry.isIntersecting) {
			let lazyGMapContainer = entry.target;
			apikey = lazyGMapContainer.dataset.lazygmapsapikey;
			callback = lazyGMapContainer.dataset.lazygmapscallback;
			var script = document.createElement('script');
			script.setAttribute('src', 'https://maps.googleapis.com/maps/api/js?key='+apikey+'&callback='+callback);
			document.head.appendChild(script);
			lazyGMapsObserver.unobserve(lazyGMapContainer);
		}
	});
},{
	rootMargin: "0px 0px 0px 0px"
});


document.addEventListener("DOMContentLoaded", function() {
  var lazyObjects = [].slice.call(document.querySelectorAll(".impuls-lle-lazy"));
  lazyObjects.forEach(function(lazyObject) {
		lazyObjectObserver.observe(lazyObject);
  });
	var lazyGMapsObjects = [].slice.call(document.querySelectorAll(".impuls-lle-lazy-gmap"));
  lazyGMapsObjects.forEach(function(lazyGMapsObject) {
		lazyGMapsObserver.observe(lazyGMapsObject);
  });
});
