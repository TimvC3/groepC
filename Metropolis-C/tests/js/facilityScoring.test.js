import assert from 'node:assert/strict';
import test from 'node:test';

import { calculateFacilityEffects } from '../../resources/js/grid/facilityScoring.js';

const categories = [
    { id: 1, name: 'Security', slug: 'security' },
    { id: 2, name: 'Recreation', slug: 'recreation' },
    { id: 3, name: 'Environmental Quality', slug: 'environmental-quality' },
    { id: 4, name: 'Facilities', slug: 'facilities' },
    { id: 5, name: 'Mobility', slug: 'mobility' },
];

function facility(id, name, slug, categoryId, categorySlug, scores = {}) {
    return {
        id,
        name,
        slug,
        categoryId,
        categorySlug,
        scores: Object.fromEntries(categories.map((category) => [
            category.id,
            scores[category.slug] || 0,
        ])),
    };
}

const facilities = Object.fromEntries([
    facility(1, 'Police Station', 'police-station', 1, 'security', { security: 5 }),
    facility(2, 'Fire Station', 'fire-station', 1, 'security', { security: 4 }),
    facility(3, 'Park', 'park', 2, 'recreation', { recreation: 5, 'environmental-quality': 4 }),
    facility(4, 'School', 'school', 4, 'facilities', { facilities: 5 }),
    facility(5, 'Road', 'road', 5, 'mobility', { mobility: 5 }),
    facility(6, 'Petrol Station', 'petrol-station', 5, 'mobility'),
    facility(7, 'Train Station', 'train-station', 5, 'mobility'),
].map((item) => [String(item.id), item]));

function score(placements) {
    return calculateFacilityEffects({ categories, facilities, placements });
}

test('different adjacent functions in the same category receive +2', () => {
    const result = score([
        { index: 1, facilityId: 1, placementOrder: 1 },
        { index: 2, facilityId: 2, placementOrder: 2 },
    ]);

    assert.equal(result.totals[1], 11);
});

test('diagonal functions do not receive proximity adjustments', () => {
    const result = score([
        { index: 1, facilityId: 1, placementOrder: 1 },
        { index: 6, facilityId: 2, placementOrder: 2 },
    ]);

    assert.equal(result.totals[1], 9);
    assert.equal(result.adjustments.length, 0);
});

test('sensitive and polluting penalties combine with specific rules', () => {
    const result = score([
        { index: 1, facilityId: 3, placementOrder: 1 },
        { index: 2, facilityId: 5, placementOrder: 2 },
    ]);

    assert.equal(result.totals[2], 2);
    assert.equal(result.totals[3], 2);
});

test('second positive copy is halved upwards and later positive copies are removed', () => {
    const result = score([
        { index: 1, facilityId: 1, placementOrder: 1 },
        { index: 5, facilityId: 1, placementOrder: 2 },
        { index: 9, facilityId: 1, placementOrder: 3 },
    ]);

    assert.equal(result.totals[1], 8);
});

test('specific positive neighbour rules apply once per orthogonal pair', () => {
    const result = score([
        { index: 1, facilityId: 1, placementOrder: 1 },
        { index: 2, facilityId: 7, placementOrder: 2 },
    ]);

    assert.equal(result.totals[1], 7);
});

test('petrol station next to school applies general and specific penalties', () => {
    const result = score([
        { index: 1, facilityId: 4, placementOrder: 1 },
        { index: 2, facilityId: 6, placementOrder: 2 },
    ]);

    assert.equal(result.totals[1], -2);
    assert.equal(result.totals[3], -2);
    assert.equal(result.totals[4], 3);
});

test('three connected roads apply the road group penalty once', () => {
    const result = score([
        { index: 1, facilityId: 5, placementOrder: 1 },
        { index: 2, facilityId: 5, placementOrder: 2 },
        { index: 3, facilityId: 5, placementOrder: 3 },
    ]);

    assert.equal(result.totals[2], -2);
    assert.equal(result.totals[3], -2);
    assert.equal(result.totals[5], 8);
});
