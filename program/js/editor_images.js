
var rc_client = tinyMCEPopup.getParam("rc_client");
if (rc_client.env.attachments)
{
   var tinyMCEImageList = new Array();
   for (var id in rc_client.env.attachments)
   {
      var att = rc_client.env.attachments[id];
      if (att.complete && att.mimetype.indexOf('image/') == 0)
        tinyMCEImageList.push([att.name, rc_client.env.comm_path+'&_action=display-attachment&_file='+id+'&_id='+rc_client.env.compose_id]);
   }
};
