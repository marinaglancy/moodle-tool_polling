# Polling page updates #

Allows to push information to users web browsers without need to refresh the page.
It uses the long-polling method to ask the server for updates.

Page that wants to listen to updates should initialise the polling and implement the
Javascript event listener.

Any other process (including other users' sessions and cron) can push notifications.
Notification will include the name of the Javascript event and additional data for
the listener.

## How to use

### Check if polling is available (it may be disabled in settings)

```
if (tool_polling_notification::is_enabled()) {
    // ...
}
```

Only makes sense if you provide alternative check for updates or you want to warn user
that they need to refresh the page to check for updates.

### Initialise (this means that the current page wants to listen to updates)

```
tool_polling_notification::init();
```

If no plugin or component calls init() the page will not be doing polling at all.

### Push notification

Single user:
```
tool_polling_notification::add_for_user(int $userid, string $event, bool $groupevents = false, array $addinfo = []);
```
Multiple users:
```
tool_polling_notification::add_for_users($userids, string $event, bool $groupevents = false, array $addinfo = []);
```
Here $userids may be either array or a callback that returns array of userids. Callback
will not be executed if polling is disabled.

### Javascript listener

Example of a listener to event 'message-received':

```
require(['core/pubsub'], function(PubSub) {
    PubSub.subscribe('message-received', updateMessages);
}
```

### Alternative way of calling plugin

When tool_polling is not specifically listed in the dependencies, the functions
may be called in the following way:

```
component_class_callback('tool_polling_notification', 'init', []);
$isenabled = component_class_callback('tool_polling_notification', 'is_enabled', [], false);
component_class_callback('tool_polling_notification', 'add_for_user', [$userid, 'message-received']);
component_class_callback('tool_polling_notification', 'add_for_users', [$userids, 'someeventname']);
```