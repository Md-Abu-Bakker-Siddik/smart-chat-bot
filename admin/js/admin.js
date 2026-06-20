(function () {
	'use strict';

	var container = document.getElementById('mdscw-rules-container');
	var addBtn = document.getElementById('mdscw-add-rule');
	var template = document.getElementById('mdscw-rule-template');

	if (!container || !addBtn || !template) {
		return;
	}

	function getNextIndex() {
		var rows = container.querySelectorAll('.mdscw-rule-row');
		var max = -1;

		rows.forEach(function (row) {
			var index = parseInt(row.getAttribute('data-index'), 10);
			if (!isNaN(index) && index > max) {
				max = index;
			}
		});

		return max + 1;
	}

	function bindRemoveButtons() {
		container.querySelectorAll('.mdscw-remove-rule').forEach(function (btn) {
			btn.removeEventListener('click', handleRemove);
			btn.addEventListener('click', handleRemove);
		});
	}

	function handleRemove(e) {
		var row = e.currentTarget.closest('.mdscw-rule-row');
		if (!row) {
			return;
		}

		var rows = container.querySelectorAll('.mdscw-rule-row');
		if (rows.length <= 1) {
			row.querySelectorAll('input, textarea').forEach(function (field) {
				field.value = '';
			});
			return;
		}

		row.remove();
	}

	addBtn.addEventListener('click', function () {
		var index = getNextIndex();
		var html = template.innerHTML.replace(/__INDEX__/g, String(index));
		var wrapper = document.createElement('div');
		wrapper.innerHTML = html.trim();
		container.appendChild(wrapper.firstElementChild);
		bindRemoveButtons();
	});

	bindRemoveButtons();
})();
