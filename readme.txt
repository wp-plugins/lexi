=== Lexi ===
Contributors: sebaxtian
Tags: rss
Requires at least: 2.4
Tested up to: 2.7.1
Stable tag: 0.6.1

An RSS feeder using ajax to show contents after the page has been loaded.

== Description ==

Sometimes an RSS feed has a low bandwidth and during the page creation Wordpress has to wait after those RSS feeds has been donwloaded. This plugin allow the site to read the RSS _after_ the page was created, not during the process.

You can use Tools -> Lexi to add, modify or delete your RSS feeds.

Add the Lexi widget to show your feeds, or you can add [lexi:id] in a page, or you can use the function `lexi(id)` in your template. Id is optional, and defines Feed to read. There is a button in the RichText Editor to add a feed in a post.

This plugin requires __[minimax](http://wordpress.org/extend/plugins/minimax/ "A minimal Ajax library")__ in order to work.

Screenshots are in spanish because it's my native language. As you should know yet I __spe'k__ english, and the plugin use it by default.

== Installation ==

1. Install __[minimax](http://wordpress.org/extend/plugins/minimax/ "A minimal Ajax library")__.
2. Decompress lexi.zip and upload `/lexi/` to the `/wp-content/plugins/` directory.
3. Activate the plugin through the __Plugins__ menu in WordPress
4. Add the Lexi widget into your side bar.
5. Use Tools -> Lexi to add, modify or delete your RSS feeds.

== Frequently Asked Questions ==

= Other RSS reader! How do you dare? =

My page (I don't know if yours too) use to get blocked reading some RSS feeds. I created Lexi to read the RSS _after_ the page was created, not during the process.

= It say something about minimax. What's this? =

This plugin requires __[minimax](http://wordpress.org/extend/plugins/minimax/ "A minimal Ajax library")__ in order to work.

== Screenshots ==

1. Feeds administrator.
2. Feed editor.
3. Lexi widget.
