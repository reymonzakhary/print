<template>
  <div>
    <menu>
      <li v-for="(item, i) in items" :key="i">
        <button
          class="group relative flex w-full items-center justify-between px-2 py-1 text-left hover:bg-gray-200 dark:hover:bg-black"
          :class="{
            'bg-theme-100 text-theme-500': active.slug === item.slug,
          }"
          @click="(set_item(item), (component = true))"
        >
          <span class="truncate">{{ $display_name(item.display_name) }}</span>
          <font-awesome-icon
            v-if="item.calc_ref"
            class=""
            fixed-width
            :icon="['fal', item?.calc_ref?.length > 0 ? calcRef(item.calc_ref) : 'calculator']"
            :class="[item?.calc_ref?.length > 0 ? 'text-theme-400' : 'text-amber-400']"
          />
        </button>
      </li>
    </menu>
    <BoxesEditPanel v-if="component" type="box" class="z-50" @on-close="closeEditPanel" />
  </div>
</template>

<script>
import { mapMutations } from "vuex";
/**
 * step one get object from boops service
 * collect the selected data
 * generate a md5 hash from it
 * post it to hash service
 * response with price and quant || add price and quant
 **/
export default {
  props: {
    items: {
      type: Array,
      required: true,
    },
  },
  setup() {
    const { permissions } = storeToRefs(useAuthStore());
    return { permissions };
  },
  data() {
    return {
      active: "",
      filter: "",
      component: "",
      menuItems: [
        {
          items: [
            {
              action: "edit",
              icon: "pencil",
              title: this.$t("edit"),
              classes: "",
              show: false,
            },
            {
              action: "delete",
              icon: "trash",
              title: this.$t("delete"),
              classes: "text-red-500",
              show: false,
            },
          ],
        },
      ],
    };
  },
  created() {
    if (
      this.permissions.includes("print-assortments-options-update") &&
      this.permissions.includes("print-assortments-options-list")
    ) {
      this.menuItems[0].items[0].show = true;
    }
    if (this.permissions.includes("print-assortments-options-delete")) {
      this.menuItems[0].items[1].show = true;
    }
  },
  methods: {
    ...mapMutations({
      set_item: "assortmentsettings/set_item",
    }),
    calcRef(calc_ref) {
      switch (calc_ref) {
        case "format":
          return "ruler-combined";
        case "material":
          return "file";
        case "weight":
          return "weight-hanging";
        case "printing_colors":
          return "circles-overlap-3";
        default:
          return "check";
      }
    },
    closeEditPanel() {
      // for transition
      setTimeout(() => (this.component = false), 200);
    },
  },
};
</script>
