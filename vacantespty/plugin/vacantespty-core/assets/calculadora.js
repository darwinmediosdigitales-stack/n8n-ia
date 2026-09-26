(function () {
	var CSS_RATE = 0.0975;
	var SE_RATE = 0.0125;
	var CSS_DECIMO_RATE = 0.0725;

	// ISR is computed on projected annual income (13 payments incl. décimo) and prorated monthly.
	function isrAnual(renta) {
		if (renta <= 11000) return 0;
		if (renta <= 50000) return (renta - 11000) * 0.15;
		return 5850 + (renta - 50000) * 0.25;
	}

	function money(n) {
		return 'B/. ' + n.toLocaleString('es-PA', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
	}

	document.querySelectorAll('[data-vpty-calc]').forEach(function (calc) {
		function out(key, value) {
			calc.querySelector('[data-out="' + key + '"]').textContent = value;
		}

		calc.querySelectorAll('[data-tab]').forEach(function (tab) {
			tab.addEventListener('click', function () {
				calc.querySelectorAll('[data-tab],[data-panel]').forEach(function (el) {
					el.classList.remove('is-active');
				});
				tab.classList.add('is-active');
				calc.querySelector('[data-panel="' + tab.dataset.tab + '"]').classList.add('is-active');
			});
		});

		calc.querySelector('[data-in="bruto"]').addEventListener('input', function (e) {
			var bruto = parseFloat(e.target.value) || 0;
			var css = bruto * CSS_RATE;
			var se = bruto * SE_RATE;
			var isr = isrAnual(bruto * 13) / 13;
			var neto = bruto - css - se - isr;
			out('css', '- ' + money(css));
			out('se', '- ' + money(se));
			out('isr', '- ' + money(isr));
			out('neto', money(neto));
			out('quincena', money(neto / 2));
		});

		calc.querySelector('[data-in="periodo"]').addEventListener('input', function (e) {
			var total = parseFloat(e.target.value) || 0;
			var bruto = total / 12;
			var css = bruto * CSS_DECIMO_RATE;
			out('dbruto', money(bruto));
			out('dcss', '- ' + money(css));
			out('dneto', money(bruto - css));
		});
	});
})();
