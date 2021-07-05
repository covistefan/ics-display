=== ICS Display ===
Contributors: appleuser
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=5762TWVRT6RQ4
Tags: calendar, events, ics, ical, icalendar, google, ajax, multi
Requires at least: 5.0
Tested up to: 5.7
Stable tag: 2.1

Display upcoming events from a shared Google, Outlook, iCal or other ICS calendar.

== Description ==

Fetch and display events from your Google, Outlook or iCal calendar (or any other .ics file) in your blog. Combine multiple ICS-files to one event table.

=== Block Support ===

Brings block item 'ICS Display'

== Installation ==

1. Upload 'ics-display' folder to your plugins folder
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Customize ICS-URLs in admin → "ICS Display"

== Changelog ==
= 2.1 =
* Fixed an error that could cause your Wordpress installation to stop working when pro license expired
= 2.0 =
* Rebuild the Gutenberg editor block
* Completed translations for German
* Added repeating events feature for PRO
* Expanded help section
* Added 'Start date' as Option
* Fixed an error, where paging was shown in table or list view when no more pages were avaiable
* Checked up to Wordpress 5.8 
= 1.3 =
* Fixed an error where error message could appear when no calendar-URI was defined
* Changed dtstamp data
    * dtstamp now shows the period of the event
* Changed dtbegin display data
    * dtbegin now shows the date and time
* Changed dtend display data
    * dtend now shows the date and time
* Added help text that provides information about the various data formats of the columns to be displayed 
* Removed false positive information about block support
= 1.2 =
* Changed base class loading to prevent usage of defined COVI classes
= 1.1 =
* Added option to name single .ICS-entries
* Added option to configure display-options
* Added PRO-option
    * Added display as list and calendar (in PRO)
* Added weekly checkup of .ICS-calls
= 1.0 =
* This was the first version available.

== Frequently Asked Questions ==

=== There is a feature that I want... ===

If you would like a new feature, or something doesn't work the way it should, do not hesitate to contact me

=== I have a URL that starts with WebCal ===

If you have a URL that starts with `webcal`, then all you have to do is change the `webcal://` to `http://` or (better) `https://` and that should work.

=== How often is the calendar checked for new events? ===

The calendar is checked on the fly.

=== Why aren't my events showing up correctly? ===

This plugin makes an attempt to support as many event definitions that follow the ICS specification (RFC 2445) as possible. However, there may be bugs in how the plugin interprets the parsed data.

If an event is showing up correctly in your calendar application (iCal, Google Calendar) but not on your blog, please contact me.