<template>
  <ConfirmationModal @on-close="closeModal">
    <template #modal-header>
      <p v-if="item" class="capitalize">
        {{ $t("remove") }}: {{ type }}
        {{ $display_name(item.display_name) }}
      </p>
    </template>

    <template #modal-body>
      <section class="flex flex-wrap max-w-lg">
        <div v-if="item" class="max-h-screen p-2" style="min-width: 400px">
          {{ $t("will remove") }}: <b>{{ type }} {{ $display_name(item.display_name) }} </b>.
          <br />
          <br />
          {{ $t("are you sure") }}
          <h3
            v-if="type === 'box'"
            class="mt-4 text-sm font-bold tracking-wide text-gray-500 uppercase"
          >
            {{ $t("contains options") }}
          </h3>
          <div
            v-if="item.ops && item.ops.length > 0"
            class="p-2 overflow-y-auto text-gray-500 bg-gray-100 divide-y rounded dark:bg-gray-800"
            style="max-height: calc(100vh - 48rem)"
          >
            <p v-for="op in item.ops">
              {{ op.name }}
            </p>
          </div>
        </div>
      </section>
    </template>

    <template #cancel-button>
      <button
        class="px-5 py-1 mr-2 text-sm text-gray-500 transition-colors bg-gray-300 rounded-full hover:bg-gray-400"
        @click="$parent.removeItem = false"
      >
        {{ $t("cancel") }}
      </button>
    </template>
    <template #confirm-button>
      <button
        class="px-5 py-1 mr-2 text-sm text-white transition-colors bg-red-500 rounded-full hover:bg-red-700"
        @click="$emit('deleteItem', item, type, box_index, option_index)"
      >
        {{ $t("yes") }}
      </button>
    </template>
  </ConfirmationModal>
</template>

<script>
export default {
  name: "RemoveModal",
  props: {
    item: Object,
    type: String,
    box_index: Number,
    option_index: Number,
  },
  emits: ["deleteItem"],
  computed: {
    // Vuex mappings
  },
  methods: {
    closeModal() {
      this.$parent.removeItem = false;
    },
  },
};
</script>
