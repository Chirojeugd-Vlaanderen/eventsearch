# be.chiro.civi.eventsearch

This CiviCRM extension provides some event related report templates.

I chose to create reports, and not custom searches, because I think that custom
searches are more relevant for searching contacts. Also the filtering
that is built in the reporting interface, is very nice.

This extension is rather basic. I welcome pull requests :-)

If you want to use the templates, you need to create a new report based on
one of the templates. You can do this by clicking 'Reports', 'Event Reports',
'New Event Report'.

## Available templates

### Participant stats

This template shows a list of events, with the number of counted and uncounted
participants per template. The numbers are hyperlinks to a list that shows the
actual participants.

You can filter on event type and event date.

### Event List

This template allows searching on event location, or on event custom fields, which is
useful if you want to manage a whole lot of events.

If you want to use this, you need to create a report based on this template;
you'll find it in Administration, CiviReport, New report from template. 

