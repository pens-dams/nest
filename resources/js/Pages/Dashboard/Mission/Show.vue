<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue'

const props = defineProps({
  drone: Object,
})

import {Loader} from '@googlemaps/js-api-loader'
import {onMounted, reactive, ref, watch} from "vue"

const mapElement = ref()

const apiOptions = {
  "apiKey": "AIzaSyB5cKCyCU8rDJjgDgWj0004ZflsFphwFCU",
  "version": "beta"
}

const mapOptions = {
  "tilt": 67,
  "heading": 0,
  "zoom": 14.5,
  "center": {lat: -7.250445, lng: 112.768845},
  "mapId": "42d59f7789a288e6",
}

let google

async function initMap() {
  const mapDiv = mapElement.value
  const apiLoader = new Loader(apiOptions)
  google = await apiLoader.load()

  return new google.maps.Map(mapDiv, mapOptions)
}

const points = reactive([])

onMounted(async () => {
  const map = await initMap()

  function getCoordinateOnClick() {

    // Configure the click listener.
    map.addListener("click", (mapsMouseEvent) => {
      points.push({
        lat: mapsMouseEvent.latLng.lat(),
        lng: mapsMouseEvent.latLng.lng(),
      })
    });
  }

  let flightPath

  let markers = []

  watch(points, (points) => {
    if (points.length > 1) {
      flightPath?.setMap(null)

      flightPath = new google.maps.Polyline({
        path: points,
        geodesic: true,
        strokeColor: "#e11d48",
        strokeOpacity: 1.0,
        strokeWeight: 5,
      });

      flightPath.setMap(map);
    }
  })

  for (let i = 0; i < points.length; i++) {
    const point = points[i]

    const marker = new google.maps.Marker({
      position: point,
      title: "Point : "+ (i+1),
    })

    marker.setMap(map)

    markers[i] = marker
  }

  getCoordinateOnClick()
})

</script>

<template>
<AppLayout>
  <template #header>
    <h2 class="font-semibold text-xl text-grey-800 leading-tight">
      Mission Control : {{drone.name}}
    </h2>
  </template>

  <div class="flex">
    <div class="flex-row pb-12 w-3/4" id="mapContainer">
      <div ref="mapElement"/>
    </div>
    <div class="h-75 w-1/4 p-4">
      <div class="border-b border-gray-200 bg-white px-4 py-5 sm:px-6 w-full">
        <div class="-ml-4 -mt-2 flex flex-wrap items-center justify-between sm:flex-nowrap">
          <div class="ml-4 mt-2">
            <h3 class="text-lg font-medium leading-6 text-gray-900">Drone</h3>
          </div>
          <div class="ml-4 mt-2 flex-shrink-0">
            <button type="button" class="relative inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Start Mission</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</AppLayout>
</template>

<style scoped>
#mapContainer > * {
  height: 800px;
}
</style>
