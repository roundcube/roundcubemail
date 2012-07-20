var Unity = null;

function unityReady() {
  // Integrate with Unity!
}

rcmail.addEventListener('init', function(evt) {
  Unity = external.getUnityObject(1.0);
  
  var title = document.title.split(' ::')[0];
  var url = window.location.href;
  var end = url.lastIndexOf('/');
  var icon = url.slice(0, end)+'/plugins/unity/media/logo.png';
 
  Unity.init({name: title,
              iconUrl: icon,
              onInit: unityReady});
});

