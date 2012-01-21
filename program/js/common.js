/*
 +-----------------------------------------------------------------------+
 | Roundcube common js library                                           |
 |                                                                       |
 | This file is part of the Roundcube web development suite              |
 | Copyright (C) 2005-2012, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
 
 $Id$
*/

// Constants
var CONTROL_KEY = 1;
var SHIFT_KEY = 2;
var CONTROL_SHIFT_KEY = 3;


/**
 * Default browser check class
 * @constructor
 */
function roundcube_browser()
{
  var n = navigator;

  this.ver = parseFloat(n.appVersion);
  this.appver = n.appVersion;
  this.agent = n.userAgent;
  this.agent_lc = n.userAgent.toLowerCase();
  this.name = n.appName;
  this.vendor = n.vendor ? n.vendor : '';
  this.vendver = n.vendorSub ? parseFloat(n.vendorSub) : 0;
  this.product = n.product ? n.product : '';
  this.platform = String(n.platform).toLowerCase();
  this.lang = (n.language) ? n.language.substring(0,2) :
              (n.browserLanguage) ? n.browserLanguage.substring(0,2) :
              (n.systemLanguage) ? n.systemLanguage.substring(0,2) : 'en';

  this.win = (this.platform.indexOf('win') >= 0);
  this.mac = (this.platform.indexOf('mac') >= 0);
  this.linux = (this.platform.indexOf('linux') >= 0);
  this.unix = (this.platform.indexOf('unix') >= 0);

  this.dom = document.getElementById ? true : false;
  this.dom2 = (document.addEventListener && document.removeEventListener);

  this.ie = (document.all && !window.opera);
  this.ie4 = (this.ie && !this.dom);
  this.ie5 = (this.dom && this.appver.indexOf('MSIE 5')>0);
  this.ie8 = (this.dom && this.appver.indexOf('MSIE 8')>0);
  this.ie9 = (this.dom && this.appver.indexOf('MSIE 9')>0);
  this.ie7 = (this.dom && this.appver.indexOf('MSIE 7')>0);
  this.ie6 = (this.dom && !this.ie8 && !this.ie7 && this.appver.indexOf('MSIE 6')>0);

  this.ns = ((this.ver < 5 && this.name == 'Netscape') || (this.ver >= 5 && this.vendor.indexOf('Netscape') >= 0));
  this.chrome = (this.agent_lc.indexOf('chrome') > 0);
  this.safari = (!this.chrome && (this.agent_lc.indexOf('safari') > 0 || this.agent_lc.indexOf('applewebkit') > 0));
  this.mz = (this.dom && !this.ie && !this.ns && !this.chrome && !this.safari && this.agent.indexOf('Mozilla') >= 0);
  this.konq   = (this.agent_lc.indexOf('konqueror') > 0);
  this.iphone = (this.safari && this.agent_lc.indexOf('iphone') > 0);
  this.ipad = (this.safari && this.agent_lc.indexOf('ipad') > 0);
  this.opera = window.opera ? true : false;

  if (this.opera && window.RegExp)
    this.vendver = (/opera(\s|\/)([0-9\.]+)/.test(this.agent_lc)) ? parseFloat(RegExp.$2) : -1;
  else if (this.chrome && window.RegExp)
    this.vendver = (/chrome\/([0-9\.]+)/.test(this.agent_lc)) ? parseFloat(RegExp.$1) : 0;
  else if (!this.vendver && this.safari)
    this.vendver = (/(safari|applewebkit)\/([0-9]+)/.test(this.agent_lc)) ? parseInt(RegExp.$2) : 0;
  else if ((!this.vendver && this.mz) || this.agent.indexOf('Camino')>0)
    this.vendver = (/rv:([0-9\.]+)/.test(this.agent)) ? parseFloat(RegExp.$1) : 0;
  else if (this.ie && window.RegExp)
    this.vendver = (/msie\s+([0-9\.]+)/.test(this.agent_lc)) ? parseFloat(RegExp.$1) : 0;
  else if (this.konq && window.RegExp)
    this.vendver = (/khtml\/([0-9\.]+)/.test(this.agent_lc)) ? parseFloat(RegExp.$1) : 0;

  // get real language out of safari's user agent
  if (this.safari && (/;\s+([a-z]{2})-[a-z]{2}\)/.test(this.agent_lc)))
    this.lang = RegExp.$1;

  this.dhtml = ((this.ie4 && this.win) || this.ie5 || this.ie6 || this.ns4 || this.mz);
  this.vml = (this.win && this.ie && this.dom && !this.opera);
  this.pngalpha = (this.mz || (this.opera && this.vendver >= 6) || (this.ie && this.mac && this.vendver >= 5) ||
                   (this.ie && this.win && this.vendver >= 5.5) || this.safari);
  this.opacity = (this.mz || (this.ie && this.vendver >= 5.5 && !this.opera) || (this.safari && this.vendver >= 100));
  this.cookies = n.cookieEnabled;

  // test for XMLHTTP support
  this.xmlhttp_test = function()
  {
    var activeX_test = new Function("try{var o=new ActiveXObject('Microsoft.XMLHTTP');return true;}catch(err){return false;}");
    this.xmlhttp = (window.XMLHttpRequest || (window.ActiveXObject && activeX_test()));
    return this.xmlhttp;
  };

  // set class names to html tag according to the current user agent detection
  // this allows browser-specific css selectors like "html.chrome .someclass"
  this.set_html_class = function()
  {
    var classname = ' js';

    if (this.ie)
      classname += ' ie ie'+parseInt(this.vendver);
    else if (this.opera)
      classname += ' opera';
    else if (this.konq)
      classname += ' konqueror';
    else if (this.safari)
      classname += ' chrome';
    else if (this.chrome)
      classname += ' chrome';

    if (this.iphone)
      classname += ' iphone';
    else if (this.ipad)
      classname += ' ipad';
    else if (this.safari || this.chrome)
      classname += ' webkit';

    if (document.documentElement)
      document.documentElement.className += classname;
  };
};


// static functions for DOM event handling
var rcube_event = {

/**
 * returns the event target element
 */
get_target: function(e)
{
  e = e || window.event;
  return e && e.target ? e.target : e.srcElement;
},

/**
 * returns the event key code
 */
get_keycode: function(e)
{
  e = e || window.event;
  return e && e.keyCode ? e.keyCode : (e && e.which ? e.which : 0);
},

/**
 * returns the event key code
 */
get_button: function(e)
{
  e = e || window.event;
  return e && e.button !== undefined ? e.button : (e && e.which ? e.which : 0);
},

/**
 * returns modifier key (constants defined at top of file)
 */
get_modifier: function(e)
{
  var opcode = 0;
  e = e || window.event;

  if (bw.mac && e)
    opcode += (e.metaKey && CONTROL_KEY) + (e.shiftKey && SHIFT_KEY);
  else if (e)
    opcode += (e.ctrlKey && CONTROL_KEY) + (e.shiftKey && SHIFT_KEY);

  return opcode;
},

/**
 * Return absolute mouse position of an event
 */
get_mouse_pos: function(e)
{
  if (!e) e = window.event;
  var mX = (e.pageX) ? e.pageX : e.clientX,
    mY = (e.pageY) ? e.pageY : e.clientY;

  if (document.body && document.all) {
    mX += document.body.scrollLeft;
    mY += document.body.scrollTop;
  }

  if (e._offset) {
    mX += e._offset.left;
    mY += e._offset.top;
  }

  return { x:mX, y:mY };
},

/**
 * Add an object method as event listener to a certain element
 */
add_listener: function(p)
{
  if (!p.object || !p.method)  // not enough arguments
    return;
  if (!p.element)
    p.element = document;

  if (!p.object._rc_events)
    p.object._rc_events = [];

  var key = p.event + '*' + p.method;
  if (!p.object._rc_events[key])
    p.object._rc_events[key] = function(e){ return p.object[p.method](e); };

  if (p.element.addEventListener)
    p.element.addEventListener(p.event, p.object._rc_events[key], false);
  else if (p.element.attachEvent) {
    // IE allows multiple events with the same function to be applied to the same object
    // forcibly detach the event, then attach
    p.element.detachEvent('on'+p.event, p.object._rc_events[key]);
    p.element.attachEvent('on'+p.event, p.object._rc_events[key]);
  }
  else
    p.element['on'+p.event] = p.object._rc_events[key];
},

/**
 * Remove event listener
 */
remove_listener: function(p)
{
  if (!p.element)
    p.element = document;

  var key = p.event + '*' + p.method;
  if (p.object && p.object._rc_events && p.object._rc_events[key]) {
    if (p.element.removeEventListener)
      p.element.removeEventListener(p.event, p.object._rc_events[key], false);
    else if (p.element.detachEvent)
      p.element.detachEvent('on'+p.event, p.object._rc_events[key]);
    else
      p.element['on'+p.event] = null;
  }
},

/**
 * Prevent event propagation and bubbeling
 */
cancel: function(evt)
{
  var e = evt ? evt : window.event;
  if (e.preventDefault)
    e.preventDefault();
  if (e.stopPropagation)
    e.stopPropagation();

  e.cancelBubble = true;
  e.returnValue = false;
  return false;
},

touchevent: function(e)
{
  return { pageX:e.pageX, pageY:e.pageY, offsetX:e.pageX - e.target.offsetLeft, offsetY:e.pageY - e.target.offsetTop, target:e.target, istouch:true };
}

};


/**
 * rcmail objects event interface
 */
function rcube_event_engine()
{
  this._events = {};
};

rcube_event_engine.prototype = {

/**
 * Setter for object event handlers
 *
 * @param {String}   Event name
 * @param {Function} Handler function
 * @return Listener ID (used to remove this handler later on)
 */
addEventListener: function(evt, func, obj)
{
  if (!this._events)
    this._events = {};
  if (!this._events[evt])
    this._events[evt] = [];

  var e = {func:func, obj:obj ? obj : window};
  this._events[evt][this._events[evt].length] = e;
},

/**
 * Removes a specific event listener
 *
 * @param {String} Event name
 * @param {Int}    Listener ID to remove
 */
removeEventListener: function(evt, func, obj)
{
  if (obj === undefined)
    obj = window;

  for (var h,i=0; this._events && this._events[evt] && i < this._events[evt].length; i++)
    if ((h = this._events[evt][i]) && h.func == func && h.obj == obj)
      this._events[evt][i] = null;
},

/**
 * This will execute all registered event handlers
 *
 * @param {String} Event to trigger
 * @param {Object} Event object/arguments
 */
triggerEvent: function(evt, e)
{
  var ret, h;
  if (e === undefined)
    e = this;
  else if (typeof e === 'object')
    e.event = evt;

  if (this._events && this._events[evt] && !this._event_exec) {
    this._event_exec = true;
    for (var i=0; i < this._events[evt].length; i++) {
      if ((h = this._events[evt][i])) {
        if (typeof h.func === 'function')
          ret = h.func.call ? h.func.call(h.obj, e) : h.func(e);
        else if (typeof h.obj[h.func] === 'function')
          ret = h.obj[h.func](e);

        // cancel event execution
        if (ret !== undefined && !ret)
          break;
      }
    }
    if (ret && ret.event) {
      try {
        delete ret.event;
      } catch (err) {
        // IE6-7 doesn't support deleting HTMLFormElement attributes (#1488017)
        $(ret).removeAttr('event');
      }
    }
  }

  this._event_exec = false;
  if (e.event) {
    try {
      delete e.event;
    } catch (err) {
      // IE6-7 doesn't support deleting HTMLFormElement attributes (#1488017)
      $(e).removeAttr('event');
    }
  }

  return ret;
}

};  // end rcube_event_engine.prototype



/**
 * Roundcube generic layer (floating box) class
 *
 * @constructor
 */
function rcube_layer(id, attributes)
{
  this.name = id;

  // create a new layer in the current document
  this.create = function(arg)
  {
    var l = (arg.x) ? arg.x : 0,
      t = (arg.y) ? arg.y : 0,
      w = arg.width,
      h = arg.height,
      z = arg.zindex,
      vis = arg.vis,
      parent = arg.parent,
      obj = document.createElement('DIV');

    obj.id = this.name;
    obj.style.position = 'absolute';
    obj.style.visibility = (vis) ? (vis==2) ? 'inherit' : 'visible' : 'hidden';
    obj.style.left = l+'px';
    obj.style.top = t+'px';
    if (w)
	  obj.style.width = w.toString().match(/\%$/) ? w : w+'px';
    if (h)
	  obj.style.height = h.toString().match(/\%$/) ? h : h+'px';
    if (z)
      obj.style.zIndex = z;

    if (parent)
      parent.appendChild(obj);
    else
      document.body.appendChild(obj);

    this.elm = obj;
  };

  // create new layer
  if (attributes != null) {
    this.create(attributes);
    this.name = this.elm.id;
  }
  else  // just refer to the object
    this.elm = document.getElementById(id);

  if (!this.elm)
    return false;


  // ********* layer object properties *********

  this.css = this.elm.style;
  this.event = this.elm;
  this.width = this.elm.offsetWidth;
  this.height = this.elm.offsetHeight;
  this.x = parseInt(this.elm.offsetLeft);
  this.y = parseInt(this.elm.offsetTop);
  this.visible = (this.css.visibility=='visible' || this.css.visibility=='show' || this.css.visibility=='inherit') ? true : false;


  // ********* layer object methods *********

  // move the layer to a specific position
  this.move = function(x, y)
  {
    this.x = x;
    this.y = y;
    this.css.left = Math.round(this.x)+'px';
    this.css.top = Math.round(this.y)+'px';
  };

  // change the layers width and height
  this.resize = function(w,h)
  {
    this.css.width  = w+'px';
    this.css.height = h+'px';
    this.width = w;
    this.height = h;
  };

  // show or hide the layer
  this.show = function(a)
  {
    if(a == 1) {
      this.css.visibility = 'visible';
      this.visible = true;
    }
    else if(a == 2) {
      this.css.visibility = 'inherit';
      this.visible = true;
    }
    else {
      this.css.visibility = 'hidden';
      this.visible = false;
    }
  };

  // write new content into a Layer
  this.write = function(cont)
  {
    this.elm.innerHTML = cont;
  };

};


// check if input is a valid email address
// By Cal Henderson <cal@iamcal.com>
// http://code.iamcal.com/php/rfc822/
function rcube_check_email(input, inline)
{
  if (input && window.RegExp) {
    var qtext = '[^\\x0d\\x22\\x5c\\x80-\\xff]',
      dtext = '[^\\x0d\\x5b-\\x5d\\x80-\\xff]',
      atom = '[^\\x00-\\x20\\x22\\x28\\x29\\x2c\\x2e\\x3a-\\x3c\\x3e\\x40\\x5b-\\x5d\\x7f-\\xff]+',
      quoted_pair = '\\x5c[\\x00-\\x7f]',
      quoted_string = '\\x22('+qtext+'|'+quoted_pair+')*\\x22',
      // Use simplified domain matching, because we need to allow Unicode characters here
      // So, e-mail address should be validated also on server side after idn_to_ascii() use
      //domain_literal = '\\x5b('+dtext+'|'+quoted_pair+')*\\x5d',
      //sub_domain = '('+atom+'|'+domain_literal+')',
      // allow punycode/unicode top-level domain
      domain = '([^@\\x2e]+\\x2e)+([^\\x00-\\x40\\x5b-\\x60\\x7b-\\x7f]{2,}|xn--[a-z0-9]{2,})',
      // ICANN e-mail test (http://idn.icann.org/E-mail_test)
      icann_domains = [
        '\\u0645\\u062b\\u0627\\u0644\\x2e\\u0625\\u062e\\u062a\\u0628\\u0627\\u0631',
        '\\u4f8b\\u5b50\\x2e\\u6d4b\\u8bd5',
        '\\u4f8b\\u5b50\\x2e\\u6e2c\\u8a66',
        '\\u03c0\\u03b1\\u03c1\\u03ac\\u03b4\\u03b5\\u03b9\\u03b3\\u03bc\\u03b1\\x2e\\u03b4\\u03bf\\u03ba\\u03b9\\u03bc\\u03ae',
        '\\u0909\\u0926\\u093e\\u0939\\u0930\\u0923\\x2e\\u092a\\u0930\\u0940\\u0915\\u094d\\u0937\\u093e',
        '\\u4f8b\\u3048\\x2e\\u30c6\\u30b9\\u30c8',
        '\\uc2e4\\ub840\\x2e\\ud14c\\uc2a4\\ud2b8',
        '\\u0645\\u062b\\u0627\\u0644\\x2e\\u0622\\u0632\\u0645\\u0627\\u06cc\\u0634\u06cc',
        '\\u043f\\u0440\\u0438\\u043c\\u0435\\u0440\\x2e\\u0438\\u0441\\u043f\\u044b\\u0442\\u0430\\u043d\\u0438\\u0435',
        '\\u0b89\\u0ba4\\u0bbe\\u0bb0\\u0ba3\\u0bae\\u0bcd\\x2e\\u0baa\\u0bb0\\u0bbf\\u0b9f\\u0bcd\\u0b9a\\u0bc8',
        '\\u05d1\\u05f2\\u05b7\\u05e9\\u05e4\\u05bc\\u05d9\\u05dc\\x2e\\u05d8\\u05e2\\u05e1\\u05d8'
      ],
      icann_addr = 'mailtest\\x40('+icann_domains.join('|')+')',
      word = '('+atom+'|'+quoted_string+')',
      delim = '[,;\s\n]',
      local_part = word+'(\\x2e'+word+')*',
      addr_spec = '(('+local_part+'\\x40'+domain+')|('+icann_addr+'))',
      reg1 = inline ? new RegExp('(^|<|'+delim+')'+addr_spec+'($|>|'+delim+')', 'i') : new RegExp('^'+addr_spec+'$', 'i');

    return reg1.test(input) ? true : false;
  }

  return false;
};


// recursively copy an object
function rcube_clone_object(obj)
{
  var out = {};

  for (var key in obj) {
    if (obj[key] && typeof obj[key] === 'object')
      out[key] = clone_object(obj[key]);
    else
      out[key] = obj[key];
  }

  return out;
};

// make a string URL safe (and compatible with PHP's rawurlencode())
function urlencode(str)
{
  if (window.encodeURIComponent)
    return encodeURIComponent(str).replace('*', '%2A');

  return escape(str)
    .replace('+', '%2B')
    .replace('*', '%2A')
    .replace('/', '%2F')
    .replace('@', '%40');
};


// get any type of html objects by id/name
function rcube_find_object(id, d)
{
  var n, f, obj, e;
  if(!d) d = document;

  if(d.getElementsByName && (e = d.getElementsByName(id)))
    obj = e[0];
  if(!obj && d.getElementById)
    obj = d.getElementById(id);
  if(!obj && d.all)
    obj = d.all[id];

  if(!obj && d.images.length)
    obj = d.images[id];

  if (!obj && d.forms.length) {
    for (f=0; f<d.forms.length; f++) {
      if(d.forms[f].name == id)
        obj = d.forms[f];
      else if(d.forms[f].elements[id])
        obj = d.forms[f].elements[id];
    }
  }

  if (!obj && d.layers) {
    if (d.layers[id]) obj = d.layers[id];
    for (n=0; !obj && n<d.layers.length; n++)
      obj = rcube_find_object(id, d.layers[n].document);
  }

  return obj;
};

// determine whether the mouse is over the given object or not
function rcube_mouse_is_over(ev, obj)
{
  var mouse = rcube_event.get_mouse_pos(ev),
    pos = $(obj).offset();

  return ((mouse.x >= pos.left) && (mouse.x < (pos.left + obj.offsetWidth)) &&
    (mouse.y >= pos.top) && (mouse.y < (pos.top + obj.offsetHeight)));
};


// cookie functions by GoogieSpell
function setCookie(name, value, expires, path, domain, secure)
{
  var curCookie = name + "=" + escape(value) +
      (expires ? "; expires=" + expires.toGMTString() : "") +
      (path ? "; path=" + path : "") +
      (domain ? "; domain=" + domain : "") +
      (secure ? "; secure" : "");
  document.cookie = curCookie;
};

function getCookie(name)
{
  var dc = document.cookie,
    prefix = name + "=",
    begin = dc.indexOf("; " + prefix);

  if (begin == -1) {
    begin = dc.indexOf(prefix);
    if (begin != 0)
      return null;
  }
  else {
    begin += 2;
  }

  var end = dc.indexOf(";", begin);
  if (end == -1)
    end = dc.length;

  return unescape(dc.substring(begin + prefix.length, end));
};

roundcube_browser.prototype.set_cookie = setCookie;
roundcube_browser.prototype.get_cookie = getCookie;

// tiny replacement for Firebox functionality
function rcube_console()
{
  this.log = function(msg)
  {
    var box = rcube_find_object('dbgconsole');

    if (box) {
      if (msg.charAt(msg.length-1)=='\n')
        msg += '--------------------------------------\n';
      else
        msg += '\n--------------------------------------\n';

      // Konqueror doesn't allow to just change the value of hidden element
      if (bw.konq) {
        box.innerText += msg;
        box.value = box.innerText;
      } else
        box.value += msg;
    }
  };

  this.reset = function()
  {
    var box = rcube_find_object('dbgconsole');
    if (box)
      box.innerText = box.value = '';
  };
};

var bw = new roundcube_browser();
bw.set_html_class();


// Add escape() method to RegExp object
// http://dev.rubyonrails.org/changeset/7271
RegExp.escape = function(str)
{
  return String(str).replace(/([.*+?^=!:${}()|[\]\/\\])/g, '\\$1');
};

// Extend Date prototype to detect Standard timezone without DST
// from http://www.michaelapproved.com/articles/timezone-detect-and-ignore-daylight-saving-time-dst/
Date.prototype.getStdTimezoneOffset = function()
{
  var m = 12,
    d = new Date(null, m, 1),
    tzo = d.getTimezoneOffset();

    while (--m) {
      d.setUTCMonth(m);
      if (tzo != d.getTimezoneOffset()) {
        return Math.max(tzo, d.getTimezoneOffset());
    }
  }

  return tzo;
}

// Make getElementById() case-sensitive on IE
if (bw.ie)
{
  document._getElementById = document.getElementById;
  document.getElementById = function(id)
  {
    var i = 0, obj = document._getElementById(id);

    if (obj && obj.id != id)
      while ((obj = document.all[i]) && obj.id != id)
        i++;

    return obj;
  }
}

// This code was written by Tyler Akins and has been placed in the
// public domain.  It would be nice if you left this header intact.
// Base64 code from Tyler Akins -- http://rumkin.com
var Base64 = (function () {
  var keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";

  var obj = {
    /**
     * Encodes a string in base64
     * @param {String} input The string to encode in base64.
     */
    encode: function (input) {
      if (typeof(window.btoa) === 'function')
        return btoa(input);

      var chr1, chr2, chr3, enc1, enc2, enc3, enc4, i = 0, output = '', len = input.length;

      do {
        chr1 = input.charCodeAt(i++);
        chr2 = input.charCodeAt(i++);
        chr3 = input.charCodeAt(i++);

        enc1 = chr1 >> 2;
        enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
        enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
        enc4 = chr3 & 63;

        if (isNaN(chr2))
          enc3 = enc4 = 64;
        else if (isNaN(chr3))
          enc4 = 64;

        output = output
          + keyStr.charAt(enc1) + keyStr.charAt(enc2)
          + keyStr.charAt(enc3) + keyStr.charAt(enc4);
      } while (i < len);

      return output;
    },

    /**
     * Decodes a base64 string.
     * @param {String} input The string to decode.
     */
    decode: function (input) {
      if (typeof(window.atob) === 'function')
         return atob(input);

      var chr1, chr2, chr3, enc1, enc2, enc3, enc4, len, i = 0, output = '';

      // remove all characters that are not A-Z, a-z, 0-9, +, /, or =
      input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");
      len = input.length;

      do {
        enc1 = keyStr.indexOf(input.charAt(i++));
        enc2 = keyStr.indexOf(input.charAt(i++));
        enc3 = keyStr.indexOf(input.charAt(i++));
        enc4 = keyStr.indexOf(input.charAt(i++));

        chr1 = (enc1 << 2) | (enc2 >> 4);
        chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
        chr3 = ((enc3 & 3) << 6) | enc4;

        output = output + String.fromCharCode(chr1);

        if (enc3 != 64)
          output = output + String.fromCharCode(chr2);
        if (enc4 != 64)
          output = output + String.fromCharCode(chr3);
      } while (i < len);

      return output;
    }
  };

  return obj;
})();
