<template>
  <div class="flex flex-wrap items-center">
    <form class="flex items-center w-full">
      <label class="flex items-center mr-2 font-bold">
        <font-awesome-icon :icon="['fal', 'bow-arrow']" class="mr-2" />
        {{ $t("range") }}
      </label>
      <div class="relative flex w-1/2 mr-2">
        <input
          v-model="printing_method.from"
          class="w-full px-2 py-1 text-sm text-black input"
          type="number"
          @change="update_selected_category()"
        />
      </div>

      {{ $t("up to") }}

      <div class="relative flex w-1/2 ml-2">
        <input
          v-model="printing_method.to"
          class="w-full px-2 py-1 text-sm text-black input"
          type="number"
          @change="update_selected_category()"
        />
      </div>
    </form>
  </div>
</template>

<script>
import { mapActions } from "vuex";
import _ from "lodash";

export default {
  name: "CategoryQuantitiesRange",
  props: {
    printing_method: {
      type: Object,
      required: true,
    },
  },
  watch: {
    "printing_method.from"(v) {
      if (this.printing_method.to <= v) {
        this.printing_method.to = Number(v) + 1;
      }
    },
    "printing_method.to": _.debounce(function (v) {
      if (
        Number(this.printing_method.to) <= Number(this.printing_method.from)
      ) {
        this.printing_method.to = Number(this.printing_method.from) + 1;
      }
    }, 300),
  },
  methods: {
    ...mapActions({
      update_selected_category: "product_wizard/update_selected_category",
    }),
  },
};
</script>
