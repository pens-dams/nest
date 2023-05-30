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

interface Log {
  ulid: string
  position: Point
  altitude: number
  speed: number
  datetime: string
  meta: object
}

interface Flight {
  speed: number
  logs: Log[]
  planned_altitude: number
  altitude: number | null
  to: Point
  code: string
  id: number
  drone?: Drone
  from: Point
  departure: string
}

interface Intersect {
  ulid: string
  intersect: Point
  altitude: number
  radius: number
  collision_time: string
  meta: object
}

export type { Flight, Point, Drone, Log, Intersect }
