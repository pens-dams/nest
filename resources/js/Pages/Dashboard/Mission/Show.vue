<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue'
import { onMounted, PropType, reactive, ref, toRefs, watch } from 'vue'
import { Drone as DroneModel } from '@/Types/laravel'
import GoogleMap from '@/Utils/google-maps'
import DroneDrawer from '@/Utils/drawers/drone-drawer'
import { ReactiveVariable } from 'vue/macros'
import { Drone, Line, Point } from '@/Types/local'
import LineDrawer from '@/Utils/drawers/line-drawer'
import _ from 'lodash'
import * as THREE from 'three'

const props = defineProps({
  drone: Object as PropType<DroneModel>,
})

const randomColor = _.sample(THREE.Color.NAMES)

const isFormCreateActive = ref(false)

const { drone } = toRefs(props)

const mapElement = ref()

const points: ReactiveVariable<Point[]> = reactive([])

points.push(
  new Point({
    lat: drone.value.standby_location.coordinates[1],
    lng: drone.value.standby_location.coordinates[0],
    altitude: 10,
  })
)

onMounted(async () => {
  const googleMap = new GoogleMap(mapElement.value)
  await googleMap.initMap()

  const droneDrawer =
    googleMap.threeRenderer.getDrawer<DroneDrawer>(DroneDrawer)
  const lineDrawer = googleMap.threeRenderer.getDrawer<LineDrawer>(LineDrawer)

  await droneDrawer.addData(
    new Drone(drone.value, {
      lat: drone.value.standby_location.coordinates[1],
      lng: drone.value.standby_location.coordinates[0],
      altitude: 10,
    })
  )

  watch(points, (newPoints) => {
    lineDrawer.clear()

    let pointBefore = newPoints[0] ?? null

    for (const point of newPoints) {
      if (pointBefore === point) {
        continue
      }

      lineDrawer.addData(
        new Line(pointBefore.position, point.position, null, randomColor)
      )

      pointBefore = point
    }
  })

  googleMap.threeRenderer.start()

  googleMap.map.addListener('click', (e) => {
    const point = e.latLng.toJSON()

    points.push(
      new Point({
        lat: point.lat,
        lng: point.lng,
        altitude: 10,
      })
    )
  })
})
</script>

<template>
  <AppLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-grey-800 leading-tight">
        Mission Control : {{ drone.name }}
      </h2>
    </template>

    <div class="flex">
      <div class="flex-row pb-12 w-3/4" id="mapContainer">
        <div ref="mapElement" />
      </div>
      <div class="h-75 w-1/4 p-4">
        <div class="border-b border-gray-200 bg-white px-4 py-5 sm:px-6 w-full">
          <div
            class="-ml-4 -mt-2 flex flex-wrap items-center justify-between sm:flex-nowrap"
          >
            <div class="ml-4 mt-2">
              <h3 class="text-lg font-medium leading-6 text-gray-900">Drone</h3>
            </div>
            <div class="ml-4 mt-2 flex-shrink-0">
              <button
                type="button"
                class="relative inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                @click="isFormCreateActive = true"
              >
                New Mission
              </button>
            </div>
          </div>
        </div>
        <div
          class="border-b mt-5 border-gray-200 bg-white px-4 py-5 sm:px-6 w-full"
        >
          <div
            class="-ml-4 -mt-2 flex flex-wrap items-center justify-between sm:flex-nowrap"
          >
            <div class="ml-4 mt-2 w-full">
              <h3 class="text-lg font-medium leading-6 text-gray-900">
                Points
              </h3>
              <div class="mt-1 max-w-2xl text-sm text-gray-500 w-full">
                <div
                  class="border-amber-500 p-3 rounded-md border-2 mt-2"
                  v-for="(point, index) in points"
                  :key="`point-${index}`"
                >
                  <div class="mt-2 flex rounded-md shadow-sm">
                    <span
                      class="inline-flex w-1/4 items-center rounded-l-md border border-r-0 border-gray-300 px-3 text-gray-500 sm:text-sm"
                      >Lat</span
                    >
                    <input
                      v-model="point.position.lat"
                      type="number"
                      disabled
                      name="company-website"
                      id="company-website"
                      class="block w-full min-w-0 flex-1 rounded-none rounded-r-md border-0 py-1.5 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                    />
                  </div>
                  <div class="mt-2 flex rounded-md shadow-sm">
                    <span
                      class="inline-flex w-1/4 items-center rounded-l-md border border-r-0 border-gray-300 px-3 text-gray-500 sm:text-sm"
                      >Lng</span
                    >
                    <input
                      v-model="point.position.lat"
                      type="number"
                      disabled
                      name="company-website"
                      id="company-website"
                      class="block w-full min-w-0 flex-1 rounded-none rounded-r-md border-0 py-1.5 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                    />
                  </div>
                  <div class="mt-2 flex rounded-md shadow-sm">
                    <span
                      class="inline-flex w-1/4 items-center rounded-l-md border border-r-0 border-gray-300 px-3 text-gray-500 sm:text-sm"
                      >Alt</span
                    >
                    <input
                      v-model="point.position.altitude"
                      type="number"
                      name="company-website"
                      id="company-website"
                      class="block w-full min-w-0 flex-1 rounded-none rounded-r-md border-0 py-1.5 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                    />
                  </div>
                </div>
                <button
                  type="button"
                  class="mt-2 relative inline-flex items-center rounded-md border border-transparent bg-amber-600 px-1 py-1 text-sm font-medium text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                  @click="points.pop()"
                >
                  Remove Last Point
                </button>
              </div>
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
