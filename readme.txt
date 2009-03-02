=== Lexi ===
Contributors: sebaxtian
Tags: rss
Requires at least: 2.4
Tested up to: 2.7.1
Stable tag: 0.7

An RSS feeder using ajax to show contents after the page has been loaded.

== Description ==

Sometimes an RSS feed has a low bandwidth and during the page creation Wordpress has to wait after those RSS feeds has been downloaded. This plugin allow the site to read the RSS _after_ the page was created, not _during_ the process.

You can use Tools -> Lexi to add, modify or delete your RSS feeds.

Add the Lexi widget to show your feeds, or the tag [lexi:id] in a page, or you can use the function `lexi(id)` in your template. The _id_ variable is optional, and defines the Feed in the Lexi list to read. If you don't define _id_ the plugin will show all the feeds. There is a button in the RichText Editor to add a feed in a post.

You can also add a feed in a page or in a template without declare it in the Lexi list. In your template use `echo lexi($rss, $max_items, $showcontents, $cached)`, in a page or post use   `[lexi:rss,max_items,showcontents,cached]` or use the RichText Editor button.

This plugin requires __[minimax](http://wordpress.org/extend/plugins/minimax/ "A minimal Ajax library")__ in order to work.

Screenshots are in spanish because it's my native language. As you should know yet I __spe'k__ english, and the plugin use it by default.

== Installation ==

1. Install __[minimax](http://wordpress.org/extend/plugins/minimax/ "A minimal Ajax library")__.
2. Decompress lexi.zip and upload `/lexi/` to the `/wp-content/plugins/` directory.
3. Activate the plugin through the __Plugins__ menu in WordPress
4. Add the Lexi widget into your side bar, or add [lexi] into your posts, or use `lexi()` into your templates.
5. Use Tools -> Lexi to add, modify or delete your RSS feeds.

== Frequently Asked Questions ==

= Other RSS reader! How do you dare? =

My page (I don't know if yours too) use to get blocked reading some RSS feeds. I created Lexi to read the RSS _after_ the page was created, not during the process.

= Some cached RSS doesn't show anything =

Yes, I noticed too. It happens with some feeds the first time it's readed, but ten minutes after it works! I think the problem is in `fetch_rss($url)`, the WordPress function that Lexi uses to save cached Feeds. There's anything I can do without hacking WP.

= It say something about minimax. What's this? =

This plugin requires __[minimax](http://wordpress.org/extend/plugins/minimax/ "A minimal Ajax library")__ in order to work.

== Screenshots ==

1. Feeds administrator.
2. Feed editor.
3. Lexi widget.
4. Widget to add a Lexi feed.
5. Widget to add an RSS feed.