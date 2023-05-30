import { Drone as DroneModel, Flight } from './laravel'
import { Drawable } from '@/Utils/drawers/drawer'

class Drone implements Drawable {
  constructor(
    public readonly origin: google.maps.LatLngAltitudeLiteral,
    public readonly destination: google.maps.LatLngAltitudeLiteral,
    public readonly drone: DroneModel,
    public current: google.maps.LatLngAltitudeLiteral,
    public speed: number = 10
  ) {}
}

class Line implements Drawable {
  constructor(
    public readonly origin: google.maps.LatLngAltitudeLiteral,
    public readonly destination: google.maps.LatLngAltitudeLiteral,
    public readonly flight: Flight,
    public readonly color?: number
  ) {}
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
  ) {}
}

export { Drone, Line, Point }
