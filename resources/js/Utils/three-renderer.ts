import Drawer from '@/Utils/drawers/drawer'
import DroneDrawer from '@/Utils/drawers/drone-drawer'
import LineDrawer from '@/Utils/drawers/line-drawer'
import { ThreeJSOverlayView, WORLD_SIZE } from '@googlemaps/three'
import { AxesHelper } from 'three'

class ThreeRenderer {
  protected drawers: Drawer<any>[] = []

  constructor(
    private map: google.maps.Map,
    private overlay: ThreeJSOverlayView
  ) {
    this.drawers.push(new DroneDrawer())
    this.drawers.push(new LineDrawer())

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
}

export default ThreeRenderer
