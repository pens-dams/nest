import Drawer, { Drawable } from '@/Utils/drawers/drawer'
import { Object3D } from 'three'
import { Line } from '@/Types/local'
import * as THREE from 'three'
import _ from 'lodash'

class LineDrawer extends Drawer<Line> {
  constructor() {
    super()
  }

  protected createObject(drawable: Drawable): Object3D {
    const line = drawable as Line

    const origin = this.overlay.latLngAltitudeToVector3({
      lat: line.origin.lat,
      lng: line.origin.lng,
      altitude: line.origin.altitude,
    })

    const destination = this.overlay.latLngAltitudeToVector3({
      lat: line.destination.lat,
      lng: line.destination.lng,
      altitude: line.destination.altitude,
    })

    // create line from cube
    const geometry = new THREE.CylinderGeometry(
      1,
      1,
      origin.distanceTo(destination),
      8
    )

    const material = new THREE.MeshBasicMaterial({
      color: line.color ?? _.sample(THREE.Color.NAMES),
    })
    const lineObject = new THREE.Mesh(geometry, material)

    // set position
    lineObject.position.copy(origin)
    lineObject.position.lerp(destination, 0.5)

    // set rotation
    lineObject.lookAt(destination)
    lineObject.rotateX(Math.PI / 2)

    return lineObject
  }

  protected objectMapping(drawable: Drawable, object: Object3D): void {}
}

export default LineDrawer
