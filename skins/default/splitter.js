
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
  this.offset = bw.ie6 ? 2 : 0;
  this.pos = attrib.start ? attrib.start * 1 : 0;
  this.relative = attrib.relative ? true : false;
  this.drag_active = false;

  this.init = function()
    {
    this.p1 = document.getElementById(this.p1id);
    this.p2 = document.getElementById(this.p2id);

    // create and position the handle for this splitter
    this.p1pos = this.relative ? $(this.p1).position() : $(this.p1).offset();
    this.p2pos = this.relative ? $(this.p2).position() : $(this.p2).offset();
    
    if (this.horizontal)
      {
      var top = this.p1pos.top + this.p1.offsetHeight;
      this.layer = new rcube_layer(this.id, {x: 0, y: top, height: 10, 
    	    width: '100%', vis: 1, parent: this.p1.parentNode});
      }
    else
      {
      var left = this.p1pos.left + this.p1.offsetWidth;
      this.layer = new rcube_layer(this.id, {x: left, y: 0, width: 10, 
    	    height: '100%', vis: 1,  parent: this.p1.parentNode});
      }

    this.elm = this.layer.elm;
    this.elm.className = 'splitter '+(this.horizontal ? 'splitter-h' : 'splitter-v');
    this.elm.unselectable = 'on';

    // add the mouse event listeners
    rcube_event.add_listener({element: this.elm, event:'mousedown', object:this, method:'onDragStart'});
    if (bw.ie)
      rcube_event.add_listener({element: window, event:'resize', object:this, method:'onResize'});

    // read saved position from cookie
    var cookie = bw.get_cookie(this.id);
    if (cookie && !isNaN(cookie))
      {
      this.pos = parseFloat(cookie);
      this.resize();
      }
    else if (this.pos)
      {
      this.resize();
      this.set_cookie();
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
      var lh = this.layer.height - this.offset * 2;
      this.p1.style.height = Math.floor(this.pos - this.p1pos.top - lh / 2) + 'px';
      this.p2.style.top = Math.ceil(this.pos + lh / 2) + 'px';
      this.layer.move(this.layer.x, Math.round(this.pos - lh / 2 + 1));
      if (bw.ie)
        {
        var new_height = (parseInt(this.p2.parentNode.offsetHeight) - parseInt(this.p2.style.top));
        this.p2.style.height = (new_height > 0 ? new_height : 0) +'px';
        }
      }
    else
      {
      this.p1.style.width = Math.floor(this.pos - this.p1pos.left - this.layer.width / 2) + 'px';
      this.p2.style.left = Math.ceil(this.pos + this.layer.width / 2) + 'px';
      this.layer.move(Math.round(this.pos - this.layer.width / 2 + 1), this.layer.y);
      if (bw.ie)
        this.p2.style.width = (parseInt(this.p2.parentNode.offsetWidth) - parseInt(this.p2.style.left))+'px';
      }
    };

  /**
   * Handler for mousedown events
   */
  this.onDragStart = function(e)
    {
    this.p1pos = this.relative ? $(this.p1).position() : $(this.p1).offset();
    this.p2pos = this.relative ? $(this.p2).position() : $(this.p2).offset();
    this.drag_active = true;
    
    // start listening to mousemove events
    rcube_event.add_listener({element:document, event:'mousemove', object:this, method:'onDrag'});
    rcube_event.add_listener({element:document, event:'mouseup', object:this, method:'onDragStop'});

    // need to listen in any iframe documents too, b/c otherwise the splitter stops moving when we move over an iframe
    var iframes = document.getElementsByTagName('iframe');
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
        var id = '#'+iframes[n].id;
        this.iframe_events[n] = function(e){ e._offset = $(id).offset(); return s.onDrag(e); }

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
    if (!this.drag_active) return false;

    var pos = rcube_event.get_mouse_pos(e);

    if (this.relative)
      {
      var parent = $(this.p1.parentNode).offset();
      pos.x -= parent.left;
      pos.y -= parent.top;
      }

    if (this.horizontal)
      {
      if (((pos.y - this.layer.height * 1.5) > this.p1pos.top) && ((pos.y + this.layer.height * 1.5) < (this.p2pos.top + this.p2.offsetHeight)))
        {
        this.pos = pos.y;
        this.resize();
        }
      }
    else
      {
      if (((pos.x - this.layer.width * 1.5) > this.p1pos.left) && ((pos.x + this.layer.width * 1.5) < (this.p2pos.left + this.p2.offsetWidth)))
        {
        this.pos = pos.x;
        this.resize();
        }
      }

    this.p1pos = this.relative ? $(this.p1).position() : $(this.p1).offset();
    this.p2pos = this.relative ? $(this.p2).position() : $(this.p2).offset();
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
    this.drag_active = false;

    var iframes = document.getElementsByTagName('iframe');

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

    this.set_cookie();

    return bw.safari ? true : rcube_event.cancel(e);
    };

  /**
   * Handler for window resize events
   */
  this.onResize = function(e)
    {
    if (this.horizontal)
      {
      var new_height = (parseInt(this.p2.parentNode.offsetHeight) - parseInt(this.p2.style.top));
      this.p2.style.height = (new_height > 0 ? new_height : 0) +'px';
      }
    else
      this.p2.style.width = (parseInt(this.p2.parentNode.offsetWidth) - parseInt(this.p2.style.left))+'px';
    };

  this.set_cookie = function()
    {
    // save state in cookie
    var exp = new Date();
    exp.setYear(exp.getFullYear() + 1);
    bw.set_cookie(this.id, this.pos, exp);
    }

  }  // end class rcube_splitter
