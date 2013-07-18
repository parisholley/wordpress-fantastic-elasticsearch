(function(){

  var ELASTICSEARCH_INDEX_ENDPOINT = 'http://api.qbox.io/jeeipzgmtkuufaby/people/person';

  var METERS_PER_MILE = 1609.34;

  var miles_max = 200;
  var miles_step = 20;
  var miles_radius = 3 * miles_step;

  var max_results = 10;

  var map, centerPt, ctrMarker, searchCircle, radiusSlider;

  var resultTemplate = Handlebars.compile('\
    <tr class="result-row" id={{id}}>\
      <td>{{first_name}}</td>\
      <td>{{last_name}}</td>\
      <td>{{email}}</td>\
      <td>{{formatted_lat}}</td>\
      <td>{{formatted_lon}}</td>\
    </tr>\
  ');

  var markers = [];

  google.maps.event.addDomListener(window, 'load', initialize);

  function initialize() {

    $('#max_results').html('(first ' + max_results + ')');
    
    centerPt = new google.maps.LatLng(36.0625, -94.1572);

    var mapOptions = {
      center: centerPt,
      zoom: 6,
      mapTypeId: google.maps.MapTypeId.ROADMAP
    };

    map = new google.maps.Map(document.getElementById("map-canvas"), mapOptions);

    var circleOptions = {
      draggable: true,
      strokeColor: '#FF0000',
      strokeOpacity: 0.8,
      strokeWeight: 2,
      fillColor: '#FF0000',
      fillOpacity: 0.35,
      map: map,
      center: centerPt,
      radius: miles_radius * METERS_PER_MILE
    };

    searchCircle = new google.maps.Circle(circleOptions);
    
    google.maps.event.addListener(searchCircle, 'mouseup', handleCenterChange);

    var sliderOptions = {
      min: miles_step,
      max: miles_max,
      step: miles_step,
      value: miles_radius,
      orientation: 'horizontal',
      selection: 'after',
      tooltip: 'show',
      handle: 'round',
      formater: formatRadius
    };

    radiusSlider = $('#radius-slider').slider(sliderOptions);
    radiusSlider.on('slideStop', updateRadius);
    radiusSlider.on('slide', circleFeedback);

    radiusSlider.slider('setValue', miles_radius);

    getResults();
  }

  function circleFeedback(e){
    searchCircle.setRadius(e.value * METERS_PER_MILE);
  }

  function updateRadius(e) {
    miles_radius = e.value;
    radiusSlider.slider('setValue', miles_radius);
    $('#radius-val').html(formatRadius(miles_radius));
    searchCircle.setRadius(miles_radius * METERS_PER_MILE);
    getResults();
  }

  function formatRadius(val) {
    return val + " mi";
  }

  function handleCenterChange() {
    var pt = searchCircle.getCenter();
    if (pt != centerPt){
      centerPt = pt;
      getResults();
    }
  }

  function getResults() {
    $('#results').html('');
    markers.map(function(i){
      i.setMap(null);
    });
    markers = [];
    hash = {};
    hash.url = ELASTICSEARCH_INDEX_ENDPOINT + '/_search';
    hash.type = 'POST';
    hash.dataType = 'json';
    hash.success = processResults;
    hash.error = function(arg) { console.error('ajax error: ', arg) };

    hash.data = JSON.stringify({
      "from" : 0,
      "size" : max_results,
      "query" : {
        "filtered" : {
          "query" : { "match_all" : {} },
          "filter" : {
            "geo_distance" : {
                "distance" : (0.95 * miles_radius) + "mi",
                "person.location" : { "lat" : centerPt.jb, "lon" : centerPt.kb }
            }
          }
        }
      }
    });

    jQuery.ajax(hash);
  }

  function processResults(json){
    json['hits']['hits'].map(function(i){
      var src = i['_source'];
      src.id = i["_id"];
      src.formatted_lat = Math.round(src.location.lat * 1000000) / 1000000;
      src.formatted_lon = Math.round(src.location.lon * 1000000) / 1000000;
      $('#results').append(resultTemplate(src));
      var marker = new google.maps.Marker({
        map: map,
        draggable: false,
        position: new google.maps.LatLng(src.location.lat, src.location.lon),
        title: [src.first_name, src.last_name].join(' ')
      });
      marker.id = src.id;
      google.maps.event.addListener(marker, 'click', handleMarkerClick);
      markers.push(marker);
    })
  }

  function handleMarkerClick(e){
    $('.result-row').removeClass('alert');
    $('#' + this.id).addClass('alert');
  }


}).call(this);