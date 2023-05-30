import * as THREE from 'three'
import { ThreeJSOverlayView } from '@googlemaps/three'
import { reactive, watch } from 'vue'
import { forEach } from 'lodash'
import { Object3D } from 'three'
import _ from 'lodash'

export interface Drawable {}

export class Object3DWrapper {
  constructor(public object: THREE.Object3D, public drawable: Drawable) {}
}

abstract class Drawer<T extends Drawable> {
  set drawing(value: boolean) {
    this._drawing = value
  }

  protected _overlay: ThreeJSOverlayView
  protected scene: THREE.Scene

  protected objects: Object3DWrapper[] = []

  protected data: T[] = reactive([])

  private _drawing: boolean = false

  protected constructor() {
    watch(this.data, async () => {
      if (!this.readyCondition()) {
        return
      }

      this.triggerRedraw()
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

  /**
   * triggered when state data change, use it to manipulate position, rotation, etc
   * after this method is called, requestRedraw() will be called
   *
   * @param drawable
   * @param object
   * @protected
   */
  protected abstract objectMapping(drawable: Drawable, object: Object3D): void

  /**
   * create an object from drawable
   *
   * @param drawable
   * @protected
   */
  protected abstract createObject(drawable: Drawable): Object3D

  public get triggerRedraw() {
    if (!this._drawing) {
      return () => {}
    }

    return _.debounce(() => {
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
    }, 100)
  }
}

export default Drawer
