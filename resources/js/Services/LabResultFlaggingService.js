export const LabResultFlaggingService = {
    getFlag(service, value, ageInDays, gender) {
        const range = this.findMatchingRange(service.reference_ranges, ageInDays, gender);

        if (!range) {
            return 'No Range';
        }

        if (range.normal_text) {
            return value.toLowerCase().trim() === range.normal_text.toLowerCase().trim() ? 'Normal' : 'Abnormal';
        }

        if (!isNaN(parseFloat(value)) && isFinite(value)) {
            const numericValue = parseFloat(value);
            if (range.range_low !== null && numericValue < range.range_low) {
                return 'Low';
            }
            if (range.range_high !== null && numericValue > range.range_high) {
                return 'High';
            }
        }

        return 'Normal';
    },

    findMatchingRange(ranges, ageInDays, gender) {
        if (!ranges || ranges.length === 0) {
            return null;
        }

        for (const range of ranges) {
            if (range.gender && range.gender !== gender && range.gender !== 'any') {
                continue;
            }
            if (range.age_min !== null && ageInDays < range.age_min) {
                continue;
            }
            if (range.age_max !== null && ageInDays > range.age_max) {
                continue;
            }
            return range;
        }

        return null;
    }
};
