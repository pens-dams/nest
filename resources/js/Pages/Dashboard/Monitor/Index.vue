<script setup lang="ts">
import AppLayout from "../../../Layouts/AppLayout.vue";
import { Loader } from "@googlemaps/js-api-loader";
import * as THREE from "three";
import { GLTFLoader } from "three/examples/jsm/loaders/GLTFLoader.js";
import { onMounted, ref } from "vue";

const mapElement = ref();

const apiOptions = {
  "apiKey": "AIzaSyB5cKCyCU8rDJjgDgWj0004ZflsFphwFCU",
  "version": "beta"
};

const mapOptions = {
  "tilt": 67,
  "heading": 0,
  "zoom": 14.5,
  "center": { lat: -7.250445, lng: 112.768845 },
  "mapId": "42d59f7789a288e6"
};

let google;

async function initMap() {
  const mapDiv = mapElement.value;
  const apiLoader = new Loader(apiOptions);
  google = await apiLoader.load();

  return new google.maps.Map(mapDiv, mapOptions);
}

function initWebGLOverlayView(map) {
  let scene, renderer, camera, loader;

  const webGLOverlayView = new google.maps.WebGLOverlayView();

  webGLOverlayView.onAdd = () => {
    scene = new THREE.Scene();
    camera = new THREE.PerspectiveCamera();
    const ambientLight = new THREE.AmbientLight(0xffffff, 1); // soft white light
    scene.add(ambientLight);
    const directionalLight = new THREE.DirectionalLight(0xffffff, 0.75);
    directionalLight.position.set(0.5, -1, 0.5);
    scene.add(directionalLight);

    loader = new GLTFLoader(THREE.DefaultLoadingManager);

    const source = "/files/drone.glb";

    loader.load(
      source,
      gltf => {
        gltf.scene.scale.set(25, 25, 25);
        gltf.scene.rotation.x = 90 * Math.PI / 180;
        scene.add(gltf.scene);
      }
    );
  };

  webGLOverlayView.onContextRestored = ({ gl }) => {
    const attributes = gl.getContextAttributes();

    renderer = new THREE.WebGLRenderer({
      canvas: gl.canvas,
      context: gl,
      alpha: attributes.alpha,
      antialias: attributes.antialias,
      depth: attributes.depth,
      failIfMajorPerformanceCaveat: attributes.failIfMajorPerformanceCaveat,
      powerPreference: attributes.powerPreference,
      premultipliedAlpha: attributes.premultipliedAlpha,
      preserveDrawingBuffer: attributes.preserveDrawingBuffer,
      stencil: attributes.stencil,
    });

    renderer.autoClear = false;
  };

  const drones = [
    {
      lat: -7.2619491,
      lng: 112.7478422,
      altitude: 40
    },
    {
      lat: -7.261949,
      lng: 112.747542,
      altitude: 80
    }
  ];

  webGLOverlayView.onDraw = ({ transformer }) => {
    webGLOverlayView.requestRedraw();

    for (const drone of drones) {
      const matrix = transformer.fromLatLngAltitude(drone);

      camera.projectionMatrix = new THREE.Matrix4().fromArray(matrix);

      renderer.render(scene, camera);
    }

    renderer.resetState();
  };

  webGLOverlayView.setMap(map);
}

onMounted(async () => {
  const map = await initMap();

  function drawRedLineMap(map) {
    const flightPlanCoordinates = [
      { lat: -7.2756967, lng: 112.7761407 }, // galaxy mall
      { lat: -7.2619491, lng: 112.7478422 }, // grand city
      { lat: -7.2627836, lng: 112.745902 },
      { lat: -7.2623683, lng: 112.7362544 },
      { lat: -7.285015, lng: 112.739325 },
      { lat: -7.3138113, lng: 112.7333834 },
      { lat: -7.2930221, lng: 112.7171467 },
      { lat: -7.3070382, lng: 112.6952806 }
    ];

    const flightPath = new google.maps.Polyline({
      path: flightPlanCoordinates,
      geodesic: true,
      strokeColor: "#e11d48",
      strokeOpacity: 1.0,
      strokeWeight: 5
    });

    flightPath.setMap(map);
  }

  function drawBlueLineMap(map) {
    const flightPlanCoordinates = [
      { lat: -7.3161807, lng: 112.7463608 }, // plaza marina
      { lat: -7.315938, lng: 112.784272 }, // kebun bibit wonorejo
      { lat: -7.2770698, lng: 112.8039157 }, // east coast
      { lat: -7.255441, lng: 112.783058 },
      { lat: -7.288537, lng: 112.744931 },
      { lat: -7.2930221, lng: 112.7171467 }
    ];

    const flightPath = new google.maps.Polyline({
      path: flightPlanCoordinates,
      geodesic: true,
      strokeColor: "#2563eb",
      strokeOpacity: 1.0,
      strokeWeight: 5
    });

    flightPath.setMap(map);
  }

  drawRedLineMap(map);
  drawBlueLineMap(map);

  initWebGLOverlayView(map);
});

</script>

<template>
  <AppLayout title="Monitor Control">
    <template #header>
      <h2 class="font-semibold text-xl text-grey-800 leading-tight">
        Air Navigation Monitoring
      </h2>
    </template>

    <div class="pb-12" id="mapContainer">
      <div ref="mapElement" />
    </div>
  </AppLayout>
</template>

<style scoped>
#mapContainer > * {
  height: 800px;
}
</style>
