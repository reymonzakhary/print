<template>
  <fieldset class="flex flex-col gap-4 p-4 rounded border">
    <legend class="px-3 pb-1 mt-1 text-sm font-bold uppercase">{{ $t('Address Information') }}</legend>

    <div>
      <label
          for="place-autocomplete-input"
          class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase"
      >
        <FontAwesomeIcon :icon="['fal', 'search']" class="mr-2" />
        {{ $t('Search Address') }}:
      </label>
      <input
          id="place-autocomplete-input"
          ref="autocompleteInput"
          type="text"
          :placeholder="$t('Start typing an address...')"
          class="w-full px-3 py-2 border-2 border-blue-500 rounded focus:outline-none focus:border-blue-500"
      />
      <span v-if="getErrorMessage('format_address')" class="text-xs text-red-500">
        {{ getErrorMessage("format_address") }}
      </span>
    </div>

    <div class="grid md:grid-cols-2 gap-6">
      <!-- Left side: Form fields -->
      <div class="space-y-4">
        <!-- Street & Number -->
        <div class="grid gap-4 md:grid-cols-2">
          <div>
            <label
                for="street"
                class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase"
            >
              {{ $t('Street') }}:
            </label>
            <input
                id="street"
                v-model="addressData.street"
                name="street"
                type="text"
                :placeholder="$t('Street name')"
                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                @blur="emit('update:address', addressData)"
            />
            <span v-if="getErrorMessage('street')" class="text-xs text-red-500">
              {{ getErrorMessage("street") }}
            </span>
          </div>

          <div>
            <label
                for="number"
                class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase"
            >
              {{ $t('House Number') }}:
            </label>
            <input
                id="number"
                v-model="addressData.number"
                name="number"
                type="text"
                :placeholder="$t('Building number')"
                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                @blur="emit('update:address', addressData)"
            />
            <span v-if="getErrorMessage('number')" class="text-xs text-red-500">
              {{ getErrorMessage("number") }}
            </span>
          </div>
        </div>

        <!-- Floor & Apartment -->
        <div class="grid gap-4 md:grid-cols-2">
          <div>
            <label
                for="floor"
                class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase"
            >
              {{ $t('House number extra (Floor)') }}:
            </label>
            <input
                id="floor"
                v-model="addressData.floor"
                name="floor"
                type="text"
                :placeholder="$t('Floor number')"
                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                @blur="emit('update:address', addressData)"
            />
            <span v-if="getErrorMessage('floor')" class="text-xs text-red-500">
              {{ getErrorMessage("floor") }}
            </span>
          </div>

          <div>
            <label
                for="apartment"
                class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase"
            >
              {{ $t('Apartment') }}:
            </label>
            <input
                id="apartment"
                v-model="addressData.apartment"
                name="apartment"
                type="text"
                :placeholder="$t('Apartment number')"
                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                @blur="emit('update:address', addressData)"
            />
            <span v-if="getErrorMessage('apartment')" class="text-xs text-red-500">
              {{ getErrorMessage("apartment") }}
            </span>
          </div>
        </div>

        <!-- Neighborhood & City -->
        <div class="grid gap-4 md:grid-cols-2">
          <div>
            <label
                for="neighborhood"
                class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase"
            >
              {{ $t('Neighborhood') }}:
            </label>
            <input
                id="neighborhood"
                v-model="addressData.neighborhood"
                name="neighborhood"
                type="text"
                :placeholder="$t('e.g. Nasr City')"
                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                @blur="emit('update:address', addressData)"
            />
            <span v-if="getErrorMessage('neighborhood')" class="text-xs text-red-500">
              {{ getErrorMessage("neighborhood") }}
            </span>
          </div>

          <div>
            <label
                for="city"
                class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase"
            >
              {{ $t('City') }}:
            </label>
            <input
                id="city"
                v-model="addressData.city"
                name="city"
                type="text"
                :placeholder="$t('e.g. Cairo')"
                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                @blur="emit('update:address', addressData)"
            />
            <span v-if="getErrorMessage('city')" class="text-xs text-red-500">
              {{ getErrorMessage("city") }}
            </span>
          </div>
        </div>

        <!-- Region & ZIP -->
        <div class="grid gap-4 md:grid-cols-2">
          <div>
            <label
                for="region"
                class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase"
            >
              {{ $t('Region/Governorate') }}:
            </label>
            <input
                id="region"
                v-model="addressData.region"
                name="region"
                type="text"
                :placeholder="$t('e.g. Cairo Governorate')"
                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                @blur="emit('update:address', addressData)"
            />
            <span v-if="getErrorMessage('region')" class="text-xs text-red-500">
              {{ getErrorMessage("region") }}
            </span>
          </div>

          <div>
            <label
                for="zip_code"
                class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase"
            >
              {{ $t('ZIP Code') }}:
            </label>
            <input
                id="zip_code"
                v-model="addressData.zip_code"
                name="zip_code"
                type="text"
                :placeholder="$t('ZIP/Postal code')"
                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                @blur="emit('update:address', addressData)"
            />
            <span v-if="getErrorMessage('zip_code')" class="text-xs text-red-500">
              {{ getErrorMessage("zip_code") }}
            </span>
          </div>
        </div>

        <!-- Landmark -->
        <div>
          <label
              for="landmark"
              class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase"
          >
            {{ $t('Landmark') }}:
          </label>
          <input
              id="landmark"
              v-model="addressData.landmark"
              name="landmark"
              type="text"
              :placeholder="$t('e.g. next to City Stars Mall')"
              class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
              @blur="emit('update:address', addressData)"
          />
          <span v-if="getErrorMessage('landmark')" class="text-xs text-red-500">
            {{ getErrorMessage("landmark") }}
          </span>
        </div>

        <!-- Country, Lat/Long -->
        <div class="grid gap-4 md:grid-cols-3">
          <div>
            <label
                for="country"
                class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase"
            >
              {{ $t('Country') }}:
            </label>
            <input
                id="country"
                v-model="addressData.country_name"
                name="country"
                type="text"
                readonly
                class="w-full px-3 py-2 border border-gray-300 rounded bg-gray-50 focus:outline-none"
            />
            <span v-if="getErrorMessage('country_name')" class="text-xs text-red-500">
              {{ getErrorMessage("country_name") }}
            </span>
          </div>

          <div>
            <label
                for="lat"
                class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase"
            >
              {{ $t('Latitude') }}:
            </label>
            <input
                id="lat"
                v-model="addressData.lat"
                name="lat"
                type="text"
                readonly
                class="w-full px-3 py-2 border border-gray-300 rounded bg-gray-50 focus:outline-none"
            />
            <span v-if="getErrorMessage('lat')" class="text-xs text-red-500">
              {{ getErrorMessage("lat") }}
            </span>
          </div>

          <div>
            <label
                for="lng"
                class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase"
            >
              {{ $t('Longitude') }}:
            </label>
            <input
                id="lng"
                v-model="addressData.lng"
                name="lng"
                type="text"
                readonly
                class="w-full px-3 py-2 border border-gray-300 rounded bg-gray-50 focus:outline-none"
            />
            <span v-if="getErrorMessage('lng')" class="text-xs text-red-500">
              {{ getErrorMessage("lng") }}
            </span>
          </div>
        </div>

        <!-- Full formatted address -->
        <div>
          <textarea
              id="format_address"
              v-model="addressData.format_address"
              name="format_address"
              rows="2"
              readonly
              hidden
              class="w-full px-3 py-2 border border-gray-300 rounded bg-gray-50 focus:outline-none"
          />
          <span v-if="getErrorMessage('format_address')" class="text-xs text-red-500">
            {{ getErrorMessage("format_address") }}
          </span>
        </div>
      </div>

      <!-- Right side: Map -->
      <div class="h-full">
        <div>
          <label class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase">
            {{ $t('Location Map') }}:
          </label>
          <div ref="mapContainer" class="h-96 rounded border border-gray-300 overflow-hidden">
            <!-- Map will be inserted here -->
          </div>
          <p class="text-xs text-gray-500 mt-1">
            {{ $t('Click on the map to set location or drag the marker to adjust it') }}
          </p>
        </div>
      </div>
    </div>
  </fieldset>
</template>

<script setup>
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { ref, onMounted, defineProps, defineEmits, onUnmounted, watch } from "vue";

// Define props
const props = defineProps({
  // Google Maps API key
  apiKey: {
    type: String,
    required: true,
  },
  // Allow passing country code from parent component
  countryCode: {
    type: String,
    default: "eg",
  },
  // Language for the Places API (ar or en)
  language: {
    type: String,
    default: "ar",
  },
  // Pass initial values if editing
  initialValue: {
    type: Object,
    default: () => ({}),
  },
  // Pass validation errors from the parent form
  errors: {
    type: Object,
    default: () => ({}),
  },
  // Default map zoom level
  defaultZoom: {
    type: Number,
    default: 6,
  },
  // Whether component is in editing mode
  editing: {
    type: Boolean,
    default: false,
  },
});

// Define emits
const emit = defineEmits(["update:address"]);

// Refs
const autocompleteInput = ref(null);
const mapContainer = ref(null);
let autocomplete = null;
let map = null;
let marker = null;
let geocoder = null;

// Form data with default values exactly matching database schema
const addressData = ref({
  // Use initial values or defaults
  type: props.initialValue?.type || "business",
  format_address: props.initialValue?.format_address || "",
  street: props.initialValue?.address || "",
  number: props.initialValue?.number || "",
  floor: props.initialValue?.floor || "",
  apartment: props.initialValue?.apartment || "",
  neighborhood: props.initialValue?.neighborhood || "",
  city: props.initialValue?.city || "",
  region: props.initialValue?.region || "",
  zip_code: props.initialValue?.zip_code || "",
  landmark: props.initialValue?.landmark || "",
  country_id: props.initialValue?.country_id || null,
  lat: Number(props.initialValue?.lat) || null,
  lng: Number(props.initialValue?.lng) || null,
  country_name: props.initialValue?.country_name || "",
  country_code: props.initialValue?.country_code || "",
  default: props.initialValue?.default !== undefined ? props.initialValue.default : true,
});

// Helper function to get error message for a specific field
const getErrorMessage = (field) => {
  if (!props.errors) return null;
  // Check for nested address errors like 'address.street'
  const addressField = `address.${field}`;
  return props.errors[addressField] || props.errors[field] || null;
};

// Address helper functions (simplified version)
const processAddressComponents = (components, formattedAddress) => {
  const processedAddress = {
    street: "",
    number: "",
    neighborhood: "",
    city: "",
    region: "",
    zip_code: "",
    country_name: "",
    country_code: "",
  };

  components.forEach((component) => {
    const types = component.types;

    if (types.includes("street_number")) {
      processedAddress.number = component.long_name;
    } else if (types.includes("route")) {
      processedAddress.street = component.long_name;
    } else if (types.includes("sublocality") || types.includes("neighborhood")) {
      processedAddress.neighborhood = component.long_name;
    } else if (types.includes("locality") || types.includes("administrative_area_level_2")) {
      processedAddress.city = component.long_name;
    } else if (types.includes("administrative_area_level_1")) {
      processedAddress.region = component.long_name;
    } else if (types.includes("postal_code")) {
      processedAddress.zip_code = component.long_name;
    } else if (types.includes("country")) {
      processedAddress.country_name = component.long_name;
      processedAddress.country_code = component.short_name;
    }
  });

  return { processedAddress };
};

// Initialize Google Maps map
const initMap = () => {
  if (!window.google || !window.google.maps || !mapContainer.value) return;

  // Set default location (Egypt center if no location specified)
  const defaultLocation = {
    lat: addressData.value.lat || 26.8206,
    lng: addressData.value.lng || 30.8025,
  };

  const zoom = props.defaultZoom || (addressData.value.lat && addressData.value.lng ? 15 : 6);

  // Create the map
  map = new google.maps.Map(mapContainer.value, {
    center: defaultLocation,
    zoom: zoom,
    mapTypeControl: false,
    streetViewControl: false,
    fullscreenControl: true,
    zoomControl: true,
  });

  // Initialize geocoder
  geocoder = new google.maps.Geocoder();

  // Add marker if we have coordinates
  if (addressData.value.lat && addressData.value.lng) {
    addMarker({ lat: addressData.value.lat, lng: addressData.value.lng });
  }

  // Add click event listener to the map
  map.addListener("click", (event) => {
    if (!event.latLng) return;

    const location = {
      lat: event.latLng.lat(),
      lng: event.latLng.lng(),
    };

    addMarker(location);
    updateAddressFromCoordinates(location);
  });
};

// Add marker to the map
const addMarker = (location) => {
  if (!map) return;

  // Remove existing marker if any
  if (marker) {
    marker.setMap(null);
  }

  // Create new marker
  marker = new google.maps.Marker({
    position: location,
    map: map,
    draggable: true,
    animation: google.maps.Animation.DROP,
  });

  // Center map on marker
  map.setCenter(location);

  // Ensure zoom is appropriate for viewing a specific location
  if (map.getZoom() < 12) {
    map.setZoom(15);
  }

  // Add drag end event listener
  marker.addListener("dragend", () => {
    if (!marker) return;

    const position = marker.getPosition();
    if (!position) return;

    const location = {
      lat: position.lat(),
      lng: position.lng(),
    };

    updateAddressFromCoordinates(location);
  });
};

// Update map location when coordinates change
const updateMapLocation = () => {
  if (!map || !addressData.value.lat || !addressData.value.lng) return;

  const location = {
    lat: addressData.value.lat,
    lng: addressData.value.lng,
  };

  addMarker(location);
};

// Reverse geocode to get address from coordinates
const updateAddressFromCoordinates = async (location) => {
  if (!geocoder) return;

  try {
    const response = await geocoder.geocode({
      location,
    });

    if (response.results && response.results.length > 0) {
      const place = response.results[0];

      // Update coordinates
      addressData.value.lat = location.lat;
      addressData.value.lng = location.lng;

      // Update formatted address
      addressData.value.format_address = place.formatted_address || "";

      // Reset address fields
      addressData.value.street = "";
      addressData.value.number = "";
      addressData.value.neighborhood = "";
      addressData.value.city = "";
      addressData.value.region = "";
      addressData.value.zip_code = "";
      addressData.value.country_name = "";
      addressData.value.country_code = "";

      // Format components
      const formattedComponents = place.address_components.map((comp) => ({
        types: comp.types,
        long_name: comp.long_name,
        short_name: comp.short_name,
      }));

      // Process with address helper
      const { processedAddress } = processAddressComponents(
        formattedComponents,
        place.formatted_address || "",
      );

      // Apply the processed values directly
      Object.assign(addressData.value, processedAddress);

      // Emit updated address data
      emit("update:address", addressData.value);
    }
  } catch (error) {
    console.error("Error in reverse geocoding:", error);
  }
};

// Handle place selection from autocomplete
const handlePlaceSelect = () => {
  if (!autocomplete) return;

  const place = autocomplete.getPlace();

  if (!place.geometry) {
    console.error("No location data available for the selected place");
    return;
  }

  // Update coordinates
  addressData.value.lat = place.geometry.location.lat();
  addressData.value.lng = place.geometry.location.lng();

  // Update formatted address
  addressData.value.format_address = place.formatted_address || "";

  // Reset address fields
  addressData.value.street = "";
  addressData.value.number = "";
  addressData.value.neighborhood = "";
  addressData.value.city = "";
  addressData.value.region = "";
  addressData.value.zip_code = "";
  addressData.value.country_name = "";
  addressData.value.country_code = "";

  // Process address components
  if (place.address_components) {
    const formattedComponents = place.address_components.map((comp) => ({
      types: comp.types,
      long_name: comp.long_name,
      short_name: comp.short_name,
    }));

    // Process with address helper
    const { processedAddress } = processAddressComponents(
      formattedComponents,
      place.formatted_address || "",
    );

    // Apply the processed values
    Object.assign(addressData.value, processedAddress);
  }

  // Update the map with the new location
  updateMapLocation();

  // Emit the updated address data
  emit("update:address", addressData.value);
};

// Load Google Maps API script
const loadGoogleMapsApi = () => {
  const existingScript = document.getElementById("google-maps-script");

  if (existingScript) {
    // If script already exists, just initialize
    if (window.google && window.google.maps) {
      initAutocomplete();
    } else {
      // Wait for it to load
      setTimeout(loadGoogleMapsApi, 100);
    }
    return;
  }

  if (!props.apiKey) {
    console.error("Google Maps API key is required");
    return;
  }

  // Create script
  const script = document.createElement("script");
  script.id = "google-maps-script";
  script.src = `https://maps.googleapis.com/maps/api/js?key=${props.apiKey}&libraries=places`;
  script.async = true;
  script.defer = true;

  script.onload = () => {
    initAutocomplete();
  };

  script.onerror = () => {
    console.error("Failed to load Google Maps API");
  };

  document.head.appendChild(script);
};

// Initialize Google Maps autocomplete
const initAutocomplete = async () => {
  if (!window.google || !window.google.maps || !autocompleteInput.value) {
    setTimeout(initAutocomplete, 100);
    return;
  }

  try {
    // Create autocomplete instance
    autocomplete = await new google.maps.places.Autocomplete(autocompleteInput.value);

    // Add place selection listener
    autocomplete.addListener("place_changed", handlePlaceSelect);

    // Initialize the map
    initMap();
  } catch (error) {
    console.error("Error initializing autocomplete:", error);
  }
};

// Watch for changes in lat/lng
watch(
  [() => addressData.value.lat, () => addressData.value.lng],
  ([newLat, newLng], [oldLat, oldLng]) => {
    if (newLat !== oldLat || newLng !== oldLng) {
      if (newLat !== null && newLng !== null) {
        updateMapLocation();
      }
    }
  },
);

// Watch for form address changes
watch(
  () => addressData.value.format_address,
  (newValue) => {
    if (newValue && (!addressData.value.lat || !addressData.value.lng)) {
      if (geocoder) {
        geocoder.geocode({ address: newValue }, (results, status) => {
          if (status === "OK" && results && results.length > 0) {
            const location = results[0].geometry.location;
            addressData.value.lat = location.lat();
            addressData.value.lng = location.lng();
            updateMapLocation();
          }
        });
      }
    }
  },
);

// Clean up event listeners on unmount
onUnmounted(() => {
  if (autocomplete) {
    google.maps.event.clearInstanceListeners(autocomplete);
  }

  if (map) {
    google.maps.event.clearInstanceListeners(map);
  }

  if (marker) {
    google.maps.event.clearInstanceListeners(marker);
    marker.setMap(null);
  }
});

// Lifecycle hooks
onMounted(() => {
  loadGoogleMapsApi();
});
</script>
