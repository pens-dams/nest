import Drawer, { Drawable } from '@/Utils/drawers/drawer'
import * as THREE from 'three'
import { Object3D } from 'three'
import { GLTF, GLTFLoader } from 'three/examples/jsm/loaders/GLTFLoader'
import { Drone } from '@/Types/local'

class DroneDrawer extends Drawer<Drone> {
  private gltf: GLTF

  constructor() {
    super()

    this.loadDroneModel().then((r) => {
      this.gltf = r
    })
  }

  protected readyCondition(): boolean {
    return super.readyCondition() && typeof this.gltf != 'undefined'
  }

  protected createObject(drawable: Drawable): Object3D {
    if (typeof this.gltf == 'undefined') {
      throw new Error('Drone model not loaded')
    }

    return this.gltf.scene.clone(true)
  }

  protected objectMapping(drawable: Drawable, object: Object3D): void {
    const drone = drawable as Drone

    object.position.copy(
      this.overlay.latLngAltitudeToVector3({
        lat: drone.current.lat,
        lng: drone.current.lng,
      })
    )

    object.position.z = drone.current.altitude
  }

  startAnimation(): void {
    for (const drone of this.data) {
      this.animateDrone(drone)
    }
  }

  protected animateDrone(drone: Drone): void {
    if (drone.destination == null) {
      return
    }

    if (
      drone.current.lat == drone.destination.lat &&
      drone.current.lng == drone.destination.lng
    ) {
      return
    }

    const distance = google.maps.geometry.spherical.computeDistanceBetween(
      new google.maps.LatLng(drone.origin.lat, drone.origin.lng),
      new google.maps.LatLng(drone.destination.lat, drone.destination.lng)
    )

    // km/h
    const time = (distance / drone.speed) * 1000 // ms

    const start = Date.now()

    const animate = () => {
      const now = Date.now()
      const elapsed = now - start

      const progress = Math.min(elapsed / time, 1)

      if (progress != 0) {
        const lat =
          drone.origin.lat +
          (drone.destination.lat - drone.origin.lat) * progress
        const lng =
          drone.origin.lng +
          (drone.destination.lng - drone.origin.lng) * progress

        drone.current = {
          lat,
          lng,
          altitude: drone.current.altitude,
        }
      }

      if (progress < 1) {
        window.requestAnimationFrame(animate)
      }
    }

    animate()
  }

  private async loadDroneModel(): Promise<GLTF> {
    return new Promise((resolve) => {
      const loader = new GLTFLoader(THREE.DefaultLoadingManager)

      loader.load('/files/drone.glb', (gltf) => {
        gltf.scene.scale.set(25, 25, 25)
        gltf.scene.rotation.x = (90 * Math.PI) / 180
        // this.scene.add(gltf.scene)

        console.log('Drone model loaded')
        resolve(gltf)
      })
    })
  }
}

export default DroneDrawer
