<template>
  <div>
    <section class="grid-cols relative mt-4 grid w-full space-y-4">
      <MachinesSingle
        v-for="(machine, index) in machines"
        :key="`machine_${index}`"
        class="rounded shadow-md shadow-gray-200 dark:shadow-gray-900"
        :machine="machine"
        :index="index"
        :printing-methods="printingMethods"
        :options="machine.type === 'printing' ? colors : finishings"
        @on-edit="emit('onChangeEdit', $event)"
        @on-delete="machineToDelete = $event"
      />

      <MachinesSingleEditable
        v-if="Object.keys(machineToEdit)?.length > 0"
        class="rounded shadow-md shadow-gray-200 dark:shadow-gray-900"
        :machine="machineToEdit"
        :printing-methods="printingMethods"
        :options="machineToEdit.type === 'printing' ? colors : finishings"
        @on-edit-off="emit('onChangeEdit', false)"
        @on-save="emit('onSaveMachine', $event)"
        @on-delete="machineToDelete = $event"
      />
    </section>

    <DeleteMachine
      v-if="Object.keys(machineToDelete).length > 0"
      :machine="machineToDelete"
      @on-delete="(emit('onDeleteMachine', $event), emit('onChangeEdit', false))"
      @on-close="machineToDelete = {}"
    />
  </div>
</template>

<script setup>
import { ref, watch } from "vue";

const props = defineProps({
  machines: {
    type: Array,
    required: true,
  },
  printingMethods: {
    type: Array,
    required: true,
  },
  colors: {
    type: Array,
    required: true,
  },
  finishings: {
    type: Array,
    required: true,
  },
  machineToEdit: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["onSaveMachine", "onDeleteMachine", "onChangeEdit"]);

const machineToDelete = ref({});
// const machineToEdit = ref(false);

watch(
  () => props.machines,
  (v) => {
    // Force component re-render
    return v;
  },
  { deep: true },
);
</script>
