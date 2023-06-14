import { reactive, ref, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'

enum NotificationType {
  Success = 'success',
  Warning = 'warning',
  Error = 'error',
}

export type Notification = {
  type: NotificationType
  message: string
  isShown: boolean
}

export type ErrorBag = {
  [key: string]: string[]
}

const notifications = reactive<Notification[]>([])

const addSuccessNotification = (message: string) => {
  console.log('addSuccessNotification', message)

  notifications.push({
    type: NotificationType.Success,
    message,
    isShown: false,
  })
}

const addWarningNotification = (message: string) => {
  notifications.push({
    type: NotificationType.Warning,
    message,
    isShown: false,
  })
}

const flushNotification = (notification: Notification) => {
  notifications.splice(notifications.indexOf(notification), 1)
}

const booted = ref<boolean>(false)

const boot = () => {
  const listenToAxios = () => {
    const onFulfilled = (response) => {
      const { config, data: responseData } = response

      if (config.method !== 'get' && responseData.message?.length > 0) {
        addSuccessNotification(responseData.message)
      }

      return response
    }

    const onRejected = async (error) => {
      if (Object.hasOwnProperty.call(error, 'response')) {
        const { data: responseData } = error.response

        addWarningNotification(responseData.message)
      }

      throw error
    }

    window.axios.interceptors.response.use(onFulfilled, onRejected)
  }

  const listenToFlashMessage = () => {
    watch(
      () => usePage().props.flash,
      (flash: FlashMessage) => {
        flash?.success && addSuccessNotification(flash.success)
        flash?.warning && addWarningNotification(flash.warning)
      }
    )
  }

  const listenToErrorBags = () => {
    watch(
      () =>
        usePage<{
          errorBags: {
            default: ErrorBag | null
          }
        }>().props.errorBags,
      (errorBags) => {
        Object.keys(errorBags?.default ?? {}).forEach((key) => {
          errorBags.default[key].forEach((message) => {
            addWarningNotification(key + ': ' + message)
          })
        })
      }
    )
  }

  if (!booted.value) {
    listenToAxios()
    listenToFlashMessage()
    listenToErrorBags()
  }

  booted.value = true
}

window.tuiNotification = {
  addSuccessNotification,
  addWarningNotification,
}

export {
  NotificationType,
  notifications,
  addSuccessNotification,
  addWarningNotification,
  flushNotification,
  boot,
}
