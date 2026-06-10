const gridColumns = 4;

const pairRules = [
    ['police-station', 'train-station', { security: 2 }, 'Police Station next to Train Station'],
    ['police-station', 'school', { security: 1, facilities: 1 }, 'Police Station next to School'],
    ['fire-station', 'road', { security: 1, mobility: 1 }, 'Fire Station next to Road'],
    ['park', 'school', { recreation: 2, 'environmental-quality': 1 }, 'Park next to School'],
    ['park', 'sports-park', { recreation: 2 }, 'Park next to Sports Park'],
    ['cinema', 'store', { facilities: 2, recreation: 1 }, 'Cinema next to Store'],
    ['hospital', 'cycling-path', { mobility: 1, 'environmental-quality': 1 }, 'Hospital next to Cycling Path'],
    ['cycling-path', 'park', { 'environmental-quality': 2, recreation: 1 }, 'Cycling Path next to Park'],
    ['cycling-path', 'train-station', { mobility: 2 }, 'Cycling Path next to Train Station'],
    ['road', 'park', { 'environmental-quality': -2, recreation: -1 }, 'Road next to Park'],
    ['petrol-station', 'park', { 'environmental-quality': -3 }, 'Petrol Station next to Park'],
    ['water-purification', 'park', { recreation: -1 }, 'Water Purification next to Park'],
    ['road', 'school', { security: -2, mobility: -1 }, 'Road next to School'],
    ['road', 'hospital', { 'environmental-quality': -1, security: -1 }, 'Road next to Hospital'],
    ['petrol-station', 'school', { security: -2, 'environmental-quality': -2 }, 'Petrol Station next to School'],
];

const sensitiveFacilitySlugs = new Set(['park', 'school', 'hospital']);
const pollutingFacilitySlugs = new Set(['road', 'store', 'petrol-station']);

function orthogonalNeighbours(index, totalCells) {
    const zeroBasedIndex = index - 1;
    const row = Math.floor(zeroBasedIndex / gridColumns);
    const column = zeroBasedIndex % gridColumns;
    const rowCount = Math.ceil(totalCells / gridColumns);
    const neighbours = [];

    if (row > 0) neighbours.push(index - gridColumns);
    if (row < rowCount - 1 && index + gridColumns <= totalCells) {
        neighbours.push(index + gridColumns);
    }
    if (column > 0) neighbours.push(index - 1);
    if (column < gridColumns - 1 && index + 1 <= totalCells) {
        neighbours.push(index + 1);
    }

    return neighbours;
}

function matchingPairRule(firstSlug, secondSlug) {
    return pairRules.filter(([left, right]) => (
        (left === firstSlug && right === secondSlug)
        || (left === secondSlug && right === firstSlug)
    ));
}

function connectedRoadGroups(placements, facilities, totalCells) {
    const roadIndexes = new Set(
        placements
            .filter((placement) => facilities[String(placement.facilityId)]?.slug === 'road')
            .map((placement) => placement.index)
    );
    const visited = new Set();
    const groups = [];

    roadIndexes.forEach((startIndex) => {
        if (visited.has(startIndex)) return;

        const queue = [startIndex];
        const group = [];
        visited.add(startIndex);

        while (queue.length > 0) {
            const index = queue.shift();
            group.push(index);

            orthogonalNeighbours(index, totalCells).forEach((neighbourIndex) => {
                if (!roadIndexes.has(neighbourIndex) || visited.has(neighbourIndex)) return;

                visited.add(neighbourIndex);
                queue.push(neighbourIndex);
            });
        }

        groups.push(group);
    });

    return groups;
}

export function calculateFacilityEffects({
    categories,
    facilities,
    placements,
    totalCells = 12,
}) {
    const categoryBySlug = Object.fromEntries(
        categories.map((category) => [category.slug, category])
    );
    const totals = Object.fromEntries(categories.map((category) => [category.id, 0]));
    const adjustments = [];
    const occurrenceCounts = {};
    const orderedPlacements = [...placements].sort((left, right) => (
        left.placementOrder - right.placementOrder
    ));

    const addAdjustment = (categorySlug, amount, reason, type) => {
        const category = categoryBySlug[categorySlug];
        if (!category || amount === 0) return;

        totals[category.id] += amount;
        adjustments.push({
            categoryId: category.id,
            categoryName: category.name,
            amount,
            reason,
            type,
        });
    };

    orderedPlacements.forEach((placement) => {
        const facility = facilities[String(placement.facilityId)];
        if (!facility) return;

        occurrenceCounts[facility.slug] = (occurrenceCounts[facility.slug] || 0) + 1;
        const occurrence = occurrenceCounts[facility.slug];

        categories.forEach((category) => {
            const baseScore = Number(facility.scores[category.id] ?? 0);
            let appliedScore = baseScore;

            if (baseScore > 0 && occurrence === 2) {
                appliedScore = Math.ceil(baseScore / 2);
            } else if (baseScore > 0 && occurrence > 2) {
                appliedScore = 0;
            }

            totals[category.id] += appliedScore;

            if (appliedScore !== baseScore) {
                adjustments.push({
                    categoryId: category.id,
                    categoryName: category.name,
                    amount: appliedScore - baseScore,
                    reason: occurrence === 2
                        ? `${facility.name} copy 2: positive base effect reduced by half`
                        : `${facility.name} copy ${occurrence}: positive base effect removed`,
                    type: 'duplicate',
                });
            }
        });
    });

    const placementByIndex = new Map(
        placements.map((placement) => [placement.index, placement])
    );

    placements.forEach((placement) => {
        const firstFacility = facilities[String(placement.facilityId)];
        if (!firstFacility) return;

        orthogonalNeighbours(placement.index, totalCells)
            .filter((neighbourIndex) => neighbourIndex > placement.index)
            .forEach((neighbourIndex) => {
                const neighbourPlacement = placementByIndex.get(neighbourIndex);
                const secondFacility = neighbourPlacement
                    ? facilities[String(neighbourPlacement.facilityId)]
                    : null;

                if (!secondFacility) return;

                if (
                    firstFacility.categoryId === secondFacility.categoryId
                    && firstFacility.slug !== secondFacility.slug
                ) {
                    addAdjustment(
                        firstFacility.categorySlug,
                        2,
                        `${firstFacility.name} and ${secondFacility.name}: different functions in the same category`,
                        'same-category'
                    );
                }

                const sensitiveFacility = sensitiveFacilitySlugs.has(firstFacility.slug)
                    ? firstFacility
                    : sensitiveFacilitySlugs.has(secondFacility.slug)
                        ? secondFacility
                        : null;
                const pollutingFacility = pollutingFacilitySlugs.has(firstFacility.slug)
                    ? firstFacility
                    : pollutingFacilitySlugs.has(secondFacility.slug)
                        ? secondFacility
                        : null;

                if (sensitiveFacility && pollutingFacility) {
                    addAdjustment(
                        sensitiveFacility.categorySlug,
                        -2,
                        `${sensitiveFacility.name} affected by neighbouring ${pollutingFacility.name}`,
                        'sensitive-function'
                    );
                }

                matchingPairRule(firstFacility.slug, secondFacility.slug)
                    .forEach(([, , effects, reason]) => {
                        Object.entries(effects).forEach(([categorySlug, amount]) => {
                            addAdjustment(categorySlug, amount, reason, 'specific-neighbour');
                        });
                    });
            });
    });

    connectedRoadGroups(placements, facilities, totalCells)
        .filter((group) => group.length >= 3)
        .forEach((group) => {
            const reason = `${group.length} connected Roads`;
            addAdjustment('recreation', -2, reason, 'road-group');
            addAdjustment('environmental-quality', -2, reason, 'road-group');
        });

    return { totals, adjustments };
}

export { orthogonalNeighbours };
