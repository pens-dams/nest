<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue'
import type { PropType } from 'vue'
import { onMounted, ref, toRaw } from 'vue'
import GoogleMap from '@/Utils/google-maps'
import type { Flight, Intersect } from '@/Types/laravel'
import { Drone, Line, Point } from '@/Types/local'
import DroneDrawer from '@/Utils/drawers/drone-drawer'
import LineDrawer from '@/Utils/drawers/line-drawer'
import DotDrawer from '@/Utils/drawers/dot-drawer'
import _ from 'lodash'
import * as THREE from 'three'

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

onMounted(async () => {
  if (props.flights.length === 0) {
    return
  }

  const googleMap = new GoogleMap(mapElement.value)
  await googleMap.initMap()

  const droneDrawer =
    googleMap.threeRenderer.getDrawer<DroneDrawer>(DroneDrawer)
  const lineDrawer = googleMap.threeRenderer.getDrawer<LineDrawer>(LineDrawer)
  const dotDrawer = googleMap.threeRenderer.getDrawer<DotDrawer>(DotDrawer)

  for (const flight of props.flights) {
    const points: google.maps.LatLngAltitudeLiteral[] = flight.paths.map(
      (path) => {
        return {
          lat: path.position.coordinates[1],
          lng: path.position.coordinates[0],
          altitude: path.altitude,
        }
      }
    )

    const lineColor = flight.color ?? _.sample(THREE.Color.NAMES)
    const firstPoint = points[0] ?? null

    let pointBefore = firstPoint

    for (const point of points) {
      if (pointBefore == point) {
        continue
      }

      await lineDrawer.addData(new Line(pointBefore, point, flight, lineColor))

      pointBefore = point
    }

    if (firstPoint) {
      await droneDrawer.addData(
        new Drone(
          flight.drone,
          flight.paths.map((path) => ({
            position: {
              lat: path.position.coordinates[1],
              lng: path.position.coordinates[0],
              altitude: path.altitude,
            },
            speed: flight.speed,
            seconds: _.get(path.meta, 'time.value', null),
          }))
        )
      )
    }
  }

  for (const intersect of props.intersections) {
    const data = new Point(null, intersect.radius, 0xff0000, {
      x: _.get(intersect.meta, 'collision.middlePoint.x'),
      y: _.get(intersect.meta, 'collision.middlePoint.y'),
      z: _.get(intersect.meta, 'collision.middlePoint.z'),
    })

    await dotDrawer.addData(data)
  }

  googleMap.threeRenderer.start()

  setTimeout(() => {
    droneDrawer.startAnimation()
  }, 3000)
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
