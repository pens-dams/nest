import { AxiosInstance } from 'axios'

declare global {
  interface Window {
    axios: AxiosInstance
    tuiNotification: object | null
    Ziggy?: object
  }
}
