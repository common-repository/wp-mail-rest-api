=== WP Mail REST-API ===
Contributors: marketingauftrag
Tags: wp mail, rest api
Requires at least: 5.1
Tested up to: 5.3.2
Requires PHP: 7.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
 
Send all e-mails created by Wordpress simply via a webhook. 
 
== Description ==

There are many SMTP plugins for Wordpress, but there are still many troubleshoots when sending email messages via Wordpress. With WP Mail REST-API you can send emails outside of Wordpress and this reduces the number of failed sendings.

Create a webhook (trigger) on Zapier, Integromat, Automate, Workato, Microsoft Flow or a service of your choice. Save your Webhook directly in WP Mail REST-API and click on Test Webhook. Now change to your Webhook provider and create an SMTP dispatch as action.
WP Mail REST-API provides you with the following variables for each dispatch:

- to
- subject
- message in html
- message in text
- from name
- from email
- attachments

WP Mail REST-API is the answer to the often asked question, "How to fix WordPress not sending Email Issue. 
There are many reasons why sending emails with WordPress fails. For example, your WordPress firewall or your hosting company's firewall may prevent you from sending messages for security reasons. If your message is sent externally via Webhook and an SMTP as action, you have simply solved your problem.

Of course you can also connect WP Mail REST-API with Outlook, Gmail, Hotmail, SMTP and other providers. If you want, you can also register and control every sent email in Google Sheet (as an action).

 
== Installation ==
 
1. Upload ` wp-mail-rest-api` directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Create a webhook by your web hook provider on which you want to receive your e-mails
4. Save this webhook in the WP Mail REST API 
5. Send a test message via the plugin
6. Link your webhook in Zapier, Automate and Integromat, Microsoft Flow, Workato or a service of your choice with an SMTP as action.