=== Lexi ===
Contributors: sebaxtian
Tags: rss
Requires at least: 2.8
Tested up to: 3.1
Stable tag: 1.1

An RSS reader that can be placed in pages, posts and sidebar, using ajax to show contents after the site has been loaded.

== Description ==

Sometimes an RSS feed has a low bandwidth and during the page creation Wordpress has to wait after those RSS feeds had been downloaded. This plugin allows the site to read the RSS _after_ the page was created, not _during_ the process.

To show a Feed in a post use `[lexi: configuration, rss, title, max_items]` or `lexiRSS($configuration, $rss, $title, $max_items)`.

The configuration number can be calculated as follows:

* Add 1 if you want to save it in cache.
* Add 2 if you want to show the content.
* Add 4 if you want to show the title (this is the channel link too).
* Add 8 if you want to open it in a new page.
* Add 16 if you want to not show the RSS icon (this is the RSS link too).
* Add 32 if you want to show the author.
* Add 64 if you want to show the date.
* Add 128 if you want to paginate the results.
* Add 256 to not show items title.
* Add 512 to show content in the reference title (usefull when using 3rd party rollover plugins).
* Add 1024 to use the internal function to truncate the content to the first complete paragraphs until having 400 letters. Only text, no images. See the FAQ for other truncate size.

If you want to use the title given by the RSS, use `[lexi: configuration, rss, max_items]` or `lexiRSS($configuration, $rss, false, $max_items)`.

There is a button in the editor (the one with the RSS icon) created by the plugin to help you to add feeds in posts or pages.

Lexi detects if your site has the __[SimplePie](http://simplepie.org/ "SimplePie: Super-fast, easy-to-use, RSS and Atom feed parsing in PHP.")__ library enabled. Since Wordpress 2.8 comes with it by default you don't need to activate anything, but in earlier versions you have to install the __[SimplePieCore Plugin](http://wordpress.org/extend/plugins/simplepie-core/ "Does little else but load the core SimplePie API library for any extension that wants to utilize it.")__ to use it instead MagpieRSS. I recomend to use SimplePie. Remember to uninstall SimplePieCore if you are using Worpress 2.8 or any latter version.

Lexi has been translated to greek by the __[HyperCom Team](http://www.hypercom.gr/ "IT and WebSite Design")__ and russian by the __[Fatcow Team](http://www.fatcow.com/ "Web Hosting &amp; Domain Names by FatCow.com")__. Thanks for your time guys!

Screenshots are in spanish because it's my native language. As you should know yet I __spe'k__ english, and the plugin use it by default.

== Installation ==

1. Decompress lexi.zip and upload `/lexi/` to the `/wp-content/plugins/` directory.
2. Activate the plugin through the __Plugins__ menu in WordPress
3. Add Lexi widgets into your side bar (if required).
4. Add tags into your posts or pages using the lexi button in the editor, the one with the RSS icon.

== Frequently Asked Questions ==

= Other RSS reader! How do you dare? =

My page (I don't know if yours too) use to get blocked reading some RSS feeds. I created Lexi to read the RSS _after_ the page was created, not during the process.

= Lexi shows strange characters with some feeds =

Lexi encodes the feed list in UTF-8. If your site uses another character encoding you can change `wp-content/plugins/lexi/lexi.php`, but I suggest you to use UTF-8.

If your site is in UTF-8, uses WP 2.7.x or an older release, and the problem persists, install __[SimplePieCore Plugin](http://wordpress.org/extend/plugins/simplepie-core/ "Does little else but load the core SimplePie API library for any extension that wants to utilize it.")__.

= Can I set my own CSS? =

Yes. Copy the file lexi.css to your theme folder. The plugin will check for it.

= Lexi can't reed the xyz feed =

First, use the __[simplepie validator](http://simplepie.org/demo/ "Simplepie Demo and Validator")__ to check the feed. If you get an error it means the problem is in the library. To solve this situation, use a Mashup engine like __[Yahoo Pipes](http://pipes.yahoo.com/pipes/ "A mashup editor")__ to create a new RSS feed from the original data.

If the validator returns the feed data, maybe the library in your WP is older than the one in the validator. Use the RSS widget (the one from Wordpress) with the feed to check if the library can read it. If it works, is time to write a comment in my __[personal page](http://www.sebaxtian.com/acerca-de/lexi "Lexi's page")__ with the RSS feed on it. Else, use a mashup engine as described in the first step.

Also, it happens with some feeds the first time they are readed, but ten minutes later they work.

= How does the truncate function work? =

This simple function takes the html code, extract the text and truncate it to the first paragraphs with less than 400 characters. If you want to truncate allways to an specific size, define the LEXI_TRUNCATE_SIZE variable into your wp-config.php file with the required number, like this:

define('LEXI_TRUNCATE_SIZE', 200);

== Screenshots ==

1. Add one feed in the sidebar.
2. Feeds in the sidebar.
3. Lexi button in the editor.
4. Box to add an RSS feed.

== Changelog ==

= 1.1 =
* Solved problem with the excerpt.
* Checked for WP 3.1

= 1.0.4 =
* Solved bug with script call.

= 1.0.3 =
* Added fix to allow commas inside RSS feeds.
* Modified TinyMCE call to solve bugs with wp-cache.

= 1.0.2 =
* Solved minor bugs.

= 1.0.1 =
* Using WP functions to add safely scripts and css.

= 1.0 =
* Stable release

= 0.9.105 =
* Function to truncate the text.
* New configuration forms to display the content as a rollover (3d party plugin required) or as text.

= 0.9.104 =
* Added configuration item to put the content in the href title. Useful to use with tooltip libraries.

= 0.9.103 =
* Solved a bug with masqued domains and subdomains.

= 0.9.102 =
* Added capability to use qtip-for-wordpress when in pagination mode.

= 0.9.101 =
* Solved a bug in English version.
* Added capability to use qtip-for-wordpress.

= 0.9.99 =
* Solved a bug with Chanel link.
* Updated sack function to show the error message in the div if something goes wrong.
* Updated sack function to display the data instead execute a function.
* First release that doesn't require Minimax.
* Now Lexi uses WP's internal Ajax routines.

= 0.9.9.3 =
* Using WP comment format functions (request by Hypercom Team) to use capabilities added with third party plugins.
* Greek translation thanks to __[HyperCom Team](http://www.hypercom.gr/ "IT and WebSite Design")__.
* Russian translation thanks to __[Fatcow Team](http://www.fatcow.com/ "Web Hosting &amp; Domain Names by FatCow.com")__.

= 0.9.9.2 =
* Solved bug with item link when it has special characters.  

= 0.9.9.1 =
* Solved bug in multipage system with new check cache function.

= 0.9.9 =
* Modified to use ajax only when the cache is old.

= 0.9.8 =
* Don't use a list when we show only one item (or only one item per page)
* Option added to show (by default) or not items title.

= 0.9.7.1 =
* Solved an issue with external CSS.
* Modified number of pages to show.

= 0.9.7 =
* Pagination system.
* Updated nonce system to enhace security. Using feed url as seed.

= 0.9.6 =
* The long awaited new cache system.

= 0.9.5 =
* Solved a bug with current path.

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
