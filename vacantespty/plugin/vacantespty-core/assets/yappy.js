(function () {
	var cfg = window.vptyYappy;
	var box = document.querySelector('[data-vpty-checkout]');
	if (!cfg || !box) return;

	// Official Yappy web component (Botón de Pago v2).
	var s = document.createElement('script');
	s.type = 'module';
	s.src = 'https://bt-cdn.yappy.cloud/v1/cdn/web-component-btn-yappy.js';
	document.head.appendChild(s);

	var form = box.querySelector('[data-checkout-form]');
	var msg = box.querySelector('[data-checkout-msg]');
	var done = box.querySelector('[data-checkout-done]');
	var btn = box.querySelector('btn-yappy');
	var current = null;

	function waLink(text) {
		return 'https://wa.me/' + cfg.whatsapp + '?text=' + encodeURIComponent(text);
	}

	document.querySelectorAll('[data-plan]').forEach(function (b) {
		b.addEventListener('click', function () {
			current = { key: b.dataset.plan, name: b.dataset.planName, price: b.dataset.planPrice };
			form.plan.value = current.key;
			box.querySelector('[data-checkout-title]').textContent = 'Plan ' + current.name + ' — B/. ' + current.price;
			form.hidden = false;
			done.hidden = true;
			msg.textContent = '';
			box.hidden = false;
			box.scrollIntoView({ behavior: 'smooth', block: 'center' });
		});
	});

	box.querySelector('[data-checkout-close]').addEventListener('click', function () {
		box.hidden = true;
	});

	function finish(ok, orderId) {
		form.hidden = true;
		done.hidden = false;
		box.querySelector('[data-checkout-done-msg]').textContent = ok ? cfg.msgOk : cfg.msgError;
		var text = ok
			? 'Hola Vacantes PTY 👋 Ya pagué el Plan ' + current.name + ' (B/. ' + current.price + ') por Yappy. Pedido: ' + orderId + '. Te envío mi currículum actual.'
			: 'Hola Vacantes PTY 👋 Quiero el Plan ' + current.name + ' pero tuve un problema pagando con Yappy.';
		box.querySelector('[data-checkout-wa]').href = waLink(text);
		if (ok && window.vptyTrack) window.vptyTrack('cv_purchase', { plan: current.key, value: parseFloat(current.price), currency: 'USD' });
	}

	var lastOrder = '';
	btn.addEventListener('eventClick', function () {
		msg.textContent = '';
		if (!form.nombre.value.trim() || form.alias.value.replace(/\D/g, '').length < 8) {
			msg.textContent = 'Escribe tu nombre y tu número de Yappy (8 dígitos).';
			return;
		}
		var body = new FormData(form);
		fetch(cfg.endpoint, { method: 'POST', body: body })
			.then(function (r) { return r.json(); })
			.then(function (res) {
				if (!res.ok) { msg.textContent = res.message || cfg.msgError; return; }
				lastOrder = res.orderId;
				btn.eventPayment({ transactionId: res.transactionId, documentName: res.documentName, token: res.token });
			})
			.catch(function () { msg.textContent = cfg.msgError; });
	});
	btn.addEventListener('eventSuccess', function () { finish(true, lastOrder); });
	btn.addEventListener('eventError', function () { finish(false, lastOrder); });
})();
