=== Lexi ===
Contributors: sebaxtian
Tags: rss
Requires at least: 2.4
Tested up to: 2.7.1
Stable tag: 0.7.95

An RSS reader that can be placed in pages, posts and sidebar, using ajax to show contents after the site has been loaded.

== Description ==

Sometimes an RSS feed has a low bandwidth and during the page creation Wordpress has to wait after those RSS feeds has been downloaded. This plugin allow the site to read the RSS _after_ the page was created, not _during_ the process.

To modify or delete the list of RSS feeds go to Tools -> Lexi.

You can add the Lexi widget to show the list, but you can also use the tag `[lexi]` in a page, or the function `lexi()` in a template.

To show just one item from the list, use `[lexi:id]` or `lexi(id)`.

To show a Feed that hasn't been declared in the list, use `[lexi: configuration, rss, title, max_items]` or `lexiRSS($configuration, $rss, $title, $max_items)`. The configuration number can be calculated as follow:

Add 1 if you want to save it in cache.
Add 2 if you want to show the contents.
Add 4 if you want to show the title.
Add 8 if you want to open it in a new page.

If you want to use the title given by the RSS, use `[lexi: configuration, rss, max_items]` or `lexiRSS($configuration, $rss, false, $max_items)`.

There is a button in the RichText editor created by the plugin to add a Feed.

This plugin requires __[minimax](http://wordpress.org/extend/plugins/minimax/ "A minimal Ajax library")__ in order to work. 

Lexi detects if your site has the __[SimplePie](http://simplepie.org/ "SimplePie: Super-fast, easy-to-use, RSS and Atom feed parsing in PHP.")__ library enabled. Since Wordpress 2.8 comes with it by default you don't need to activate anithing, but in earlier versions you have to install the __[SimplePieCore Plugin](http://wordpress.org/extend/plugins/simplepie-core/ "Does little else but load the core SimplePie API library for any extension that wants to utilize it.")__ to use it instead MagpieRSS. I recomend to use SimplePie. Remember to uninstall SimplePieCore if you are using Worpress 2.8 or any latter version.

Screenshots are in spanish because it's my native language. As you should know yet I __spe'k__ english, and the plugin use it by default.

== Installation ==

1. Install __[minimax](http://wordpress.org/extend/plugins/minimax/ "A minimal Ajax library")__.
2. Decompress lexi.zip and upload `/lexi/` to the `/wp-content/plugins/` directory.
3. Activate the plugin through the __Plugins__ menu in WordPress
4. Add the Lexi widget into your side bar, or add [lexi] into your posts, or use `lexi()` into your templates.
5. Use Tools -> Lexi to add, modify or delete your RSS feeds list.

== Frequently Asked Questions ==

= Other RSS reader! How do you dare? =

My page (I don't know if yours too) use to get blocked reading some RSS feeds. I created Lexi to read the RSS _after_ the page was created, not during the process.

= Lexi shows strange characters with some feeds =

Lexi encodes the feed list in UTF-8. If your site uses another character encoding you can change `wp-content/plugins/lexi/lexi.php`, but I suggest you to use UTF-8.
If your site is in UTF-8, uses WP 2.7.x or an older release, and the problem persists, install __[SimplePieCore Plugin](http://wordpress.org/extend/plugins/simplepie-core/ "Does little else but load the core SimplePie API library for any extension that wants to utilize it.")__.

= There is a cached RSS that doesn't show anything =

Yes, I noticed too. It happens with some feeds the first time they are readed, but ten minutes later they work! I think the problem is in `fetch_rss($url)`, the WordPress function Lexi uses to save cached Feeds. I'll try to fix it in a future version.

= It says something about a write problem in cache =

Sure you are using SimplePie. Check you have write permission in `wp-content/plugins/lexi/cache`.

= It say something about minimax. What's this? =

This plugin requires __[minimax](http://wordpress.org/extend/plugins/minimax/ "A minimal Ajax library")__ in order to work.

== Screenshots ==

1. Feeds administrator.
2. Feed editor.
3. Lexi widget.
4. Box to add a Lexi feed.
5. Box to add an RSS feed.