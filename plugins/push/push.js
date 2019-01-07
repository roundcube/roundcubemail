/*
 * Push plugin
 *
 * Websocket code based on https://github.com/mattermost/mattermost-redux/master/client/websocket_client.js
 *
 * @author Aleksander Machniak <alec@alec.pl>
 *
 * @licstart  The following is the entire license notice for the
 * JavaScript code in this file.
 *
 * Copyright (C) 2015-2018 Mattermost, Inc.
 * Copyright (C) 2010-2019 The Roundcube Dev Team
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see http://www.gnu.org/licenses/.
 */

var MAX_WEBSOCKET_FAILS = 7;
var MIN_WEBSOCKET_RETRY_TIME = 3 * 1000;        // 3 sec
var MAX_WEBSOCKET_RETRY_TIME = 5 * 60 * 1000;   // 5 mins
var WEBSOCKET_PING_INTERVAL = 30 * 1000;        // 30 sec

function PushSocketClient()
{
    var Socket;

    this.conn = null;
    this.connectionUrl = null;
    this.sequence = 1;
    this.connectFailCount = 0;
    this.eventCallback = null;
    this.firstConnectCallback = null;
    this.reconnectCallback = null;
    this.errorCallback = null;
    this.closeCallback = null;
    this.connectingCallback = null;
    this.dispatch = null;
    this.getState = null;
    this.stop = false;
    this.platform = '';
    this.ping_timeout = null;
    this.debug = false;

    this.initialize = function(dispatch, getState, opts)
    {
        var forceConnection = opts.forceConnection || true,
            webSocketConnector = opts.webSocketConnector || WebSocket,
            connectionUrl = opts.connectionUrl,
            platform = opts.platform,
            self = this;

        if (platform) {
            this.platform = platform;
        }

        if (forceConnection) {
            this.stop = false;
        }

        this.debug = opts.debug;

        return new Promise(function(resolve, reject) {
            if (self.conn) {
                resolve();
                return;
            }

            if (connectionUrl == null) {
                console.error('websocket must have connection url');
                reject('websocket must have connection url');
                return;
            }

            if (!dispatch) {
                console.error('websocket must have a dispatch');
                reject('websocket must have a dispatch');
                return;
            }

            if (self.connectFailCount === 0) {
                self.log('websocket connecting to ' + connectionUrl);
            }

            Socket = webSocketConnector;
            if (self.connectingCallback) {
                self.connectingCallback(dispatch, getState);
            }

            var regex = /^(?:https?|wss?):(?:\/\/)?[^/]*/;
            var captured = (regex).exec(connectionUrl);
            var origin;

            if (captured) {
                origin = captured[0];

                if (platform === 'android') {
                    // this is done cause for android having the port 80 or 443 will fail the connection
                    // the websocket will append them
                    var split = origin.split(':');
                    var port = split[2];
                    if (port === '80' || port === '443') {
                        origin = split[0] + ':' + split[1];
                    }
                }
            } else {
                // If we're unable to set the origin header, the websocket won't connect, but the URL is likely malformed anyway
                console.warn('websocket failed to parse origin from ' + connectionUrl);
            }

            self.conn = new Socket(connectionUrl, [], {headers: {origin}});
            self.connectionUrl = connectionUrl;
            self.dispatch = dispatch;
            self.getState = getState;

            self.conn.onopen = function() {
                self.log('websocket connected');

                if (self.connectFailCount > 0) {
                    self.log('websocket re-established connection');
                    if (self.reconnectCallback) {
                        self.reconnectCallback(self.dispatch, self.getState);
                    }
                } else if (self.firstConnectCallback) {
                    self.firstConnectCallback(self.dispatch, self.getState);
                }

                self.connectFailCount = 0;
                resolve();
            };

            self.conn.onclose = function() {
                self.conn = null;
                self.sequence = 1;

                if (self.connectFailCount === 0) {
                    self.log('websocket closed');
                }

                self.connectFailCount++;

                clearTimeout(this.ping_timeout);

                if (self.closeCallback) {
                    self.closeCallback(self.connectFailCount, self.dispatch, self.getState);
                }

                var retryTime = MIN_WEBSOCKET_RETRY_TIME;

                // If we've failed a bunch of connections then start backing off
                if (self.connectFailCount > MAX_WEBSOCKET_FAILS) {
                    retryTime = MIN_WEBSOCKET_RETRY_TIME * self.connectFailCount;
                    if (retryTime > MAX_WEBSOCKET_RETRY_TIME) {
                        retryTime = MAX_WEBSOCKET_RETRY_TIME;
                    }
                }

                setTimeout(function() {
                        if (self.stop) {
                            return;
                        }
                        self.initialize(dispatch, getState, Object.assign({}, opts, {forceConnection: true}));
                    },
                    retryTime
                );
            };

            self.conn.onerror = function(evt) {
                if (self.connectFailCount <= 1) {
                    self.log('websocket error');
                    console.error(evt);
                }

                clearTimeout(this.ping_timeout);

                if (self.errorCallback) {
                    self.errorCallback(evt, self.dispatch, self.getState);
                }
            };

            self.conn.onmessage = function(evt) {
                var msg = JSON.parse(evt.data);

                self.log(msg);

                if (msg.error) {
                    console.warn(msg);
                }
                else if (self.eventCallback) {
                    self.eventCallback(msg, self.dispatch, self.getState);
                }
                self.ping();
            };
        });
    }

    this.setConnectingCallback = function(callback)
    {
        this.connectingCallback = callback;
    }

    this.setEventCallback = function(callback)
    {
        this.eventCallback = callback;
    }

    this.setFirstConnectCallback = function(callback)
    {
        this.firstConnectCallback = callback;
    }

    this.setReconnectCallback = function(callback)
    {
        this.reconnectCallback = callback;
    }

    this.setErrorCallback = function(callback)
    {
        this.errorCallback = callback;
    }

    this.setCloseCallback = function(callback)
    {
        this.closeCallback = callback;
    }

    this.log = function(data)
    {
        if (this.debug) {
            console.log(data);
        }
    }

    this.ping = function()
    {
        var self = this;
        clearTimeout(this.ping_timeout);
        this.ping_timeout = setTimeout(function() { self.sendMessage('ping', {}); }, WEBSOCKET_PING_INTERVAL);
    }

    this.close = function(stop)
    {
        this.stop = stop;
        this.connectFailCount = 0;
        this.sequence = 1;

        if (this.conn && this.conn.readyState === Socket.OPEN) {
            this.conn.onclose = function(){};
            this.conn.close();
            this.conn = null;
            this.log('websocket closed');
        }
    }

    this.sendMessage = function(action, data)
    {
        var msg = $.extend({}, data, {action: action, seq: this.sequence++});

        if (this.conn && this.conn.readyState === Socket.OPEN) {
            this.conn.send(JSON.stringify(msg));
            this.ping();
        } else if (!this.conn || this.conn.readyState === Socket.CLOSED) {
            this.conn = null;
            this.initialize(this.dispatch, this.getState, {forceConnection: true, platform: this.platform});
        }
    }
}

/**
 * Initializes and starts websocket connection with Mattermost
 */
function push_websocket_init()
{
    var api = new PushSocketClient(),
        onconnect = function() {
            api.sendMessage('authenticate', {token: rcmail.env.request_token, session: rcmail.env.sessid});
        };

    api.setEventCallback(function(e) { push_event_handler(e); });
    api.setFirstConnectCallback(onconnect);
    api.setReconnectCallback(onconnect);

    api.initialize({}, {}, {connectionUrl: rcmail.env.push_url, debug: rcmail.env.push_debug});
}

/**
 * Handles websocket events
 */
function push_event_handler(event)
{
    if (!event || !event.event) {
        return;
    }

    var event_name = event.event,
        folder = event.folder_name;

    // All events can provide unseen messages count,
    // mark the specified folder accordingly
    if ('unseen' in event && folder) {
        rcmail.set_unread_count(folder, event.unseen, folder == 'INBOX',
            event_name == 'MessageNew' ? 'recent' : null);
    }

    if ('exists' in event && folder === rcmail.env.trash_mailbox) {
        rcmail.set_trash_count(event.exists);
    }

    if (folder === rcmail.env.mailbox) {
        // New messages or deleted messages in the current folder
        if (event_name === 'MessageNew' || event_name === 'MessageAppend'
            || event_name === 'MessageExpire' || event_name === 'MessageExpunge'
            || event_name === 'MessageCopy' || event_name === 'MessageMove'
        ) {
            // TODO: Update messages list
        }

        if (event_name === 'FlagsSet' || event_name === 'FlagsClear') {
            // TODO: Update messages list
        }
    }

    if (event_name === 'MailboxCreate' || event_name === 'MailboxDelete'
        || event_name === 'MailboxRename' || event_name === 'MailboxSubscribe'
        || event_name === 'MailboxUnSubscribe'
    ) {
        // TODO: display a notification and ask the user if he want's to reload the page
        // to refresh folders list, or (more complicated) update folders list automatically
    }

    if (event_name === 'MessageNew') {
        // TODO: display newmail_notifier-like notification about a new message
    }
}

window.WebSocket && window.rcmail && rcmail.addEventListener('init', function() {
    push_websocket_init();
});
