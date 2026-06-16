(function () {
	'use strict';

	if (typeof scbData === 'undefined') {
		return;
	}

	var widget = document.getElementById('scb-widget');
	var toggle = document.getElementById('scb-toggle');
	var closeBtn = document.getElementById('scb-close');
	var windowEl = document.getElementById('scb-window');
	var messages = document.getElementById('scb-messages');
	var form = document.getElementById('scb-form');
	var input = document.getElementById('scb-input');

	if (!widget || !toggle || !windowEl || !messages || !form || !input) {
		return;
	}

	var SESSION_KEY = 'scb_session_id';
	var isOpen = false;
	var hasWelcomed = false;
	var sessionId = '';
	var lastMessageId = 0;
	var pollTimer = null;

	function getSessionId() {
		try {
			return sessionStorage.getItem(SESSION_KEY) || '';
		} catch (e) {
			return '';
		}
	}

	function saveSessionId(id) {
		sessionId = id;
		try {
			sessionStorage.setItem(SESSION_KEY, id);
		} catch (e) {
			// sessionStorage unavailable.
		}
	}

	function escapeHtml(text) {
		var div = document.createElement('div');
		div.textContent = text;
		return div.innerHTML;
	}

	function appendMessage(text, type) {
		var el = document.createElement('div');
		el.className = 'scb-message scb-message-' + type;
		el.innerHTML = escapeHtml(text);
		messages.appendChild(el);
		messages.scrollTop = messages.scrollHeight;
	}

	function showTyping() {
		if (document.getElementById('scb-typing-indicator')) {
			return;
		}
		var el = document.createElement('div');
		el.className = 'scb-message scb-message-bot scb-typing';
		el.id = 'scb-typing-indicator';
		el.innerHTML = '<span></span><span></span><span></span>';
		messages.appendChild(el);
		messages.scrollTop = messages.scrollHeight;
	}

	function hideTyping() {
		var el = document.getElementById('scb-typing-indicator');
		if (el) {
			el.remove();
		}
	}

	function startPolling() {
		stopPolling();
		if (!scbData.liveChat || !sessionId) {
			return;
		}
		pollTimer = setInterval(pollAdminReplies, scbData.pollMs || 4000);
	}

	function stopPolling() {
		if (pollTimer) {
			clearInterval(pollTimer);
			pollTimer = null;
		}
	}

	function pollAdminReplies() {
		if (!sessionId || !isOpen) {
			return;
		}

		var body = new FormData();
		body.append('action', 'scb_poll_messages');
		body.append('nonce', scbData.nonce);
		body.append('session_id', sessionId);
		body.append('since_id', String(lastMessageId));

		fetch(scbData.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			body: body
		})
			.then(function (res) {
				return res.json();
			})
			.then(function (data) {
				if (!data.success || !data.data || !data.data.messages) {
					return;
				}
				data.data.messages.forEach(function (msg) {
					if (msg.id > lastMessageId) {
						lastMessageId = msg.id;
					}
					appendMessage(msg.message, 'admin');
				});
			})
			.catch(function () {
				// Silent fail on poll.
			});
	}

	function openChat() {
		isOpen = true;
		windowEl.hidden = false;
		widget.classList.add('scb-open');
		toggle.setAttribute('aria-expanded', 'true');
		toggle.setAttribute('aria-label', 'Close chat');

		if (!hasWelcomed) {
			appendMessage(scbData.welcome, 'bot');
			hasWelcomed = true;
		}

		input.focus();
		startPolling();
	}

	function closeChat() {
		isOpen = false;
		windowEl.hidden = true;
		widget.classList.remove('scb-open');
		toggle.setAttribute('aria-expanded', 'false');
		toggle.setAttribute('aria-label', 'Open chat');
		stopPolling();
	}

	function toggleChat() {
		if (isOpen) {
			closeChat();
		} else {
			openChat();
		}
	}

	function sendMessage(text) {
		var sendBtn = form.querySelector('.scb-send');
		sendBtn.disabled = true;

		appendMessage(text, 'user');
		showTyping();

		var body = new FormData();
		body.append('action', 'scb_send_message');
		body.append('nonce', scbData.nonce);
		body.append('message', text);
		if (sessionId) {
			body.append('session_id', sessionId);
		}

		fetch(scbData.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			body: body
		})
			.then(function (res) {
				return res.json();
			})
			.then(function (data) {
				hideTyping();
				if (data.success && data.data) {
					if (data.data.session_id) {
						saveSessionId(data.data.session_id);
					}
					if (data.data.reply) {
						appendMessage(data.data.reply, 'bot');
					}
					if (isOpen) {
						startPolling();
					}
				} else {
					var errMsg = (data.data && data.data.message) ? data.data.message : 'Something went wrong. Please try again.';
					appendMessage(errMsg, 'bot');
				}
			})
			.catch(function () {
				hideTyping();
				appendMessage('Unable to connect. Please try again.', 'bot');
			})
			.finally(function () {
				sendBtn.disabled = false;
				input.focus();
			});
	}

	sessionId = getSessionId();

	toggle.addEventListener('click', toggleChat);

	if (closeBtn) {
		closeBtn.addEventListener('click', closeChat);
	}

	form.addEventListener('submit', function (e) {
		e.preventDefault();
		var text = input.value.trim();
		if (!text) {
			return;
		}
		input.value = '';
		sendMessage(text);
	});
})();
