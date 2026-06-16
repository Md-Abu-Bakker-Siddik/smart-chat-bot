(function () {
	'use strict';

	var data = window.scbGoProData || {};
	var activateForm = document.getElementById('scb-license-form');
	var deactivateBtn = document.getElementById('scb-deactivate-license');
	var noticeEl = document.getElementById('scb-license-notice');

	if (!data.ajaxUrl || !data.nonce) {
		return;
	}

	function showNotice(message, type) {
		if (!noticeEl) {
			return;
		}

		noticeEl.textContent = message;
		noticeEl.className = 'scb-license-notice scb-license-notice-' + type;
		noticeEl.hidden = false;
	}

	function postLicense(action, body, onSuccess) {
		var formData = new FormData();
		formData.append('action', action);
		formData.append('nonce', data.nonce);

		Object.keys(body).forEach(function (key) {
			formData.append(key, body[key]);
		});

		return fetch(data.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			body: formData,
		})
			.then(function (response) {
				return response.json();
			})
			.then(function (json) {
				if (json.success) {
					if (typeof onSuccess === 'function') {
						onSuccess(json.data || {});
					} else {
						window.location.reload();
					}
					return;
				}

				var errorMessage =
					(json.data && json.data.message) ||
					data.i18n.errorGeneric ||
					'Something went wrong. Please try again.';
				showNotice(errorMessage, 'error');
			})
			.catch(function () {
				showNotice(data.i18n.errorGeneric || 'Something went wrong. Please try again.', 'error');
			});
	}

	if (activateForm) {
		activateForm.addEventListener('submit', function (e) {
			e.preventDefault();

			var input = document.getElementById('scb-license-key');
			var submitBtn = activateForm.querySelector('button[type="submit"]');
			var key = input ? input.value.trim() : '';

			if (submitBtn) {
				submitBtn.disabled = true;
			}

			postLicense(
				'scb_activate_license',
				{ license_key: key },
				function () {
					window.location.reload();
				}
			).finally(function () {
				if (submitBtn) {
					submitBtn.disabled = false;
				}
			});
		});
	}

	if (deactivateBtn) {
		deactivateBtn.addEventListener('click', function (e) {
			e.preventDefault();

			if (!window.confirm(data.i18n.deactivateConfirm)) {
				return;
			}

			deactivateBtn.disabled = true;

			postLicense('scb_deactivate_license', {}, function () {
				window.location.reload();
			});
		});
	}
})();
