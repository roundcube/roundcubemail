var mh_cur_row;

$(document).ready(function() {
  if(window.rcmail) {

    /* listen to roundcube insertrow event so we can color the rows */
    rcmail.addEventListener('insertrow', mh_insert_row);

    /* listen to receive row event after asking for another row */
    rcmail.addEventListener('plugin.mh_receive_row', mh_receive_row);

    /* initialise button click events */
    $('.mh_delete').click(function(e) {
        e.preventDefault();
        mh_delete(this);
    });

    $('.mh_add').click(function(e) {
        e.preventDefault();
        mh_add(this);
    });
  }
});

/* insert row event to color a mail row */
function mh_insert_row(evt) {

    if(!rcmail.env.messages) return;

    var message = rcmail.env.messages[evt.row.uid];

    // check if our color info is present
    if(message.flags && message.flags.plugin_mh_color) {
        var row = $(evt.row.obj);
        row.addClass('rcmfd_mh_row');

        evt.row.obj.style.backgroundColor = message.flags.plugin_mh_color;

        var color_brightness = brightness(message.flags.plugin_mh_color);
        if (color_brightness !== null && color_brightness < 123) {
            row.addClass('rcmfd_mh_row_dark');
        }

    }
}

/* delete a settings row */
function mh_delete(button) {
  if (confirm(rcmail.get_label('message_highlight.deleteconfirm'))) {
    $(button).closest('tr').remove();
  }
}

// do an ajax call to get a new row
function mh_add(button) {
  mh_cur_row = $(button).closest('tr', '.mh_preferences');
  lock = rcmail.set_busy(true, 'loading');
  rcmail.http_request('plugin.mh_add_row', '', lock);
}

// ajax return call
function mh_receive_row(data) {
  var row = $('<tr class="form-group row"><td colspan="2" style="width: 100%;">'+data.row+'</td></tr>');
  $(mh_cur_row).after(row);

  $(row).find('.mh_color_input').mColorPicker();

  $('.mh_delete').unbind('click').click(function(e) {
      e.preventDefault();
      mh_delete(this);
  });

  $('.mh_add').unbind('click').click(function(e) {
      e.preventDefault();
      mh_add(this);
  });

}

/* calculate the brightness of a color */
function brightness(hex) {
    var shorthandRegex = /^#?([a-f\d])([a-f\d])([a-f\d])$/i;
    hex = hex.replace(shorthandRegex, function(m, r, g, b) {
        return r + r + g + g + b + b;
    });
    var rgb = hex.match(/^#([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})$/i)
    if (rgb === null) {
        return null;
    }
    rgb = rgb.slice(1,4)
             .map(function(x) { return parseInt(x, 16); });

    return (rgb[0] * 299 + rgb[1] * 587 + rgb[2] * 114) / 1000
}

