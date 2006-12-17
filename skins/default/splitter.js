
/**
 * RoundCube splitter GUI class
 *
 * @constructor
 */
function rcube_splitter(attrib)
  {
  this.p1id = attrib.p1;
  this.p2id = attrib.p2;
  this.id = attrib.id ? attrib.id : this.p1id + '_' + this.p2id + '_splitter';
  this.orientation = attrib.orientation;
  this.horizontal = (this.orientation == 'horizontal' || this.orientation == 'h');
  this.offset_1 = bw.ie ? 0 : (bw.safari ? 2 : -1);
  this.offset_2 = bw.ie ? -2 : (bw.safari ? -2 : 1);
  this.pos = 0;

  this.init = function()
    {
    this.p1 = document.getElementById(this.p1id);
    this.p2 = document.getElementById(this.p2id);
    
    // create and position the handle for this splitter
    this.p1pos = rcube_get_object_pos(this.p1);
    this.p2pos = rcube_get_object_pos(this.p2);
    var top = this.p1pos.y + this.p1.offsetHeight;
    var height = this.p2pos.y - this.p1pos.y - this.p1.offsetHeight;
    var left = this.p1pos.x + this.p1.offsetWidth;
    var width = this.p2pos.x - this.p1pos.x - this.p1.offsetWidth;
    
    if (this.horizontal)
      this.layer = new rcube_layer(this.id, {x: this.p1pos.x, y: top, height: height, width: this.p1.offsetWidth, vis: 1});
    else
      this.layer = new rcube_layer(this.id, {x: left, y: this.p1pos.y, width: width, height: this.p1.offsetHeight, vis: 1});

    this.elm = this.layer.elm;
    this.elm.className = 'splitter '+(this.horizontal ? 'splitter-h' : 'splitter-v');

    // add the mouse event listeners
    rcube_event.add_listener({element: this.elm, event:'mousedown', object:this, method:'onDragStart'});
    rcube_event.add_listener({element: window, event:'resize', object:this, method:'onResize'});

    // read saved position form cookie
    var cookie = bw.get_cookie(this.id);
    if (cookie)
      {
      var param = cookie.split(':');
      for (var i=0, p; i<param.length; i++)
        {
        p = param[i].split('=');
        this[p[0]] = !isNaN(p[1]) ? parseFloat(p[1]) : p[1];
        }

      this.resize();
      }
    };

  /**
   * Set size and position of all DOM objects
   * according to the saved splitter position
   */
  this.resize = function()
    {
     if (this.horizontal)
      {
      this.p1.style.height = Math.floor(this.pos - this.p1pos.y - this.layer.height / 2 + this.offset_1) + 'px';
      this.p2.style.top = Math.ceil(this.pos + (this.layer.height / 2 + this.offset_2)) + 'px';
      this.layer.move(this.layer.x, Math.round(this.pos - this.layer.height / 2 + 1));
      }
    else
      {
      this.p1.style.width = Math.floor(this.pos - this.p1pos.x - this.layer.width / 2 + this.offset_1) + 'px';
      this.p2.style.left = Math.ceil(this.pos + this.layer.width / 2 + this.offset_2) + 'px';
      this.layer.move(Math.round(this.pos - this.layer.width / 2 + 1), this.layer.y);
      }
    };

  /**
   * Handler for mousedown events
   */
  this.onDragStart = function(e)
    {
    this.p1pos = rcube_get_object_pos(this.p1);
    this.p2pos = rcube_get_object_pos(this.p2);

    // start listening to mousemove events
    rcube_event.add_listener({element:document, event:'mousemove', object:this, method:'onDrag'});
    rcube_event.add_listener({element:document, event:'mouseup', object:this, method:'onDragStop'});

    // need to listen in any iframe documents too, b/c otherwise the splitter stops moving when we move over an iframe
    var iframes = document.getElementsByTagName('IFRAME');
    this.iframe_events = Object();
    for (var n in iframes)
      {
      var iframedoc = null;
      if (iframes[n].contentDocument)
        iframedoc = iframes[n].contentDocument;
      else if (iframes[n].contentWindow)
        iframedoc = iframes[n].contentWindow.document;
      else if (iframes[n].document)
        iframedoc = iframes[n].document;
      if (iframedoc)
        {
        // I don't use the add_listener function for this one because I need to create closures to fetch
        // the position of each iframe when the event is received
        var s = this;
        var id = iframes[n].id;
        this.iframe_events[n] = function(e){ e._rc_pos_offset = rcube_get_object_pos(document.getElementById(id)); return s.onDrag(e); }
        if (iframedoc.addEventListener)
          iframedoc.addEventListener('mousemove', this.iframe_events[n], false);
        else if (iframes[n].attachEvent)
          iframedoc.attachEvent('onmousemove', this.iframe_events[n]);
        else
          iframedoc['onmousemove'] = this.iframe_events[n];

        rcube_event.add_listener({element:iframedoc, event:'mouseup', object:this, method:'onDragStop'});
        }
      }
    }

  /**
   * Handler for mousemove events
   */
  this.onDrag = function(e)
    {
    var pos = rcube_event.get_mouse_pos(e);
    if (e._rc_pos_offset)
      {
      pos.x += e._rc_pos_offset.x;
      pos.y += e._rc_pos_offset.y;
      }

    if (this.horizontal)
      {
      if (((pos.y - this.layer.height * 1.5) > this.p1pos.y) && ((pos.y + this.layer.height * 1.5) < (this.p2pos.y + this.p2.offsetHeight)))
        {
        this.pos = pos.y;
        this.resize();
        }
      }
    else
      {
      if (((pos.x - this.layer.width * 1.5) > this.p1pos.x) && ((pos.x + this.layer.width * 1.5) < (this.p2pos.x + this.p2.offsetWidth)))
        {
        this.pos = pos.x;
        this.resize();
        }
      }

    this.p1pos = rcube_get_object_pos(this.p1);
    this.p2pos = rcube_get_object_pos(this.p2);
    return false;
    };

  /**
   * Handler for mouseup events
   */
  this.onDragStop = function(e)
    {
    // cancel the listening for drag events
    rcube_event.remove_listener({element:document, event:'mousemove', object:this, method:'onDrag'});
    rcube_event.remove_listener({element:document, event:'mouseup', object:this, method:'onDragStop'});
    var iframes = document.getElementsByTagName('IFRAME');

    for (var n in iframes)
      {
      var iframedoc;
      if (iframes[n].contentDocument)
        iframedoc = iframes[n].contentDocument;
      else if (iframes[n].contentWindow)
        iframedoc = iframes[n].contentWindow.document;
      else if (iframes[n].document)
        iframedoc = iframes[n].document;
      if (iframedoc)
        {
        if (this.iframe_events[n]) {
          if (iframedoc.removeEventListener)
            iframedoc.removeEventListener('mousemove', this.iframe_events[n], false);
          else if (iframedoc.detachEvent)
            iframedoc.detachEvent('onmousemove', this.iframe_events[n]);
          else
            iframedoc['onmousemove'] = null;
        }

        rcube_event.remove_listener({element:iframedoc, event:'mouseup', object:this, method:'onDragStop'});
        }
      }

    // save state in cookie
    var exp = new Date();
    exp.setYear(exp.getFullYear() + 1);
    bw.set_cookie(this.id, 'pos='+this.pos, exp);

    return bw.safari ? true : rcube_event.cancel(e);
    };

  /**
   * Handler for window resize events
   */
  this.onResize = function(e)
    {
    this.p1pos = rcube_get_object_pos(this.p1);
    this.p2pos = rcube_get_object_pos(this.p2);
    var height = this.horizontal ? this.p2pos.y - this.p1pos.y - this.p1.offsetHeight : this.p1.offsetHeight;
    var width = this.horizontal ? this.p1.offsetWidth : this.p2pos.x - this.p1pos.x - this.p1.offsetWidth;
    this.layer.resize(width, height);
    };

  }  // end class rcube_splitter
