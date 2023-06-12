import Drawer from '@/Utils/drawers/drawer'
import { Point } from '@/Types/local'
import { Font, FontLoader } from 'three/examples/jsm/loaders/FontLoader.js'
import { Object3D, Mesh, MeshBasicMaterial, Vector3 } from 'three'
import * as THREE from 'three'
import { TextGeometry } from 'three/examples/jsm/geometries/TextGeometry'

export class TextDrawer extends Drawer<Point> {
  private font: Font

  constructor() {
    super()

    this.loadFont().then((r) => {
      this.font = r
    })
  }

  protected createObject(point: Point): Object3D {
    if (typeof this.font == 'undefined') {
      throw new Error('Font not loaded')
    }

    const geometry = new TextGeometry(point.text ?? '-', {
      font: this.font,
      size: 5,
      height: 1,
    })

    const material = new MeshBasicMaterial({
      color: point.color || 0x000000,
      transparent: true,
      opacity: 0.5,
    })

    const text = new Mesh(geometry, material)

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
      position.z = point.coordinate.z + 10
    }

    text.rotateX(Math.PI / 2)

    text.position.copy(position)

    return text
  }

  protected objectMapping(drawable: Point, object: Object3D): void {}

  private async loadFont(): Promise<Font> {
    return new Promise((resolve) => {
      const loader = new FontLoader(THREE.DefaultLoadingManager)

      loader.load('/files/roboto.json', (font) => {
        console.log('Font loaded')
        resolve(font)
      })
    })
  }
}
