<template>
  <ConfirmationModal @on-close="closeModal">
    <template #modal-header>
      <span class="capitalize">{{ $t("remove") }} {{ item.name }}</span>
    </template>

    <template #modal-body>
      <section class="flex flex-wrap max-w-lg">
        <div class="max-h-screen p-2" style="min-width: 400px">
          {{ $t("This will remove") }}
          <b>{{ item.name }}</b
          >. {{ $t("Are you sure?") }}

          <div
            v-if="item.resources.length > 0"
            class="p-2 mt-2 bg-gray-100 rounded dark:bg-gray-800"
          >
            <h2 class="text-sm font-bold tracking-wide uppercase">
              <font-awesome-icon
                :icon="['fad', 'triangle-exclamation']"
                class="text-base text-orange-500"
              />
              {{ $t("page contains the following resources") }}
            </h2>
            <h3 class="mt-2 ml-6 text-xs font-bold tracking-wide uppercase">
              {{ $t("resources") }}:
            </h3>
            <ul class="ml-6 divide-y">
              <li v-for="resource in item.resources" :key="resource">
                {{ resource.title }}
              </li>
            </ul>
          </div>
        </div>
      </section>
    </template>

    <template #confirm-button>
      <button
        class="px-5 py-1 mr-2 text-sm text-white transition-colors bg-red-500 rounded-full hover:bg-red-700"
        @click="deleteItem(item.id)"
      >
        {{ $t("remove") }}
      </button>
    </template>
  </ConfirmationModal>
</template>

<script>
import { mapActions } from "vuex";
import moment from "moment";

export default {
  name: "ResourceRemoveItem",
  props: {
    item: Object,
  },
  emits: ["on-close"],
  data() {
    return {
      moment: moment,
    };
  },
  methods: {
    ...mapActions({
      get_resource_groups: "resources/get_resource_groups",
      delete_resource_group: "resources/delete_resource_group",
    }),
    closeModal() {
      this.$emit("on-close");
    },
    deleteItem(id) {
      this.delete_resource_group(id).then(() => {
        this.get_resource_groups();
        this.closeModal();
      });
    }
  },
};
</script>
