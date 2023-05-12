<script setup lang="ts">
import AppLayout from '../../../Layouts/AppLayout.vue'
import { onMounted, ref, computed, ComputedRef } from 'vue'
import type { PropType } from 'vue'
import GoogleMap from '@/Utils/google-maps'
import type { Flight } from '@/Types/laravel'
import { Drone } from '@/Types/local'
import LatLngAltitudeLiteral = google.maps.LatLngAltitudeLiteral
import DroneDrawer from '@/Utils/drawers/drone-drawer'
import LineDrawer from '@/Utils/drawers/line-drawer'
import { usePage } from '@inertiajs/vue3'

const mapElement = ref()

const props = defineProps({
  flights: {
    type: Array as PropType<Flight[]>,
    required: true,
  },
})

const anchor: ComputedRef<{ lat: number; lng: number }> = computed(
  () => usePage().props?.nest?.anchor
)

const drones = _.map(props.flights, (flight: Flight) => {
  const origin: LatLngAltitudeLiteral = {
    lat: flight.from.coordinates[1],
    lng: flight.from.coordinates[0],
    altitude: 40,
  }

  const destination: LatLngAltitudeLiteral = {
    lat: flight.to.coordinates[1],
    lng: flight.to.coordinates[0],
    altitude: 40,
  }

  const current: LatLngAltitudeLiteral = {
    lat: flight.from.coordinates[1],
    lng: flight.from.coordinates[0],
    altitude: 40,
  }

  return new Drone(origin, destination, flight.drone, current)
})

onMounted(async () => {
  if (drones.length === 0) {
    return
  }

  const googleMap = new GoogleMap(mapElement.value)
  await googleMap.initMap({
    lat: anchor.value.lat,
    lng: anchor.value.lng,
  })

  const droneDrawer =
    googleMap.threeRenderer.getDrawer<DroneDrawer>(DroneDrawer)

  for await (const drone of drones) {
    await droneDrawer.addData(drone)
  }

  droneDrawer.startAnimation()

  const lineDrawer = await googleMap.threeRenderer.getDrawer<LineDrawer>(
    LineDrawer
  )

  for await (const flight of props.flights) {
    await lineDrawer.addData({
      origin: {
        lat: flight.from.coordinates[1],
        lng: flight.from.coordinates[0],
        altitude: 40,
      },
      destination: {
        lat: flight.to.coordinates[1],
        lng: flight.to.coordinates[0],
        altitude: 40,
      },
      flight,
    })
  }
})
</script>

<template>
  <AppLayout title="Monitor Control">
    <template #header>
      <h2 class="font-semibold text-xl text-grey-800 leading-tight">
        Air Navigation Monitoring
      </h2>
    </template>

    <div class="pb-12" id="mapContainer">
      <div ref="mapElement" />
    </div>
  </AppLayout>
</template>

<style scoped>
#mapContainer > * {
  height: 800px;
}
</style>
