<template>
  <div class="relative mx-auto w-full max-w-lg">
    <!-- Search Input -->
    <input
      v-model="query"
      type="text"
      placeholder="Search Adobe"
      class="w-full rounded-lg border px-4 py-3 shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500"
      @focus="showDropdown = true"
      @blur="hideDropdown"
    />

    <!-- Dropdown -->
    <transition name="fade">
      <div
        v-if="showDropdown && query.length > 0"
        class="absolute left-0 z-50 mt-2 w-full rounded-xl border bg-white p-4 shadow-lg"
      >
        <!-- Apps & Services -->
        <div v-if="filteredApps.length">
          <h3 class="text-sm font-semibold text-gray-600">Apps & Services</h3>
          <ul>
            <li
              v-for="app in filteredApps"
              :key="app.name"
              class="flex cursor-pointer items-center rounded-lg p-2 hover:bg-gray-100"
            >
              <img :src="app.icon" alt="icon" class="mr-3 h-6 w-6" />
              <div>
                <p class="font-medium text-gray-800">{{ app.name }}</p>
                <p class="text-xs text-gray-500">{{ app.description }}</p>
              </div>
            </li>
          </ul>
        </div>

        <!-- Adobe Express Templates -->
        <div v-if="filteredTemplates.length" class="mt-4">
          <h3 class="text-sm font-semibold text-gray-600">Adobe Express Templates</h3>
          <div class="flex space-x-2 overflow-x-auto">
            <img
              v-for="template in filteredTemplates"
              :key="template.id"
              :src="template.image"
              class="h-16 w-24 cursor-pointer rounded-lg shadow-md"
            />
          </div>
        </div>

        <!-- Quick Actions -->
        <div v-if="quickActions.length" class="mt-4">
          <h3 class="text-sm font-semibold text-gray-600">Quick Actions</h3>
          <div class="mt-2 grid grid-cols-2 gap-3">
            <button
              v-for="action in quickActions"
              :key="action.name"
              class="flex items-center justify-center rounded-lg bg-gray-100 p-3 hover:bg-gray-200"
            >
              <img :src="action.icon" alt="action icon" class="mr-2 h-5 w-5" />
              <span class="text-sm font-medium">{{ action.name }}</span>
            </button>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed } from "vue";

// Reactive state
const query = ref("");
const showDropdown = ref(false);

// Mock Data
const apps = [
  {
    name: "Adobe Express",
    description: "Create video & social content",
    icon: "https://via.placeholder.com/24",
  },
  {
    name: "Photoshop",
    description: "Ideate & create assets",
    icon: "https://via.placeholder.com/24",
  },
  {
    name: "Adobe Fonts",
    description: "Thousands of fonts",
    icon: "https://via.placeholder.com/24",
  },
];

const templates = [
  { id: 1, image: "https://via.placeholder.com/80x50" },
  { id: 2, image: "https://via.placeholder.com/80x50" },
  { id: 3, image: "https://via.placeholder.com/80x50" },
];

const quickActions = [
  { name: "Remove background", icon: "https://via.placeholder.com/20" },
  { name: "Resize image", icon: "https://via.placeholder.com/20" },
  { name: "Convert to GIF", icon: "https://via.placeholder.com/20" },
  { name: "Convert to MP4", icon: "https://via.placeholder.com/20" },
];

// Computed filters
const filteredApps = computed(() => {
  return apps.filter((app) => app.name.toLowerCase().includes(query.value.toLowerCase()));
});

const filteredTemplates = computed(() => {
  return templates.slice(0, 3);
});

// Hide dropdown after delay
const hideDropdown = () => {
  setTimeout(() => {
    showDropdown.value = false;
  }, 200);
};
</script>

<style>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease-in-out;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
