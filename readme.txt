=== Football Club Manager for Sportlink ===
Contributors: vincentbitter
Tags: soccer, teams, matches, Sportlink, KNVB
Requires at least: 6.8
Tested up to: 6.8
Stable tag: 0.8.0
Requires PHP: 7.4
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Automatically import Sportlink data into the Football Club Manager WordPress plugin.

== Description ==

This plugin bridges the gap between Sportlink and [Football Club Manager](https://wordpress.org/plugins/football-club-manager/), enabling seamless synchronization of teams, players, and matches directly into your WordPress site.

It automatically imports KNVB data from Sportlink, updates team rosters and player profiles, downloads player photos, and syncs matches and league standings.

== Features ==

* Automatic import of KNVB data from Sportlink
* Update team rosters and player profiles
* Download player photos from Sportlink (smart detection prevents overwriting manually uploaded images)
* Sync match schedules and results
* Import league standings (coming soon!)
* Import birthdays
* Manual import to apply changes faster and test the integration
* Easy integration with Football Club Manager

== Installation ==

1. Upload the plugin to your website and activate it.
3. Go to **Football Club Manager → Sportlink** in your WordPress dashboard.
4. Enter your club’s **Sportlink Client ID**.
5. Choose whether to enable **automatic import**.
6. Optionally, use the **“Sync Now”** button to manually fetch the latest data.

== Privacy & External Services ==

This plugin connects to the Sportlink API (https://data.sportlink.com) to retrieve up-to-date information about teams, players, and matches.

It sends the Sportlink client ID and previously received team IDs from Sportlink. The plugin does not transmit any WordPress content, configuration, files, or user data.

For more information, please refer to Sportlink's Terms of Service and Privacy Policy:
- Terms of Service: https://sportlinkservices.freshdesk.com/nl/support/solutions/folders/9000176717
- Privacy Policy: https://www.sportlink.nl/privacybeleid/

== Frequently Asked Questions ==

= What is Sportlink, and why should I integrate it with Football Club Manager? =
Sportlink is a platform used by many Dutch football clubs to manage match schedules, team data, and results. Integrating it with Football Club Manager allows you to automatically display up-to-date information on your WordPress site.

= Is this plugin developed or supported by Sportlink? =
No. This plugin is an independent project and is not developed, endorsed, or affiliated with Sportlink. It is designed to integrate with Sportlink's data services, but all development and support are provided by the open-source community.

= Do I need coding experience to use this plugin? =
Not at all! Once installed and configured, the plugin handles the data import automatically.

= How often does the plugin sync data from Sportlink? =
Data is synchronized hourly using WordPress cron.

= What kind of data is imported from Sportlink? =
- Team rosters and player profiles
- Player photos
- Match schedules and results
- League standings _(coming soon!)_
- Birthdays

= Why are not all players of my club are imported? =
Only public profiles from Sportlink are imported. The Sportlink API doesn't provide access to the names of players that are not set to public in Voetbal.nl. Please ask your members to set their profile to public so it is shown in both Voetbal.nl and on your website.

= Birthdays are all in 1900, is this a bug? =
It's not a bug, unfortunately. Sportlink only exposes the day and month via their API. That's why the year is set to 1900. Using this year, makes sure FCM won't show the age, as it excludes all years <= 1900. The import will never override dates of birth, so it's safe to complete the date of birth by filling in the year yourself.

= Why are not all birthdays set in Football Club Manager while they are available in Sportlink? =
Sportlink only exposes birthdays for the next 21 days. That's why the import needs to be done frequently. Ideally, via the automated import job.
If a birthday is missing that is in the next 21 days, make sure you synchronized the data. This can be done manually or via the automated import.

= Can player photos still be uploaded in WordPress, or will these be overridden? =
The plugin contains a smart algorithm to check if the current player photo was manually uploaded in WordPress. If so, it won't override it with the photos from Sportlink.

= Can I manually trigger a sync? =
Yes! There’s a “Sync Now” button in the plugin settings.

= Is this plugin compatible with other WordPress themes or plugins? =
It’s designed to work seamlessly with Football Club Manager. Custom styling may be needed depending on your theme.

= Where can I report bugs or request features? =
Visit the [GitHub Issues page](https://github.com/vincentbitter/fcm-for-sportlink/issues).

== Changelog ==

Visit the [GitHub Releases page](https://github.com/vincentbitter/fcm-for-sportlink/releases).

== Disclaimer ==

This plugin is **not developed, endorsed, or affiliated with Sportlink** in any way. All references to Sportlink are solely for compatibility and integration purposes.