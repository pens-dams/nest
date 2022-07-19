<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue'
import {
  CalendarIcon,
  LocationMarkerIcon,
  DesktopComputerIcon,
  RefreshIcon,
  EyeIcon,
  EyeOffIcon,
  KeyIcon
} from '@heroicons/vue/solid'
import {computed, ref, toRefs} from "vue";
import {dateTimeFormat} from '../../../Utils/data-time-formatter'

const props = defineProps({
  payload: {
    type: Object,
    required: true,
  },
  user: {
    type: Object,
    required: true,
  },
})

const {payload, user} = toRefs(props)

const showToken = ref(false)

const token = computed(() => showToken.value ? user.value.current_team?.compute_token : 'click to show!')
</script>

<template>
  <AppLayout title="Ground Control Settings">
    <template #header>
      <h2 class="font-semibold text-xl text-grey-800 leading-tight">
        Ground Control Settings
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
          <div class="px-4 py-4 sm:px-6">
            <label for="compute_token" class="block text-sm font-medium text-gray-700">Token</label>
            <div class="mt-1 flex rounded-md shadow-sm">
              <div class="relative flex items-stretch flex-grow focus-within:z-10">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <KeyIcon class="h-5 w-5 text-gray-400" aria-hidden="true"/>
                </div>
                <input type="text" name="compute_token" id="compute_token"
                       class="focus:ring-indigo-500 focus:border-indigo-500 block w-full rounded-none rounded-l-md pl-10 sm:text-sm border-gray-300 text-gray-600"
                       :value="token"
                       placeholder="AaaaabbBbbbbbbbCaaccccccccccc22211"
                       :disabled="true"
                />
              </div>
              <button type="button" @click="showToken = !showToken"
                      class="-ml-px relative inline-flex items-center space-x-2 px-4 py-2 border border-gray-300 text-sm font-medium text-gray-700 bg-gray-50 hover:bg-gray-100 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                <EyeOffIcon v-if="showToken" class="h-5 w-5 text-gray-400" aria-hidden="true"/>
                <EyeIcon v-else class="h-5 w-5 text-gray-400" aria-hidden="true"/>
                <span>{{ showToken ? 'Hide' : 'Show' }}</span>
              </button>
              <button type="button"
                      class="-ml-px relative inline-flex items-center space-x-2 px-4 py-2 border border-gray-300 text-sm font-medium rounded-r-md text-gray-700 bg-gray-50 hover:bg-gray-100 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                <RefreshIcon class="h-5 w-5 text-gray-400" aria-hidden="true"/>
                <span>Regenerate Token</span>
              </button>
            </div>
          </div>
        </div>

        <div class="bg-white shadow overflow-hidden sm:rounded-lg mt-3">
          <div v-if="payload.data.length === 0" class="text-center m-4 text-gray-600">No connected computer yet!</div>
          <ul v-else role="list" class="divide-y divide-gray-200">
            <li v-for="computer in payload.data" :key="computer.id">
              <a href="#" class="block hover:bg-gray-50">
                <div class="px-4 py-4 sm:px-6">
                  <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-indigo-600 truncate">
                      {{ computer.name }}
                    </p>
                    <div class="ml-2 flex-shrink-0 flex">
                      <p
                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                        :class="{
                          'bg-green-100 text-green-800': computer.state === 'connected',
                          'bg-red-100 text-red-800' : computer.state === 'disconnected',
                        }">
                        {{ computer.state === 'connected' ? 'Connected' : 'Disconnected' }}
                      </p>
                    </div>
                  </div>
                  <div class="mt-2 sm:flex sm:justify-between">
                    <div class="sm:flex">
                      <p class="flex items-center text-sm text-gray-500">
                        <DesktopComputerIcon class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" aria-hidden="true"/>
                        {{ computer.ip }}
                      </p>
                      <p class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0 sm:ml-6">
                        <LocationMarkerIcon class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" aria-hidden="true"/>
                        {{ computer.location }}
                      </p>
                    </div>
                    <div class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0">
                      <CalendarIcon class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" aria-hidden="true"/>
                      <p>
                        Latest Handshake at
                        {{ ' ' }}
                        <time :datetime="computer.latest_handshake">
                          {{dateTimeFormat(computer.latest_handshake) }}
                        </time>
                      </p>
                    </div>
                  </div>
                </div>
              </a>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
