=== DP Widgets Plus ===
Contributors: Cloud Stone
Donate link: http://dedepress.org/donate/
Tags: widget, widgets, widget-icon, widget-banner, widget-image
Requires at least: 2.7
Tested up to: 3.1
Stable tag: 1.0

Add extra control options to each widget. You can specify the link of widget title, custom CSS classs name, subtitle, icon and more advanced settings to gives you total control over the output of your widgets.

== Description ==

This is not an individual widget, in addition to adding numerous extremely useful options for each widget:

1. the icon/banner/image and link to it
2. The link to the title
3. Sub title
4. Custom CSS class name
5. More custom content applied to the end of widget

You can also specify the postion and float attribute of the icon to output a variety of widgets.

== Installation ==

1. Unzip the plugin archive
2. Upload the entire folder 'dp-widgets-plus' to '/wp-content/plugins/'
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Go to Widgets page in admin and configure in 'DP Widgets Plus' panel of each widget

test.

== Usage ==

DP Widget Plus generates friendly markup for custom styling in your own stylesheet.

Before using DP Widget Plus, the HTML markup of each widget usually like this:
	
	<div id="widget-xxx" class="widget">
		<h3 class="widget-title">Widget Title</h3>
		<!-- Different output for each widget -->
	</div>
	
After using DP Widget Plus(We assume that all options of DP Widget Plus has been used), the HTML markup of a widget will like this:

	<div id="widget-xxx" class="widget">
		<h3 class="widget-title">
			<a href="#" class="widget-icon"><img src="" /></a>
			<span class="widget-main-title">Widget Title</span>
			<span class="widget-sub-title">Widget sub title</span>
		</h3>
		<!-- Different output for each widget -->
		<div class="widget-footer"></div>
	</div>

So, you can style the output with these CSS selectors: 
	<pre>
	<code>
	.widget-icon{}
	.widget-icon img{}
	.widget-main-title{}
	.widget-sub-title{}
	.widget-footer{}
	</code>
	</pre>

== Screenshots ==

1. Widget example that using DP Widgets Plus
2. The 'DP Widgets Plus' panel in each widget
3. 
4.

== Changelog ==

= 0.1, 07 January 2011 =

* Initial public release