<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue'
import {
  computed,
  onMounted,
  PropType,
  reactive,
  Ref,
  ref,
  toRaw,
  toRefs,
  watch,
} from 'vue'
import { Drone as DroneModel, Flight } from '@/Types/laravel'
import GoogleMap from '@/Utils/google-maps'
import DroneDrawer from '@/Utils/drawers/drone-drawer'
import { ReactiveVariable } from 'vue/macros'
import { Drone, Line, Point } from '@/Types/local'
import LineDrawer from '@/Utils/drawers/line-drawer'
import _ from 'lodash'
import * as THREE from 'three'
import { XMarkIcon } from '@heroicons/vue/24/outline'
import { router } from '@inertiajs/vue3'
import route from 'ziggy-js'
import addMinutes from 'date-fns/addMinutes'
import DotDrawer from '@/Utils/drawers/dot-drawer'

const props = defineProps({
  drone: Object as PropType<DroneModel>,
  flights: Array as PropType<Flight[]>,
})

const randomColor = _.sample(THREE.Color.NAMES)

const isFormCreateActive: Ref<boolean> = ref(false)
const isFormEditActive: Ref<boolean> = ref(false)
const isLookingForPoint: Ref<boolean> = ref(false)
const selectedFlightUlid: Ref<string | null> = ref(null)

const selectedFlight = computed(() => {
  return _.find(
    toRaw(props.flights),
    (flight) => flight.ulid == selectedFlightUlid.value
  )
})

const { drone } = toRefs(props)

const mapElement = ref()

const departureTime = ref()
const points: ReactiveVariable<Point[]> = reactive([])

points.push(
  new Point({
    lat: drone.value.standby_location.coordinates[1],
    lng: drone.value.standby_location.coordinates[0],
    altitude: 10,
  })
)

const canSaveFlight = computed(() => {
  return points.length > 1
})

const saveFlight = () => {
  if (!canSaveFlight.value) {
    return
  }

  const departure = new Date(departureTime.value)

  router.post(
    route('dashboard.mission.store', { drone: drone.value.id }),
    {
      departure: departure.toISOString(),
      points: points.map((point) => {
        return {
          lat: point.position.lat,
          lng: point.position.lng,
          alt: point.position.altitude,
        }
      }),
    },
    {
      onSuccess: () => {
        isFormCreateActive.value = false
        points.splice(1, points.length - 1)
      },
    }
  )
}

const clearForm = () => {
  isFormCreateActive.value = false
  isFormEditActive.value = false
  selectedFlightUlid.value = null
  points.splice(1, points.length - 1)
}

onMounted(async () => {
  const googleMap = new GoogleMap(mapElement.value)
  await googleMap.initMap()

  const droneDrawer =
    googleMap.threeRenderer.getDrawer<DroneDrawer>(DroneDrawer)
  const lineDrawer = googleMap.threeRenderer.getDrawer<LineDrawer>(LineDrawer)
  const dotDrawer = googleMap.threeRenderer.getDrawer<DotDrawer>(DotDrawer)

  watch(selectedFlightUlid, (newValue) => {
    if (newValue === null) {
      dotDrawer.clear()
      lineDrawer.clear()
      droneDrawer.clear()

      isFormEditActive.value = false
      points.splice(1, points.length)
      return
    }

    isFormEditActive.value = true
    if (selectedFlight.value?.departure) {
      let date = new Date(selectedFlight.value.departure)

      date = addMinutes(date, date.getTimezoneOffset())

      departureTime.value = date.toISOString().slice(0, 16)
    }

    droneDrawer.addData(
      new Drone(
        selectedFlight.value.drone,
        selectedFlight.value.logs.map((log) => ({
          position: {
            lat: log.position.coordinates[1],
            lng: log.position.coordinates[0],
            altitude: log.altitude,
          },
          speed: log.speed,
        }))
      )
    )

    points.splice(0, points.length)
    for (const path of selectedFlight.value?.paths ?? []) {
      points.push(
        new Point({
          lat: path.position.coordinates[1],
          lng: path.position.coordinates[0],
          altitude: path.altitude,
        })
      )
    }

    for (const log of selectedFlight.value.logs) {
      dotDrawer.addData(
        new Point(
          {
            lat: log.position.coordinates[1],
            lng: log.position.coordinates[0],
            altitude: log.altitude,
          },
          1.5
        )
      )
    }

    setTimeout(() => {
      droneDrawer.startAnimation()
    }, 4000)
  })

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

    console.log(toRaw(newPoints.map((point) => toRaw(point.position))))
  })

  googleMap.threeRenderer.start()

  googleMap.map.addListener('click', (e) => {
    if (!isLookingForPoint.value || !isFormCreateActive.value) {
      return
    }

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
      <div
        class="flex-row pb-12 w-3/4"
        id="mapContainer"
        :class="{ cursorPointer: isLookingForPoint }"
      >
        <div ref="mapElement" />
      </div>
      <div class="h-[50rem] w-[32rem] overflow-auto p-4">
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
          v-if="isFormCreateActive || isFormEditActive"
          class="border-b mt-5 border-gray-200 bg-white px-4 py-5 sm:px-6 w-full"
        >
          <div class="-ml-4 -mt-2 flex flex-wrap items-center justify-between">
            <div class="pl-4 mt-2 w-1/2">
              <h3 class="text-lg font-medium leading-6 text-gray-900">
                Points
              </h3>
            </div>
            <div class="mt-2 text-right">
              <a href="#" @click="clearForm">
                <XMarkIcon class="w-5" />
              </a>
            </div>
            <div class="ml-4 mt-2 w-full">
              <div class="mt-1 max-w-2xl text-sm text-gray-500 w-full">
                <div class="mt-2 flex rounded-md shadow-sm">
                  <span
                    class="inline-flex w-1/4 items-center rounded-l-md border border-r-0 border-gray-300 px-3 text-gray-500 sm:text-sm"
                    >Departure</span
                  >
                  <input
                    v-model="departureTime"
                    type="datetime-local"
                    name="company-website"
                    id="company-website"
                    class="block w-full min-w-0 flex-1 rounded-none rounded-r-md border-0 py-1.5 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                  />
                </div>
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
                  class="mt-2 relative inline-flex items-center rounded-md border border-transparent bg-amber-600 px-1 py-1 text-sm font-medium text-white shadow-sm hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2"
                  @click="points.pop()"
                >
                  Remove Last Point
                </button>
                <button
                  type="button"
                  class="mt-2 ml-3 relative inline-flex items-center rounded-md border border-transparent border-blue-400 px-1 py-1 text-sm font-medium text-emerald-950 shadow-sm hover:bg-blue-700 hover:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                  :class="{
                    'bg-blue-700 text-white': isLookingForPoint,
                    'text-emerald-950': !isLookingForPoint,
                  }"
                  @click="isLookingForPoint = !isLookingForPoint"
                >
                  Add Point
                </button>
                <button
                  v-if="canSaveFlight"
                  type="button"
                  class="mt-2 ml-3 relative inline-flex items-center rounded-md border border-transparent border-green-400 px-1 py-1 text-sm font-medium text-green-950 shadow-sm hover:bg-green-700 hover:text-white focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                  @click="saveFlight"
                >
                  Save
                </button>
              </div>
            </div>
          </div>
        </div>
        <div
          class="border-b mt-5 border-gray-200 bg-white px-4 py-5 sm:px-6 w-full"
        >
          <div class="-ml-4 -mt-2 flex flex-wrap items-center justify-between">
            <a
              href="#"
              v-for="flight in flights"
              @click="selectedFlightUlid = flight.ulid"
            >
              <div
                class="border-amber-500 p-3 rounded-md border-2 mt-2"
                :class="{
                  'bg-amber-500 text-white': selectedFlightUlid === flight.ulid,
                }"
              >
                #{{ flight.code }}
              </div>
            </a>
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
