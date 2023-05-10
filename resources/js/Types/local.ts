import { Drone as DroneModel, Flight } from './laravel'
import { Drawable } from '@/Utils/drawers/drawer'

class Drone implements Drawable {
  constructor(
    public readonly origin: google.maps.LatLngAltitudeLiteral,
    public readonly destination: google.maps.LatLngAltitudeLiteral,
    public readonly drone: DroneModel,
    public current: google.maps.LatLngAltitudeLiteral
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

export { Drone, Line }
