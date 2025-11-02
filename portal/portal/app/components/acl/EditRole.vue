<template>
  <div>
    <SidePanel>
      <template #side-panel-header>
        <h2 class="p-4 font-bold uppercase tracking-wide text-theme-900">
          <font-awesome-icon :icon="['fal', 'pencil']" class="mr-2 text-sm" /><span
            class="text-gray-500"
            >Edit</span
          >
          {{ role.display_name }}
        </h2>
      </template>

      <template #side-panel-content>
        <div class="p-4">
          <div class="mx-auto my-2 w-1/2">
            <label for="display_name" class="text-sm font-bold uppercase tracking-wide">
              {{ $t("Name") }}
            </label>
            <input
              id="display_name"
              v-model="localRole.display_name"
              name="display_name"
              type="text"
              :placeholder="$t('My new role')"
              class="mb-4 block w-full rounded border bg-white px-2 py-1 text-black focus:border-theme-300 focus:outline-none focus:ring dark:border-gray-900 dark:bg-gray-700 dark:text-white"
            />

            <label for="description" class="text-sm font-bold uppercase tracking-wide">
              {{ $t("Description") }}
            </label>
            <input
              id="description"
              v-model="localRole.description"
              name="description"
              type="text"
              placeholder="description"
              class="mb-4 block w-full rounded border bg-white px-2 py-1 text-black focus:border-theme-300 focus:outline-none focus:ring dark:border-gray-900 dark:bg-gray-700 dark:text-white"
            />

            <button
              class="ml-auto mr-1 flex justify-self-end rounded border border-green-500 px-2 py-1 text-green-500 hover:bg-green-100"
              @click="saveRole"
            >
              {{ $t("Save role") }}
            </button>
          </div>
        </div>
      </template>
    </SidePanel>
  </div>
</template>

<script>
export default {
  name: "EditRole",
  props: {
    role: {
      type: Object,
      required: true,
    },
  },
  emits: ["close", "update-role"],
  data() {
    return {
      localRole: { ...this.role },
    };
  },
  methods: {
    saveRole() {
      this.$emit("update-role", this.localRole);
      this.close();
    },
    close() {
      this.$emit("close");
    },
  },
};
</script>
