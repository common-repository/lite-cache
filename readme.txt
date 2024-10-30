
=== Lite Cache ===
Tags: cache,performance,staticizer,apache,htaccess,tuning,speed,bandwidth,optimization,tidy,gzip,compression,server load,boost
Requires at least: 3.0
Tested up to: 3.9.2
Stable tag: trunk
Donate link: http://www.satollo.net/donations

The smallest cache plugin ever released (but still greatly efficient).

== Description ==

New! The Lite Cache technology is now part of [Hyper Cache](http://www.satollo.net/plugins/hyper-cache). You should migrate
to Hyper Cache, Lite Cache won't be update anymore (other than important fixes).

Lite Cache is an ultra efficient cache plugin. It's small and lite because it
does only one thing: caching.

Lite Cache is compatible with gzip compression and handles it automatically.

Lite Cache can detect mobile devices and use a different theme for them creating a separate
cache.

Lite Cache works even with commenters, people who left a comment in the past.
Other caching plugin usually are not able to serve cached content to commenters creating
performance issues on higly partecipative blogs.

Usage of .htaccess rules is possible using the code generated inside the administrative panel (but
pay attention that a too much complex .htaccess seems to slow down more than serving
cached pages via PHP).

Install it and go to the options panel to find how to configure it.

Lite Cache official page: http://www.satollo.net/plugins/lite-cache

Lite Cache official forum: http://www.satollo.net/forums/forum/lite-cache

= Tanslations =

* Russian by Artnikov

== Installation ==

1. Put the plug-in folder into [wordpress_dir]/wp-content/plugins/
2. Go into the WordPress admin interface and activate the plugin
3. Go to Lite Cache options panel, choose a woring mode and mody your blog as explained

== Frequently Asked Questions ==

= Where can I find the latest FAQ?

Here: (http://www.satollo.net/plugins/lite-cache)

= How can I know if it is working? =

Open the admin side of your blog. Log out (yes, log out). Go to the home page
or any other page of your blog. Load it twice. Look at the source code of the page:
on the bottom you should see the lite cache signature.

= I installed it, but nothing changed =

Did you choose the working mode and followed the configuration panel instructions?
Please double check them.

== Screen shots ==

No screen shots are available at this time.

== Changelog ==

= 2.3.4 =

* Russian translation

= 2.3.3 =

* Readme link fix

= 2.3.2 =

* Added the dismissing procedure

= 2.3.1 =

* Added the cache header

= 2.3.0 =

* Added the option to disable the "commentator optimization"

= 2.2.9 =

* Added a compatibility check with XML Sitemap 4.x

= 2.2.8 =

* Fixed blog on Windows
* Added the Content-Length header

= 2.2.7 =

* Changed from readfile to file_get_contents (see www.satollo.net)

= 2.2.6 =

* Fixed user agent detection

= 2.2.5 =

* Added user agent rejection
* Added custom latest post invalidation

= 2.2.4 =

* Fixed a little issue with comment form autocompletion

= 2.2.3 =

* Fixed the constant AGENTS

= 2.2.2 =

* Fixed the comment javascript injection
* Removed style images
* Extracted the administrative code (for efficiency)

= 2.2.1 =

* Fixed the host name moving it to lowercase version
* Added the gzip disable control

= 2.2.0 =

* Added support for IS_PHONE constant
* Fix a bug when user is logged in (introduced on version 2.1.9)

= 2.1.9 =

* Added the Vary: User-Agent
* Code improvements to avoid debug noticies

= 2.1.8 =

* Fixed the home clean up on post/page change

= 2.1.7 =

* Improved the cache invalidation on comments

= 2.1.6 =

* Little fix on foreach instructions

= 2.1.5 =

* Added regular expression for folder names clean up
* Added host name to (may be) support multisite with 3rd level hosts

= 2.1.4 =

* Some fixes on options panel

= 2.1.3 =

* fixed a first installation problem when option was not saved

= 2.1.2 =

* added max cache age
* added Vary header while serving gzip

= 2.1.1 =

* terrible bug on clean cache from administrative panel

= 2.1.0 =

* added the 304 response
* ability to put in cache only newer post
* admin panel reviewed
* cache folder selectable


= 2.0.6 =

* fixed the last modified header

= 2.0.5 =

* script added before the body closing

= 2.0.4 =

* Added URLs rejection
* Option to simulate favicon.ico
* Option to simulate crossdomain.xml

= 2.0.3 =

* Evaluate the $hyper_cache_stop to be compatible with plugins already integrated with Hyper Cache

= 2.0.2 =

* Added bbpress integration

= 2.0.1 =

* Small changes to the options page
* Better update and activation process

= 2.0.0 =

* mobile detection with theme change
* logged in user cache
* reviewed the plugin page (http://www.satollo.net/plugins/lite-cache)
* autoinstallation
* administrative panel warnings

= 1.1.4 =

* Fixed the plugin name...

= 1.1.3 =

* Fixed metadata

= 1.1.2 =

* Added a check for the WPCACHE definition

= 1.1.1 =

* added compatibility with "type" archives
* added compatibility with "year" archives

= 1.1.0 =

* cache_buffer filter support
* changed the cache folder to cache/lite-cache
* improved documentation on option panel
* changed the .htaccess code (please update)

= 1.0.0 =

* First release
