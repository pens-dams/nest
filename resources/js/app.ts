import './bootstrap.ts'
import '../css/app.css'

import { createApp, DefineComponent, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/vue.m'

const appName =
  window.document.getElementsByTagName('title')[0]?.innerText || 'HCMS'

// @ts-ignore
createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  progress: {
    color: '#29d',
  },
  resolve: async (name) => {
    return await resolvePageComponent<DefineComponent>(
      `./Pages/${name}.vue`,
      import.meta.glob<DefineComponent>('./Pages/**/*.vue')
    )
  },
  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(ZiggyVue, window.Ziggy)
      .mount(el)
  },
}).then((_) => _)
