import Drawer from '@/Utils/drawers/drawer'
import DroneDrawer from '@/Utils/drawers/drone-drawer'
import LineDrawer from '@/Utils/drawers/line-drawer'
import { ThreeJSOverlayView, WORLD_SIZE } from '@googlemaps/three'
import { AxesHelper } from 'three'
import DotDrawer from '@/Utils/drawers/dot-drawer'

class ThreeRenderer {
  protected drawers: Drawer<any>[] = []

  constructor(
    private map: google.maps.Map,
    private overlay: ThreeJSOverlayView
  ) {
    this.drawers.push(new DroneDrawer())
    this.drawers.push(new LineDrawer())
    this.drawers.push(new DotDrawer())

    this.drawers.forEach((drawer) => {
      drawer.overlay = this.overlay
    })

    this.overlay.scene.add(new AxesHelper(WORLD_SIZE))
  }

  getDrawer<T extends Drawer<any>>(type: new () => T): T {
    const drawer = this.drawers.find((drawer) => drawer instanceof type)

    if (!drawer) {
      throw new Error(`Drawer ${type.name} not found`)
    }

    return drawer as T
  }

  clear(): void {
    this.drawers.forEach((drawer) => {
      drawer.clear().then((r) => r)
    })
  }

  start(): void {
    this.drawers.forEach((drawer) => {
      drawer.drawing = true
      drawer.triggerRedraw()
    })
  }
}

export default ThreeRenderer
