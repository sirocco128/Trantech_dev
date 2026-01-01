# Trantech_dev

A transportation technology utility library providing geolocation services and calculations.

## Features

### Geolocation Utils

The `geolocation` module provides comprehensive geographic calculation functions:

- **Distance Calculation**: Calculate distances between coordinates using the Haversine formula
- **Bearing Calculation**: Determine the compass bearing between two points
- **Midpoint Calculation**: Find the geographic midpoint between coordinates
- **Coordinate Validation**: Validate latitude/longitude values
- **Coordinate Formatting**: Format coordinates in decimal or DMS (Degrees, Minutes, Seconds) format
- **Bounds Checking**: Check if a coordinate is within a bounding box

## Installation

```bash
npm install
```

## Running Tests

```bash
# Run all tests
npm test

# Run tests in watch mode
npm run test:watch

# Run tests with coverage
npm run test:coverage
```

## Usage Example

```typescript
import {
  calculateDistance,
  calculateBearing,
  findMidpoint,
  formatCoordinates,
  isWithinBounds
} from './src/utils/geolocation';

// Calculate distance between two cities
const newYork = { latitude: 40.7128, longitude: -74.0060 };
const losAngeles = { latitude: 34.0522, longitude: -118.2437 };

const distance = calculateDistance(newYork, losAngeles);
console.log(`Distance: ${distance.kilometers} km (${distance.miles} miles)`);

// Calculate bearing
const bearing = calculateBearing(newYork, losAngeles);
console.log(`Bearing: ${bearing}°`);

// Find midpoint
const midpoint = findMidpoint(newYork, losAngeles);
console.log(`Midpoint: ${formatCoordinates(midpoint)}`);
```

## Test Coverage

The geolocation utility has comprehensive test coverage including:

- ✓ Coordinate validation (valid/invalid ranges, edge cases, type checking)
- ✓ Distance calculations (known distances, edge cases, poles, date line)
- ✓ Bearing calculations (cardinal directions, 360° normalization)
- ✓ Midpoint calculations (equator crossing, identical points)
- ✓ Coordinate formatting (decimal and DMS formats)
- ✓ Bounds checking (inside/outside, boundaries, corners)

Run `npm run test:coverage` to see detailed coverage reports.