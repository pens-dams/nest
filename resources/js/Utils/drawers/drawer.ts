import * as THREE from 'three'
import { ThreeJSOverlayView } from '@googlemaps/three'
import { reactive, watch } from 'vue'
import { forEach } from 'lodash'
import { Object3D } from 'three'

export interface Drawable {}

export class Object3DWrapper {
  constructor(public object: THREE.Object3D, public drawable: Drawable) {}
}

abstract class Drawer<T extends Drawable> {
  protected _overlay: ThreeJSOverlayView
  protected scene: THREE.Scene

  protected objects: Object3DWrapper[] = []

  protected data: T[] = reactive([])

  protected constructor() {
    watch(this.data, async () => {
      if (!this.readyCondition()) {
        return
      }

      forEach(this.data, async (drawable: Drawable) => {
        let objectWrapper: Object3DWrapper | undefined = this.objects.find(
          (o) => o.drawable == drawable
        )

        if (typeof objectWrapper == 'undefined') {
          objectWrapper = new Object3DWrapper(
            this.createObject(drawable),
            drawable
          )

          this.scene.add(objectWrapper.object)

          this.objects.push(objectWrapper)
        }

        this.objectMapping(drawable, objectWrapper.object)
      })

      this._overlay.requestRedraw()
    })
  }

  set overlay(overlay: ThreeJSOverlayView) {
    this._overlay = overlay
    this.scene = overlay.scene
  }

  get overlay(): ThreeJSOverlayView {
    return this._overlay
  }

  async addData(data: T): Promise<void> {
    await this.isReady()

    this.data.push(data)
  }

  protected async isReady(): Promise<boolean> {
    return new Promise((resolve, reject) => {
      if (!this.readyCondition()) {
        setTimeout(() => {
          this.isReady().then(resolve).catch(reject)
        }, 100)

        return
      }

      resolve(true)
    })
  }

  protected readyCondition(): boolean {
    return typeof this.scene != 'undefined'
  }

  protected abstract objectMapping(drawable: Drawable, object: Object3D): void

  protected abstract createObject(drawable: Drawable): Object3D
}

export default Drawer
