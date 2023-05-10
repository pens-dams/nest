type Point = {
  type: string
  coordinates: Number[]
}

interface Drone {
  id: number
  name: string
  standby_location: Point
  photo_path: string
  serial_number: string
}

interface Flight {
  altitude: number | null
  to: Point
  code: string
  id: number
  drone?: Drone
  from: Point
  departure: string
}

export type { Flight, Point, Drone }
