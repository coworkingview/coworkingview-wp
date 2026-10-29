=== CoworkingView – Coworking Spaces & Office Prices ===
Contributors: coworkingview
Tags: coworking, office space, real estate, prices, shortcode
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Show coworking spaces, private offices and market rates for a city, with the prices operators publish. Two shortcodes, no API key.

== Description ==

[CoworkingView](https://coworkingview.com) compares coworking spaces, private offices, flexible desks and meeting rooms across Europe and beyond, with the prices operators publish. This plugin puts that catalogue on your site with two shortcodes.

**List spaces in a city**

`[coworkingview_spaces city="berlin" type="private_office" limit="5"]`

Shows each space with its district and entry price, linked to its page with the full offer. Useful for relocation guides, city blogs, startup and expat sites, real estate agencies and event venues.

**Show what workspace costs**

`[coworkingview_rates city="madrid"]`

A table of median prices per workspace type (private office, fixed desk, flex desk, day pass, meeting room…), with how many spaces each figure comes from. These are the figures behind the [Coworking Price Index](https://coworkingview.com/en/tools/price-index).

**Cities**

Coverage includes [Berlin](https://coworkingview.com/en/coworking/berlin), [Munich](https://coworkingview.com/en/coworking/munich), [Hamburg](https://coworkingview.com/en/coworking/hamburg), [Frankfurt](https://coworkingview.com/en/coworking/frankfurt), [Madrid](https://coworkingview.com/en/coworking/madrid), [Barcelona](https://coworkingview.com/en/coworking/barcelona), [Málaga](https://coworkingview.com/en/coworking/malaga), [Valencia](https://coworkingview.com/en/coworking/valencia), [London](https://coworkingview.com/en/coworking/london), [Manchester](https://coworkingview.com/en/coworking/manchester), [Amsterdam](https://coworkingview.com/en/coworking/amsterdam), [Dubai](https://coworkingview.com/en/coworking/dubai) and more than 60 other cities. Use the city's slug, as it appears in its coworkingview.com address.

**Features**

* No API key, no account, no settings needed to start.
* Responses are cached (12 hours by default) so your pages stay fast.
* English, German and Spanish.
* Light markup with a small stylesheet that inherits your theme's fonts and colours.
* Nothing is shown to visitors when the data is unavailable; editors see why.

== Installation ==

1. Install from **Plugins → Add New**, searching for "CoworkingView", or upload the plugin folder to `/wp-content/plugins/`.
2. Activate it.
3. Add a shortcode to any post, page or widget, for example `[coworkingview_spaces city="london" limit="5"]`. In the block editor, use the Shortcode block.
4. Optional: **Settings → CoworkingView** sets the default language, the cache lifetime and the credit line.

== Frequently Asked Questions ==

= Which attributes do the shortcodes take? =

`[coworkingview_spaces]`: `city` (required), `type` (`private_office`, `fix_desk`, `flex_desk`, `day_pass`, `meeting_room`, `virtual_office`, `event_space`, `membership`, `exclusive_bereich`), `limit` (1–50, default 5), `zone` (a district slug such as `mitte`), `operator` (a brand slug such as `wework`) and `locale` (`en`, `de`, `es`).

`[coworkingview_rates]`: `city` (required), `currency` (an ISO code such as `USD` to convert the figures) and `locale`.

= How do I find a city's slug? =

Open the city on coworkingview.com: the slug is the last part of the address, for example `berlin` in https://coworkingview.com/en/coworking/berlin.

= Are the prices up to date? =

They are what operators publish, refreshed as listings change, and cached on your site for the time you set. Each space links to its page with the current offer.

= Do I have to credit CoworkingView? =

The market rates are licensed CC BY 4.0, which asks for a credit and a link when you republish them. Turn on **Settings → CoworkingView → Credit** to show a "Data: CoworkingView" line. It is off by default.

= Does the plugin track my visitors? =

No. Your server calls the API when the cache is empty; visitors' browsers never contact CoworkingView, and no cookies are set.

== External services ==

This plugin connects to the CoworkingView API (https://api.coworkingview.com) to fetch the coworking spaces and market rates it displays. That is its only purpose.

* When: when a page with one of the shortcodes is rendered and no cached copy exists (by default at most once every 12 hours for each shortcode configuration).
* What is sent: the shortcode's parameters (city, workspace type, limit, district, brand, currency, language), your site's address in the User-Agent header, and your server's IP address as part of the HTTP request. No visitor data is sent.

The service is provided by CoworkingView: [terms of use](https://coworkingview.com/en/terms), [privacy policy](https://coworkingview.com/en/privacy).

== Screenshots ==

1. A list of private offices in Berlin rendered by `[coworkingview_spaces]`.
2. Market rates in Madrid rendered by `[coworkingview_rates]`.
3. The settings page.

== Changelog ==

= 0.1.0 =
* First release: `[coworkingview_spaces]` and `[coworkingview_rates]`, settings page, caching.
