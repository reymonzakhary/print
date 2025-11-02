<template>
  <div class="p-4 bg-white rounded shadow-md shadow-gray-200 dark:shadow-gray-900 dark:bg-gray-700">
    <h2 class="mb-4 font-bold tracking-wide uppercase">
      {{ $t("chosen product") }}
    </h2>

    <ol>
      <transition-group name="enterlistleft">
        <li
          v-for="(item, i) in active_items"
          :key="`selected_list_item_${i}`"
          class="flex flex-wrap items-center justify-between py-2 text-sm border-b dark:border-b-gray-800 last:border-0"
        >
          <!-- v-if="
              (boops[0]?.boops[i - 1]?.divider !== box.divider || (i === 0 && boops[0].divided)) &&
              i <= activeIndex
            " -->
          <div
            v-if="
              (boops[0]?.boops[i - 1]?.divider !== boops[0]?.boops[i].divider ||
                (i === 0 && boops[0].divided)) &&
              i <= active_items.length
            "
            class="w-full py-2 text-sm font-bold tracking-wider text-gray-500 uppercase"
          >
            {{ boops[0].boops[i].divider }}
          </div>
          <b class="w-1/2">
            {{ $display_name(boops[0].boops[i].display_name) }}
          </b>
          <span class="flex items-center w-1/2">
            <ShopThumbnail
              v-if="item.media && item.media.length > 0"
              class="w-6"
              disk="tenancy"
              :file="{ path: item.media[0] }"
            />

            <div v-if="collection.find((x) => x.dynamic && x.value === item.slug)">
              <div v-if="collection.find((x) => x.value === item.slug)?._">
                <!-- <font-awesome-icon :icon="['fad', 'files']" class="text-gray-500" /> -->
                {{ collection.find((x) => x.value === item.slug)?._?.pages }}
              </div>
              <div v-if="collection.find((x) => x.value === item.slug)?._?.sides">
                <font-awesome-icon :icon="['fad', 'note-sticky']" class="text-gray-500" />
                {{ collection.find(x.value === item.slug)._.sides }}
              </div>
              <div
                v-if="
                  collection.find((x) => x.value === item.slug)?._?.height &&
                  collection.find((x) => x.value === item.slug)?._?.width
                "
              >
                {{ collection.find((x) => x.value === item.slug)._.height }} {{ item.unit }}
                <span class="mx-1"> x </span>
                {{ collection.find((x) => x.value === item.slug)._.width }} {{ item.unit }}
              </div>
            </div>
            <div v-else>
              {{ $display_name(item.display_name) }}
            </div>
          </span>
        </li>
      </transition-group>
    </ol>

    <div
      v-if="selectedPrice && Object.keys(selectedPrice).length"
      class="grid grid-cols-2 p-4 mt-6 -m-4 text-lg font-bold border-t-8 border-white border-dashed dark:border-gray-700 bg-gray-50 dark:bg-gray-800"
    >
      <div class="text-base">{{ $t("total ex vat") }}</div>
      <div class="text-right">{{ selectedPrice.display_selling_price_ex }}</div>
      <div class="text-base font-normal">
        {{ $t("vat") }} <small class="text-gray-500">{{ selectedPrice.vat }}% </small>
      </div>
      <div class="text-base font-normal text-right">
        {{ selectedPrice.display_vat_p }}
      </div>
      <div class="text-base">{{ $t("total") }}</div>
      <div class="text-right">{{ selectedPrice.display_selling_price_inc }}</div>
    </div>
    <!-- {{ selectedPrice }} -->
  </div>
</template>

<script>
import { mapState } from "vuex";
export default {
  props: {
    selectedPrice: {
      type: Object,
      required: false,
      default: () => {},
    },
    collection: {
      type: Array,
      required: false,
      default: () => [],
    },
  },
  computed: {
    ...mapState({
      categories: (state) => state.shop.categories,
      active_items: (state) => state.shop.active_items,
      boops: (state) => state.shop.boops,
    }),
  },
};
</script>
