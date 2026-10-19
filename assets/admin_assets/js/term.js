/*
 * Small helpers for the terms of a batch. A batch runs yearly, by semester or by trimester,
 * and has a program length (for example 4 years, 6 semesters, 12 trimesters).
 * Used by the batch page and by the student pages.
 */

function Sobuj_termName(system) {
    return { yearly: 'Year', semester: 'Semester', trimester: 'Trimester' }[system] || '';
}

function Sobuj_termPlural(system) {
    return { yearly: 'years', semester: 'semesters', trimester: 'trimesters' }[system] || 'terms';
}

// "Semester 4 of 6"
function Sobuj_termLabel(system, number, total) {
    if (!system) {
        return '-';
    }
    return Sobuj_termName(system) + ' ' + number + (total ? ' of ' + total : '');
}
