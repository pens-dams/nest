<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue'
import type { PropType } from 'vue'
import { onMounted, ref } from 'vue'
import GoogleMap from '@/Utils/google-maps'
import type { Flight, Intersect } from '@/Types/laravel'
import { Drone, Line, Point } from '@/Types/local'
import DroneDrawer from '@/Utils/drawers/drone-drawer'
import LineDrawer from '@/Utils/drawers/line-drawer'
import DotDrawer from '@/Utils/drawers/dot-drawer'
import LatLngAltitudeLiteral = google.maps.LatLngAltitudeLiteral

const mapElement = ref()

const props = defineProps({
  flights: {
    type: Array as PropType<Flight[]>,
    required: true,
  },
  intersections: {
    type: Array as PropType<Intersect[]>,
    required: true,
  },
})

const drones = _.map(props.flights, (flight: Flight) => {
  const origin: LatLngAltitudeLiteral = {
    lat: flight.from.coordinates[1],
    lng: flight.from.coordinates[0],
    altitude: flight.planned_altitude,
  }

  const destination: LatLngAltitudeLiteral = {
    lat: flight.to.coordinates[1],
    lng: flight.to.coordinates[0],
    altitude: flight.planned_altitude,
  }

  const current: LatLngAltitudeLiteral = {
    lat: flight.from.coordinates[1],
    lng: flight.from.coordinates[0],
    altitude: flight.planned_altitude,
  }

  return new Drone(flight.drone, current, origin, destination, flight.speed)
})

onMounted(async () => {
  if (drones.length === 0) {
    return
  }

  const googleMap = new GoogleMap(mapElement.value)
  await googleMap.initMap()

  const droneDrawer =
    googleMap.threeRenderer.getDrawer<DroneDrawer>(DroneDrawer)

  for (const drone of drones) {
    await droneDrawer.addData(drone)
  }
  droneDrawer.startAnimation()

  const lineDrawer = await googleMap.threeRenderer.getDrawer<LineDrawer>(
    LineDrawer
  )
  for (const flight of props.flights) {
    await lineDrawer.addData(
      new Line(
        {
          lat: flight.from.coordinates[1],
          lng: flight.from.coordinates[0],
          altitude: flight.planned_altitude ?? 40,
        },
        {
          lat: flight.to.coordinates[1],
          lng: flight.to.coordinates[0],
          altitude: flight.planned_altitude ?? 40,
        },
        flight
      )
    )

    // let i = 1
    //   for (const log of flight.logs) {
    //     setTimeout(async () => {
    //       await dotDrawer.addData(
    //         new Point(
    //           {
    //             lat: log.position.coordinates[1],
    //             lng: log.position.coordinates[0],
    //             altitude: log.altitude,
    //           },
    //           2
    //         )
    //       )
    //     }, 1000 * i++)
    //   }
  }

  const dotDrawer = await googleMap.threeRenderer.getDrawer<DotDrawer>(
    DotDrawer
  )

  for (const intersect of props.intersections) {
    const data = new Point(null, intersect.radius, 0xff0000, {
      x: _.get(intersect.meta, 'collision.middlePoint.x'),
      y: _.get(intersect.meta, 'collision.middlePoint.y'),
      z: _.get(intersect.meta, 'collision.middlePoint.z'),
    })

    await dotDrawer.addData(data)
  }

  googleMap.threeRenderer.start()
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
