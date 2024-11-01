=== SS Old URLs ===
Contributors: strangerstudios
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=7693347
Tags: redirects, 404, ssoldurls
Requires at least: 2.2
Tested up to: 2.8.4
Stable tag: 1.0
This plugin allows you to add URL redirects through the WP admin for old content URLs. For example, you can redirect /about.html to /about/.

== Description ==

SS Old URLs allows you to add URL redirects through the WP admin for old content URLs. 

For example, if your old site had its about page at /about.html, but your new Wordpress site uses a nice URL like /about/, you can add the rule /about.html ==> /about/. This will preserve any existing links to the old URL. In addition, we perform our redirects as "301" redirects so you should maintain as much "Google Juice" as possible.

This plugin is especially useful for developers and designers who are porting older websites to Wordpress. You can also use it if your move pages around or change URLs/slugs for any reason.

== Installation ==

1. Upload `ssoldurls.php` to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Navigate to Tools --> Old URLs to add your redirect rules.

== Frequently Asked Questions ==

= My redirect isn't working? Why is that? =

Our redirect code is only fired if no other Wordpress page is found for the given URL. For example, if you add the rule /contact-us/ => /contact/, but there is a Wordpress page with the slug /contact-us/, that Wordpress page will be shown.

= When exactly do you check for redirect rules? =

The plugin uses the "status_header" plugin hook to detect when Wordpress tries to send a 404 status to the header. (That means it couldn't find a page or post for the given URL.) We then check against the redirect rules and perform a 301 redirect if a rule is found.

= What if I want to force the redirect? =

You can force the redirect by copying the 301 code from the SS Old URLs settings page to your .htaccess file. For example, by pasting this code above the rewrite code for Wordpress, you'll ensure the redirect is performed before Wordpress even gets to have a chance to resolve the URL.Redirect 301 /about.php /about/

== Screenshots ==

1. The SS Old URLs setup interface.


== Changelog ==

= 1.0 =
* This is the launch version. No changes yet.
