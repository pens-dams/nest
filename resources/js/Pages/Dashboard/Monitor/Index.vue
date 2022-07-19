<script setup>
import AppLayout from '../../../Layouts/AppLayout.vue'
import {Loader} from '@googlemaps/js-api-loader'
import * as THREE from 'three'
import {GLTFLoader} from 'three/examples/jsm/loaders/GLTFLoader.js'
import {onMounted, ref} from "vue"

const mapElement = ref()

const apiOptions = {
  "apiKey": "AIzaSyB5cKCyCU8rDJjgDgWj0004ZflsFphwFCU",
  "version": "beta"
}

const mapOptions = {
  "tilt": 67,
  "heading": 0,
  "zoom": 19.5,
  "center": {lat: 35.6594945, lng: 139.6999859},
  "mapId": "43bf1eaa5b58a8df",
}

let google

async function initMap() {
  const mapDiv = mapElement.value
  const apiLoader = new Loader(apiOptions)
  google = await apiLoader.load()

  return new google.maps.Map(mapDiv, mapOptions)
}

function initWebGLOverlayView(map) {
  let scene, renderer, camera, loader

  const webGLOverlayView = new google.maps.WebGLOverlayView()

  webGLOverlayView.onAdd = () => {
    scene = new THREE.Scene()
    camera = new THREE.PerspectiveCamera()
    const ambientLight = new THREE.AmbientLight(0xffffff, 1) // soft white light
    scene.add(ambientLight)
    const directionalLight = new THREE.DirectionalLight(0xffffff, 0.75)
    directionalLight.position.set(0.5, -1, 0.5)
    scene.add(directionalLight)

    loader = new GLTFLoader()

    const source = '/files/drone.glb'

    loader.load(
      source,
      gltf => {
        gltf.scene.scale.set(25, 25, 25)
        gltf.scene.rotation.x = 90 * Math.PI / 180
        scene.add(gltf.scene)
      }
    )
  }
  webGLOverlayView.onContextRestored = ({gl}) => {
    renderer = new THREE.WebGLRenderer({
      canvas: gl.canvas,
      context: gl,
      ...gl.getContextAttributes(),
    })

    renderer.autoClear = false

    // loader.manager.onLoad = () => {
    //     renderer.setAnimationLoop(() => {
    //       map.moveCamera({
    //         "tilt": mapOptions.tilt,
    //         "heading": mapOptions.heading,
    //         "zoom": mapOptions.zoom
    //       })
    //
    //       if (mapOptions.tilt < 67.5) {
    //         mapOptions.tilt += 0.5
    //       } else if (mapOptions.heading <= 360) {
    //         mapOptions.heading += 0.2
    //       } else {
    //         renderer.setAnimationLoop(null)
    //       }
    //     })
    // }
  }


  webGLOverlayView.onDraw = ({gl, transformer}) => {
    const latLngAltitudeLiteral = {
      lat: mapOptions.center.lat,
      lng: mapOptions.center.lng,
      altitude: 40
    }

    const matrix = transformer.fromLatLngAltitude(latLngAltitudeLiteral)
    camera.projectionMatrix = new THREE.Matrix4().fromArray(matrix)

    webGLOverlayView.requestRedraw()
    renderer.render(scene, camera)
    renderer.resetState()
  }

  webGLOverlayView.setMap(map)
  // WebGLOverlayView code goes here
}

onMounted(async () => {
  const map = await initMap()
  initWebGLOverlayView(map)
})

</script>

<template>
  <AppLayout title="Monitor Control">
    <template #header>
      <h2 class="font-semibold text-xl text-grey-800 leading-tight">
        Air Navigation Monitoring
      </h2>
    </template>

    <div class="pb-12" id="mapContainer">
      <div ref="mapElement"/>
    </div>
  </AppLayout>
</template>

<style scoped>
#mapContainer > * {
  height: 800px;
}
</style>
