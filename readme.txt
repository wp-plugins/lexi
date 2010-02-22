=== Lexi ===
Contributors: sebaxtian
Tags: rss
Requires at least: 2.8
Tested up to: 2.9.2
Stable tag: 0.9.4

An RSS reader that can be placed in pages, posts and sidebar, using ajax to show contents after the site has been loaded.

== Description ==

Sometimes an RSS feed has a low bandwidth and during the page creation Wordpress has to wait after those RSS feeds had been downloaded. This plugin allows the site to read the RSS _after_ the page was created, not _during_ the process.

To show a Feed in a post use `[lexi: configuration, rss, title, max_items]` or `lexiRSS($configuration, $rss, $title, $max_items)`.

The configuration number can be calculated as follows:

* Add 1 if you want to save it in cache.
* Add 2 if you want to show the contents.
* Add 4 if you want to show the title (this is the channel link too).
* Add 8 if you want to open it in a new page.
* Add 16 if you want to not show the RSS icon (this is the RSS link too).
* Add 32 if you want to show the author.
* Add 64 if you want to show the date.

If you want to use the title given by the RSS, use `[lexi: configuration, rss, max_items]` or `lexiRSS($configuration, $rss, false, $max_items)`.

There is a button in the RichText editor created by the plugin to add a Feed.

This plugin requires __[minimax](http://wordpress.org/extend/plugins/minimax/ "A minimal Ajax library")__ in order to work. 

Lexi detects if your site has the __[SimplePie](http://simplepie.org/ "SimplePie: Super-fast, easy-to-use, RSS and Atom feed parsing in PHP.")__ library enabled. Since Wordpress 2.8 comes with it by default you don't need to activate anything, but in earlier versions you have to install the __[SimplePieCore Plugin](http://wordpress.org/extend/plugins/simplepie-core/ "Does little else but load the core SimplePie API library for any extension that wants to utilize it.")__ to use it instead MagpieRSS. I recomend to use SimplePie. Remember to uninstall SimplePieCore if you are using Worpress 2.8 or any latter version.

Screenshots are in spanish because it's my native language. As you should know yet I __spe'k__ english, and the plugin use it by default.

== Installation ==

1. Install __[minimax](http://wordpress.org/extend/plugins/minimax/ "A minimal Ajax library")__.
2. Decompress lexi.zip and upload `/lexi/` to the `/wp-content/plugins/` directory.
3. Activate the plugin through the __Plugins__ menu in WordPress
4. Add the Lexi widget into your side bar, or add `[lexi]` into your posts, or use `lexi()` into your templates.
5. Use __Tools > Lexi__ to add, modify or delete your RSS feeds list.

== Frequently Asked Questions ==

= Other RSS reader! How do you dare? =

My page (I don't know if yours too) use to get blocked reading some RSS feeds. I created Lexi to read the RSS _after_ the page was created, not during the process.

= Lexi shows strange characters with some feeds =

Lexi encodes the feed list in UTF-8. If your site uses another character encoding you can change `wp-content/plugins/lexi/lexi.php`, but I suggest you to use UTF-8.

If your site is in UTF-8, uses WP 2.7.x or an older release, and the problem persists, install __[SimplePieCore Plugin](http://wordpress.org/extend/plugins/simplepie-core/ "Does little else but load the core SimplePie API library for any extension that wants to utilize it.")__.

= It says something about a write problem in cache =

Sure you are using SimplePie. Check you have write permission in `wp-content/cache/lexi`.

= It says something about minimax. What's this? =

This plugin requires __[minimax](http://wordpress.org/extend/plugins/minimax/ "A minimal Ajax library")__ in order to work.

= Can I set my own CSS? =

Yes. Copy the file lexi.css to your theme folder. The plugin will check for it.

= Lexi can't reed the xyz feed =

First, use the __[simplepie validator](http://simplepie.org/demo/ "Simplepie Demo and Validator")__ to check the feed. If you get an error it means the problem is in the library. To solve this situation, use a Mashup engine like __[Yahoo Pipes](http://pipes.yahoo.com/pipes/ "A mashup editor")__ to create a new RSS feed from the original data.

If the validator returns the feed data, maybe the library in your WP is older than the one in the validator. Use the RSS widget (the one from Wordpress) with the feed to check if the library can read it. If it works, is time to write a comment in my __[personal page](http://www.sebaxtian.com/acerca-de/lexi "Lexi's page")__ with the RSS feed on it. Else, use a mashup engine as described in the first step.

Also, it happens with some feeds the first time they are readed, but ten minutes later they work.

== Screenshots ==

1. Add one feed in the sidebar.
2. Feeds in the sidebar.
3. Lexi button in the editor.
4. Box to add an RSS feed.

== Changelog ==

= 0.9.4 =
* New cache system fixed.

= 0.9.3 =
* Solved a strange bug in the editor.
* New cache system to solve a bug with cache plugins.

= 0.9.2 =
* Option added to show the author.
* Option added to show the date.

= 0.9.1 =
* Solved a bug with old PHP versions.

= 0.9 =
* Multiple widget.
* Manage activation errors.
* New API without numbered list. See all your posts, pages and sidebar with lexi for changes.

= 0.8.4 =
* FAQ and screenshots updated.

= 0.8.3 =
* New configuration item - Not show icon (add 16 to configuration number).
* Updated UI to use the new configuration number.

= 0.8.2 =
* User interface modified to set more items per feed.

= 0.8.1 =
* Using nonce to not show data when someone call the ajax script outside the plugin.
* Silence is gold.

= 0.8 =
* Using minimax 0.3

= 0.7.99 =

* Solved a bug with the cache system.

= 0.7.98 =
* The code has been indented, documented and standardised.
* Solved a bug with the headers, now Lexi works with the plugin POD.
* Solved a bug when tinyMCE editor in full window.

= 0.7.97 =
* Now you can set your own css file (see FAQ).
