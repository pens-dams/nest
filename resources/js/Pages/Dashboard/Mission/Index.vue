<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue'
import {ComputerDesktopIcon} from '@heroicons/vue/24/solid'
import {toRefs} from "vue";
import route from "ziggy-js";

const props = defineProps({
  drones: {
    required: true,
    type: Array,
  }
})

const { drones } = toRefs(props)

</script>

<template>
  <AppLayout title="Mission Control">
    <template #header>
      <h2 class="font-semibold text-xl text-grey-800 leading-tight">
        Mission Control
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mt-12 max-w-lg mx-auto grid gap-5 lg:grid-cols-3 lg:max-w-none">
          <a v-for="drone in drones" :key="drone.id" :href="route('dashboard.mission.show', {drone: drone.id})">
            <div class="flex flex-col rounded-lg shadow-lg overflow-hidden">
              <div class="flex-shrink-0">
                <img class="h-48 w-full object-cover" :src="drone.photo_path" alt="" />
              </div>
              <div class="flex-1 bg-white p-6 flex flex-col justify-between">
                <div class="flex-1">
                  <p class="mt-2 flex items-center text-sm sm:mt-0 text-indigo-600">
                    <ComputerDesktopIcon class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" aria-hidden="true"/>
                    <a href="#">
                      {{ drone.serial_number }}
                    </a>
                  </p>
                  <a :href="drone.href" class="block mt-2">
                    <p class="text-xl font-semibold text-gray-900">
                      {{ drone.name }}
                    </p>
                  </a>
                </div>
              </div>
            </div>
          </a>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
