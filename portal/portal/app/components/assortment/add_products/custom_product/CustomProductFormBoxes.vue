<template>
  <div>
    <header
      class="flex justify-between p-2 mb-1 transition bg-white rounded shadow-md cursor-pointer dark:bg-gray-700 group hover:shadow-lg"
      :class="`ml-${i * 3}`"
      @click="toggleCheck(box)"
    >
      <div>
        <input
          :id="box.name"
          type="checkbox"
          :name="box.name"
          :checked="isChecked(box.id)"
        />

        <label :for="box.name">
          {{ box.name }}
        </label>
      </div>
      <button aria-label="more options">
        <font-awesome-icon aria-hidden="true" :icon="['fal', 'angle-down']" />
      </button>
    </header>

    <div class="accordion" :class="{ open: isChecked(box.id) }">
      <section>
        <div
          v-if="isChecked(box.id)"
          class="h-full overflow-y-auto"
          style="max-height: calc(100vh - 28rem)"
        >
          <CustomProductFormOptions
            v-for="option in box.options"
            :key="'option_' + option.slug"
            :box_id="box.id"
            :option="option"
            :i="0"
            :edit_mode="edit_mode"
          />
        </div>
      </section>
    </div>

    <CustomProductFormBoxes
      v-for="(box, index) in box.children"
      :key="`box_${index}`"
      :box="box"
      :i="i + 1"
    />
  </div>
</template>

<script>
import { mapState, mapMutations } from "vuex";

export default {
  props: {
    box: {
      type: Object,
      required: true,
    },
    i: {
      type: Number,
      required: true,
    },
    edit_mode: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      active_box: null,
    };
  },
  computed: {
    ...mapState({
      variations: (state) => state.product.custom_product_variations,
    }),
  },
  beforeUnmount() {
    this.set_variations([]);
  },
  methods: {
    ...mapMutations({
      add_variation_box: "product/add_variation_box",
      remove_variation_box: "product/remove_variation_box",
      set_variations: "product/set_variations",
    }),
    isChecked(id) {
      if (this.variations?.length > 0) {
        return this.variations.find((box) => box.id === id);
      }
    },
    toggleCheck(box) {
      if (!this.isChecked(box.id)) {
        this.add_variation_box(box);
      } else {
        this.remove_variation_box(box);
      }
    },
  },
};
</script>

<style lang="scss" scoped>
.accordion {
  overflow: hidden;
}
.accordion.open {
  transition: all ease 200ms;
  max-height: 100%;
  opacity: 1;
  padding: 1.5rem !important;
}
.accordion {
  transition: all ease 200ms;
  max-height: 0;
  opacity: 0;
  padding: 0 !important;
}
</style>
