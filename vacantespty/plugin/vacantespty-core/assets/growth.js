(function () {
	var cfg = window.vptyGrowth || {};
	var store = {
		get: function (k) { try { return localStorage.getItem(k); } catch (e) { return null; } },
		set: function (k, v) { try { localStorage.setItem(k, v); } catch (e) {} }
	};

	// A/B variant per visitor, reused for every capture point.
	var variant = store.get('vpty_ab');
	if (variant !== 'a' && variant !== 'b') {
		variant = Math.random() < 0.5 ? 'a' : 'b';
		store.set('vpty_ab', variant);
	}
	document.querySelectorAll('[data-ab-' + variant + ']').forEach(function (el) {
		el.textContent = el.getAttribute('data-ab-' + variant);
	});

	// GA4 events (works with the plugin's gtag or with Site Kit).
	function track(name, params) {
		params = params || {};
		params.variante = variant;
		if (typeof window.gtag === 'function') window.gtag('event', name, params);
		else if (window.dataLayer) window.dataLayer.push(Object.assign({ event: name }, params));
	}
	window.vptyTrack = track;

	document.addEventListener('click', function (e) {
		var el = e.target.closest('[data-vpty-track]');
		if (!el) return;
		var params = { punto: el.getAttribute('data-vpty-point') || '' };
		var program = el.getAttribute('data-vpty-program');
		if (program) params.programa = program;
		track(el.getAttribute('data-vpty-track'), params);
	});

	// Lead forms.
	var registered = store.get('vpty_lead') === '1';
	document.querySelectorAll('[data-vpty-lead]').forEach(function (form) {
		form.ts.value = String(Date.now());
		form.variante.value = variant;
		var msg = form.querySelector('.vpty-lead__msg');
		form.addEventListener('submit', function (e) {
			e.preventDefault();
			if (!form.checkValidity()) {
				form.reportValidity();
				return;
			}
			if (form.querySelectorAll('input[name="categorias[]"]:checked').length === 0 && !form.querySelector('select[name="categorias[]"]')) {
				msg.textContent = 'Elige al menos un área de interés.';
				return;
			}
			var button = form.querySelector('button[type="submit"]');
			button.disabled = true;
			msg.textContent = '';
			fetch(cfg.endpoint, { method: 'POST', body: new FormData(form) })
				.then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d }; }); })
				.then(function (res) {
					if (!res.ok || !res.data.ok) throw new Error(res.data.message || cfg.error);
					store.set('vpty_lead', '1');
					track('generate_lead', { punto: form.getAttribute('data-point') });
					window.location.href = cfg.thanks + '?punto=' + encodeURIComponent(form.getAttribute('data-point'));
				})
				.catch(function (err) {
					msg.textContent = err.message || cfg.error;
					button.disabled = false;
				});
		});
	});

	// Share bar: native share (includes Instagram on phones) and copy link.
	document.querySelectorAll('[data-vpty-share]').forEach(function (bar) {
		var url = bar.getAttribute('data-url');
		var title = bar.getAttribute('data-title');
		var native = bar.querySelector('[data-share-native]');
		if (navigator.share && native) {
			native.hidden = false;
			native.addEventListener('click', function () {
				track('share', { punto: 'nativo' });
				navigator.share({ title: title, text: title, url: url }).catch(function () {});
			});
		}
		var copy = bar.querySelector('[data-share-copy]');
		if (copy) {
			copy.addEventListener('click', function () {
				var label = copy.querySelector('span');
				var done = function () {
					var old = label.textContent;
					label.textContent = cfg.copied;
					setTimeout(function () { label.textContent = old; }, 2000);
				};
				track('share', { punto: 'copiar' });
				if (navigator.clipboard) navigator.clipboard.writeText(url).then(done);
				else { window.prompt('', url); }
			});
		}
	});

	// Exit-intent popup: max once every 7 days, never for registered visitors.
	var popup = document.querySelector('[data-vpty-popup]');
	if (!popup || registered) return;
	var last = parseInt(store.get('vpty_popup_at') || '0', 10);
	if (Date.now() - last < 7 * 24 * 3600 * 1000) return;

	var shown = false;
	function open() {
		if (shown) return;
		shown = true;
		store.set('vpty_popup_at', String(Date.now()));
		popup.hidden = false;
		document.body.classList.add('vpty-popup-open');
		track('popup_view', { punto: 'popup' });
	}
	function close() {
		popup.hidden = true;
		document.body.classList.remove('vpty-popup-open');
	}
	popup.querySelectorAll('[data-popup-close]').forEach(function (el) { el.addEventListener('click', close); });
	document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });

	var isTouch = window.matchMedia('(hover: none)').matches;
	if (!isTouch) {
		document.addEventListener('mouseout', function (e) {
			if (!e.relatedTarget && e.clientY <= 5) open();
		});
	} else {
		// Mobile: after 25 s on the page or once past 60% of the scroll.
		setTimeout(open, 25000);
		window.addEventListener('scroll', function () {
			var h = document.documentElement;
			if ((h.scrollTop + window.innerHeight) / h.scrollHeight > 0.6) open();
		}, { passive: true });
	}
})();
