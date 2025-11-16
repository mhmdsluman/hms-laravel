export const CbcCalculationService = {
    calculate(results, services) {
        const calculated = {};
        const values = {};
        for (const serviceId in results) {
            const service = services.find(s => s.id == serviceId);
            if (service) {
                values[service.name] = results[serviceId];
            }
        }

        if (values['White Blood Cell Count']) {
            const wbc = parseFloat(values['White Blood Cell Count']);
            if (values['Neutrophils']) {
                calculated['Neutrophils Abs'] = ((parseFloat(values['Neutrophils']) / 100.0) * wbc).toFixed(3);
            }
            if (values['Lymphocytes']) {
                calculated['Lymphocytes Abs'] = ((parseFloat(values['Lymphocytes']) / 100.0) * wbc).toFixed(3);
            }
        }

        const rbc = values['Red Blood Cell Count'];
        const hb = values['Hemoglobin'];
        const hct = values['Hematocrit'];

        if (rbc && hct) {
            calculated['Mean Corpuscular Volume'] = ((parseFloat(hct) * 10.0) / parseFloat(rbc)).toFixed(2);
        }
        if (rbc && hb) {
            calculated['Mean Corpuscular Hemoglobin'] = ((parseFloat(hb) * 10.0) / parseFloat(rbc)).toFixed(2);
        }
        if (hct && hb) {
            calculated['Mean Corpuscular Hemoglobin Concentration'] = ((parseFloat(hb) * 100.0) / parseFloat(hct)).toFixed(2);
        }

        return calculated;
    }
};
