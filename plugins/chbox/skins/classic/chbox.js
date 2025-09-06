
function chbox_menu(){
  var link_html = '<a href="#" onclick="return rcmail.command(\'plugin.chbox.selectmenu\')">'+rcmail.env.chboxicon;
  $('#rcmchbox').html(link_html);
}

$(document).ready(function(){
  chbox_menu();
  var li = '<label><input type="checkbox" name="list_col[]" value="chbox" id="cols_chbox" checked="checked" type="checkbox"/><span>'+rcmail.get_label('chbox.chbox')+'</span></label>';
  if ( $("#listmenu #listoptions-columns").length ) {
    $("#listmenu #listoptions-columns ul.toolbarmenu li:first()").after('<li>'+li+'</li>');
  }
  else {
    $("#listmenu ul.toolbarmenu li:first()").after('<li>'+li+'</li>');
  }
});
