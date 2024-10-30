=== Locale for WordPress ===
Contributors: Locale, Inpsyde, Eurotext, paddyullrich, wido
Tags: translation, localization
Requires at least: 4.6
Tested up to: 6.0
Stable tag: 1.0.0
Requires PHP: 7.2 or higher
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

With _Locale_, connect your WordPress Multisite to your Language Service Providers, like [RWS](https://www.rws.com/), [TextMaster](https://www.textmaster.com/) and [Alpha](https://www.alphacrc.com/).

## Description

With the WordPress plugin from _Locale_ you can send your pages and posts to your LSP. The plugin requires _MultilingualPress_ 2 or 3.

## Setup

1. In your WordPress project, go to **Locale > Settings > Credentials**
2. Copy the API key
3. Paste it into the **API key** field in WordPress
4. Click on **Save changes**

You are now ready to create, and import translation jobs.

## Creating Translation Jobs

1. Go to your posts or pages list
2. In the **Bulk actions** dropdown, select **Bulk Translate**
3. Tick the checkboxes of the posts or pages that you want to translate
4. And click on the **Apply** button beside **Bulk Translate**

This will create a translation job, that can later be imported into the languages that you have configured in _MultilingualPress_

## Managing Translation Jobs

1. Go to **Locale > Jobs**
2. Here you can view, or delete an existing translation job

## Importing Translation Jobs

1. Go to **Locale > Jobs**
2. Select the translation job that you want to import
3. On the translation job information box, click on **Import**

This will update the posts indicated in the translation job with the corresponding translations based on the matching language codes.

### Project Statuses
Here are the following statuses that a translation job goes through before finally being imported:

1. _In Progress_  - The translation is in progress.
2. _Delivered_ - The translation has been completed, and is now ready for importing.

The **Import** button in a translation job's information box will only be displayed once it is delivered. Click on **Update**, occasionally to check on the translation's status.

== Installation ==
1. Go to **Network Admin > Plugins**
2. Click on **Add New**, and search for _Locale_
3. Click on **Install Now**
4. Click on **Activate**
5. Install _MultilingualPress_ 2 or 3

== Frequently Asked Questions ==
= Do I need an API key? =
Yes, you need an API key to connect with the [Locale](https://www.locale.to/).

= Where do I get my own API key? =
1. Sign in to your [Locale account](https://app.locale.to/en/signin/)
2. From the _Dashboard_, click **Add another project**
3. Under the _CMS_ category, look for **WordPress**, and hit **Create Project**
4. Enter the **Source language**, and **Target languages**
   > **Note**: It is important that the language codes of the languages that you have configured in _MultilingualPress_ match the language codes of **Source language**, and **Target languages**. Otherwise, the translations will not be imported correctly.
5. Click on **Add Project**

== Screenshots ==
1. Where you add your Locale's API key
2. Create a new translation job
3. Your list of translation jobs

== Changelog ==
= 1.0.0 =
* First version of Locale released with basic functions.
