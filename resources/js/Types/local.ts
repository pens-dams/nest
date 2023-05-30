import { Drone as DroneModel, Flight } from './laravel'
import { Drawable } from '@/Utils/drawers/drawer'
import { ulid } from 'ulid'

class Drone implements Drawable {
  constructor(
    public readonly drone: DroneModel,
    public current: google.maps.LatLngAltitudeLiteral,
    public readonly origin: google.maps.LatLngAltitudeLiteral | null = null,
    public readonly destination: google.maps.LatLngAltitudeLiteral | null = null,
    public speed: number = 10
  ) {
    this.id = ulid()
  }

  id: string
}

class Line implements Drawable {
  constructor(
    public readonly origin: google.maps.LatLngAltitudeLiteral,
    public readonly destination: google.maps.LatLngAltitudeLiteral,
    public readonly flight?: Flight,
    public readonly color?: number
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
