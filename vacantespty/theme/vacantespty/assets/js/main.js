(function () {
	var toggle = document.querySelector('.nav-toggle');
	var nav = document.getElementById('site-nav');
	if (!toggle || !nav) return;
	toggle.addEventListener('click', function () {
		var open = toggle.getAttribute('aria-expanded') === 'true';
		toggle.setAttribute('aria-expanded', String(!open));
		nav.classList.toggle('is-open', !open);
		document.body.classList.toggle('nav-open', !open);
	});
})();
