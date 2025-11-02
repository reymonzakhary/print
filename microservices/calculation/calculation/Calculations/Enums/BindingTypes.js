/**
 * Enumeration of different binding types for printed materials.
 * Each binding type has a key, inside divider, and outside divider properties.
 * The `find` method can be used to find a binding type by its key (case-insensitive).
 */
const BindingTypes = Object.freeze({
    SADDLE_STITCH: {
        key: 'saddle_stitch',
        inside_divider: 2,
        outside_divider: 2,
        endpapers: false,
        endpapers_calculation: {
            is_sides: false,
            divided_by : 2,
            qty: 2
        }
    },
    PERFECT_BOUND: {
        key: 'perfect_bound',
        inside_divider: 1,
        outside_divider: 2,
        endpapers: true,
        endpapers_calculation: {
            is_sides: false,
            divided_by : 2,
            qty: 2
        }
    },
    CASE_BOUND: {
        key: 'case_bound',
        inside_divider: 2,
        outside_divider: 2,
        endpapers: true,
        endpapers_calculation: {
            is_sides: false,
            divided_by : 2,
            qty: 2
        }
    },
    SPIRAL_BOUND: {
        key: 'spiral_bound',
        inside_divider: 1,
        outside_divider: 4,
        endpapers: true,
        endpapers_calculation: {
            is_sides: false,
            divided_by : 2,
            qty: 2
        }
    },
    WIRE_O: {
        key: 'wire_o',
        inside_divider: 1, // 210 * 1
        outside_divider: 4, // 2/4 = // 210 * 2
        endpapers: true,
        endpapers_calculation: {
            is_sides: false,
            divided_by : 1,
            qty: 2
        }
    },
    COMB_BOUND: {
        key: 'comb_bound',
        inside_divider: 2,
        outside_divider: 2,
        endpapers: true,
        endpapers_calculation: {
            is_sides: false,
            divided_by : 2,
            qty: 2
        }
    },
    SECTION_SEWN: {
        key: 'section_sewn',
        inside_divider: 2,
        outside_divider: 2,
        endpapers: true,
        endpapers_calculation: {
            is_sides: false,
            divided_by : 2,
            qty: 2
        }
    },
    LAY_FLAT: {
        key: 'lay_flat',
        inside_divider: 2,
        outside_divider: 2,
        endpapers: true,
        endpapers_calculation: {
            is_sides: false,
            divided_by : 2,
            qty: 2
        }
    },
    THERMAL_BINDING: {
        key: 'thermal_binding',
        inside_divider: 2,
        outside_divider: 2,
        endpapers: true,
        endpapers_calculation: {
            is_sides: false,
            divided_by : 2,
            qty: 2
        }
    },
    TAPE_BINDING: {
        key: 'tape_binding',
        inside_divider: 2,
        outside_divider: 2,
        endpapers: true,
        endpapers_calculation: {
            is_sides: false,
            divided_by : 2,
            qty: 2
        }
    },
    COPTIC_STITCH: {
        key: 'coptic_stitch',
        inside_divider: 2,
        outside_divider: 2,
        endpapers: true,
        endpapers_calculation: {
            is_sides: false,
            divided_by : 2,
            qty: 2
        }
    },
    STAB_BINDING: {
        key: 'stab_binding',
        inside_divider: 2,
        outside_divider: 2,
        endpapers: true,
        endpapers_calculation: {
            is_sides: false,
            divided_by : 2,
            qty: 2
        }
    },
    PAMPHLET: {
        key: 'pamphlet',
        inside_divider: 2,
        outside_divider: 2,
        endpapers: true,
        endpapers_calculation: {
            is_sides: false,
            divided_by : 2,
            qty: 2
        }
    },
    ACCORDION_FOLD: {
        key: 'accordion_fold',
        inside_divider: 2,
        outside_divider: 2,
        endpapers: true,
        endpapers_calculation: {
            is_sides: false,
            divided_by : 2,
            qty: 2
        }
    },

    find(value) {
        const key = Object.keys(this).find(
            key => this[key].key?.toLowerCase() === value?.toLowerCase()
        );
        return key ? this[key] : {};
    }
});

module.exports = BindingTypes;