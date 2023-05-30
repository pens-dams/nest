<script setup lang="ts">
import { onMounted } from 'vue'
import Message from './Message.vue'
import { notifications, boot } from "@/Store/notification"

const props = defineProps({
  duration: {
    type: Number,
    default: 5000,
  },
})

onMounted(boot)
</script>

<template>
  <Teleport to="body">
    <div
      aria-live="assertive"
      class="flex flex-col items-center space-y-4 sm:items-end fixed inset-0 px-4 py-6 pointer-events-none sm:p-sm:block mt-20 z-50"
    >
      <div
        v-for="notification in notifications"
        class="w-full flex flex-col items-center sm:items-end"
      >
        <Message :notification="notification" />
      </div>
    </div>
  </Teleport>
</template>
