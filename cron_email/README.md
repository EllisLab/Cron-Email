# Cron-Email

This plugin requires the [Cron plugin](https://github.com/EllisLab/Cron). See that add-on for documentation on use of the base plugin.

Allows you to schedule the sending of an email to the email addresses specified in the parameters. The tag data (that which is between the opening and closing tag) will be the contents of the email's message.

As a perk, you can have the tag data parsed exactly as if it were part of an `{exp:channel:entries}` tag. This allows you to, say, schedule the sending of an email at the beginning of every day containing the most recently posted entries of that day.

## Usage

### plugin="cron_email"

#### Example Usage

    {exp:cron plugin="cron_email" day="23" minute="59" to="webmaster@mysite.com" subject="Daily Email"}

        Hello There!

    {/exp:cron}

#### Parameters

- `to=""` - Recipient(s) of email [required]
- `from=""` - Sender of email [optional, default webmaster of site]
- `cc=""` - CC Recipient(s) of email [required]
- `bcc=""` - BCC Recipient(s) of email [required]
- `subject=""` - Subject line of email [required]
- `parse_tag=""` - If set to 'on' it will parse the tagdata as if it were part of a
`{exp:channel:entries}` tag. When set to 'on' the tag will accept all of the usual
parameters for the `{exp:channel:entries}` tag as well. [optional]

#### Pair Variables

- `{email_top}{/email_top}` - When parse_tag is set to "on" the content between this variable pair will be removed from
the tagdata (i.e. not parsed) and placed at the top of the sent email. Think email heading and opening statement
- `{email_bottom}{/email_bottom}` - When parse_tag is set to "on" the content between this variable pair will be removed from
the tagdata (i.e. not parsed) and placed at the bottom of the sent email. Think signature.



## Change Log

- 2.0.1

    - Added add-on icon
    - Update license information

- 2.0
	- Updated plugin to be 3.0 compatible
- 1.1
	- Updated plugin to be 2.0 compatible
