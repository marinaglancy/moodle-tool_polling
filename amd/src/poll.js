/**
 * User tour control library.
 *
 * @module     tool_polling/poll
 * @package    tool_polling
 * @copyright  2020 Marina Glancy
 */
define(['core/pubsub'], function(PubSub) {

    var params;
    var requestscounter = [];
    var pollURL;

    var checkRequestCounter = function() {
        var curDate = new Date(),
            curTime = curDate.getTime();
        requestscounter.push(curTime);
        requestscounter = requestscounter.slice(-10);
        // If there were 10 requests in less than 5 seconds, it must be an error. Stop polling.
        return !(requestscounter.length >= 10 && curTime - requestscounter[0] < 5000);
    };

    /**
     * Group same events that have 'groupevents' property together
     *
     * This is useful for events that will initiate AJAX request asking for updates.
     *
     * @param {[]} events
     * @return {[]}
     */
    var groupEvents = function(events) {
        var groupped = [],
            idx,
            gidx;
        for (idx in events) {
            var found = false;
            if (events[idx].groupevents) {
                for (gidx in groupped) {
                    if (groupped[gidx].event === events[idx].event && groupped[gidx].groupevents) {
                        groupped[gidx].event.id = events[idx].id;
                        found = true;
                        break;
                    }
                }
            }
            if (!found) {
                groupped.push(events[idx]);
            }
        }
        return groupped;
    };

    var poll = function() {
        if (!checkRequestCounter()) {
            // Too many requests, stop polling.
            return;
        }

        var ajax = new XMLHttpRequest(),
            json;
        ajax.onreadystatechange = function() {
            if (this.readyState === 4 && this.status === 200) {
                if (this.status === 200) {
                    try {
                        json = JSON.parse(this.responseText);
                    } catch {
                        poll();
                        return;
                    }

                    if (!json.success || json.success !== 1) {
                        // Poll.php returned an error or an exception. Stop trying to poll.
                        return;
                    }

                    // Process results - trigger all necessary Javascript/jQuery events.
                    var groupped = groupEvents(json.results);
                    for (var i in groupped) {
                        PubSub.publish(groupped[i].event, groupped[i].addinfo);
                        // Remember the last id.
                        params.fromid = groupped[i].id;
                    }

                    // And start polling again.
                    poll();
                } else {
                    // Must be a server timeout or loss of network - start new process.
                    poll();
                }
            }
        };
        var url = pollURL + '?userid=' + encodeURIComponent(params.userid) +
            '&token=' + encodeURIComponent(params.token) + '&fromid=' + encodeURIComponent(params.fromid) +
            '&pageURL=' + encodeURIComponent(params.pageurl);
        ajax.open('GET', url, true);
        ajax.send();
    };

    return /** @alias tool_polling/poll */ {
        init: function(userId, token, fromId, pageURL, pollURLParam) {
            if (params && params.userid) {
                // Already initialised.
                return;
            }
            params = {
                userid: userId,
                token: token,
                fromid: fromId,
                pageurl: pageURL
            };
            pollURL = pollURLParam;
            poll();
        }
    };
});
