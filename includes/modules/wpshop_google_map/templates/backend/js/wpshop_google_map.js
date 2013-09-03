 /**
 * This function set the required parameters for displaying the map, the landmark and their label
 * @param array serializarray, encoded by php json_encode function and containing each users localisation and address label
 * @return, allow to load google_map in the corresponding box
 */
 function display_google_map(serializarray)
  {
    array_landmark = jQuery.parseJSON(serializarray);
    var options, map, infowindow, marker, landmark, i;
    if(array_landmark != null)
    {
      var size = array_landmark.length;
      options = {
          zoom: 15,
          center: new google.maps.LatLng(array_landmark[size-1]['moy'].lat,array_landmark[size-1]['moy'].lng),
          mapTypeId: google.maps.MapTypeId.ROADMAP
      };
      map = new google.maps.Map(document.getElementById("map"), options);
    }
    else{
      options = { zoom:15, center : new google.maps.LatLng(43.605,3.86), mapTypeId: google.maps.MapTypeId.ROADMAP}
      map = new google.maps.Map(document.getElementById("map"),options);
    }

    infowindow = new google.maps.InfoWindow();
    for(i = 0 ; i < size ; i++){
      if(typeof(array_landmark[i]) != 'undefined'){
        if(array_landmark[i].lat != '' && array_landmark[i].lng != '' && array_landmark[i].address != '') {
      	  landmark = new google.maps.Marker({
        		position : new google.maps.LatLng(array_landmark[i].lat,array_landmark[i].lng),
        		map : map,
            title : array_landmark[i].address
      	  });
          google.maps.event.addListener(landmark,'click', (function(landmark, i){
            return function() {
              infowindow.setContent(array_landmark[i].address);
              infowindow.open(map,landmark);
            }
          })(landmark, i));
        }
      }
    }
}
