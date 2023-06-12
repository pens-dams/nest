type Point = {
  type: string
  coordinates: number[]
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

interface Path {
  ulid: string
  sequence: number
  position: Point
  altitude: number
  meta: object
}

interface Flight {
  paths?: Path[]
  speed: number
  logs?: Log[]
  planned_altitude: number
  altitude: number | null
  to: Point
  code: string
  ulid: string
  drone?: Drone
  from: Point
  departure: string
  name: string | null
  color: string | null
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
