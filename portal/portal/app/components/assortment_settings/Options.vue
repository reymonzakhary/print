<template>
  <div>
    <menu class="h-full w-full overflow-hidden">
      <li v-for="(item, i) in items" :key="i">
        <button
          class="group relative flex w-full items-center justify-between px-2 py-1 text-left hover:bg-gray-200 dark:hover:bg-black"
          @click="
            (setItem(item),
            setRuns(item.runs),
            setFlag(''),
            (component = 'SupplierOptionsEditPanel'))
          "
        >
          <span class="flex w-full justify-between">
            <span
              v-tooltip="$display_name(item.display_name)"
              class="flex w-1/2 items-center truncate"
            >
              <Thumbnail
                v-if="item.media[0]"
                disk="assets"
                :file="{ path: item.media[0] }"
                class="flex px-1"
              />
              {{ $display_name(item.display_name) }}
            </span>
            <div class="flex w-1/2 items-center justify-end">
              <font-awesome-icon
                v-if="item.additional?.calc_ref"
                class="ml-1 text-sm text-theme-400"
                fixed-width
                :icon="['fal', calcRef(item.additional?.calc_ref)]"
              />
              <small
                v-tooltip="$t('system key') + ': ' + $display_name(item.system_key)"
                class="ml-2 w-1/2 truncate text-right text-gray-500"
              >
                {{ item.system_key }}
              </small>
            </div>
          </span>
        </button>
      </li>
    </menu>
    <OptionsEditPanel
      v-if="component"
      type="option"
      class="z-50 h-full"
      :show-runs-panel="false"
      @on-close="component = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useRoute } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import { useStore } from "vuex";
import { storeToRefs } from "pinia";

const props = defineProps({
  items: {
    type: Array,
    required: true,
  },
});

const { permissions } = storeToRefs(useAuthStore());
const store = useStore();
const route = useRoute();

// const filter = ref("");
const component = ref(false);

const menuItems = ref([
  {
    items: [
      {
        action: "edit",
        icon: "pencil",
        title: "edit", // Using $t in the template is better for reactivity
        classes: "",
        show: false,
      },
    ],
  },
]);

// Methods converted to functions
const setItem = (item) => store.commit("assortmentsettings/set_item", item);
const setRuns = (runs) => store.commit("assortmentsettings/set_runs", runs);
const setFlag = (flag) => store.commit("assortmentsettings/set_flag", flag);

// const menuItemClicked = (event) => {
//   switch (event) {
//     case "edit":
//       component.value = true;
//       break;
//     default:
//       break;
//   }
// };

const calcRef = function (calc_ref) {
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
};

// Lifecycle hooks
onMounted(() => {
  if (route.query.option) {
    setTimeout(() => {
      const item = props.items.find((x) => x.slug === route.query.option);
      if (item) {
        setItem(item);
        setRuns(item.runs);
        component.value = "SupplierOptionsEditPanel";
      }
    }, 500);
  }

  // Set permissions
  if (
    permissions.value.includes("print-assortments-options-update") &&
    permissions.value.includes("print-assortments-options-list")
  ) {
    menuItems.value[0].items[0].show = true;
  }
});
</script>
