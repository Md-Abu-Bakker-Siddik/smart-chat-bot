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
	var channelBar = document.getElementById('scb-channel-bar');

	if (!widget || !toggle || !windowEl || !messages || !form || !input) {
		return;
	}

	var SESSION_KEY = 'scb_session_id';
	var CHANNEL_KEY = 'scb_current_channel';
	var CHANNEL_SELECTED_KEY = 'scb_channel_selected';
	var HUMAN_TAKEOVER_PREFIX = 'scb_human_takeover_';

	var current_channel = 'live_chat';
	var isOpen = false;
	var hasInitialized = false;
	var sessionId = '';
	var lastMessageId = 0;
	var pollTimer = null;
	var humanTakeover = false;

	function getStorage(key) {
		try {
			return sessionStorage.getItem(key) || '';
		} catch (e) {
			return '';
		}
	}

	function setStorage(key, value) {
		try {
			sessionStorage.setItem(key, value);
		} catch (e) {
			// sessionStorage unavailable.
		}
	}

	function escapeHtml(text) {
		var div = document.createElement('div');
		div.textContent = text;
		return div.innerHTML;
	}

	function isExternalChannel(channel) {
		return channel === 'whatsapp' || channel === 'messenger' || channel === 'telegram';
	}

	function getHumanTakeoverKey(id) {
		return HUMAN_TAKEOVER_PREFIX + (id || sessionId || '');
	}

	function loadHumanTakeoverState() {
		if (!sessionId) {
			humanTakeover = false;
			return;
		}
		humanTakeover = getStorage(getHumanTakeoverKey(sessionId)) === '1';
	}

	function setHumanTakeover(active) {
		humanTakeover = !!active;
		if (!sessionId) {
			return;
		}
		setStorage(getHumanTakeoverKey(sessionId), humanTakeover ? '1' : '0');
		updateInputState();
	}

	function isLiveHumanChat() {
		return !!(scbData.liveChat && current_channel === 'live_chat' && humanTakeover);
	}

	function getPollInterval() {
		if (isLiveHumanChat()) {
			return scbData.humanPollMs || 2000;
		}
		return scbData.pollMs || 4000;
	}

	function getEnabledChannels() {
		var list = [];
		if (!scbData.channels) {
			return list;
		}
		Object.keys(scbData.channels).forEach(function (slug) {
			if (scbData.channels[slug].enabled) {
				list.push(slug);
			}
		});
		return list;
	}

	function hasMultipleChannels() {
		return getEnabledChannels().length > 1;
	}

	function updateChannelUI() {
		if (!channelBar) {
			return;
		}
		channelBar.querySelectorAll('.scb-channel-btn').forEach(function (btn) {
			var active = btn.getAttribute('data-channel') === current_channel;
			btn.classList.toggle('is-active', active);
			btn.setAttribute('aria-pressed', active ? 'true' : 'false');
		});
	}

	function setChannel(channel) {
		if (!scbData.channels || !scbData.channels[channel] || !scbData.channels[channel].enabled) {
			channel = 'live_chat';
		}
		current_channel = channel;
		setStorage(CHANNEL_KEY, channel);
		updateChannelUI();
		updateInputState();
	}

	function updateInputState() {
		var external = isExternalChannel(current_channel);
		input.disabled = false;
		form.querySelector('.scb-send').disabled = false;
		if (external) {
			input.placeholder = scbData.placeholder || 'Ask a question…';
		} else if (isLiveHumanChat()) {
			input.placeholder = scbData.i18n.liveChatPlaceholder || scbData.placeholder || 'Type your message…';
		} else {
			input.placeholder = scbData.placeholder || '';
		}
	}

	function appendMessage(text, type) {
		var el = document.createElement('div');
		el.className = 'scb-message scb-message-' + type;
		el.innerHTML = escapeHtml(text);
		messages.appendChild(el);
		messages.scrollTop = messages.scrollHeight;
		return el;
	}

	function appendBotMessage(text, cta) {
		var wrap = document.createElement('div');
		wrap.className = 'scb-message-wrap';

		var el = document.createElement('div');
		el.className = 'scb-message scb-message-bot';
		el.innerHTML = escapeHtml(text);
		wrap.appendChild(el);

		if (cta && cta.url && cta.label) {
			var btn = document.createElement('a');
			btn.className = 'scb-channel-cta scb-channel-cta-' + current_channel;
			btn.href = cta.url;
			btn.target = '_blank';
			btn.rel = 'noopener noreferrer';
			btn.textContent = cta.label;
			wrap.appendChild(btn);
		}

		messages.appendChild(wrap);
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

	function renderChannelSelector() {
		var wrap = document.createElement('div');
		wrap.className = 'scb-channel-selector';

		var prompt = document.createElement('p');
		prompt.className = 'scb-channel-prompt';
		prompt.textContent = scbData.channelPrompt || 'How would you like to connect with us today?';
		wrap.appendChild(prompt);

		var pills = document.createElement('div');
		pills.className = 'scb-channel-pills';

		getEnabledChannels().forEach(function (slug) {
			var ch = scbData.channels[slug];
			var btn = document.createElement('button');
			btn.type = 'button';
			btn.className = 'scb-channel-pill';
			btn.setAttribute('data-channel', slug);
			btn.innerHTML = '<span class="scb-pill-icon">' + escapeHtml(ch.icon) + '</span> ' + escapeHtml(ch.label);
			btn.addEventListener('click', function () {
				setChannel(slug);
				setStorage(CHANNEL_SELECTED_KEY, '1');
				wrap.remove();
				showWelcomeFlow();
			});
			pills.appendChild(btn);
		});

		wrap.appendChild(pills);
		messages.appendChild(wrap);
		messages.scrollTop = messages.scrollHeight;
	}

	function renderFaqButtons() {
		if (!scbData.faqs || !scbData.faqs.length) {
			return;
		}

		var wrap = document.createElement('div');
		wrap.className = 'scb-faq-buttons';

		scbData.faqs.forEach(function (faq) {
			var btn = document.createElement('button');
			btn.type = 'button';
			btn.className = 'scb-faq-btn';
			btn.textContent = faq.label;
			btn.addEventListener('click', function () {
				sendMessage(faq.message);
			});
			wrap.appendChild(btn);
		});

		messages.appendChild(wrap);
		messages.scrollTop = messages.scrollHeight;
	}

	function showWelcomeFlow() {
		appendMessage(scbData.welcome, 'bot');
		renderFaqButtons();
		hasInitialized = true;
	}

	function initializeChatContent() {
		messages.innerHTML = '';

		var channelWasSelected = getStorage(CHANNEL_SELECTED_KEY) === '1';
		var savedChannel = getStorage(CHANNEL_KEY);

		if (savedChannel && scbData.channels[savedChannel] && scbData.channels[savedChannel].enabled) {
			current_channel = savedChannel;
		}

		updateChannelUI();

		if (!channelWasSelected && hasMultipleChannels()) {
			renderChannelSelector();
			return;
		}

		if (!channelWasSelected) {
			setStorage(CHANNEL_SELECTED_KEY, '1');
		}

		showWelcomeFlow();
	}

	function startPolling() {
		stopPolling();
		if (!scbData.liveChat || !sessionId || !isOpen || current_channel !== 'live_chat') {
			return;
		}
		pollTimer = setInterval(pollAdminReplies, getPollInterval());
	}

	function stopPolling() {
		if (pollTimer) {
			clearInterval(pollTimer);
			pollTimer = null;
		}
	}

	function pollAdminReplies() {
		if (!sessionId || !isOpen || current_channel !== 'live_chat') {
			return;
		}

		var body = new FormData();
		body.append('action', 'scb_poll_messages');
		body.append('nonce', scbData.nonce);
		body.append('session_id', sessionId);
		body.append('since_id', String(lastMessageId));

		fetch(scbData.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: body })
			.then(function (res) { return res.json(); })
			.then(function (data) {
				if (!data.success || !data.data) {
					return;
				}
				if (typeof data.data.human_takeover === 'boolean') {
					setHumanTakeover(data.data.human_takeover);
				}
				if (data.data.messages && data.data.messages.length) {
					setHumanTakeover(true);
					data.data.messages.forEach(function (msg) {
						if (msg.id > lastMessageId) {
							lastMessageId = msg.id;
						}
						appendMessage(msg.message, 'admin');
					});
				}
			})
			.catch(function () {});
	}

	function openChat() {
		isOpen = true;
		windowEl.hidden = false;
		windowEl.classList.remove('scb-closed');
		widget.classList.add('scb-open');
		toggle.setAttribute('aria-expanded', 'true');
		toggle.setAttribute('aria-label', scbData.i18n.closeChat || 'Close chat');

		if (!hasInitialized) {
			initializeChatContent();
		}

		input.focus();
		startPolling();
		if (sessionId && scbData.liveChat && current_channel === 'live_chat') {
			pollAdminReplies();
		}
	}

	function closeChat(e) {
		if (e) {
			e.preventDefault();
			e.stopPropagation();
		}
		isOpen = false;
		windowEl.hidden = true;
		windowEl.classList.add('scb-closed');
		widget.classList.remove('scb-open');
		toggle.setAttribute('aria-expanded', 'false');
		toggle.setAttribute('aria-label', scbData.i18n.openChat || 'Open chat');
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
		var liveHuman = isLiveHumanChat();

		appendMessage(text, 'user');

		if (!liveHuman) {
			showTyping();
			sendBtn.disabled = true;
		} else {
			hideTyping();
		}

		var body = new FormData();
		body.append('action', 'scb_send_message');
		body.append('nonce', scbData.nonce);
		body.append('message', text);
		body.append('current_channel', current_channel);
		if (sessionId) {
			body.append('session_id', sessionId);
		}

		fetch(scbData.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: body })
			.then(function (res) { return res.json(); })
			.then(function (data) {
				hideTyping();
				if (data.success && data.data) {
					if (data.data.session_id) {
						sessionId = data.data.session_id;
						setStorage(SESSION_KEY, sessionId);
						loadHumanTakeoverState();
					}
					if (data.data.human_takeover) {
						setHumanTakeover(true);
					} else if (data.data.human_takeover === false) {
						setHumanTakeover(false);
					}
					if (data.data.reply) {
						if (isExternalChannel(current_channel) && data.data.cta) {
							appendBotMessage(data.data.reply, data.data.cta);
						} else {
							appendMessage(data.data.reply, 'bot');
						}
					}
					if (isOpen && current_channel === 'live_chat') {
						startPolling();
					}
				} else if (!liveHuman) {
					var errMsg = (data.data && data.data.message) ? data.data.message : (scbData.i18n.errorGeneric || 'Something went wrong.');
					appendMessage(errMsg, 'bot');
				}
			})
			.catch(function () {
				hideTyping();
				if (!liveHuman) {
					appendMessage(scbData.i18n.errorNetwork || 'Unable to connect.', 'bot');
				}
			})
			.finally(function () {
				sendBtn.disabled = false;
				input.focus();
			});
	}

	sessionId = getStorage(SESSION_KEY);
	loadHumanTakeoverState();
	var savedChannel = getStorage(CHANNEL_KEY);
	if (savedChannel) {
		current_channel = savedChannel;
	}

	if (channelBar) {
		channelBar.querySelectorAll('.scb-channel-btn').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var channel = btn.getAttribute('data-channel');
				setChannel(channel);
				setStorage(CHANNEL_SELECTED_KEY, '1');
				stopPolling();
				startPolling();
			});
		});
	}

	toggle.addEventListener('click', toggleChat);

	if (closeBtn) {
		closeBtn.addEventListener('click', closeChat);
	}

	/* Fallback: event delegation in case theme blocks direct binding */
	widget.addEventListener('click', function (e) {
		if (e.target.closest('#scb-close')) {
			closeChat(e);
		}
	});

	form.addEventListener('submit', function (e) {
		e.preventDefault();
		var text = input.value.trim();
		if (!text) {
			return;
		}
		input.value = '';
		sendMessage(text);
	});

	updateChannelUI();
	updateInputState();
})();
