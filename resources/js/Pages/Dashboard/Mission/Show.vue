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
      console.log(mapsMouseEvent)
      points.push({
        lat: mapsMouseEvent.latLng.lat(),
        lng: mapsMouseEvent.latLng.lng(),
      })
    });
  }

  let flightPath

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

  <div class="pb-12" id="mapContainer">
    <div ref="mapElement"/>
  </div>
</AppLayout>
</template>

<style scoped>
#mapContainer > * {
  height: 800px;
}
</style>
