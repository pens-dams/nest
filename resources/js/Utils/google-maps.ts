import { Loader } from '@googlemaps/js-api-loader'
import _ from 'lodash'
import ThreeRenderer from '@/Utils/three-renderer'
import { ThreeJSOverlayView } from '@googlemaps/three'
import { computed, ComputedRef } from 'vue'
import { usePage } from '@inertiajs/vue3'

const apiOptions = {
  apiKey: import.meta.env.VITE_GOOGLE_MAPS_API_KEY,
  version: 'beta',
}

let google

class GoogleMap {
  get map(): google.maps.Map {
    return this.#map
  }

  private overlay: ThreeJSOverlayView

  get threeRenderer(): ThreeRenderer {
    return this.#threeRenderer
  }

  #map: google.maps.Map
  #threeRenderer: ThreeRenderer

  constructor(protected readonly mapElement: HTMLElement) {}

  public addPolyline(
    path: google.maps.LatLngLiteral[],
    options?: google.maps.PolylineOptions
  ): google.maps.Polyline {
    const colors = [
      '#65a30d',
      '#0284c7',
      '#e11d48',
      '#c026d3',
      '#4b5563',
      '#dc2626',
      '#d97706',
      '#ca8a04',
      '#059669',
      '#047857',
      '#065f46',
      '#064e3b',
      '#1d4ed8',
      '#1e40af',
      '#6b7280',
      '#374151',
      '#1f2937',
      '#111827',
      '#7c3aed',
      '#a78bfa',
    ]

    const polyline = new google.maps.Polyline({
      path,
      ...(options ?? {
        geodesic: true,
        strokeColor: _.sample(colors),
        strokeOpacity: 1.0,
        strokeWeight: 5,
      }),
    })

    polyline.setMap(this.#map)

    return polyline
  }

  public async initMap(center?: google.maps.LatLngLiteral) {
    if (!center) {
      const anchor: ComputedRef<{ lat: number; lng: number }> = computed(
        // @ts-ignore
        () => usePage().props?.nest?.anchor
      )

      center = {
        lat: anchor.value.lat,
        lng: anchor.value.lng,
      }
    }

    const apiLoader = new Loader(apiOptions)

    google = await apiLoader.load()

    // load a geometry library
    await google.maps.importLibrary('geometry')

    this.#map = new google.maps.Map(this.mapElement, {
      tilt: 67,
      heading: 0,
      zoom: 18,
      center: center ?? { lat: -7.275884, lng: 112.794072 },
      mapId: '42d59f7789a288e6',
    })

    this.overlay = new ThreeJSOverlayView({
      map: this.#map,
      anchor: {
        lat: center.lat,
        lng: center.lng,
        altitude: 0,
      },
      upAxis: 'Z',
    })

    this.#threeRenderer = new ThreeRenderer(this.#map, this.overlay)
  }
}

export default GoogleMap
