import Drawer, { Drawable } from '@/Utils/drawers/drawer'
import { Object3D } from 'three'
import { Line } from '@/Types/local'

class LineDrawer extends Drawer<Line> {
  constructor() {
    super()
  }

  protected createObject(drawable: Drawable): Object3D {
    return undefined
  }

  protected objectMapping(drawable: Drawable, object: Object3D): void {}
}

export default LineDrawer
