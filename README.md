Mailchimp Integration
=====================

Example code showing integrating of Nexmo and MailChimp. Shows acting on a 
MailChimp list via SMS, as well as using MailChimp list fields to broadcast 
SMS to a group.

Subscribing Addresses
---------------------
The `subscribe.php` script checks incoming SMS messages for an email address 
and subscribes those addresses to a MailChimp list. The sender's number is 
stored in MailChimp as well.

Sending SMS to a List
---------------------
The `broadcast.php` script fetches a MailChimp list, and sends a pre-defined 
SMS when a phone number is found in the configured field.

Configure
---------
Configuration can be set in `config.ini` or as environment variables, with 
environment variables taking precedence. 
