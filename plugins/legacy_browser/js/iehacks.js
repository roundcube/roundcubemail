
// Make getElementById() case-sensitive on IE7
document._getElementById = document.getElementById;
document.getElementById = function(id) {
  var i = 0, obj = document._getElementById(id);

  if (obj && obj.id != id)
    while ((obj = document.all[i]) && obj.id != id)
      i++;

  return obj;
}

// fix missing :last-child selectors
$(document).ready(function() {
  if (rcmail && rcmail.env.skin != 'classic')
    $('ul.treelist ul').each(function(i, ul) {
      $('li:last-child', ul).css('border-bottom', 0);
  });
});
