![alt text](https://travis-ci.com/wielebenwir/commonsbooking.svg?branch=master "Travis CI")

# CommonsBooking

Contributors: wielebenwirteam, m0rb, flegfleg, chriwen
Donate link: https://www.wielebenwir.de/verein/unterstutzen
Tags: booking, commons, sharing, calendar, 
Requires at least: 5.2
Tested up to: 5.7.1
Stable Tag: 2.4.5
Requires PHP: 7.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Commons Booking is a plugin for management and booking of common goods. 

## Description

This plugin gives associations, groups and individuals the ability to share items (e.g. cargo bikes, tools) with users. It is based on the idea of Commons and sharing resources for the benefit of the community. 

CommonsBooking was developed for the ["Commons Cargobike" movement](http://commons-cargobikes.org/), but it can be used for any kind items.

**Unique features:**

* Items can be assigned to different locations for the duration of a timeframe, each with their own contact information.  
* Simple booking process:  bookable timeframes can be configured with hourly slots oder daily slots.
* Auto-accept bookings: A registered user can book items without the need for administration. 
* Codes: The plugin automatically generates booking codes, which are used at the station to validate the booking. 
* Managers can set holidays or repair slots to prevent items from beeing booked.


**Use cases:**

* Your association owns special tools that are not in use every day, and you want to make them available to a local group.
* You own a cargo bike that you want to share with the community, and it will be placed at different locations throughout the year.

**Plugin websites**

* [Official Website](https://commonsbooking.org)
* [Bug-Tracker](https://github.com/wielebenwir/commonsbooking/issues) 


## Installation

### Using The WordPress Dashboard 

1. Navigate to the 'Add New' in the plugins dashboard
2. Search for 'commonsbooking'
3. Click 'Install Now'
4. Activate the plugin on the Plugin dashboard

### Uploading in WordPress Dashboard 

1. Navigate to the 'Add New' in the plugins dashboard
2. Navigate to the 'Upload' area
3. Select `commonsbooking.zip` from your computer
4. Click 'Install Now'
5. Activate the plugin in the Plugin dashboard

### Using FTP

1. Download `commonsbooking.zip`
2. Extract the `commonsbooking` directory to your computer
3. Upload the `commonsbooking` directory to the `/wp-content/plugins/` directory
4. Activate the plugin in the Plugin dashboard


## Frequently Asked Questions

### Where can i find help/report bugs? =

* [Bug-Tracker](https://github.com/wielebenwir/commonsbooking/issues)
* [Support](https://commonsbooking.org/kontakt/)


## Screenshots

1. Booking calendar
2. Items list
3. Booking confirmation
4. User bookings list

## Changelog 

### 2.4.5 (10.05.2021)
* NEW: Restrict bookings to user groups. It is now possible to restrict bookable timeframes to one or more user groups to restrict bookings based on these timeframes.
* FIXED: In case of consecutive time frames, it could happen that not all time frames were displayed in the calendar. This is now fixed. (#612)
* FIXED: In a some combination of time frames it could happen that an already existing booking could be overwritten (in case of slotwise booking). (#610)
* FIXED: Some parts in the calendar were not translated to English when the website language was set to English. (#545)
* FIXED: API was available by default - this is standard behaviour of the wordpress integrated API too. Now the CommonsBooking API is deactived by default an can be activated in CommonsBooking options. We also removed the Owner information from items that has been available via the API (first and last name)
* FIXED: In the email template tags, the tag following the pattern {{xxx:yyy}} could not be used within an a href link as it is not allowed by Wordpress security methods. We have now added the alternative divider #. This now also works in a href links. Example a href="{{xxxx#yyyy}}"
* FIXED: New booking codes could not be generated in some cases.


### 2.4.4 (26.04.2021) 
* NEW: Added category filter in items and locations shortcode. You can use [cb_items category_slug=category_slug] to show items by a single category.
* NEW: Added the p attribute to cb_items shortcode, so you can display a single item by using [cb_items p=POSTID]
* CHANGED: Item and location list in select dropdown in timeframe editor is not restricted to published elements anymore. 
* ENHANCED: template improvements: not available notice now in separate line in item/location lists
* ENHANCED: pickupinstructions now inclueded in the location section on the booking page (changed template: booking-single.php)
* ENHANCED: inlcuded pickupinstructions in the following templates: location-calendar-header.php / location-single-meta.php
* ENHANCED: Changed the standard image thumbnail size in listings
* FIXED: If multiple timeframes are set the calendar only showed the last timeframe in booking calendar. 
* FIXED: Fixed some issues with map category filter
* FIXED: fixed interaction issues with calender when using timeslots. pickup field resets when selecting pickup time (fixed issues #629 and #619)


### 2.4.3 (09.04.2021)
* NEW: Eport-Tool for exporting timeframes (Bookings etc.) with flexible data fields. Useful for external analytics or to create connections to external systems like automatic lockers etc.
* NEW: Booking comment: Users can add an internal comment to a booking that can be viewed by location administrators and can be used in email template via template tags (see template tags in documentation)
* NEW: Maximum bookable days are now without limitation. You can choose the maximum days in the timeframe editor.
* NEW: We added 2 new menu items in the CommonsBooking section so that you can now the edit Commonsbooking categories for locations and items (rename, remove etc.)
* NEW: Hide Contact Details: It is now possible to configure whether contact details of the station are only displayed after the booking has been confirmed by the user. This prevents users from already receiving booking details for an unconfirmed booking and thus possibly already contacting the location without having completed the booking.
* ENHANCED: Added migration of elementor special fields
* ENHANCED: Added map link to dashboard
* ENHANCED: Validation of bookings optimized
* FIXED: Bookable timeframe without enddate caused some issues in frontend calendar. Now it is possible to leave end date empty to allow infinite booking timeframe
* FIXED: performance issue on some systems in backend view (issue #546)
* FIXED: cancelation of an unconfirmed booking triggered a cancelation mail to user and location. Now the cancelation mail will not be send anymore.  (issue #532)
* FIXED: fixed a timeframe validation error (isse #548)
* FIXED: calendar not shown in edge / explorer in some versions. Thanks to @danielappelt for fixing it
* FIXED: Added tooltips in map configuration
* FIXED: Multiple categories are not imported during migration.
* TEMPLATES: modification in templates: booking-single-form.php and booking-single.php 
* ENHANCED: Make CommonsBooking Menu entry fit better in WP Admin für Wordpress 5.7 #593

### 2.4.2 (15.02.2021)
* FIXED: Fixed permission issue on booking lists

### 2.4.1 (14.02.2021)
* FIXED: Avoid Uncaught Exception during Geo Coding on Update

### 2.4.0 (12.02.2021)
* NEW: Booking list for frontend users now available (my bookings)
* NEW: Booking Widget now available (Widget display links to my bookings, login, logout) 
* MODIFIED: Permissions changed so that only administrators can assign CBManagers to locations and items. #478
* ENHANCED: Implementent message if backend users try to open preview of timeframes other than bookings
* ENHANCED: Interface and layout map filter optimized
* FIXED: generated duplicate booking codes if location was changed in existing timeframe. Now booking codes are deleted if location is not assigned to a timeframe #466
* FIXED: Export booking codes as CSV caused formatting issues when opening in Excel for some users due to incorrect character encoding. UTF-8 encoding added to avoid this error. #467
* FIXED: Small Commons API compatibility issues #281
* ENHANCED: Added internal Class for better admin message management
* FIXED: issue with filtered item list with role CB Manager (pagination based on inital filter)
* FIXED: minor issue: Headers already sent error on restore default options
* ADDED: function to remove deprecated user roles from former commonsbooking versions. affected users will get the role 'subscriber'
* FIXED: migration issues when using elementor are solved. all postmeta fields are imported

### 2.3.2 (18.01.2021
* FIXED: map error due to missing option value

### 2.3.1 (16.01.2021)
* FIXED: minor translation issue

### 2.3 (15.01.2021)
* NEW: Map Feature now included in CommonsBooking. Map Feature was originally based on the Map Plugin made by fLotte Berlin. Many many thanks to fLotte for their great work and support.
* NEW: added automatic reset to default values for some options if they are empty but needed for the plugin to work properly
* NEW: Added customizable avilablity messages for location and item pages (can be set in options -> templates)
* ENHANCED: reworked save options process so that permalink page refresh is not longer needed after updating url slugs
* ENHANCED: Optimized timframe validation so that not overlapping weekdays on overlapping timeframes doesn't result in an validation error
* ENHANCED: API route
* ENHANCED: Removed default limitation of 2 months for maxium advance booking time. Now users can book as long as the timeframe is defined in advance. In a future release we will add the option to set the maximum advance booking time in admin options.
* FIXED: booking caelndar not shown on some iphone models in portrait mode

### 2.2.15 (25.12.2020)
* optmizized migration process
* fixed issue when default options fields are missing after migration
* added: set show booking-codes default=on to all imported timeframes from cb1

### 2.2.14
* fixed: error when using individual table prefix other than wp_
* fixed: refresh permalink on save individual slug (no need to call permalinks settings page after saving slug)
* fixed: categories not shown in gutenberg editor
* added: You can set if booking codes should be shown to user or not on fullday booking slots in timeframe settings (timeframe editor)

### 2.2.13
* Added notice to refresh permalinks due to unsolved issue

### 2.2.11
* Fixed bug default options not set on update

### 2.2.10
* Fixed template issues (usernmame not shown, formatting issues in mail an booking template)

### 2.2.9
* Fixed template issup pickup instructions not shown on booking page

### 2.2.8
* Updated translation and minor text edits
* Set default values on activation and upates
* Fix: 404-page after installation because of missing permalink refresh

### 2.2.7
* add: Updated translation

### 2.2.6
* Enhanced import wizard for automatic migration from previous Commons Booking version (version < 1.0). Migration of time frames, articles, locations, bookings, booking codes, settings for blocked days. During migration, parallel operation of the old and new version is possible. No data from the previous installation is deleted or changed.
* Unconfirmed bookings are automatically deleted (after approx. 10 minutes)
* Several usability improvements and bug fixes
* Improvements of the CommonsBooking API

### 2.2.0
* inital stable release

