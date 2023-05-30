<script setup lang="ts">
import { XMarkIcon } from '@heroicons/vue/24/solid'
import {
  CheckCircleIcon,
  ExclamationCircleIcon,
} from '@heroicons/vue/24/outline'
import { onMounted, ref, toRef } from 'vue'
import type { PropType, Ref } from 'vue'
import { flushNotification, NotificationType } from '@/Store/notification'
import type { Notification } from '@/Store/notification'

const props = defineProps({
  notification: {
    type: Object as PropType<Notification>,
    required: true,
  },
  duration: {
    type: Number,
    default: 5000,
  },
})

const notification: Ref<Notification> = toRef(props, 'notification')

const startCountdown = ref(false)

onMounted(() => {
  setTimeout(() => {
    startCountdown.value = true

    setTimeout(() => {
      props.notification.isShown = true

      setTimeout(() => {
        flushNotification(props.notification)
      }, 1000)
    }, props.duration)
  }, 500)
})
</script>

<template>
  <transition
    enter-active-class="transform ease-out duration-500 transition"
    enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
    enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
    leave-active-class="transition ease-in duration-500"
    leave-from-class="translate-y-0 opacity-100 sm:translate-x-0"
    leave-to-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
  >
    <div
      v-if="!notification.isShown"
      class="max-w-sm w-full shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden bg-white"
    >
      <div class="p-4">
        <div class="flex items-start">
          <div class="flex-shrink-0">
            <CheckCircleIcon
              v-if="notification.type === NotificationType.Success"
              class="h-6 w-6 text-green-400"
              aria-hidden="true"
            />
            <ExclamationCircleIcon
              v-else
              class="h-6 w-6 text-primary-600"
              aria-hidden="true"
            />
          </div>
          <div class="ml-3 w-0 flex-1 pt-0.5">
            <p
              class="text-sm font-medium"
              :class="{
                'text-gray-900': notification.type === NotificationType.Success,
                'text-primary-600':
                  notification.type === NotificationType.Error,
              }"
            >
              {{
                notification.type === NotificationType.Success
                  ? 'Berhasil!'
                  : 'Perhatian!'
              }}
            </p>
            <p class="mt-1 text-sm text-gray-500">
              {{ notification.message }}
            </p>
          </div>
          <div class="ml-4 flex-shrink-0 flex">
            <button
              type="button"
              @click="notification.isShown = true"
              class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500"
            >
              <span class="sr-only">Close</span>
              <XMarkIcon class="h-5 w-5" aria-hidden="true" />
            </button>
          </div>
        </div>
      </div>
      <div
        class="transition-transform ease-out w-100"
        :class="[
          'border-b-4',
          notification.type === NotificationType.Success
            ? 'border-green-400'
            : 'border-red-600',
          startCountdown ? 'closed' : '',
        ]"
        :style="{ transitionDuration: `${duration}ms` }"
      />
    </div>
  </transition>
</template>

<style scoped>
/*noinspection ALL*/
.closed {
  transform: translateX(-100%);
}
</style>
