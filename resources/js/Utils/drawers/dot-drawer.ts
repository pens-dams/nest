import Drawer from '@/Utils/drawers/drawer'
import { Point } from '@/Types/local'
import {
  Object3D,
  SphereGeometry,
  Mesh,
  MeshBasicMaterial,
  Vector3,
} from 'three'

class DotDrawer extends Drawer<Point> {
  constructor() {
    super()
  }

  protected createObject(point: Point): Object3D {
    const geometry = new SphereGeometry(point.radius, 32, 32)

    const material = new MeshBasicMaterial({
      color: point.color || 0x000000,
      transparent: true,
      opacity: 0.5,
    })

    const sphere = new Mesh(geometry, material)

    let position: Vector3 = new Vector3(0, 0, 0)

    if (point.position) {
      position = this.overlay.latLngAltitudeToVector3({
        lat: point.position.lat,
        lng: point.position.lng,
        altitude: point.position.altitude,
      })
    } else if (point.coordinate) {
      position.x = point.coordinate.x
      position.y = point.coordinate.y
      position.z = point.coordinate.z
    }

    sphere.position.copy(position)

    return sphere
  }

  protected objectMapping(drawable: Point, object: Object3D): void {}
}

export default DotDrawer
