function chbox_menu(){
  var link_html = '<a href="#" onclick="return rcmail.command(\'plugin.chbox.selectmenu\')">'+rcmail.env.chboxicon;
  $('#rcmchbox').html(link_html);
}

$(document).ready(function(){
  chbox_menu();
    var li = '<label class="disabled"><input type="checkbox" name="list_col[]" value="chbox" name="cols_chbox" checked="checked" disabled="disabled" type="checkbox"/><span>'+rcmail.get_label('chbox.chbox')+'</span></label>';
  $("#listmenu fieldset ul input#cols_threads").parent().after(li);
  $("#listoptions fieldset ul.proplist:first li:first-child").after('<li>'+li+'</li>');
});
