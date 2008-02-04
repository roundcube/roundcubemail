/*
 +-----------------------------------------------------------------------+
 | RoundCube common js library                                           |
 |                                                                       |
 | This file is part of the RoundCube web development suite              |
 | Copyright (C) 2005-2007, RoundCube Dev, - Switzerland                 |
 | Licensed under the GNU GPL                                            |
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
 * @construcotr
 */
function roundcube_browser()
  {
  this.ver = parseFloat(navigator.appVersion);
  this.appver = navigator.appVersion;
  this.agent = navigator.userAgent;
  this.name = navigator.appName;
  this.vendor = navigator.vendor ? navigator.vendor : '';
  this.vendver = navigator.vendorSub ? parseFloat(navigator.vendorSub) : 0;
  this.product = navigator.product ? navigator.product : '';
  this.platform = String(navigator.platform).toLowerCase();
  this.lang = (navigator.language) ? navigator.language.substring(0,2) :
              (navigator.browserLanguage) ? navigator.browserLanguage.substring(0,2) :
              (navigator.systemLanguage) ? navigator.systemLanguage.substring(0,2) : 'en';

  this.win = (this.platform.indexOf('win')>=0) ? true : false;
  this.mac = (this.platform.indexOf('mac')>=0) ? true : false;
  this.linux = (this.platform.indexOf('linux')>=0) ? true : false;
  this.unix = (this.platform.indexOf('unix')>=0) ? true : false;

  this.dom = document.getElementById ? true : false;
  this.dom2 = (document.addEventListener && document.removeEventListener);

  this.ie = (document.all) ? true : false;
  this.ie4 = (this.ie && !this.dom);
  this.ie5 = (this.dom && this.appver.indexOf('MSIE 5')>0);
  this.ie6 = (this.dom && this.appver.indexOf('MSIE 6')>0);

  this.mz = (this.dom && this.ver>=5);  // (this.dom && this.product=='Gecko')
  this.ns = ((this.ver<5 && this.name=='Netscape') || (this.ver>=5 && this.vendor.indexOf('Netscape')>=0));
  this.ns6 = (this.ns && parseInt(this.vendver)==6);  // (this.mz && this.ns) ? true : false;
  this.ns7 = (this.ns && parseInt(this.vendver)==7);  // this.agent.indexOf('Netscape/7')>0);
  this.safari = (this.agent.toLowerCase().indexOf('safari')>0 || this.agent.toLowerCase().indexOf('applewebkit')>0);
  this.konq   = (this.agent.toLowerCase().indexOf('konqueror')>0);

  this.opera = (window.opera) ? true : false;

  if(this.opera && window.RegExp)
    this.vendver = (/opera(\s|\/)([0-9\.]+)/i.test(navigator.userAgent)) ? parseFloat(RegExp.$2) : -1;
  else if(!this.vendver && this.safari)
    this.vendver = (/(safari|applewebkit)\/([0-9]+)/i.test(this.agent)) ? parseInt(RegExp.$2) : 0;
  else if((!this.vendver && this.mz) || this.agent.indexOf('Camino')>0)
    this.vendver = (/rv:([0-9\.]+)/.test(this.agent)) ? parseFloat(RegExp.$1) : 0;
  else if(this.ie && window.RegExp)
    this.vendver = (/msie\s+([0-9\.]+)/i.test(this.agent)) ? parseFloat(RegExp.$1) : 0;
  else if(this.konq && window.RegExp)
    this.vendver = (/khtml\/([0-9\.]+)/i.test(this.agent)) ? parseFloat(RegExp.$1) : 0;


  // get real language out of safari's user agent
  if(this.safari && (/;\s+([a-z]{2})-[a-z]{2}\)/i.test(this.agent)))
    this.lang = RegExp.$1;

  this.dhtml = ((this.ie4 && this.win) || this.ie5 || this.ie6 || this.ns4 || this.mz);
  this.vml = (this.win && this.ie && this.dom && !this.opera);
  this.pngalpha = (this.mz || (this.opera && this.vendver>=6) || (this.ie && this.mac && this.vendver>=5) ||
                   (this.ie && this.win && this.vendver>=5.5) || this.safari);
  this.opacity = (this.mz || (this.ie && this.vendver>=5.5 && !this.opera) || (this.safari && this.vendver>=100));
  this.cookies = navigator.cookieEnabled;
  
  // test for XMLHTTP support
  this.xmlhttp_test = function()
    {
    var activeX_test = new Function("try{var o=new ActiveXObject('Microsoft.XMLHTTP');return true;}catch(err){return false;}");
    this.xmlhttp = (window.XMLHttpRequest || (window.ActiveXObject && activeX_test())) ? true : false;
    return this.xmlhttp;
    }
  }


// static functions for event handling
var rcube_event = {

 /**
  * returns the event key code
  */
 get_keycode: function(e)
 {
   e = e || window.event;
   return e && e.keyCode ? e.keyCode : (e && e.which ? e.which : 0);
 },

/**
 * returns modifier key (constants defined at top of file)
 */
get_modifier: function(e)
{
  var opcode = 0;
  e = e || window.event;

  if (bw.mac && e)
    {
    opcode += (e.metaKey && CONTROL_KEY) + (e.shiftKey && SHIFT_KEY);
    return opcode;    
    }
  if (e)
    {
    opcode += (e.ctrlKey && CONTROL_KEY) + (e.shiftKey && SHIFT_KEY);
    return opcode;
    }
},

/**
 * Return absolute mouse position of an event
 */
get_mouse_pos: function(e)
{
  if (!e) e = window.event;
  var mX = (e.pageX) ? e.pageX : e.clientX;
  var mY = (e.pageY) ? e.pageY : e.clientY;

  if (document.body && document.all)
  {
    mX += document.body.scrollLeft;
    mY += document.body.scrollTop;
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
  else if (p.element.attachEvent)
    p.element.attachEvent('on'+p.event, p.object._rc_events[key]);
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
}

};


var rcube_layer_objects = new Array();


/**
 * RoundCube generic layer (floating box) class
 *
 * @constructor
 */
function rcube_layer(id, attributes)
  {
  this.name = id;
  
  // create a new layer in the current document
  this.create = function(arg)
    {
    var l = (arg.x) ? arg.x : 0;
    var t = (arg.y) ? arg.y : 0;
    var w = arg.width;
    var h = arg.height;
    var z = arg.zindex;
    var vis = arg.vis;
    var parent = arg.parent;
    var obj;

    obj = document.createElement('DIV');
    with(obj)
      {
      id = this.name;
      with(style)
        {
        position = 'absolute';
        visibility = (vis) ? (vis==2) ? 'inherit' : 'visible' : 'hidden';
        left = l+'px';
        top = t+'px';
        if(w) width = w+'px';
        if(h) height = h+'px';
        if(z) zIndex = z;
        }
      }
      
    if(parent) parent.appendChild(obj);
    else document.body.appendChild(obj);

    this.elm = obj;
    };


  // create new layer
  if(attributes!=null)
    {
    this.create(attributes);
    this.name = this.elm.id;
    }
  else  // just refer to the object
    this.elm = document.getElementById(id);


  if(!this.elm)
    return false;


  // ********* layer object properties *********

  this.css = this.elm.style;
  this.event = this.elm;
  this.width = this.elm.offsetWidth;
  this.height = this.elm.offsetHeight;
  this.x = parseInt(this.elm.offsetLeft);
  this.y = parseInt(this.elm.offsetTop);
  this.visible = (this.css.visibility=='visible' || this.css.visibility=='show' || this.css.visibility=='inherit') ? true : false;

  this.id = rcube_layer_objects.length;
  this.obj = 'rcube_layer_objects['+this.id+']';
  rcube_layer_objects[this.id] = this;


  // ********* layer object methods *********


  // move the layer to a specific position
  this.move = function(x, y)
    {
    this.x = x;
    this.y = y;
    this.css.left = Math.round(this.x)+'px';
    this.css.top = Math.round(this.y)+'px';
    }


  // move the layer for a specific step
  this.shift = function(x,y)
    {
    x = Math.round(x*100)/100;
    y = Math.round(y*100)/100;
    this.move(this.x+x, this.y+y);
    }


  // change the layers width and height
  this.resize = function(w,h)
    {
    this.css.width  = w+'px';
    this.css.height = h+'px';
    this.width = w;
    this.height = h;
    }


  // cut the layer (top,width,height,left)
  this.clip = function(t,w,h,l)
    {
    this.css.clip='rect('+t+' '+w+' '+h+' '+l+')';
    this.clip_height = h;
    this.clip_width = w;
    }


  // show or hide the layer
  this.show = function(a)
    {
    if(a==1)
      {
      this.css.visibility = 'visible';
      this.visible = true;
      }
    else if(a==2)
      {
      this.css.visibility = 'inherit';
      this.visible = true;
      }
    else
      {
      this.css.visibility = 'hidden';
      this.visible = false;
      }
    }


  // write new content into a Layer
  this.write = function(cont)
    {
    this.elm.innerHTML = cont;
    }


  // set the given color to the layer background
  this.set_bgcolor = function(c)
    {
    if(!c || c=='#')
      c = 'transparent';

    this.css.backgroundColor = c;
    }


  // set the opacity of a layer to the given ammount (in %)
  this.set_opacity = function(v)
    {
    if(!bw.opacity)
      return;

    var op = v<=1 ? Math.round(v*100) : parseInt(v);

    if(bw.ie)
      this.css.filter = 'alpha(opacity:'+op+')';
    else if(bw.safari)
      {
      this.css.opacity = op/100;
      this.css.KhtmlOpacity = op/100;
      }
    else if(bw.mz)
      this.css.MozOpacity = op/100;
    }
  }


// check if input is a valid email address
// By Cal Henderson <cal@iamcal.com>
// http://code.iamcal.com/php/rfc822/
function rcube_check_email(input, inline)
  {
  if (input && window.RegExp)
    {
    var qtext = '[^\\x0d\\x22\\x5c\\x80-\\xff]';
    var dtext = '[^\\x0d\\x5b-\\x5d\\x80-\\xff]';
    var atom = '[^\\x00-\\x20\\x22\\x28\\x29\\x2c\\x2e\\x3a-\\x3c\\x3e\\x40\\x5b-\\x5d\\x7f-\\xff]+';
    var quoted_pair = '\\x5c[\\x00-\\x7f]';
    var domain_literal = '\\x5b('+dtext+'|'+quoted_pair+')*\\x5d';
    var quoted_string = '\\x22('+qtext+'|'+quoted_pair+')*\\x22';
    var sub_domain = '('+atom+'|'+domain_literal+')';
    var word = '('+atom+'|'+quoted_string+')';
    var domain = sub_domain+'(\\x2e'+sub_domain+')*';
    var local_part = word+'(\\x2e'+word+')*';
    var addr_spec = local_part+'\\x40'+domain;
    var delim = '[,;\s\n]';
    var reg1 = inline ? new RegExp('(^|<|'+delim+')'+addr_spec+'($|>|'+delim+')', 'i') : new RegExp('^'+addr_spec+'$', 'i');
    return reg1.test(input) ? true : false;
    }
  return false;
  }
  

// find a value in a specific array and returns the index
function find_in_array()
  {
  var args = find_in_array.arguments;
  if(!args.length) return -1;

  var haystack = typeof(args[0])=='object' ? args[0] : args.length>1 && typeof(args[1])=='object' ? args[1] : new Array();
  var needle = typeof(args[0])!='object' ? args[0] : args.length>1 && typeof(args[1])!='object' ? args[1] : '';
  var nocase = args.length==3 ? args[2] : false;

  if(!haystack.length) return -1;

  for(var i=0; i<haystack.length; i++)
    if(nocase && haystack[i].toLowerCase()==needle.toLowerCase())
      return i;
    else if(haystack[i]==needle)
      return i;

  return -1;
  }


// make a string URL safe
function urlencode(str)
{
  return window.encodeURIComponent ? encodeURIComponent(str) : escape(str);
}


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

  if(!obj && d.forms.length)
    for(f=0; f<d.forms.length; f++)
      {
      if(d.forms[f].name == id)
        obj = d.forms[f];
      else if(d.forms[f].elements[id])
        obj = d.forms[f].elements[id];
      }

  if(!obj && d.layers)
    {
    if(d.layers[id]) obj = d.layers[id];
    for(n=0; !obj && n<d.layers.length; n++)
      obj = rcube_find_object(id, d.layers[n].document);
    }

  return obj;
  }


// return the absolute position of an object within the document
function rcube_get_object_pos(obj)
  {
  if(typeof(obj)=='string')
    obj = rcube_find_object(obj);

  if(!obj) return {x:0, y:0};

  var iX = (bw.layers) ? obj.x : obj.offsetLeft;
  var iY = (bw.layers) ? obj.y : obj.offsetTop;

  if(bw.ie || bw.mz)
    {
    var elm = obj.offsetParent;
    while(elm && elm!=null)
      {
      iX += elm.offsetLeft;
      iY += elm.offsetTop;
      elm = elm.offsetParent;
      }
    }

  //if(bw.mac && bw.ie5) iX += document.body.leftMargin;
  //if(bw.mac && bw.ie5) iY += document.body.topMargin;

  return {x:iX, y:iY};
  }


/**
 * Return the currently applied value of a css property
 *
 * @param {Object} html_element  Node reference
 * @param {String} css_property  Property name to read in Javascript notation (eg. 'textAlign')
 * @param {String} mozilla_equivalent  Equivalent property name in CSS notation (eg. 'text-align')
 * @return CSS property value
 * @type String
 */
function get_elements_computed_style(html_element, css_property, mozilla_equivalent)
  {
  if (arguments.length==2)
    mozilla_equivalent = css_property;

  var el = html_element;
  if (typeof(html_element)=='string')
    el = rcube_find_object(html_element);

  if (el && el.currentStyle)
    return el.currentStyle[css_property];
  else if (el && document.defaultView && document.defaultView.getComputedStyle)
    return document.defaultView.getComputedStyle(el, null).getPropertyValue(mozilla_equivalent);
  else
    return false;
  }
  

// cookie functions by GoogieSpell
function setCookie(name, value, expires, path, domain, secure)
  {
  var curCookie = name + "=" + escape(value) +
      (expires ? "; expires=" + expires.toGMTString() : "") +
      (path ? "; path=" + path : "") +
      (domain ? "; domain=" + domain : "") +
      (secure ? "; secure" : "");
  document.cookie = curCookie;
  }

roundcube_browser.prototype.set_cookie = setCookie;

function getCookie(name)
  {
  var dc = document.cookie;
  var prefix = name + "=";
  var begin = dc.indexOf("; " + prefix);
  if (begin == -1)
    {
    begin = dc.indexOf(prefix);
    if (begin != 0) return null;
    }
  else
    begin += 2;  
  var end = document.cookie.indexOf(";", begin);
  if (end == -1)
    end = dc.length;
  return unescape(dc.substring(begin + prefix.length, end));
  }

roundcube_browser.prototype.get_cookie = getCookie;


// tiny replacement for Firebox functionality
function rcube_console()
{
  this.box = rcube_find_object('console');
  
  this.log = function(msg)
  {
    if (this.box)
      this.box.value += str+'\n--------------------------------------\n';
  };
  
  this.reset = function()
  {
    if (this.box)
      this.box.value = '';
  };
}

var bw = new roundcube_browser();

if (!window.console)
  console = new rcube_console();


// Add escape() method to RegExp object
// http://dev.rubyonrails.org/changeset/7271
RegExp.escape = function(str)
  {
  return String(str).replace(/([.*+?^=!:${}()|[\]\/\\])/g, '\\$1');
  }
