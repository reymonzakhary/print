<?php

namespace App\Http\Requests\Clients;

use Illuminate\Foundation\Http\FormRequest;

class DeliveryZoneRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // Single zone submission
            'id' => 'sometimes|integer|exists:delivery_zones,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'active' => 'boolean',
            'polygon' => 'required|array|min:3', // At least 3 points for a valid polygon
            'polygon.*.lat' => 'required|numeric|between:-90,90',
            'polygon.*.lng' => 'required|numeric|between:-180,180',

            // Multiple zones submission
            'delivery_zones' => 'sometimes|array|min:1|max:50', // Max 50 delivery_zones per request
            'delivery_zones.*.id' => 'sometimes|integer|exists:delivery_zones,id', // Max 50 delivery_zones per request
            'delivery_zones.*.name' => 'required|string|max:255',
            'delivery_zones.*.description' => 'nullable|string|max:1000',
            'delivery_zones.*.active' => 'boolean',
            'delivery_zones.*.polygon' => 'required|array|min:3',
            'delivery_zones.*.polygon.*.lat' => 'required|numeric|between:-90,90',
            'delivery_zones.*.polygon.*.lng' => 'required|numeric|between:-180,180',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check for duplicate zone names in batch submission
            if ($this->has('delivery_zones')) {
                $names = collect($this->delivery_zones)->pluck('name')->filter();
                $duplicates = $names->duplicates();

                if ($duplicates->isNotEmpty()) {
                    $validator->errors()->add('zones', 'Duplicate zone names found: ' . $duplicates->implode(', '));
                }
            }

            // Validate polygon complexity (warn if too many points)
            $this->validatePolygonComplexity($validator);
        });
    }

    /**
     * Validate polygon complexity and provide warnings
     */
    private function validatePolygonComplexity($validator)
    {
        $maxRecommendedPoints = 1000; // Soft limit for warnings
        $absoluteMaxPoints = 5000; // Hard limit

        $zones = $this->has('delivery_zones') ? $this->delivery_zones : [$this->only(['name', 'description', 'active', 'polygon'])];

        foreach ($zones as $index => $zone) {
            if (!isset($zone['polygon']) || !is_array($zone['polygon'])) {
                continue;
            }

            $pointCount = count($zone['polygon']);
            $zoneName = $zone['name'] ?? "Zone #" . ($index + 1);

            // Hard limit check
            if ($pointCount > $absoluteMaxPoints) {
                $validator->errors()->add(
                    $this->has('delivery_zones') ? "delivery_zones.{$index}.polygon" : 'polygon',
                    "Zone '{$zoneName}' has {$pointCount} points. Maximum allowed is {$absoluteMaxPoints} points."
                );
            }
            // Soft warning (logged but doesn't fail validation)
            elseif ($pointCount > $maxRecommendedPoints) {
                \Log::warning("Large polygon detected", [
                    'zone_name' => $zoneName,
                    'point_count' => $pointCount,
                    'recommended_max' => $maxRecommendedPoints,
                    'message' => 'Consider optimizing for better performance'
                ]);
            }
        }
    }

    /**
     * Get custom validation messages
     */
    public function messages()
    {
        return [
            'polygon.min' => 'A polygon must have at least 3 coordinate points.',
            'polygon.*.lat.between' => 'Latitude must be between -90 and 90 degrees.',
            'polygon.*.lng.between' => 'Longitude must be between -180 and 180 degrees.',
            'delivery_zones.max' => 'Maximum 50 zones can be submitted per request.',
            'delivery_zones.*.polygon.min' => 'Each zone polygon must have at least 3 coordinate points.',
            'delivery_zones.*.polygon.*.lat.between' => 'All latitude values must be between -90 and 90 degrees.',
            'delivery_zones.*.polygon.*.lng.between' => 'All longitude values must be between -180 and 180 degrees.',
        ];
    }

    /**
     * Prepare data for validation
     */
    protected function prepareForValidation()
    {
        // Ensure active defaults to true if not provided
        if ($this->has('zones')) {
            $zones = $this->zones;
            foreach ($zones as &$zone) {
                if (!isset($zone['active'])) {
                    $zone['active'] = true;
                }
            }
            $this->merge(['zones' => $zones]);
        } else {
            if (!$this->has('active')) {
                $this->merge(['active' => true]);
            }
        }
    }

    /**
     * Get validated zones data in consistent format
     */
    public function getValidatedZones(): array
    {
        $validated = $this->validated();

        if (isset($validated['zones'])) {
            return $validated['zones'];
        }

        return [
            [
                'id' => $validated['id'] ?? null,
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'active' => $validated['active'] ?? true,
                'polygon' => $validated['polygon'],
            ]
        ];
    }
}
