import Drawer, { Drawable } from '@/Utils/drawers/drawer'
import * as THREE from 'three'
import { Object3D } from 'three'
import { GLTF, GLTFLoader } from 'three/examples/jsm/loaders/GLTFLoader'
import { Drone } from '@/Types/local'
import { ReactiveVariable } from 'vue/macros'
import { Tween } from '@tweenjs/tween.js'
import { toRaw } from 'vue'

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

  protected animateDrone(drone: ReactiveVariable<Drone>): void {
    const paths = drone.paths

    const firstPath = paths[0] ?? null

    if (firstPath == null) {
      return
    }

    const pathBefore = firstPath

    let delayInSeconds = 0
    for (const path of paths) {
      if (path == pathBefore) {
        continue
      }

      setTimeout(() => {
        const animation = new Tween(toRaw(pathBefore.position))
        animation.to(toRaw(path.position), (path.seconds ?? 1) * 1000)

        animation.onUpdate((object) => {
          drone.current = {
            lat: object.lat,
            lng: object.lng,
            altitude: object.altitude,
          }
        })

        const animate = () => {
          requestAnimationFrame(animate)

          animation.update()
        }

        animation.start()
        animate()
      }, 1000 * delayInSeconds)

      delayInSeconds += path.seconds ?? 1
    }
  }

  private async loadDroneModel(): Promise<GLTF> {
    return new Promise((resolve) => {
      const loader = new GLTFLoader(THREE.DefaultLoadingManager)

      loader.load('/files/drone.glb', (gltf) => {
        gltf.scene.scale.set(25, 25, 25)
        gltf.scene.rotation.x = (90 * Math.PI) / 180

        console.log('Drone model loaded')
        resolve(gltf)
      })
    })
  }
}

export default DroneDrawer
