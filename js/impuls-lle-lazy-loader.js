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

document.addEventListener("DOMContentLoaded", function() {
  var lazyObjects = [].slice.call(document.querySelectorAll(".impuls-lle-lazy"));
  lazyObjects.forEach(function(lazyObject) {
		lazyObjectObserver.observe(lazyObject);
  });
});