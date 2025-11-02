<template>
  <Teleport to="body">
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
      <div class="w-full max-w-md rounded-md bg-white p-4 shadow-xl dark:bg-gray-800">
        <div class="mb-4 flex items-center justify-between">
          <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
            {{ $t("Create new list") }}
          </h2>
          <button
            class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
            @click="$emit('close')"
          >
            <font-awesome-icon :icon="['fal', 'xmark']" class="h-5 w-5" />
          </button>
        </div>

        <form @submit.prevent="createList">
          <div class="mb-6">
            <label
              for="list-name"
              class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
            >
              {{ $t("List name") }}
            </label>
            <input
              id="list-name"
              v-model="listName"
              type="text"
              :placeholder="$t('Enter a name for your list')"
              required
              class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 text-sm focus:border-theme-500 focus:outline-none dark:border-gray-700 dark:bg-gray-700 dark:text-gray-200"
            />
          </div>

          <div class="mb-6">
            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
              {{ $t("List color (optional)") }}
            </label>
            <div class="flex flex-wrap gap-2">
              <button
                v-for="color in colorOptions"
                :key="color"
                type="button"
                :class="`h-8 w-8 rounded-full bg-${color}-500 hover:ring-2 hover:ring-${color}-300 ${selectedColor === color ? `ring-2 ring-${color}-600 ring-offset-2` : ''}`"
                @click="selectedColor = color"
              />
              <button
                type="button"
                class="h-8 w-8 rounded-full bg-gray-200 hover:ring-2 hover:ring-gray-300 dark:bg-gray-700"
                :class="[selectedColor === null ? 'ring-2 ring-gray-600 ring-offset-2' : '']"
                @click="selectedColor = null"
              >
                <font-awesome-icon :icon="['fal', 'xmark']" class="h-4 w-4 text-gray-500" />
              </button>
            </div>
          </div>

          <div class="flex justify-end space-x-3">
            <button
              type="button"
              class="rounded-full bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600"
              @click="$emit('close')"
            >
              {{ $t("Cancel") }}
            </button>
            <button
              type="submit"
              :disabled="!listName.trim()"
              class="rounded-full bg-theme-500 px-4 py-2 text-sm font-medium text-white hover:bg-theme-600 disabled:opacity-70 dark:bg-theme-600 dark:hover:bg-theme-700"
            >
              {{ $t("Create list") }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
const emit = defineEmits(["close", "create"]);

const listName = ref("");
const selectedColor = ref(null);

// List of available color options
const colorOptions = [
  "blue",
  "green",
  "red",
  "yellow",
  "purple",
  "pink",
  "indigo",
  "teal",
  "orange",
];

const createList = () => {
  if (!listName.value.trim()) return;

  emit("create", {
    name: listName.value.trim(),
    color: selectedColor.value,
    categories: [],
  });

  // Reset form
  listName.value = "";
  selectedColor.value = null;
};
</script>
