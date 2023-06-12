import { Drone as DroneModel, Flight } from './laravel'
import { Drawable } from '@/Utils/drawers/drawer'
import { ulid } from 'ulid'
import { toRaw } from 'vue'

interface Path {
  position: google.maps.LatLngAltitudeLiteral
  coordinate?: {
    x: number
    y: number
    z: number
  }
  seconds?: number
  speed?: number
  datetime?: Date
}

class Drone implements Drawable {
  public id: string
  public current: google.maps.LatLngAltitudeLiteral | null

  constructor(
    public readonly drone: DroneModel,
    public readonly paths: Path[] = []
  ) {
    this.id = ulid()
    if (paths.length > 0) {
      this.current = toRaw(paths[0].position)
    } else {
      this.current = {
        lat: drone.standby_location.coordinates[1],
        lng: drone.standby_location.coordinates[0],
        altitude: 0,
      }
    }
  }
}

class Line implements Drawable {
  constructor(
    public readonly origin: google.maps.LatLngAltitudeLiteral,
    public readonly destination: google.maps.LatLngAltitudeLiteral,
    public readonly flight?: Flight,
    public readonly color?: number | string
  ) {
    this.id = ulid()
  }

  id: string
}

class Point implements Drawable {
  constructor(
    public readonly position: google.maps.LatLngAltitudeLiteral | null = null,
    public readonly radius: number = 0,
    public readonly color?: number,
    public readonly coordinate?: {
      x: number
      y: number
      z: number
    }
  ) {
    this.id = ulid()
  }

  id: string
}

export { Drone, Line, Point }
