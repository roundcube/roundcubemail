/*
 * authres_status plugin
 * @author pimlie
 */

var authres_status = {
    initColumn: function() {
       $('#rcmauthres_status span').html('&nbsp;');
       var li = '<label><input type="checkbox" name="list_col[]" value="authres_status" id="cols_authres_status" /> <span>'+rcmail.get_label('authres_status.column_title')+'</span></label>';
       $("#listoptions-columns ul.proplist").append('<li>'+li+'</li>');
    },
    insertrow: function(evt) {
        if (typeof(rcmail.env.authres_flags[evt.uid]) !== "undefined") {
            $('.fromto', evt.row.obj).prepend($('<span/>').html(rcmail.env.authres_flags[evt.uid]));
        }
    }
};

window.rcmail && rcmail.addEventListener('init', function(evt) {
    if (rcmail.env.layout == 'widescreen') {
        if (rcmail.gui_objects.messagelist) {
            rcmail.addEventListener('insertrow', authres_status.insertrow);
        }
    } else {
        authres_status.initColumn();
    }
});
