<template>
  <div v-if="collection && Object.keys(collection).length > 0">
    <div class="flex mx-auto text-sm scroll-grab">
      <template v-for="(box, index) in boops.boops" :key="`shopBoops_${index}`">
        <div
          class="relative"
          :class="{
            'border-2 mx-1 mt-1 border-gray-300 dark:border-gray-500 pl-8 pt-4 pb-4 pr-2 rounded flex flex-col':
              selectedCategory.boops[0].divided,
            '!border-l-0 rounded-l-none ml-0 !pl-0':
              boops.boops[index - 1]?.divider === box.divider,
            '!border-r-0 rounded-r-none mr-0 !pr-0':
              boops.boops[index + 1]?.divider === box.divider,
          }"
        >
          <div
            v-if="
              selectedCategory.boops[0].divided &&
              (boops.boops[index - 1]?.divider !== box.divider ||
                (index === 0 && selectedCategory.boops[0].divided))
            "
            class="absolute mx-auto text-sm font-bold tracking-wider text-gray-500 uppercase bg-gray-100 pdx-2 dark:bg-gray-800 -mt-7"
          >
            {{ box.divider }}
          </div>
          <div
            v-show="
              selectedCategory.boops[0].divided &&
              (boops.boops[index - 1]?.divider !== box.divider ||
                (index === 0 && selectedCategory.boops[0].divided))
            "
            class="absolute left-0 flex flex-col h-full my-auto ml-2"
          >
            <font-awesome-icon
              :icon="['fad', 'calculator']"
              class="text-gray-300 dark:text-gray-500 fa-lg"
            />
            <span
              class="[writing-mode:vertical-rl] mt-2 text-gray-500 truncate h-full pb-4"
              :title="$t('calculation')"
            >
              {{ $t("calculation") }}
            </span>
          </div>
          <nav
            :key="index"
            class="p-2 mx-1 my-2 bg-white rounded shadow-md shadow-gray-200 dark:shadow-gray-900 dark:bg-gray-700"
          >
            <h2 class="mb-2 text-sm font-bold tracking-wide uppercase select-text cursor-text">
              {{ $display_name(box.display_name) }}
            </h2>

            <ul>
              <template v-for="(item, idx) in activeItems">
                <li
                  v-if="parseInt(index) === parseInt(idx)"
                  :key="`active_item_${idx}`"
                  class="flex items-center w-52"
                >
                  <div v-if="collection.find((x) => x.dynamic && x.value === item.value)">
                    <div
                      v-if="collection.find((x) => x.value === item.value)?._?.pages"
                      class="select-text cursor-text"
                    >
                      <font-awesome-icon :icon="['fal', 'files']" class="text-gray-500" />
                      {{ collection.find((x) => x.value === item.value)?._?.pages }}
                    </div>
                    <div
                      v-if="collection.find((x) => x.value === item.value)?._?.sides"
                      class="select-text cursor-text"
                    >
                      <font-awesome-icon :icon="['fal', 'note-sticky']" class="text-gray-500" />
                      {{ collection.find((x) => x.value === item.value)?._?.sides }}
                    </div>
                    <div
                      v-if="
                        collection.find((x) => x.value === item.value)?._?.height &&
                        collection.find((x) => x.value === item.value)?._?.width
                      "
                      class="select-text cursor-text"
                    >
                      {{ collection.find((x) => x.value === item.value)._.height }}
                      {{ boops.boops[index].ops[idx].unit }}
                      <span class="mx-1"> x </span>
                      {{ collection.find((x) => x.value === item.value)._.width }}
                      {{ boops.boops[index].ops[idx].unit }}
                    </div>
                  </div>
                  <div v-else-if="item.display_name" class="select-text cursor-text">
                    {{ $display_name(item.display_name) }}
                  </div>
                  <div v-else class="select-text cursor-text">{{ item.name }}</div>
                </li>
              </template>
            </ul>
          </nav>
        </div>
      </template>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  boops: {
    type: Object,
    required: true,
  },
  collection: {
    type: Array,
    required: true,
  },
  selectedCategory: {
    type: Object,
    required: true,
  },
  activeItems: {
    type: Object,
    required: true,
  },
});

onMounted(() => {
  const slider = document.querySelector(".scroll-grab");
  let isDown = false;
  let startX;
  let scrollLeft;
  slider.addEventListener("mousedown", (e) => {
    isDown = true;
    slider.classList.add("active");
    slider.classList.add("cursor-grabbing");
    startX = e.pageX - slider.offsetLeft;
    scrollLeft = slider.scrollLeft;
  });
  slider.addEventListener("mouseleave", () => {
    isDown = false;
    slider.classList.remove("active");
    slider.classList.remove("cursor-grabbing");
  });
  slider.addEventListener("mouseup", () => {
    isDown = false;
    slider.classList.remove("active");
    slider.classList.remove("cursor-grabbing");
  });
  slider.addEventListener("mousemove", (e) => {
    if (!isDown) return;
    e.preventDefault();
    const x = e.pageX - slider.offsetLeft;
    const walk = (x - startX) * 1; // set to 2 or 3 to scroll-fast(er)
    slider.scrollLeft = scrollLeft - walk;
  });
});

const { boops, collection, selectedCategory, activeItems } = toRefs(props);
</script>
