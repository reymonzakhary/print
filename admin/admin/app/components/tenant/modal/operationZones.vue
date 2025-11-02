<script setup>
import { ref, onMounted, watch, nextTick } from "vue";

// Define props
const props = defineProps({
  countryCode: String,
  language: String,
  initialZones: Array,
  errors: Object,
});

// Define emits
const emit = defineEmits(["update:zones"]);

// Refs and state
const mapContainer = ref(null);
const autocompleteInput = ref(null);
let autocomplete = null;
const geocoder = null;
const searchInputContainer = ref(null);
const zones = ref(props.initialZones || []);
const originalPolygon = ref(null);
const isLoading = ref(false);
const isSearching = ref(false);
const isMapInitialized = ref(false);
const isSearchInitialized = ref(false);
const scriptLoaded = ref(false);
const editingPolygonIndex = ref(null);
const showingPoints = ref({});

// Map objects
let map = null;
let polygons = [];
let markers = [];
let polyMarkers = [];
const placeAutocompleteElement = null;

// Default center based on country code (default to Cairo, Egypt)
const defaultMapCenter =
  props.countryCode?.toLowerCase() === "eg"
    ? { lat: 30.0444, lng: 31.2357 } // Cairo, Egypt
    : { lat: 0, lng: 0 }; // Default fallback

// HELPER FUNCTIONS

// Get error message for a specific field
const getErrorMessage = (field) => {
  if (!props.errors) return null;
  const zonesField = `delivery_zones.${field}`;
  return props.errors[zonesField] || props.errors[field] || null;
};

// Check if Google Maps is available
const isGoogleMapsAvailable = () => {
  return (
    typeof window !== "undefined" &&
    typeof window.google !== "undefined" &&
    typeof window.google.maps !== "undefined"
  );
};

// Get color for a polygon based on index
const getPolygonColor = (index) => {
  // Array of different colors for polygons
  const colors = [
    "#4338CA", // Indigo
    "#EF4444", // Red
    "#10B981", // Green
    "#F59E0B", // Amber
    "#6366F1", // Blue
    "#8B5CF6", // Purple
    "#EC4899", // Pink
    "#F97316", // Orange
    "#14B8A6", // Teal
    "#06B6D4", // Cyan
  ];

  return colors[index % colors.length];
};

// INITIALIZATION FUNCTIONS

// Load Google Maps API script
const loadGoogleMapsScript = () => {
  // If script is already loading or loaded, wait for it
  if (document.getElementById("google-maps-script")) {
    const checkGoogleInterval = setInterval(() => {
      if (isGoogleMapsAvailable()) {
        clearInterval(checkGoogleInterval);
        scriptLoaded.value = true;
        nextTick(() => {
          initMap();
          initPlaceSearch();
        });
      }
    }, 100);
    return;
  }

  // Get API key from page props
  const apiKey = "AIzaSyBOp0kmoa4L7L1pMxHs5C9ScnX7xS75J9c";
  if (!apiKey) {
    console.error("Google Maps API key not found");
    return;
  }

  // Create script element
  const script = document.createElement("script");
  script.id = "google-maps-script";
  script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}&libraries=places,marker&v=weekly`;
  script.async = true;
  script.defer = true;

  // Add event listeners
  script.addEventListener("load", () => {
    scriptLoaded.value = true;

    // Initialize with a slight delay to ensure script is fully loaded
    setTimeout(() => {
      initMap();
      initPlaceSearch();
    }, 200);
  });

  script.addEventListener("error", (e) => {
    console.error("Error loading Google Maps API:", e);
  });

  // Append to document
  document.head.appendChild(script);
};

// Suppress Google Maps warnings
const suppressGoogleMapsWarnings = () => {
  const originalWarn = console.warn;
  console.warn = function (message, ...args) {
    if (
      typeof message === "string" &&
      (message.includes("google.maps.Marker is deprecated") ||
        message.includes("Please use google.maps.marker.AdvancedMarkerElement"))
    ) {
      return; // Suppress this warning
    }
    return originalWarn.apply(console, [message, ...args]);
  };
};

// Initialize Google Maps
const initMap = async () => {
  if (!mapContainer.value || isMapInitialized.value || !isGoogleMapsAvailable()) return;

  try {
    isLoading.value = true;

    // Create the map
    map = new window.google.maps.Map(mapContainer.value, {
      center: defaultMapCenter,
      zoom: 8,
      mapTypeId: window.google.maps.MapTypeId.ROADMAP,
      streetViewControl: false,
      mapTypeControl: false,
    });

    isMapInitialized.value = true;

    // Show existing zones if available
    if (zones.value.length > 0) {
      displayExistingZones();
    }

    // Set initial bounds based on country code
    // setInitialBounds();
  } catch (error) {
    console.error("Error initializing map:", error);
  } finally {
    isLoading.value = false;
  }
};

// Initialize place search
const initPlaceSearch = async () => {
  try {
    if (!autocompleteInput.value || !isGoogleMapsAvailable()) return;

    // Create autocomplete instance
    autocomplete = new google.maps.places.Autocomplete(autocompleteInput.value, {
      fields: ["name", "formatted_address", "geometry", "place_id"],
      types: ["establishment", "geocode"],
    });

    // Add place selection listener with debouncing
    autocomplete.addListener("place_changed", handlePlaceSelectDebounced);

    // Add input event listeners to prevent duplicate submissions
    autocompleteInput.value.addEventListener("keydown", handleInputKeyDown);
    autocompleteInput.value.addEventListener("keyup", handleInputKeyUp);

    isSearchInitialized.value = true;
    console.log("Place search initialized successfully");
  } catch (error) {
    console.error("Error initializing autocomplete:", error);
  }
};

// Handle input keydown events (prevents Enter key issues)
const handleInputKeyDown = (event) => {
  if (event.key === "Enter") {
    event.preventDefault(); // Prevent form submission

    // Check if there's a visible autocomplete dropdown
    const pacContainer = document.querySelector(".pac-container");
    if (pacContainer && pacContainer.style.display !== "none") {
      // If dropdown is visible, let autocomplete handle the selection
      return;
    }

    // If no dropdown, trigger search manually
    handleManualSearch();
  }
};

// Handle input keyup events
const handleInputKeyUp = (event) => {
  // Clear any pending selection timeout on new input
  if (selectionTimeout) {
    clearTimeout(selectionTimeout);
    selectionTimeout = null;
  }
};

// Debounced place selection handler
const handlePlaceSelectDebounced = () => {
  // Clear any existing timeout
  if (selectionTimeout) {
    clearTimeout(selectionTimeout);
  }

  // Set a small delay to prevent rapid-fire selections
  selectionTimeout = setTimeout(() => {
    handlePlaceSelect();
  }, 150);
};

// Handle manual search when user presses Enter without selecting from dropdown
const handleManualSearch = async () => {
  const searchText = autocompleteInput.value?.value?.trim();

  if (!searchText) {
    return;
  }

  try {
    // Prevent duplicate processing
    if (isProcessingSelection.value) {
      console.log("Already processing a selection, skipping manual search...");
      return;
    }

    isProcessingSelection.value = true;
    console.log(`Manual search for: ${searchText}`);

    // Check if already added
    const existingZone = zones.value.find((z) => z.name.toLowerCase() === searchText.toLowerCase());
    if (existingZone) {
      alert(`${searchText} is already in your operation zones`);
      clearSearchInput();
      return;
    }

    // Process the search
    isSearching.value = true;
    await fetchOsmBoundary(searchText, "");

    // Clear input after successful processing
    clearSearchInput();
  } catch (error) {
    console.error("Error in manual search:", error);
    isSearching.value = false;
    alert("An error occurred while searching. Please try again.");
  } finally {
    isProcessingSelection.value = false;
  }
};

// Clear and reset search input
const clearSearchInput = () => {
  if (autocompleteInput.value) {
    autocompleteInput.value.value = "";
    autocompleteInput.value.blur(); // Remove focus

    // Clear any pending timeouts
    if (selectionTimeout) {
      clearTimeout(selectionTimeout);
      selectionTimeout = null;
    }
  }
};

// MAP INTERACTION FUNCTIONS

// Handle place selection

const handlePlaceSelect = async () => {
  try {
    // Prevent multiple simultaneous selections
    if (isProcessingSelection.value) {
      console.log("Already processing a selection, skipping...");
      return;
    }

    // Get the place details from autocomplete
    const place = autocomplete.getPlace();

    if (!place || !place.name) {
      console.log("No place selected or place has no name");
      return;
    }

    // Check if this is the same place we just processed
    if (
      lastSelectedPlace &&
      lastSelectedPlace.place_id === place.place_id &&
      Date.now() - lastSelectedPlace.timestamp < 3000
    ) {
      console.log("Duplicate selection detected, ignoring");
      return;
    }

    // Mark as processing
    isProcessingSelection.value = true;
    lastSelectedPlace = {
      place_id: place.place_id,
      name: place.name,
      timestamp: Date.now(),
    };

    console.log(`Processing selection: ${place.name}`);

    // Check if already added
    const existingZone = zones.value.find((z) => z.name === place.name);
    if (existingZone) {
      alert(`${place.name} is already in your operation zones`);
      clearSearchInput();
      return;
    }

    // Get OpenStreetMap boundary
    isSearching.value = true;
    await fetchOsmBoundary(place.name, place.formatted_address || "");

    // Clear input after successful processing
    clearSearchInput();
  } catch (error) {
    console.error("Error handling place selection:", error);
    isSearching.value = false;
    alert("An error occurred while processing your selection. Please try again.");
  } finally {
    // Always reset processing flag
    isProcessingSelection.value = false;
  }
};

// Fetch boundary from OpenStreetMap Nominatim API
const fetchOsmBoundary = async (placeName, placeAddress) => {
  try {
    // Double-check we're not already processing this exact same place
    const currentTime = Date.now();
    if (
      lastSelectedPlace &&
      lastSelectedPlace.name === placeName &&
      currentTime - lastSelectedPlace.timestamp < 3000
    ) {
      console.log("Duplicate boundary fetch detected, skipping");
      return;
    }

    // Update last processed
    lastSelectedPlace = {
      ...lastSelectedPlace,
      name: placeName,
      timestamp: currentTime,
    };

    isSearching.value = true;

    // Use geocoder to get approximate location
    const geocoder = new window.google.maps.Geocoder();

    return new Promise((resolve, reject) => {
      geocoder.geocode({ address: placeName }, async (results, status) => {
        if (status === "OK" && results && results.length > 0) {
          const center = {
            lat: results[0].geometry.location.lat(),
            lng: results[0].geometry.location.lng(),
          };

          // Zoom to location
          map.setCenter(center);
          map.setZoom(10);

          try {
            // Format query for OSM Nominatim with better parameters
            let searchQuery = placeName;

            // Special handling for Netherlands to avoid getting Aruba
            if (
              placeName.toLowerCase().includes("netherlands") ||
              placeName.toLowerCase() === "nederland"
            ) {
              searchQuery = "Netherlands Europe"; // Add Europe to disambiguate
            }

            const encodedQuery = encodeURIComponent(searchQuery);

            // Build URL with additional parameters for better results
            const url =
              `https://nominatim.openstreetmap.org/search?` +
              `q=${encodedQuery}&` +
              `format=json&` +
              `polygon_geojson=1&` +
              `limit=10&` + // Get more results to filter
              `addressdetails=1&` + // Get address details for filtering
              `accept-language=${props.language || "en"}&` + // Use language preference
              `bounded=1&` + // Prefer results within the viewbox
              `viewbox=-180,-90,180,90`; // Global viewbox but will be filtered

            const response = await fetch(url, {
              headers: {
                Accept: "application/json",
                "User-Agent": "DeliveryZoneComponent/1.0", // Nominatim requires a User-Agent
              },
            });

            const data = await response.json();

            // Filter and find the best matching result
            const bestResult = findBestMatchingResult(data, placeName, center);

            // Process GeoJSON if available
            if (bestResult && bestResult.geojson) {
              const polygonPaths = processGeoJsonToPolygon(
                bestResult.geojson,
                results[0].geometry,
                center,
              );

              // Create a zone with the polygon paths if we have any
              if (polygonPaths.length > 0) {
                addNewZone(placeName, placeAddress, polygonPaths);
              } else {
                // Create fallback polygon if no valid points found
                createFallbackPolygon(placeName, placeAddress, center);
              }
            } else {
              // No geojson data found, create fallback
              createFallbackPolygon(placeName, placeAddress, center);
            }

            resolve();
          } catch (error) {
            console.error("Error fetching OSM boundary:", error);
            isSearching.value = false;
            createFallbackPolygon(placeName, placeAddress, center);
            resolve(); // Don't reject, we have fallback
          }
        } else {
          console.error("Geocode was not successful:", status);
          isSearching.value = false;
          reject(new Error(`Couldn't find location: ${placeName}`));
        }
      });
    });
  } catch (error) {
    console.error("Error fetching boundary:", error);
    isSearching.value = false;
    throw error;
  }
};

// Enhanced result matching function
const findBestMatchingResult = (results, searchTerm, googleCenter) => {
  if (!results || results.length === 0) return null;

  // If only one result, return it
  if (results.length === 1) return results[0];

  // Enhanced scoring function for better boundary selection
  const scoreResult = (result) => {
    let score = 0;

    // 1. Check if the display name contains the search term (case insensitive)
    const displayName = (result.display_name || "").toLowerCase();
    const searchLower = searchTerm.toLowerCase();

    if (displayName.includes(searchLower)) {
      score += 15; // Increased weight for name matching
    }

    // 2. Prefer results with higher importance
    if (result.importance) {
      score += result.importance * 8; // Increased weight
    }

    // 3. Special handling for Netherlands vs Caribbean territories
    if (searchLower.includes("netherlands") || searchLower.includes("nederland")) {
      // Heavy bonus for European Netherlands
      if (displayName.includes("europe") || displayName.includes("european")) {
        score += 100;
      }
      // Heavy penalty for Caribbean territories
      if (
        displayName.includes("aruba") ||
        displayName.includes("caribbean") ||
        displayName.includes("curaçao") ||
        displayName.includes("curacao") ||
        displayName.includes("sint maarten") ||
        displayName.includes("bonaire")
      ) {
        score -= 200;
      }
      // Check coordinates - Netherlands proper is around 52°N, 5°E
      if (result.lat && result.lon) {
        const lat = parseFloat(result.lat);
        const lon = parseFloat(result.lon);
        // Netherlands proper coordinates
        if (lat > 50 && lat < 54 && lon > 3 && lon < 8) {
          score += 50;
        }
        // Caribbean coordinates (heavy penalty)
        if (lat > 10 && lat < 15 && lon > -75 && lon < -65) {
          score -= 100;
        }
      }
    }

    // 4. Check place type - strongly prefer administrative boundaries
    const placeType = result.type || "";
    const placeClass = result.class || "";
    const adminLevel = result.admin_level;

    // Strongly prefer administrative boundaries
    if (placeClass === "boundary" && placeType === "administrative") {
      score += 25;

      // Admin level scoring (lower levels are typically more detailed)
      if (adminLevel) {
        const level = parseInt(adminLevel);
        if (level <= 2)
          score += 20; // Country level
        else if (level <= 4)
          score += 15; // State/province level
        else if (level <= 6) score += 10; // Region level
      }
    } else if (placeType === "administrative") {
      score += 15;
    } else if (["city", "town", "village"].includes(placeType)) {
      score += 8;
    } else if (["country", "state", "province"].includes(placeType)) {
      score += 12;
    }

    // 5. Heavily favor results with detailed polygon data
    if (result.geojson) {
      score += 10;

      // Check polygon complexity (more points = more detailed)
      if (
        result.geojson.type === "Polygon" &&
        result.geojson.coordinates &&
        result.geojson.coordinates[0]
      ) {
        const pointCount = result.geojson.coordinates[0].length;
        if (pointCount > 50)
          score += 15; // Very detailed
        else if (pointCount > 20)
          score += 10; // Moderately detailed
        else if (pointCount > 4) score += 5; // Basic polygon
      } else if (result.geojson.type === "MultiPolygon" && result.geojson.coordinates) {
        score += 12; // MultiPolygon usually means more complex/accurate boundaries

        // Calculate total points across all polygons
        let totalPoints = 0;
        result.geojson.coordinates.forEach((polygon) => {
          if (polygon[0]) totalPoints += polygon[0].length;
        });

        if (totalPoints > 100) score += 20;
        else if (totalPoints > 50) score += 15;
        else if (totalPoints > 20) score += 10;
      }
    } else {
      // Penalty for results without polygon data
      score -= 5;
    }

    // 6. Distance from Google's geocoded center (if available)
    if (result.lat && result.lon && googleCenter) {
      const distance = Math.sqrt(
        Math.pow(parseFloat(result.lat) - googleCenter.lat, 2) +
          Math.pow(parseFloat(result.lon) - googleCenter.lng, 2),
      );

      // Penalize results that are very far from Google's result
      if (distance > 10) {
        score -= 10;
      } else if (distance < 1) {
        score += 5; // Bonus for close matches
      }
    }

    // 7. Prefer results with bounding box information
    if (result.boundingbox && result.boundingbox.length === 4) {
      score += 3;

      // Calculate bounding box area as indicator of detail level
      const bbox = result.boundingbox.map((coord) => parseFloat(coord));
      const area = Math.abs(bbox[1] - bbox[0]) * Math.abs(bbox[3] - bbox[2]);

      // Reasonable area bonus (not too small, not too large)
      if (area > 0.01 && area < 100) {
        score += 5;
      }
    }

    // 8. Language preference bonus
    if (props.language && result.namedetails) {
      const langKey = `name:${props.language}`;
      if (result.namedetails[langKey]) {
        score += 3;
      }
    }

    // 9. Penalty for very generic or unclear results
    if (displayName.includes("unnamed") || displayName.includes("no name")) {
      score -= 10;
    }

    return score;
  };

  // Score all results and sort by score
  const scoredResults = results
    .map((result) => ({
      ...result,
      score: scoreResult(result),
    }))
    .sort((a, b) => b.score - a.score);

  // Enhanced logging for debugging
  console.log(
    "Enhanced Nominatim results scoring:",
    scoredResults.slice(0, 5).map((r) => ({
      display_name: r.display_name,
      type: r.type,
      class: r.class,
      admin_level: r.admin_level,
      lat: r.lat,
      lon: r.lon,
      has_geojson: !!r.geojson,
      geojson_type: r.geojson?.type,
      polygon_points:
        r.geojson?.type === "Polygon"
          ? r.geojson.coordinates?.[0]?.length
          : r.geojson?.type === "MultiPolygon"
            ? r.geojson.coordinates?.length
            : 0,
      score: r.score,
    })),
  );

  // Return the best result
  return scoredResults[0];
};

// Process GeoJSON data to polygon coordinates
const processGeoJsonToPolygon = (geojson, geometry, center) => {
  let polygonPaths = [];

  // Process GeoJSON based on its type
  if (geojson.type === "Polygon" && geojson.coordinates && geojson.coordinates[0]) {
    // Single polygon - take the outer ring
    polygonPaths = geojson.coordinates[0].map((coord) => {
      return { lng: coord[0], lat: coord[1] };
    });
  } else if (
    geojson.type === "MultiPolygon" &&
    geojson.coordinates &&
    geojson.coordinates[0] &&
    geojson.coordinates[0][0]
  ) {
    // Multi-polygon - take the first polygon's outer ring
    polygonPaths = geojson.coordinates[0][0].map((coord) => {
      return { lng: coord[0], lat: coord[1] };
    });
  } else if (geojson.type === "Point" && geojson.coordinates) {
    // If we only have a point, create a polygon based on bounds

    // See if we have a bounding box from nominatim
    if (geojson.boundingbox) {
      const box = geojson.boundingbox;
      // boundingbox is [south, north, west, east]
      const south = parseFloat(box[0]);
      const north = parseFloat(box[1]);
      const west = parseFloat(box[2]);
      const east = parseFloat(box[3]);

      // Create polygon from bounding box
      polygonPaths = [
        { lat: south, lng: west },
        { lat: south, lng: east },
        { lat: north, lng: east },
        { lat: north, lng: west },
      ];
    } else if (geometry && geometry.viewport) {
      // Use the geocoding result bounds if available
      const viewport = geometry.viewport;
      const ne = viewport.getNorthEast();
      const sw = viewport.getSouthWest();

      polygonPaths = [
        { lat: sw.lat(), lng: sw.lng() },
        { lat: sw.lat(), lng: ne.lng() },
        { lat: ne.lat(), lng: ne.lng() },
        { lat: ne.lat(), lng: sw.lng() },
      ];
    } else {
      // Last resort - create a rectangular area around the center
      const offset = 0.02; // ~2km offset
      polygonPaths = [
        { lat: center.lat - offset, lng: center.lng - offset },
        { lat: center.lat - offset, lng: center.lng + offset },
        { lat: center.lat + offset, lng: center.lng + offset },
        { lat: center.lat + offset, lng: center.lng - offset },
      ];
    }
  }

  return polygonPaths;
};

// Create a fallback polygon when no boundary is found
const createFallbackPolygon = (placeName, placeAddress, center) => {
  // Create a rectangular polygon around center point
  const offset = 0.02; // Roughly 2km offset
  const polygonPaths = [
    { lat: center.lat - offset, lng: center.lng - offset },
    { lat: center.lat - offset, lng: center.lng + offset },
    { lat: center.lat + offset, lng: center.lng + offset },
    { lat: center.lat + offset, lng: center.lng - offset },
  ];

  addNewZone(placeName, `${placeAddress} (approximate boundary)`, polygonPaths);
};

// Add these variables at the top of your script, after other refs
const isProcessingSelection = ref(false);
let lastSelectedPlace = null;
let selectionTimeout = null;

// Add a new zone
const addNewZone = (name, description, polygonPaths) => {
  // Final check before adding - ensure no duplicates
  const existingZone = zones.value.find((z) => z.name === name);
  if (existingZone) {
    console.log(`Zone ${name} already exists, skipping addition`);
    return;
  }

  const newZone = {
    name,
    description: description + (polygonPaths.length <= 4 ? " (approximate boundary)" : ""),
    active: true,
    polygon: polygonPaths,
  };

  // Add to zones list
  zones.value.push(newZone);

  // Draw polygon on map
  addPolygonForZone(newZone, zones.value.length - 1);

  // Emit update
  emit("update:zones", zones.value);

  console.log(`Successfully added zone "${name}" with ${polygonPaths.length} points`);
};
// Add cleanup function for component unmounting
const cleanup = () => {
  if (selectionTimeout) {
    clearTimeout(selectionTimeout);
    selectionTimeout = null;
  }

  isProcessingSelection.value = false;
  lastSelectedPlace = null;

  // Remove event listeners
  if (autocompleteInput.value) {
    autocompleteInput.value.removeEventListener("keydown", handleInputKeyDown);
    autocompleteInput.value.removeEventListener("keyup", handleInputKeyUp);
  }
};

// Add to your onUnmounted lifecycle hook (or create it if it doesn't exist)
onUnmounted(() => {
  cleanup();
});
// MARKER AND POLYGON MANAGEMENT

// Add polygon for a zone
const addPolygonForZone = (zone, index) => {
  if (!map || !zone.polygon || zone.polygon.length === 0 || !isGoogleMapsAvailable()) return;

  // Get color for this polygon
  const polygonColor = getPolygonColor(index);
  const isEditing = editingPolygonIndex.value === index;

  // Create polygon
  const polygon = new window.google.maps.Polygon({
    paths: zone.polygon,
    strokeColor: isEditing ? "#FF4500" : polygonColor,
    strokeOpacity: 0.8,
    strokeWeight: 2,
    fillColor: polygonColor,
    fillOpacity: zone.active ? 0.35 : 0.1,
    map: map,
    editable: isEditing, // Only editable if being edited
    draggable: false, // We don't allow dragging the whole polygon
  });

  // Store polygon reference
  polygons[index] = polygon;

  // Calculate bounds and center
  const bounds = new window.google.maps.LatLngBounds();
  zone.polygon.forEach((path) => {
    bounds.extend(new window.google.maps.LatLng(path.lat, path.lng));
  });
  const center = bounds.getCenter();

  // Create marker at center of polygon
  const marker = addMarker(center, zone.name);
  if (!marker) return;

  // Create info window
  const infoWindow = new window.google.maps.InfoWindow({
    content: `
      <div style="padding: 10px; max-width: 200px;">
        <div style="font-weight: bold; font-size: 14px; margin-bottom: 4px; color: ${polygonColor};">
          ${zone.name}
        </div>
        <div style="font-size: 12px; color: #666;">
          ${zone.description || ""}
        </div>
        <div style="font-size: 11px; color: #888; margin-top: 4px;">
          ${zone.polygon.length} boundary points
        </div>
      </div>
    `,
  });

  // Add click listener to marker
  marker.addListener("click", () => {
    infoWindow.open(map, marker);
  });

  // Add click listener to polygon
  polygon.addListener("click", () => {
    infoWindow.open(map, marker);
  });

  // Add event listeners for polygon editing if in edit mode
  if (isEditing) {
    // Add listeners for vertex changes
    const path = polygon.getPath();

    // When vertices change, update the polygon data
    const updateFunc = () => updatePolygonPath(index);

    polygon.addListener("mouseup", updateFunc);
    google.maps.event.addListener(path, "set_at", updateFunc);
    google.maps.event.addListener(path, "insert_at", updateFunc);
    google.maps.event.addListener(path, "remove_at", updateFunc);
  }

  // Fit map to polygon
  map.fitBounds(bounds);
  isSearching.value = false;
};

// Update polygon path data after editing
const updatePolygonPath = (index) => {
  if (!polygons[index]) return;

  const polygon = polygons[index];
  const path = polygon.getPath();
  const points = [];

  // Convert path to our polygon format
  for (let i = 0; i < path.getLength(); i++) {
    const point = path.getAt(i);
    points.push({
      lat: Number(point.lat()),
      lng: Number(point.lng()),
    });
  }

  // Update data model directly
  if (zones.value[index] && zones.value[index].polygon) {
    zones.value[index].polygon = points;
    emit("update:zones", zones.value);
  }
};

// Add a marker
const addMarker = (position, title) => {
  if (!isGoogleMapsAvailable()) return null;

  try {
    // Just use standard marker for simplicity and reliability
    const marker = new window.google.maps.Marker({
      position,
      map,
      title,
    });

    markers.push(marker);
    return marker;
  } catch (error) {
    console.error("Failed to create marker:", error);
    return null;
  }
};

// Clear all markers
const clearMarkers = () => {
  markers.forEach((marker) => {
    if (marker && marker.setMap) {
      marker.setMap(null);
    }
  });
  markers = [];
};

// Clear polygon point markers
const clearPolyMarkers = () => {
  for (let i = 0; i < polyMarkers.length; i++) {
    const marker = polyMarkers[i];
    if (marker && marker.setMap) {
      marker.setMap(null);
    }
  }
  polyMarkers = [];
};

// Display existing zones
const displayExistingZones = () => {
  if (!isGoogleMapsAvailable()) return;

  // Clear existing polygons
  polygons.forEach((polygon) => {
    if (polygon) polygon.setMap(null);
  });
  polygons = [];

  // Clear existing markers
  clearMarkers();
  clearPolyMarkers();

  // Add polygons for each zone
  zones.value.forEach((zone, index) => {
    if (zone.polygon && zone.polygon.length > 0) {
      addPolygonForZone(zone, index);
    } else if (zone.name) {
      // If zone doesn't have polygon data yet, fetch it
      fetchOsmBoundary(zone.name, zone.description || "");
    }
  });
};

// ZONE MANAGEMENT FUNCTIONS

// Center map on selected zone
const centerOnZone = (index) => {
  if (
    !map ||
    !zones.value[index] ||
    !zones.value[index].polygon ||
    zones.value[index].polygon.length === 0
  )
    return;

  // Create bounds object from polygon points
  const bounds = new google.maps.LatLngBounds();

  // Add all points to bounds
  zones.value[index].polygon.forEach((point) => {
    bounds.extend(new google.maps.LatLng(point.lat, point.lng));
  });

  // Center and zoom map to show the entire polygon
  map.fitBounds(bounds);

  // Highlight the polygon briefly to indicate selection
  if (polygons[index]) {
    try {
      // Get current options
      const originalOptions = {
        strokeWeight: polygons[index].get("strokeWeight") || 2,
        strokeColor: polygons[index].get("strokeColor") || getPolygonColor(index),
      };

      // Highlight effect
      polygons[index].setOptions({
        strokeWeight: 3,
        strokeColor: "#FFD700", // Gold color for highlight
      });

      // Restore after brief highlight
      setTimeout(() => {
        if (polygons[index]) {
          polygons[index].setOptions({
            strokeWeight: originalOptions.strokeWeight,
            strokeColor: originalOptions.strokeColor,
          });
        }
      }, 1500);
    } catch (error) {
      console.warn("Unable to highlight polygon:", error);
    }
  }

  // Clear search input
  clearSearchInput();
};

// Toggle showing polygon coordinates
const togglePointsDisplay = (index) => {
  // Create a new object to ensure reactivity
  const newState = { ...showingPoints.value };
  newState[index] = !newState[index];
  showingPoints.value = newState;
};

// Toggle zone active state
const toggleZoneActive = (index) => {
  if (zones.value[index]) {
    zones.value[index].active = !zones.value[index].active;

    // Update polygon opacity
    if (polygons[index]) {
      polygons[index].setOptions({
        fillOpacity: zones.value[index].active ? 0.35 : 0.1,
      });
    }

    // Emit update
    emit("update:zones", zones.value);
  }
};

// Delete a zone
const deleteZone = (index) => {
  if (confirm("Are you sure you want to delete this zone?")) {
    // First make sure we're not in edit mode for this zone
    if (editingPolygonIndex.value === index) {
      editingPolygonIndex.value = null;
    }

    // Remove polygon from map
    if (polygons[index]) {
      polygons[index].setMap(null);
    }

    // Remove from zones array (preserving reactivity)
    const newZones = [...zones.value];
    newZones.splice(index, 1);
    zones.value = newZones;

    // Create new polygons array
    const newPolygons = [];
    for (let i = 0; i < polygons.length; i++) {
      if (i !== index && polygons[i]) {
        newPolygons.push(polygons[i]);
      }
    }
    polygons = newPolygons;

    // Emit update
    emit("update:zones", zones.value);

    // Refresh display
    nextTick(() => {
      clearPolyMarkers(); // Make sure all markers are cleared
      displayExistingZones();
    });
  }
};

// Export polygon data as JSON
const getPolygonJson = (index) => {
  if (!zones.value[index] || !zones.value[index].polygon) return "";
  return JSON.stringify(zones.value[index].polygon, null, 2);
};

// LIFECYCLE HOOKS AND WATCHERS

onMounted(() => {
  // Suppress Google Maps warnings
  suppressGoogleMapsWarnings();

  // Load Google Maps
  loadGoogleMapsScript();

  // Set up watchers for GoogleMaps availability
  const initInterval = setInterval(() => {
    if (scriptLoaded.value) {
      if (!isMapInitialized.value) {
        initMap();
      }
      if (!isSearchInitialized.value) {
        initPlaceSearch();
      }

      // Stop checking if both are initialized
      if (isMapInitialized.value && isSearchInitialized.value) {
        clearInterval(initInterval);
      }
    }
  }, 200);
});

// Watch for changes to initialZones prop
watch(
  () => props.initialZones,
  (newZones) => {
    if (newZones) {
      zones.value = [...newZones];

      // Update polygons if map is initialized
      if (isMapInitialized.value && map) {
        displayExistingZones();
      }
    }
  },
  { deep: true },
);

// Watch for script loaded state
watch(
  () => scriptLoaded.value,
  (loaded) => {
    if (loaded) {
      initMap();
      initPlaceSearch();
    }
  },
);
</script>
<template>
  <div class="mt-5">
    <!-- Loading State -->
    <div v-if="isLoading" class="flex justify-center items-center p-4">
      <UILoader />
      <span>Loading map...</span>
    </div>

    <!-- Search Loading State -->
    <div
      v-if="isSearching"
      class="fixed inset-0 bg-black bg-opacity-30 flex justify-center items-center z-50"
    >
      <div class="bg-white p-4 rounded-lg shadow-lg flex flex-col items-center">
        <UILoader />
        <span>Fetching boundary data...</span>
      </div>
    </div>

    <!-- Map Container -->
    <div>
      <div ref="mapContainer" class="w-full h-96 rounded-lg mb-4" />

      <!-- Search Input -->
      <div class="mb-4">
        <label
          for="place-autocomplete-input"
          class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase"
        >
          Search Location:
        </label>
        <input
          id="place-autocomplete-input"
          ref="autocompleteInput"
          type="text"
          placeholder="Start typing an address..."
          class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
        />
      </div>

      <!-- Zone Listing -->
      <h3 class="text-lg font-medium mb-2">Delivery Zones</h3>

      <div v-if="zones.length === 0" class="p-4 border rounded-md text-center text-gray-500">
        No delivery zones defined yet. Search for a location above to add one.
      </div>

      <div v-else class="space-y-4">
        <div v-for="(zone, index) in zones" :key="index" class="border rounded-md overflow-hidden">
          <!-- Zone Header -->
          <div class="p-3 bg-gray-50 flex items-center justify-between">
            <div>
              <h4 class="font-medium">{{ zone.name }}</h4>
              <p class="text-sm text-gray-500">{{ zone.description }}</p>
              <p class="text-xs text-gray-400">
                {{ zone.polygon ? zone.polygon.length : 0 }} boundary points
              </p>
            </div>
            <div class="flex items-center gap-2">
              <!-- Center Map Button -->
              <button
                class="text-sm text-white flex justify-center bg-green-500 rounded py-1 px-2"
                @click="centerOnZone(index)"
              >
                <font-awesome-icon :icon="['fas', 'arrows-to-circle']" />
              </button>

              <!-- Toggle Points Display Button -->
              <button
                class="text-sm text-white flex justify-center bg-green-500 rounded py-1 px-2"
                @click="togglePointsDisplay(index)"
              >
                <font-awesome-icon :icon="['fal', showingPoints[index] ? 'eye-slash' : 'eye']" />
              </button>

              <!-- Toggle Active State Button -->
              <div
                class="relative mx-2 w-10 h-4 rounded-full transition duration-200 ease-linear cursor-pointer"
                :class="[zone.active ? 'bg-theme-400' : 'bg-gray-300']"
              >
                <label
                  :for="`toggle-zone-${zone.name}`"
                  class="absolute left-0 mb-2 w-4 h-4 bg-white rounded-full border-2 transition duration-100 ease-linear transform cursor-pointer"
                  :class="[
                    zone.active
                      ? 'translate-x-6 border-theme-500'
                      : 'translate-x-0 border-gray-300',
                  ]"
                />
                <input
                  :id="`toggle-zone-${zone.name}`"
                  v-model="zone.active"
                  type="checkbox"
                  :name="`toggle-zone-${zone.name}`"
                  class="w-full h-full appearance-none active:outline-none focus:outline-none"
                  @click="toggleZoneActive(index)"
                />
              </div>

              <!-- Delete Button -->
              <button
                class="text-sm text-white flex justify-center bg-red-500 rounded py-1 px-2 underline"
                @click="deleteZone(index)"
              >
                <font-awesome-icon aria-hidden="true" :icon="['fal', 'trash-can']" />
              </button>
            </div>
          </div>

          <!-- Polygon coordinates viewer -->
          <div
            v-if="zone.polygon && zone.polygon.length > 0 && showingPoints[index]"
            class="border-t p-3"
          >
            <div class="flex justify-between items-center mb-2">
              <h5 class="font-medium text-sm">Polygon Coordinates</h5>
              <button
                class="text-xs text-red-500 underline"
                @click="() => navigator.clipboard.writeText(getPolygonJson(index))"
              >
                Copy JSON
              </button>
            </div>

            <div class="max-h-48 overflow-y-auto">
              <table class="w-full text-xs">
                <thead>
                  <tr class="bg-gray-100">
                    <th class="p-1 text-left font-medium">Point</th>
                    <th class="p-1 text-left font-medium">Latitude</th>
                    <th class="p-1 text-left font-medium">Longitude</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="(point, pointIndex) in zone.polygon"
                    :key="pointIndex"
                    class="border-t border-gray-100"
                  >
                    <td class="p-1">{{ pointIndex + 1 }}</td>
                    <td class="p-1">{{ point.lat.toFixed(6) }}</td>
                    <td class="p-1">{{ point.lng.toFixed(6) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
