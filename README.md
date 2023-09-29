# Gravity Forms Email Filtering Add-On

Adds the ability to filter domains on the email field. Forked from CrossPeak Software's [Gravity Forms Email Blacklist](https://wordpress.org/plugins/gravity-forms-email-blacklist/) plugin.

## Installation

Download the latest zip from [Releases](https://github.com/KineticTeam/gravityforms-addon-email-filtering/releases).

Install like any other plugin and activate.

## Description from the original plugin

This plugin allows site admins to create a list of domains that if used in a Gravity Forms email field, will cause a validation error and block the submission. A default email denylist and validation message can be created to use across all email fields. These default settings can be overridden on a per email field basis.

Global settings can be added on `Forms > Settings > Email Filtering`. To add settings to an individual email field, select the field and navigate to the `Advanced Settings` tab.

This plugin works by blocking either individual email addresses (ex. jsmith@example.com), email address domains (ex. gmail.com), and/or email address top-level domains (ex. *.com).

[Original readme and changelog](./crosspeak-readme.txt)
