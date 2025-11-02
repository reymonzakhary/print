<template>
  <ul :class="`ml-${i * 2}`">
    <li
      v-for="option in box ? box.options : option.children"
      :key="`option_${option.id}`"
      class="flex flex-wrap items-center justify-between w-full p-1 transition cursor-pointer group"
      :class="`hover:bg-gray-${i}00`"
    >
      <div class="flex items-center w-full">
        <div class="flex items-center justify-between w-full">
          <span>
            <font-awesome-icon :icon="['fal', 'apple-whole']" />
            {{ option.name }}
          </span>
          <span class="mr-4 font-mono">
            {{ !option.switch_price ? "+ " : "" }}
            {{ option.display_price }}</span
          >
        </div>
        <button
          class="invisible px-2 py-1 ml-auto mr-1 rounded-full hover:bg-theme-100 group-hover:visible text-theme-500"
          @click="
            set_active_option(option),
              set_edit(true),
              set_type('option'),
              set_add_variation(true)
          "
        >
          <font-awesome-icon :icon="['fal', 'pencil']" />
        </button>
        <button
          class="invisible px-2 py-1 mr-1 text-red-500 rounded-full hover:bg-red-100 group-hover:visible"
          @click="$emit('on-delete-option', option.id)"
        >
          <font-awesome-icon :icon="['fal', 'trash-can']" />
        </button>
      </div>

      <VariationOptionList :option="option" :i="i + 1" class="w-full" />
    </li>
  </ul>
</template>

<script>
import { mapState, mapMutations } from "vuex";

export default {
  props: {
    box: {
      required: false,
      type: Object,
    },
    option: {
      required: false,
      type: Object,
    },
    i: {
      required: true,
      type: Number,
    },
  },
emits: ['on-delete-option'],
  computed: {
    ...mapState({
      add_variation: (state) => state.assortmentsettings.add_variation,
      type: (state) => state.assortmentsettings.type,
      edit: (state) => state.assortmentsettings.edit,
      active_box: (state) => state.assortmentsettings.active_box,
      active_option: (state) => state.assortmentsettings.active_option,
    }),
  },
  methods: {
    ...mapMutations({
      set_active_box: "assortmentsettings/set_active_box",
      set_active_option: "assortmentsettings/set_active_option",
      set_add_variation: "assortmentsettings/set_add_variation",
      set_type: "assortmentsettings/set_type",
      set_edit: "assortmentsettings/set_edit",
    }),
  },
};
</script>

<style></style>
