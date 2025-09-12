@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.edit') }} {{ trans('global.workUnit.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route("admin.workUnit.update", [$work_unit->id]) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                <label for="name">{{ trans('global.workUnit.fields.name') }}*</label>
                <input type="text" id="name" name="name" class="form-control" value="{{ old('name', isset($work_unit) ? $work_unit->name : '') }}" required>
                @if($errors->has('name'))
                    <em class="invalid-feedback">
                        {{ $errors->first('name') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.workUnit.fields.name_helper') }}
                </p>
            </div>

            <div class="form-group {{ $errors->has('serial_number') ? 'has-error' : '' }}">
                <label for="serial_number">{{ trans('global.workUnit.fields.serial_number') }}*</label>
                <input type="number" id="serial_number" name="serial_number" class="form-control" value="{{ old('serial_number', isset($work_unit) ? $work_unit->serial_number : '') }}" required>
                @if($errors->has('serial_number'))
                    <em class="invalid-feedback">
                        {{ $errors->first('serial_number') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.workUnit.fields.serial_number_helper') }}
                </p>
            </div>

            <input
            id="pac-input"
            class="controls"
            type="text"
            placeholder="Search Box"
          />

            <div id="map" style="height: 500px;"></div>

            <!-- Async script executes immediately and must be after any DOM elements used in callback. -->
            {{-- <script
              src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBxJpfNfWPonmRTm-TktgyaNEVyQxpBHd0&callback=initMap&v=weekly&channel=2"
              async
            ></script> --}}
        
            <script
            src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBDJyer8hPQZAOmynZbfVkizngMNZ3Hkkw&callback=initMap&v=weekly&libraries=places"
            defer
          ></script>

          <div class="form-group {{ $errors->has('lat') ? 'has-error' : '' }}">
            <label for="lat">{{ trans('global.workUnit.fields.lat') }}*</label>
            <input type="text" id="lat" name="lat" class="form-control" value="{{ old('lat', isset($work_unit) ? $work_unit->lat : '') }}" required>
            @if($errors->has('lat'))
                <em class="invalid-feedback">
                    {{ $errors->first('lat') }}
                </em>
            @endif
            <p class="helper-block">
                {{ trans('global.workUnit.fields.lat_helper') }}
            </p>
        </div>
        
        <div class="form-group {{ $errors->has('lng') ? 'has-error' : '' }}">
            <label for="lng">{{ trans('global.workUnit.fields.lng') }}*</label>
            <input type="text" id="lng" name="lng" class="form-control" value="{{ old('lng', isset($work_unit) ? $work_unit->lng : '') }}" required>
            @if($errors->has('lng'))
                <em class="invalid-feedback">
                    {{ $errors->first('lng') }}
                </em>
            @endif
            <p class="helper-block">
                {{ trans('global.workUnit.fields.lng_helper') }}
            </p>
        </div>

        <div class="form-group {{ $errors->has('radius') ? 'has-error' : '' }}">
            <label for="radius">{{ trans('global.workUnit.fields.radius') }} (Meter)*</label>
            <input type="number" id="radius" min = "1" name="radius" class="form-control" value="{{ old('radius', isset($work_unit) ? $work_unit->radius : '') }}" required>
            @if($errors->has('radius'))
                <em class="invalid-feedback">
                    {{ $errors->first('radius') }}
                </em>
            @endif
            <p class="helper-block">
                {{ trans('global.workUnit.fields.radius_helper') }}
            </p>
        </div>

        <div id="center"></div>
        {{-- <div id="radius"></div> --}}
        <div id="map"></div>

            <div>
                <input class="btn btn-danger" type="submit" value="{{ trans('global.save') }}">
            </div>
        </form>
    </div>
</div>

@endsection
@section('scripts')
@parent
<script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>
<!-- jsFiddle will insert css and js -->

<script type="text/javascript">

function initMap() {
  // Create the map.
  const lat = parseFloat(document.getElementById("lat").value)
      const lng = parseFloat(document.getElementById("lng").value)
      console.log(lng,lat)
      const myLatlng = { lat: lat, lng: lng };
  const map = new google.maps.Map(document.getElementById("map"), {
    zoom: 4,
    center: myLatlng,
    mapTypeId: 'hybrid',
  });
  map.circles = [];

// search start

 // Create the search box and link it to the UI element.
 const input = document.getElementById("pac-input");
  const searchBox = new google.maps.places.SearchBox(input);

  map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);
  // Bias the SearchBox results towards current map's viewport.
  map.addListener("bounds_changed", () => {
    searchBox.setBounds(map.getBounds());
  });


  searchBox.addListener("places_changed", () => {
    const places = searchBox.getPlaces();

    if (places.length == 0) {
      return;
    }



    const bounds = new google.maps.LatLngBounds();

    places.forEach((place) => {
      if (!place.geometry || !place.geometry.location) {
        console.log("Returned place contains no geometry");
        return;
      }
      marker.setPosition({lat : place.geometry.location.lat(), lng : place.geometry.location.lng()})
      console.log('tfddfd', place.geometry.location.lat(), place.geometry.location.lng())
      marker.Circle.bindTo('center', marker, 'position');
  marker.Circle.addListener('center_changed', function() {
    // document.getElementById('center').innerHTML = "center=" + marker.getPosition().toUrlValue(6);
    console.log(place.geometry.location.lat())
    document.getElementById('lat').value = place.geometry.location.lat();
    document.getElementById('lng').value = place.geometry.location.lng();
  });
  marker.Circle.addListener('radius_changed', function() {
    // document.getElementById('radius').innerHTML = "radius=" + marker.Circle.getRadius().toFixed(2);
    document.getElementById("radius").value = marker.Circle.getRadius().toFixed(0)
    console.log(marker.Circle.getRadius().toFixed(2))
  })
// marker = [];

      const icon = {
        url: place.icon,
        size: new google.maps.Size(71, 71),
        origin: new google.maps.Point(0, 0),
        anchor: new google.maps.Point(17, 34),
        scaledSize: new google.maps.Size(25, 25),
      };

      // Create a marker for each place.
    //   markers.push(
    //     new google.maps.Marker({
    //       map,
    //       icon,
    //       title: place.name,
    //       position: place.geometry.location,
    //     })
    //   );
      
      
      if (place.geometry.viewport) {
        // Only geocodes have viewport.
        bounds.union(place.geometry.viewport);
      } else {
        bounds.extend(place.geometry.location);
      }
    });
    map.fitBounds(bounds);
  });

// search end


  radius.addEventListener("input", function (e) {
    marker.Circle.setRadius(parseFloat(document.getElementById("radius").value))
    // alert('tess')
});

  let marker = new google.maps.Marker({
    position: map.getCenter(),
    label: "Pusat",
    map: map,
    draggable: true
  });
  console.log('kkk',marker.position.lat())
  marker.Circle = new google.maps.Circle({
    center: marker.getPosition(),
    strokeColor: '#FF0000',    
      strokeOpacity: 0.8,    
      strokeWeight: 2,    
      fillColor: '#FF0000',    
      fillOpacity: 0.35,  
    radius: parseFloat(document.getElementById("radius").value),
    map: map,
    editable: true
  })
  marker.Circle.bindTo('center', marker, 'position');
  marker.Circle.addListener('center_changed', function() {
    // document.getElementById('center').innerHTML = "center=" + marker.getPosition().toUrlValue(6);
    console.log(marker.position.lat())
    document.getElementById('lat').value = marker.position.lat();
    document.getElementById('lng').value = marker.position.lng();
  });
  marker.Circle.addListener('radius_changed', function() {
    document.getElementById('radius').innerHTML = "radius=" + marker.Circle.getRadius().toFixed(2);
    console.log(marker.Circle.getRadius().toFixed(2))
  })


  map.circles.push(marker)
  map.fitBounds(marker.Circle.getBounds())
}

</script>

<style type="text/css">
    
    /* Always set the map height explicitly to define the size of the div
   * element that contains the map. */
#map {
height: 100%;
}

/* Optional: Makes the sample page fill the window. */
html,
body {
height: 100%;
margin: 0;
padding: 0;
}

</style>
@endsection