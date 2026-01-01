/**
 * Geolocation utility functions for Trantech
 * Provides distance calculations, coordinate validation, and geographic operations
 */

export interface Coordinates {
  latitude: number;
  longitude: number;
}

export interface DistanceResult {
  kilometers: number;
  miles: number;
  meters: number;
}

/**
 * Validates if coordinates are within valid ranges
 * Latitude: -90 to 90, Longitude: -180 to 180
 */
export function isValidCoordinates(coords: Coordinates): boolean {
  if (!coords || typeof coords.latitude !== 'number' || typeof coords.longitude !== 'number') {
    return false;
  }

  if (isNaN(coords.latitude) || isNaN(coords.longitude)) {
    return false;
  }

  return coords.latitude >= -90 &&
         coords.latitude <= 90 &&
         coords.longitude >= -180 &&
         coords.longitude <= 180;
}

/**
 * Converts degrees to radians
 */
function toRadians(degrees: number): number {
  return degrees * (Math.PI / 180);
}

/**
 * Converts radians to degrees
 */
function toDegrees(radians: number): number {
  return radians * (180 / Math.PI);
}

/**
 * Calculates the distance between two coordinates using the Haversine formula
 * Returns distance in kilometers, miles, and meters
 */
export function calculateDistance(
  coord1: Coordinates,
  coord2: Coordinates
): DistanceResult {
  if (!isValidCoordinates(coord1)) {
    throw new Error('Invalid first coordinate');
  }

  if (!isValidCoordinates(coord2)) {
    throw new Error('Invalid second coordinate');
  }

  const R = 6371; // Earth's radius in kilometers

  const lat1Rad = toRadians(coord1.latitude);
  const lat2Rad = toRadians(coord2.latitude);
  const deltaLat = toRadians(coord2.latitude - coord1.latitude);
  const deltaLon = toRadians(coord2.longitude - coord1.longitude);

  const a = Math.sin(deltaLat / 2) * Math.sin(deltaLat / 2) +
            Math.cos(lat1Rad) * Math.cos(lat2Rad) *
            Math.sin(deltaLon / 2) * Math.sin(deltaLon / 2);

  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
  const kilometers = R * c;

  return {
    kilometers: Math.round(kilometers * 1000) / 1000,
    miles: Math.round(kilometers * 0.621371 * 1000) / 1000,
    meters: Math.round(kilometers * 1000)
  };
}

/**
 * Calculates the bearing (direction) from one coordinate to another
 * Returns bearing in degrees (0-360) where 0 is North
 */
export function calculateBearing(
  coord1: Coordinates,
  coord2: Coordinates
): number {
  if (!isValidCoordinates(coord1)) {
    throw new Error('Invalid first coordinate');
  }

  if (!isValidCoordinates(coord2)) {
    throw new Error('Invalid second coordinate');
  }

  const lat1Rad = toRadians(coord1.latitude);
  const lat2Rad = toRadians(coord2.latitude);
  const deltaLon = toRadians(coord2.longitude - coord1.longitude);

  const y = Math.sin(deltaLon) * Math.cos(lat2Rad);
  const x = Math.cos(lat1Rad) * Math.sin(lat2Rad) -
            Math.sin(lat1Rad) * Math.cos(lat2Rad) * Math.cos(deltaLon);

  let bearing = toDegrees(Math.atan2(y, x));

  // Normalize to 0-360
  bearing = (bearing + 360) % 360;

  return Math.round(bearing * 100) / 100;
}

/**
 * Finds the midpoint between two coordinates
 */
export function findMidpoint(
  coord1: Coordinates,
  coord2: Coordinates
): Coordinates {
  if (!isValidCoordinates(coord1)) {
    throw new Error('Invalid first coordinate');
  }

  if (!isValidCoordinates(coord2)) {
    throw new Error('Invalid second coordinate');
  }

  const lat1Rad = toRadians(coord1.latitude);
  const lon1Rad = toRadians(coord1.longitude);
  const lat2Rad = toRadians(coord2.latitude);
  const deltaLon = toRadians(coord2.longitude - coord1.longitude);

  const bx = Math.cos(lat2Rad) * Math.cos(deltaLon);
  const by = Math.cos(lat2Rad) * Math.sin(deltaLon);

  const lat3Rad = Math.atan2(
    Math.sin(lat1Rad) + Math.sin(lat2Rad),
    Math.sqrt((Math.cos(lat1Rad) + bx) * (Math.cos(lat1Rad) + bx) + by * by)
  );

  const lon3Rad = lon1Rad + Math.atan2(by, Math.cos(lat1Rad) + bx);

  return {
    latitude: Math.round(toDegrees(lat3Rad) * 1000000) / 1000000,
    longitude: Math.round(toDegrees(lon3Rad) * 1000000) / 1000000
  };
}

/**
 * Formats coordinates to a human-readable string
 */
export function formatCoordinates(
  coords: Coordinates,
  format: 'decimal' | 'dms' = 'decimal'
): string {
  if (!isValidCoordinates(coords)) {
    throw new Error('Invalid coordinates');
  }

  if (format === 'decimal') {
    return `${coords.latitude.toFixed(6)}, ${coords.longitude.toFixed(6)}`;
  }

  // DMS format (Degrees, Minutes, Seconds)
  const latDir = coords.latitude >= 0 ? 'N' : 'S';
  const lonDir = coords.longitude >= 0 ? 'E' : 'W';

  const latAbs = Math.abs(coords.latitude);
  const lonAbs = Math.abs(coords.longitude);

  const latDeg = Math.floor(latAbs);
  const latMin = Math.floor((latAbs - latDeg) * 60);
  const latSec = Math.round(((latAbs - latDeg) * 60 - latMin) * 60 * 100) / 100;

  const lonDeg = Math.floor(lonAbs);
  const lonMin = Math.floor((lonAbs - lonDeg) * 60);
  const lonSec = Math.round(((lonAbs - lonDeg) * 60 - lonMin) * 60 * 100) / 100;

  return `${latDeg}°${latMin}'${latSec}"${latDir} ${lonDeg}°${lonMin}'${lonSec}"${lonDir}`;
}

/**
 * Checks if a coordinate is within a bounding box
 */
export function isWithinBounds(
  coord: Coordinates,
  northEast: Coordinates,
  southWest: Coordinates
): boolean {
  if (!isValidCoordinates(coord) || !isValidCoordinates(northEast) || !isValidCoordinates(southWest)) {
    throw new Error('Invalid coordinates');
  }

  return coord.latitude <= northEast.latitude &&
         coord.latitude >= southWest.latitude &&
         coord.longitude <= northEast.longitude &&
         coord.longitude >= southWest.longitude;
}
