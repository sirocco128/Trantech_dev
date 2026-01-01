import {
  Coordinates,
  isValidCoordinates,
  calculateDistance,
  calculateBearing,
  findMidpoint,
  formatCoordinates,
  isWithinBounds
} from './geolocation';

describe('Geolocation Utils', () => {
  describe('isValidCoordinates', () => {
    it('should return true for valid coordinates', () => {
      expect(isValidCoordinates({ latitude: 0, longitude: 0 })).toBe(true);
      expect(isValidCoordinates({ latitude: 45.5, longitude: -122.6 })).toBe(true);
      expect(isValidCoordinates({ latitude: -33.8688, longitude: 151.2093 })).toBe(true);
    });

    it('should return true for boundary values', () => {
      expect(isValidCoordinates({ latitude: 90, longitude: 180 })).toBe(true);
      expect(isValidCoordinates({ latitude: -90, longitude: -180 })).toBe(true);
      expect(isValidCoordinates({ latitude: 90, longitude: -180 })).toBe(true);
      expect(isValidCoordinates({ latitude: -90, longitude: 180 })).toBe(true);
    });

    it('should return false for out-of-range latitude', () => {
      expect(isValidCoordinates({ latitude: 91, longitude: 0 })).toBe(false);
      expect(isValidCoordinates({ latitude: -91, longitude: 0 })).toBe(false);
      expect(isValidCoordinates({ latitude: 100, longitude: 0 })).toBe(false);
    });

    it('should return false for out-of-range longitude', () => {
      expect(isValidCoordinates({ latitude: 0, longitude: 181 })).toBe(false);
      expect(isValidCoordinates({ latitude: 0, longitude: -181 })).toBe(false);
      expect(isValidCoordinates({ latitude: 0, longitude: 200 })).toBe(false);
    });

    it('should return false for null or undefined', () => {
      expect(isValidCoordinates(null as any)).toBe(false);
      expect(isValidCoordinates(undefined as any)).toBe(false);
    });

    it('should return false for invalid types', () => {
      expect(isValidCoordinates({ latitude: 'invalid' as any, longitude: 0 })).toBe(false);
      expect(isValidCoordinates({ latitude: 0, longitude: 'invalid' as any })).toBe(false);
      expect(isValidCoordinates({} as any)).toBe(false);
    });

    it('should return false for NaN values', () => {
      expect(isValidCoordinates({ latitude: NaN, longitude: 0 })).toBe(false);
      expect(isValidCoordinates({ latitude: 0, longitude: NaN })).toBe(false);
      expect(isValidCoordinates({ latitude: NaN, longitude: NaN })).toBe(false);
    });
  });

  describe('calculateDistance', () => {
    it('should return zero distance for the same coordinates', () => {
      const coord = { latitude: 40.7128, longitude: -74.0060 };
      const result = calculateDistance(coord, coord);

      expect(result.kilometers).toBe(0);
      expect(result.miles).toBe(0);
      expect(result.meters).toBe(0);
    });

    it('should calculate distance between New York and Los Angeles', () => {
      const newYork: Coordinates = { latitude: 40.7128, longitude: -74.0060 };
      const losAngeles: Coordinates = { latitude: 34.0522, longitude: -118.2437 };

      const result = calculateDistance(newYork, losAngeles);

      // Expected distance is approximately 3944 km
      expect(result.kilometers).toBeGreaterThan(3900);
      expect(result.kilometers).toBeLessThan(4000);
      expect(result.miles).toBeGreaterThan(2400);
      expect(result.miles).toBeLessThan(2500);
      expect(result.meters).toBeGreaterThan(3900000);
      expect(result.meters).toBeLessThan(4000000);
    });

    it('should calculate distance between London and Paris', () => {
      const london: Coordinates = { latitude: 51.5074, longitude: -0.1278 };
      const paris: Coordinates = { latitude: 48.8566, longitude: 2.3522 };

      const result = calculateDistance(london, paris);

      // Expected distance is approximately 344 km
      expect(result.kilometers).toBeGreaterThan(340);
      expect(result.kilometers).toBeLessThan(350);
    });

    it('should calculate distance between Sydney and Tokyo', () => {
      const sydney: Coordinates = { latitude: -33.8688, longitude: 151.2093 };
      const tokyo: Coordinates = { latitude: 35.6762, longitude: 139.6503 };

      const result = calculateDistance(sydney, tokyo);

      // Expected distance is approximately 7823 km
      expect(result.kilometers).toBeGreaterThan(7800);
      expect(result.kilometers).toBeLessThan(7900);
    });

    it('should handle coordinates near the poles', () => {
      const northPole: Coordinates = { latitude: 89, longitude: 0 };
      const nearNorthPole: Coordinates = { latitude: 88, longitude: 180 };

      const result = calculateDistance(northPole, nearNorthPole);

      expect(result.kilometers).toBeGreaterThan(0);
    });

    it('should handle crossing the date line', () => {
      const fiji: Coordinates = { latitude: -18.1248, longitude: 178.4501 };
      const samoa: Coordinates = { latitude: -13.7590, longitude: -172.1046 };

      const result = calculateDistance(fiji, samoa);

      expect(result.kilometers).toBeGreaterThan(0);
    });

    it('should throw error for invalid first coordinate', () => {
      const valid: Coordinates = { latitude: 0, longitude: 0 };
      const invalid: Coordinates = { latitude: 100, longitude: 0 };

      expect(() => calculateDistance(invalid, valid)).toThrow('Invalid first coordinate');
    });

    it('should throw error for invalid second coordinate', () => {
      const valid: Coordinates = { latitude: 0, longitude: 0 };
      const invalid: Coordinates = { latitude: 0, longitude: 200 };

      expect(() => calculateDistance(valid, invalid)).toThrow('Invalid second coordinate');
    });
  });

  describe('calculateBearing', () => {
    it('should calculate bearing from New York to Los Angeles (west)', () => {
      const newYork: Coordinates = { latitude: 40.7128, longitude: -74.0060 };
      const losAngeles: Coordinates = { latitude: 34.0522, longitude: -118.2437 };

      const bearing = calculateBearing(newYork, losAngeles);

      // Expected bearing is approximately 270° (west) with some south component
      expect(bearing).toBeGreaterThan(250);
      expect(bearing).toBeLessThan(280);
    });

    it('should calculate bearing pointing north', () => {
      const start: Coordinates = { latitude: 0, longitude: 0 };
      const end: Coordinates = { latitude: 10, longitude: 0 };

      const bearing = calculateBearing(start, end);

      // Should be 0° (north)
      expect(bearing).toBeCloseTo(0, 1);
    });

    it('should calculate bearing pointing south', () => {
      const start: Coordinates = { latitude: 10, longitude: 0 };
      const end: Coordinates = { latitude: 0, longitude: 0 };

      const bearing = calculateBearing(start, end);

      // Should be 180° (south)
      expect(bearing).toBeCloseTo(180, 1);
    });

    it('should calculate bearing pointing east', () => {
      const start: Coordinates = { latitude: 0, longitude: 0 };
      const end: Coordinates = { latitude: 0, longitude: 10 };

      const bearing = calculateBearing(start, end);

      // Should be 90° (east)
      expect(bearing).toBeCloseTo(90, 1);
    });

    it('should calculate bearing pointing west', () => {
      const start: Coordinates = { latitude: 0, longitude: 0 };
      const end: Coordinates = { latitude: 0, longitude: -10 };

      const bearing = calculateBearing(start, end);

      // Should be 270° (west)
      expect(bearing).toBeCloseTo(270, 1);
    });

    it('should return a value between 0 and 360', () => {
      const coord1: Coordinates = { latitude: 51.5074, longitude: -0.1278 };
      const coord2: Coordinates = { latitude: -33.8688, longitude: 151.2093 };

      const bearing = calculateBearing(coord1, coord2);

      expect(bearing).toBeGreaterThanOrEqual(0);
      expect(bearing).toBeLessThan(360);
    });

    it('should throw error for invalid first coordinate', () => {
      const valid: Coordinates = { latitude: 0, longitude: 0 };
      const invalid: Coordinates = { latitude: 100, longitude: 0 };

      expect(() => calculateBearing(invalid, valid)).toThrow('Invalid first coordinate');
    });

    it('should throw error for invalid second coordinate', () => {
      const valid: Coordinates = { latitude: 0, longitude: 0 };
      const invalid: Coordinates = { latitude: 0, longitude: 200 };

      expect(() => calculateBearing(valid, invalid)).toThrow('Invalid second coordinate');
    });
  });

  describe('findMidpoint', () => {
    it('should find midpoint between two coordinates on equator', () => {
      const coord1: Coordinates = { latitude: 0, longitude: 0 };
      const coord2: Coordinates = { latitude: 0, longitude: 10 };

      const midpoint = findMidpoint(coord1, coord2);

      expect(midpoint.latitude).toBeCloseTo(0, 4);
      expect(midpoint.longitude).toBeCloseTo(5, 4);
    });

    it('should find midpoint between New York and Los Angeles', () => {
      const newYork: Coordinates = { latitude: 40.7128, longitude: -74.0060 };
      const losAngeles: Coordinates = { latitude: 34.0522, longitude: -118.2437 };

      const midpoint = findMidpoint(newYork, losAngeles);

      // Midpoint should be somewhere in the middle of USA
      expect(midpoint.latitude).toBeGreaterThan(34);
      expect(midpoint.latitude).toBeLessThan(41);
      expect(midpoint.longitude).toBeGreaterThan(-118);
      expect(midpoint.longitude).toBeLessThan(-74);
    });

    it('should find midpoint between London and Paris', () => {
      const london: Coordinates = { latitude: 51.5074, longitude: -0.1278 };
      const paris: Coordinates = { latitude: 48.8566, longitude: 2.3522 };

      const midpoint = findMidpoint(london, paris);

      // Midpoint should be in the English Channel
      expect(midpoint.latitude).toBeGreaterThan(48);
      expect(midpoint.latitude).toBeLessThan(52);
      expect(midpoint.longitude).toBeGreaterThan(-1);
      expect(midpoint.longitude).toBeLessThan(3);
    });

    it('should return same coordinate when both inputs are identical', () => {
      const coord: Coordinates = { latitude: 45.5, longitude: -122.6 };

      const midpoint = findMidpoint(coord, coord);

      expect(midpoint.latitude).toBeCloseTo(45.5, 4);
      expect(midpoint.longitude).toBeCloseTo(-122.6, 4);
    });

    it('should handle coordinates crossing the equator', () => {
      const north: Coordinates = { latitude: 10, longitude: 0 };
      const south: Coordinates = { latitude: -10, longitude: 0 };

      const midpoint = findMidpoint(north, south);

      expect(midpoint.latitude).toBeCloseTo(0, 4);
      expect(midpoint.longitude).toBeCloseTo(0, 4);
    });

    it('should throw error for invalid first coordinate', () => {
      const valid: Coordinates = { latitude: 0, longitude: 0 };
      const invalid: Coordinates = { latitude: 100, longitude: 0 };

      expect(() => findMidpoint(invalid, valid)).toThrow('Invalid first coordinate');
    });

    it('should throw error for invalid second coordinate', () => {
      const valid: Coordinates = { latitude: 0, longitude: 0 };
      const invalid: Coordinates = { latitude: 0, longitude: 200 };

      expect(() => findMidpoint(valid, invalid)).toThrow('Invalid second coordinate');
    });
  });

  describe('formatCoordinates', () => {
    describe('decimal format', () => {
      it('should format coordinates in decimal format', () => {
        const coord: Coordinates = { latitude: 40.7128, longitude: -74.0060 };

        const formatted = formatCoordinates(coord, 'decimal');

        expect(formatted).toBe('40.712800, -74.006000');
      });

      it('should format positive coordinates', () => {
        const coord: Coordinates = { latitude: 51.5074, longitude: 0.1278 };

        const formatted = formatCoordinates(coord, 'decimal');

        expect(formatted).toBe('51.507400, 0.127800');
      });

      it('should format coordinates at equator and prime meridian', () => {
        const coord: Coordinates = { latitude: 0, longitude: 0 };

        const formatted = formatCoordinates(coord, 'decimal');

        expect(formatted).toBe('0.000000, 0.000000');
      });

      it('should use decimal format by default', () => {
        const coord: Coordinates = { latitude: 45.5, longitude: -122.6 };

        const formatted = formatCoordinates(coord);

        expect(formatted).toBe('45.500000, -122.600000');
      });
    });

    describe('DMS format', () => {
      it('should format coordinates in DMS format for positive values', () => {
        const coord: Coordinates = { latitude: 40.7128, longitude: 74.0060 };

        const formatted = formatCoordinates(coord, 'dms');

        expect(formatted).toContain('40°');
        expect(formatted).toContain('42\'');
        expect(formatted).toContain('N');
        expect(formatted).toContain('74°');
        expect(formatted).toContain('E');
      });

      it('should format coordinates in DMS format for negative values', () => {
        const coord: Coordinates = { latitude: -33.8688, longitude: -118.2437 };

        const formatted = formatCoordinates(coord, 'dms');

        expect(formatted).toContain('33°');
        expect(formatted).toContain('S');
        expect(formatted).toContain('118°');
        expect(formatted).toContain('W');
      });

      it('should format coordinates at equator', () => {
        const coord: Coordinates = { latitude: 0, longitude: 0 };

        const formatted = formatCoordinates(coord, 'dms');

        expect(formatted).toContain('0°0\'0"N');
        expect(formatted).toContain('0°0\'0"E');
      });

      it('should format northern and eastern coordinates', () => {
        const coord: Coordinates = { latitude: 51.5074, longitude: 0.1278 };

        const formatted = formatCoordinates(coord, 'dms');

        expect(formatted).toContain('N');
        expect(formatted).toContain('E');
      });

      it('should format southern and western coordinates', () => {
        const coord: Coordinates = { latitude: -33.8688, longitude: -151.2093 };

        const formatted = formatCoordinates(coord, 'dms');

        expect(formatted).toContain('S');
        expect(formatted).toContain('W');
      });
    });

    it('should throw error for invalid coordinates', () => {
      const invalid: Coordinates = { latitude: 100, longitude: 0 };

      expect(() => formatCoordinates(invalid)).toThrow('Invalid coordinates');
    });
  });

  describe('isWithinBounds', () => {
    const northEast: Coordinates = { latitude: 50, longitude: 10 };
    const southWest: Coordinates = { latitude: 40, longitude: 0 };

    it('should return true for coordinate inside bounds', () => {
      const inside: Coordinates = { latitude: 45, longitude: 5 };

      expect(isWithinBounds(inside, northEast, southWest)).toBe(true);
    });

    it('should return true for coordinate on the boundary', () => {
      const onBoundary: Coordinates = { latitude: 50, longitude: 10 };

      expect(isWithinBounds(onBoundary, northEast, southWest)).toBe(true);
    });

    it('should return true for coordinate on south-west corner', () => {
      const corner: Coordinates = { latitude: 40, longitude: 0 };

      expect(isWithinBounds(corner, northEast, southWest)).toBe(true);
    });

    it('should return false for coordinate north of bounds', () => {
      const outside: Coordinates = { latitude: 51, longitude: 5 };

      expect(isWithinBounds(outside, northEast, southWest)).toBe(false);
    });

    it('should return false for coordinate south of bounds', () => {
      const outside: Coordinates = { latitude: 39, longitude: 5 };

      expect(isWithinBounds(outside, northEast, southWest)).toBe(false);
    });

    it('should return false for coordinate east of bounds', () => {
      const outside: Coordinates = { latitude: 45, longitude: 11 };

      expect(isWithinBounds(outside, northEast, southWest)).toBe(false);
    });

    it('should return false for coordinate west of bounds', () => {
      const outside: Coordinates = { latitude: 45, longitude: -1 };

      expect(isWithinBounds(outside, northEast, southWest)).toBe(false);
    });

    it('should handle bounding box crossing equator', () => {
      const ne: Coordinates = { latitude: 10, longitude: 20 };
      const sw: Coordinates = { latitude: -10, longitude: -20 };
      const inside: Coordinates = { latitude: 0, longitude: 0 };

      expect(isWithinBounds(inside, ne, sw)).toBe(true);
    });

    it('should handle bounding box crossing prime meridian', () => {
      const ne: Coordinates = { latitude: 50, longitude: 10 };
      const sw: Coordinates = { latitude: 40, longitude: -10 };
      const inside: Coordinates = { latitude: 45, longitude: 0 };

      expect(isWithinBounds(inside, ne, sw)).toBe(true);
    });

    it('should throw error for invalid coordinate', () => {
      const invalid: Coordinates = { latitude: 100, longitude: 0 };

      expect(() => isWithinBounds(invalid, northEast, southWest)).toThrow('Invalid coordinates');
    });

    it('should throw error for invalid north-east boundary', () => {
      const coord: Coordinates = { latitude: 45, longitude: 5 };
      const invalid: Coordinates = { latitude: 100, longitude: 0 };

      expect(() => isWithinBounds(coord, invalid, southWest)).toThrow('Invalid coordinates');
    });

    it('should throw error for invalid south-west boundary', () => {
      const coord: Coordinates = { latitude: 45, longitude: 5 };
      const invalid: Coordinates = { latitude: 0, longitude: 200 };

      expect(() => isWithinBounds(coord, northEast, invalid)).toThrow('Invalid coordinates');
    });
  });
});
