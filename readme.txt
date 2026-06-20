=== Siddik Chat Widget ===
Contributors: mdabubakkersiddik
Tags: chat, chatbot, live chat, customer support, faq
Requires at least: 6.0
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.0.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A lightweight rule-based chat bot widget with omnichannel routing for your WordPress site.

== Description ==

Siddik Chat Widget adds a modern, floating chat widget to your WordPress site so visitors can get instant answers to common questions.

**Free features included:**

* **Floating chat widget** – Modern bubble UI with customizable color and position.
* **Rule-based replies** – Define keyword triggers and automatic responses (great for FAQs).
* **Omnichannel routing** – Route visitors to Live Chat, WhatsApp, Messenger, or Telegram.
* **FAQ quick replies** – Optional button labels for common questions.
* **Fallback message** – Custom message when no rule matches.
* **Typing indicator** – Smooth UX while the bot is responding.
* **Fully translatable** – Ready for localization.

**Upgrade to PRO** for live admin inbox, message storage, real-time replies, and OpenAI integration. The separate **Siddik Chat Widget PRO** add-on may be offered as a free early-access download from the author. See **Siddik Chat Widget → Go PRO** in your dashboard.

**How it works**

1. Install and activate the plugin.
2. Go to **Siddik Chat Widget → Settings** to configure the widget, response rules, and optional messenger links.
3. Visitors chat from the frontend widget and receive instant keyword-based replies.

**Privacy**

The free plugin does not store chat messages on your server. Messages are processed per request for rule matching only. Message storage is available in the PRO add-on.

== Installation ==

1. Upload the `siddik-chat-widget` folder to `/wp-content/plugins/`, or install through the **Plugins** screen.
2. Activate the plugin.
3. Navigate to **Siddik Chat Widget → Settings** to configure your bot.
4. Visit your site's frontend to see the chat widget.

== Frequently Asked Questions ==

= Does this plugin require OpenAI? =

No. The free plugin uses keyword-based rules and a fallback message. OpenAI is available in the separate **Siddik Chat Widget PRO** add-on.

= Can I connect WhatsApp, Messenger, or Telegram? =

Yes. In **Siddik Chat Widget → Settings → Omnichannel Settings**, enable each channel and add your messenger link (for example, a `wa.me`, `m.me`, or `t.me` URL).

= Can I reply to visitors manually? =

Live admin replies are a PRO feature. Install **Siddik Chat Widget PRO** or visit **Siddik Chat Widget → Go PRO**.

= Is the chat widget mobile-friendly? =

Yes. The widget is fully responsive.

= Does the free plugin store chat history? =

No. The free version does not save messages to the database.

= What user capability is required? =

Users with `manage_options` (typically Administrators) can access settings.

== Screenshots ==

1. Frontend chat widget with channel selector.
2. Settings page with response rules configuration.
3. Omnichannel settings for WhatsApp, Messenger, and Telegram.
4. Go PRO upgrade page with feature comparison.

== Changelog ==

= 1.0.4 =
* Renamed plugin to Siddik Chat Widget (shorter distinctive name and slug).

= 1.0.3 =
* Replaced generic scb prefix with mdscw across all declarations, globals, and stored data.
* Removed load_plugin_textdomain() (WordPress.org handles translations automatically).

= 1.0.2 =
* Go PRO page supports free early-access messaging for the separate PRO add-on.
* Settings notice updated for early-access period.
* Tested up to WordPress 7.0.

= 1.0.1 =
* Human live chat extension API for PRO add-ons (`mdscw_session_human_takeover`).
* Improved channel tab layout on the frontend widget.
* Close button and chat window visibility fixes.

= 1.0.0 =
* Initial WordPress.org release.
* Floating chat widget with customizable appearance.
* Keyword-based response rules with FAQ quick replies.
* Omnichannel routing (Live Chat, WhatsApp, Messenger, Telegram).
* Go PRO upgrade page.
* Extension API for Siddik Chat Widget PRO add-on.

== Upgrade Notice ==

= 1.0.4 =
Renamed to Siddik Chat Widget. Upload this version and reactivate.

= 1.0.3 =
Renamed for WordPress.org guidelines with mdscw prefix update.

= 1.0.2 =
Go PRO page now highlights free early access for the separate PRO add-on.

= 1.0.1 =
Improved live chat extension API and frontend widget fixes.

= 1.0.0 =
Initial release of Siddik Chat Widget.
