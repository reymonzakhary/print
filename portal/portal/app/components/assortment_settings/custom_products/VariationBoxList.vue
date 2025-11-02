<template>
  <ul class="p-2 divide-y">
    <li
      v-for="box in boxes"
      :key="`box_${box.id}`"
      class="flex flex-wrap items-center justify-between p-1 transition cursor-pointer group"
      :class="`hover:bg-gray-${i}00`"
    >
      <span>
        <font-awesome-icon :icon="['fal', 'crate-apple']" />
        {{ box.name }}

        <span class="ml-2 text-sm">
          <font-awesome-icon
            v-tooltip="
              $t('Multi select') +
              ': ' +
              (box.input_type === 'multiple' ? 'true' : 'false')
            "
            :icon="['fal', 'list-check']"
            :class="
              box.input_type === 'multiple' ? 'text-theme-500' : 'text-gray-300'
            "
          />
          <font-awesome-icon
            v-tooltip="
              $t('Incremental') +
              ': ' +
              (box.incremental === true ? 'true' : 'false')
            "
            :icon="['fal', 'layer-plus']"
            :class="
              box.incremental === true ? 'text-theme-500' : 'text-gray-300'
            "
          />
          <font-awesome-icon
            v-tooltip="
              $t('Appendage') +
              ': ' +
              (box.appendage === true ? 'true' : 'false')
            "
            :icon="['fal', 'trailer']"
            :class="box.appendage === true ? 'text-theme-500' : 'text-gray-300'"
          />
        </span>
      </span>

      <button
        class="invisible px-2 py-1 ml-auto mr-1 rounded-full hover:bg-theme-100 group-hover:visible text-theme-500"
        @click="
          set_active_box(box),
            set_edit(true),
            set_type('box'),
            set_add_variation(true)
        "
      >
        <font-awesome-icon :icon="['fal', 'pencil']" />
      </button>

      <button
        class="invisible px-2 py-1 mr-1 text-red-500 rounded-full hover:bg-red-100 group-hover:visible"
        @click="$emit('on-delete-box', box.id)"
      >
        <font-awesome-icon :icon="['fal', 'trash-can']" />
      </button>

      <transition name="slide">
        <VariationOptionList
          :box="box"
          :i="2"
          class="w-full"
          @on-delete-option="(option) => $emit('on-delete-option', option)"
        />
      </transition>

      <VariationBoxList
        v-if="box.children.length > 0"
        :boxes="box.children"
        class="w-full"
        :i="i + 1"
      />
    </li>
  </ul>
</template>

<script>
import { mapState, mapMutations } from "vuex";
export default {
  props: {
    boxes: {
      type: Array,
      required: false,
    },
    i: {
      type: Number,
      required: true,
    },
  },
  emits: ["deleteBox", "on-delete-box", "on-delete-option"],
  data() {
    return {
      showChildren: null,
    };
  },
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
