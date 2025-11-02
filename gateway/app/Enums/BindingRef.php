<?php

namespace App\Enums;

use EmreYarligan\EnumConcern\EnumConcern;

enum BindingRef: string
{
    use EnumConcern;

    case SADDLE_STITCH = 'saddle_stitch';
    case PERFECT_BOUND = 'perfect_bound';
    case CASE_BOUND = 'case_bound';
    case SPIRAL_BOUND = 'spiral_bound';
    case WIRE_O = 'wire_o';
    case COMB_BOUND = 'comb_bound';
    case SECTION_SEWN = 'section_sewn';
    case LAY_FLAT = 'lay_flat';
    case THERMAL_BINDING = 'thermal_binding';
    case TAPE_BINDING = 'tape_binding';
    case COPTIC_STITCH = 'coptic_stitch';
    case STAB_BINDING = 'stab_binding';
    case PAMPHLET = 'pamphlet';
    case ACCORDION_FOLD = 'accordion_fold';
    case GLUED = 'glued';  // New case added


    /**
     * Get the label for the binding type based on the current instance.
     *
     * @return string The label for the binding type.
     *
     * @throws \Exception If the binding type is not implemented.
     */
    public function label(): string
    {
        return match ($this) {
            self::SADDLE_STITCH => 'Saddle Stitch Binding',
            self::PERFECT_BOUND => 'Perfect Bound',
            self::CASE_BOUND => 'Case Bound (Hardcover)',
            self::SPIRAL_BOUND => 'Spiral Bound',
            self::WIRE_O => 'Wire-O Binding',
            self::COMB_BOUND => 'Comb Binding',
            self::SECTION_SEWN => 'Section Sewn Binding',
            self::LAY_FLAT => 'Lay-Flat Binding',
            self::THERMAL_BINDING => 'Thermal Binding',
            self::TAPE_BINDING => 'Tape Binding',
            self::COPTIC_STITCH => 'Coptic Stitch Binding',
            self::STAB_BINDING => 'Japanese Stab Binding',
            self::PAMPHLET => 'Pamphlet Binding',
            self::ACCORDION_FOLD => 'Accordion Fold Binding',
            self::GLUED => 'Glued Binding',
            default => throw new \Exception('To be implemented'),
        };
    }
}
