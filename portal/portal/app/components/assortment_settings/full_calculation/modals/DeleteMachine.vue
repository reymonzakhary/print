<template>
  <confirmation-modal @on-close="closeModal">
    <template #modal-header>
      <font-awesome-icon :icon="['fad', 'siren-on']" class="mr-1 text-red-600" />
      <span class="pr-8"> {{ $t("delete machine") }} {{ machine.internalMachine.name }} </span>
    </template>

    <template #modal-body>
      <div>
        <span class="text-red-500">{{ $t("delete machine") }}: </span>
        <span class="font-bold">{{ machine.internalMachine.name }}</span>
      </div>
    </template>

    <template #confirm-button>
      <button
        class="px-4 py-1 mr-2 text-sm text-white transition-colors bg-red-600 rounded-full hover:bg-red-700"
        @click="del(), closeModal()"
      >
        {{ $t("delete") }}
      </button>
    </template>
    <template #cancel-button>
      <button
        class="px-4 py-1 mr-2 text-sm transition-colors bg-gray-100 rounded-full hover:bg-gray-200"
        @click="closeModal()"
      >
        {{ $t("close") }}
      </button>
    </template>
  </confirmation-modal>
</template>

<script>
export default {
  props: {
    machine: {
      type: Object,
      required: true,
    },
  },
  emits: ["onDelete", "onClose"],
  watch: {
    machine(v) {
      return v;
    },
  },
  methods: {
    del() {
      this.$emit("onDelete", this.machine);
    },
    closeModal() {
      this.$emit("onClose");
    },
  },
};
</script>
