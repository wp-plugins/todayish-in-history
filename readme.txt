=== Todayish in History ===
Contributors: stuporglue
Donate link: https://www.dwolla.com/hub/stuporglue
Tags: history, widget,theme
Requires at least: 3.0
Tested up to: 3.3.1
Stable tag: trunk

Shows a list of links to posts from previous years on or near this date, 1 per year. Provides a function to use in a theme, as well as a widget

== Description ==

If your blog posts from the same time of year are relavant each and every
year, then this is the plugin for you. Todayish In History shows a list of
links to blog posts from your blog which were posted in previous years at 
about the same date as today. 

The plugin is named Todayish because if there isn't a blog post from today's
date in previous years it will use the blog post which is the least
number of days away from todays date. 

Todayish in History provides a function for use in themes as well as a
widget for use in your sidebar.

== Installation ==

1. Unzip the todayish_in_history.zip file to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

If you want to use the widget:

1. Place the Todayish In History in a sidebar using the WordPress Widgets tool in the Appearence menu
2. Change the settings if desired

If you want to use Todayish In History in your theme:

1. Place 

    `<?php todayish_in_history(); ?>` 

    in your template

2. You can optionally set the following parameters

	* parameter (default,type) -- Description
	* limit (100,int) -- How many years of history to show
	* title (Todayish In History,string) -- The title displayed next to the dropdown
	* class (horizontal,css legal string) -- The class added to the outermost div for styling purposes
	* width (200px,valid css width) -- How wide should the dropdown be. 
	* iswidget (FALSE,boolean) -- If TRUE adds class 'widgettitle' to h2 widget title 

By default the dropdown drops directly down, and titles too wide for the
drop-down are truncated. To change this behavior so that the dropped-down list
is as wide as it needs to be, see the comment in todayish_in_history.css

== Frequently Asked Questions ==

= What's a use case for this thing? =

I am entering my 3rd year of garden blogging.  Every year the same types of things 
are applicable in the same season of the year. Other seasonal topics like
hunting, fishing, sports, etc. may also find this sort of thing useful.

== Screenshots ==

1. Todayish in collapsed form in a theme.
2. Todayish expanded in a theme.
3. Todaysih widget in vertical mode.
4. Todayish widget in horzontal mode.
5.  Todayish widget options.

== Changelog ==

= 0.1.1 = 
Fix to use sidebar's title size instead of hard coded h2. Added !important to
commented out CSS that enabled full text links to appear. 

= 0.1 =
* Welcome to Todayish In History

== Upgrade Notice ==

= 0.1.1 = 
Fixed to use sidebar's title size instead of hard coded h2. 

= 0.1 = 
Welcome to Todayish In History. Thanks for installing!

