<?php
/**
 * CalDAV sync for the Calendar plugin
 *
 * @version @package_version@
 * @author Daniel Morlock <daniel.morlock@awesome-it.de>
 *
 * Copyright (C) Awesome IT GbR <info@awesome-it.de>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

if (!class_exists('caldav_client')) {
	require_once (dirname(__FILE__).'/../../lib/caldav-client.php');
}

class caldav_sync
{
    const ACTION_NONE = 1;
    const ACTION_UPDATE = 2;
    const ACTION_CREATE = 4;

    private $cal_id = null;
    private $ctag = null;
    private $url = null;

    /**
     *  Default constructor for calendar synchronization adapter.
     *
     * @param array Hash array with caldav properties at least the following:
     *                    id: Calendar ID
     *            caldav_url: Caldav calendar URL.
     *           caldav_user: Caldav http basic auth user.
     *           caldav_pass: Password für caldav user.
     * caldav_oauth_provider: ID for optional OAuth2 provider
     *            caldav_tag: Caldav ctag for calendar.
     */
    public function __construct($cal)
    {
        $this->cal_id = $cal["id"];
        $this->url = $cal["caldav_url"];
        $this->ctag = isset($cal["caldav_tag"]) ? $cal["caldav_tag"] : null;

        // CalDAV client auth
        $username = isset($cal["caldav_user"]) ? $cal["caldav_user"] : null;
        $pass = isset($cal["caldav_pass"]) ? $cal["caldav_pass"] : null;
        $oauth_client = isset($cal["caldav_oauth_provider"]) && $cal["caldav_oauth_provider"] ?
            new oauth_client(rcmail::get_instance(), $cal["caldav_oauth_provider"]) : null;
        
        $this->caldav = new caldav_client($this->url, $username, $pass, $oauth_client);
    }

    /**
     * Getter for current calendar ctag.
     * @return string
     */
    public function get_ctag()
    {
        return $this->ctag;
    }

    /**
     * Determines whether current calendar needs to be synced
     * regarding the CalDAV ctag.
     *
     * @return True if the current calendar ctag differs from the CalDAV tag which
     *         indicates that there are changes that must be synched. Returns false
     *         if the calendar is up to date, no sync necesarry.
     */
    public function is_synced()
    {
        $is_synced = $this->ctag == $this->caldav->get_ctag() && $this->ctag;
        caldav_driver::debug_log("Ctag indicates that calendar \"$this->cal_id\" ".($is_synced ? "is synced." : "needs update!"));

        return $is_synced;
    }

    /**
     * Synchronizes given events with caldav server and returns updates.
     *
     * @param array List of hash arrays with event properties, must include "caldav_url" and "tag".
     * @return array Tuple containing the following lists:
     *
     * Caldav properties for events to be created or to be updated with the keys:
     *          url: Event ical URL relative to calendar URL
     *         etag: Remote etag of the event
     *  local_event: The local event in case of an update.
     * remote_event: The current event retrieved from caldav server.
     *
     * A list of event ids that are in sync.
     */
    public function get_updates($events)
    {
        $ctag = $this->caldav->get_ctag();

        if($ctag)
        {
            $this->ctag = $ctag;
            $etags = $this->caldav->get_etags();

            list($updates, $synced_event_ids) = $this->_get_event_updates($events, $etags);
            return array($this->_get_event_data($updates), $synced_event_ids);
        }
        else
        {
            caldav_driver::debug_log("Unkown error while fetching calendar ctag for calendar \"$this->cal_id\"!");
        }
        
        return null;
    }

    /**
     * Determines sync status and requried updates for the given events using given list of etags.
     *
     * @param array List of hash arrays with event properties, must include "caldav_url" and "caldav_tag".
     * @param array List of current remote etags.
     * @return array Tuple containing the following lists:
     *
     * Caldav properties for events to be created or to be updated with the keys:
     *          url: Event ical URL relative to calendar URL
     *         etag: Remote etag of the event
     *  local_event: The local event in case of an update.
     *
     * A list of event ids that are in sync.
     */
    private function _get_event_updates($events, $etags)
    {
        $updates = array();
        $in_sync = array();

        foreach ($etags as $etag)
        {
            $url = $etag["url"];
            $etag = $etag["etag"];
            $event_found = false;
            foreach($events as $event)
            {
	        if (str_replace("//", "/", $event["caldav_url"]) == $url)
                {
                    $event_found = true;

                    if ($event["caldav_tag"] != $etag)
                    {
                        caldav_driver::debug_log("Event ".$event["uid"]." needs update.");

                        array_push($updates, array(
                            "local_event" => $event,
                            "etag" => $etag,
                            "url" => $url
                        ));
                    }
                    else
                    {
                        array_push($in_sync, $event["id"]);
                    }
                }
            }

            if (!$event_found)
            {
                caldav_driver::debug_log("Found new event ".$url);

                array_push($updates, array(
                    "url" => $url,
                    "etag" => $etag
                ));
            }
        }

        return array($updates, $in_sync);
    }

    /**
     * Fetches event data and attaches it to the given update properties.
     *
     * @param $updates List of update properties.
     * @return array List of update properties with additional key "remote_event" containing the current caldav event.
     */
    private function _get_event_data($updates)
    {
        $urls = array();

        foreach ($updates as $update)
        {
            array_push($urls, $update["url"]);
        }

        $events = $this->caldav->get_events($urls);
        foreach($updates as &$update)
        {
            // Attach remote events to the appropriate updates.
            // Note that this assumes unique event URL's!
            $url = $update["url"];
            if($events[$url]) {
                $update["remote_event"] = $events[$url];
                $update["remote_event"]["calendar"] = $this->cal_id;
            }
        }

        return $updates;
    }

    /**
     * Creates the given event on the CalDAV server.
     *
     * @param array Hash array with event properties.
     * @return Event with updated "caldav_url" and "caldav_tag" attributes, false on error.
     */
    public function create_event($event)
    {
        $props = array(
            "caldav_url" => parse_url($this->url, PHP_URL_PATH)."/".$event["uid"].".ics",
            "caldav_tag" => null
        );

        caldav_driver::debug_log("Push new event to url ".$props["caldav_url"]);
        $result = $this->caldav->put_event($props["caldav_url"], $event);

        if($result == false || $result < 0) return false;
        return array_merge($event, $props);
    }

    /**
     * Updates the given event on the CalDAV server.
     *
     * @param array Hash array with event properties to update, must include "uid", "caldav_url" and "caldav_tag".
     * @return True on success, false on error, -1 if the given event/etag is not up to date.
     */
    public function update_event($event)
    {
        caldav_driver::debug_log("Updating event uid \"".$event["uid"]."\".");
        return $this->caldav->put_event($event["caldav_url"], $event, $event["caldav_tag"]);
    }

    /**
     * Removes the given event from the caldav server.
     *
     * @param array Hash array with events properties, must include "caldav_url".
     * @return True on success, false on error.
     */
    public function remove_event($event)
    {
        caldav_driver::debug_log("Removing event uid \"".$event["uid"]."\".");
        return $this->caldav->remove_event($event["caldav_url"]);
    }
};
?>
