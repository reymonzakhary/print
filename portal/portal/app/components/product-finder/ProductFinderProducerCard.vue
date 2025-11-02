<!-- ProducerCard.vue -->
<template>
  <article
    :class="
      cn(
        'flex aspect-square w-full cursor-pointer flex-col rounded-lg border border-gray-200 bg-gray-50 transition-all duration-300 hover:scale-[1.025]',
        props.class,
      )
    "
  >
    <div class="relative flex-1 p-5">
      <!-- Logo -->
      <div class="grid h-full place-items-center">
        <img
          :src="randomImage"
          :alt="`${producer.name} logo`"
          class="h-6 max-w-[75%] object-contain"
        />
      </div>

      <!-- Context Menu -->
      <div class="absolute right-2 top-2">
        <button
          class="aspect-square rounded-full p-1 px-2 text-gray-500 transition-colors hover:bg-gray-100"
          @click="toggleMenu"
        >
          <font-awesome-icon :icon="['fas', 'ellipsis-vertical']" class="size-4" />
        </button>

        <!-- Dropdown menu -->
        <div
          v-if="showMenu"
          class="absolute right-0 z-10 mt-3 w-40 rounded-md bg-white py-1 text-sm text-gray-700 shadow-lg"
        >
          <a href="#" class="block px-4 py-3 hover:bg-gray-100">View Profile</a>
          <a href="#" class="block px-4 py-3 hover:bg-gray-100">Contact Producer</a>
          <a href="#" class="block px-4 py-3 hover:bg-gray-100">Add to Favorites</a>
        </div>
      </div>
    </div>

    <div class="h-fit rounded-b-lg bg-white pb-3 pt-4">
      <div class="px-5 pb-2.5">
        <div class="mb-1 min-w-0 flex-grow">
          <div class="flex items-center text-xs">
            <template v-for="i in 5" :key="i">
              <font-awesome-icon
                :icon="['fas', 'star']"
                :class="i <= (producer.rating || 4) ? 'text-theme-400' : 'text-gray-300'"
                class="mr-0.5"
              />
            </template>
          </div>
        </div>
        <h3 v-tooltip="producer.name" class="truncate font-medium text-gray-900">
          {{ producer.name }}
        </h3>
      </div>
      <!-- Price and partnership section - most important info -->
      <div
        class="flex justify-between px-5 pt-2.5"
        :class="producer.handShaked || producer.ownProduction ? 'items-end' : 'items-center'"
      >
        <div
          class="flex items-center"
          :class="producer.handShaked || producer.ownProduction ? 'pb-0.5' : ''"
        >
          <template v-if="producer.handShaked">
            <font-awesome-icon :icon="['fas', 'handshake']" class="mr-1 text-theme-300" />
            <span class="text-sm font-medium text-theme-600">Partner</span>
          </template>
          <template v-else-if="producer.ownProduction">
            <font-awesome-icon :icon="['fas', 'print']" class="mr-1 text-green-300" />
            <span class="text-sm font-medium text-green-600">Eigen productie</span>
          </template>
          <template v-else>
            <font-awesome-icon :icon="['fas', 'hand-holding-hand']" class="mr-1 text-gray-400" />
            <span class="text-sm font-medium text-gray-600">Request</span>
          </template>
        </div>
        <div
          v-if="producer.handShaked || producer.ownProduction"
          class="text-lg font-bold text-gray-900"
        >
          {{ producer.price }}
        </div>
        <div v-else class="relative text-lg font-bold text-gray-900">
          <span class="select-none blur-sm">€ NV.T0</span>
        </div>
      </div>
    </div>
  </article>
</template>

<script setup>
const props = defineProps({
  // You can pass the producer as a prop
  producer: {
    type: Object,
    default: () => ({}),
  },
  class: {
    type: String,
    default: "",
  },
});

const { cn } = useUtilities();

const randomImage = computed(() => {
  const mockImages = [
    "/mock/images/controlmedia.png",
    "/mock/images/drukwerkconcurrent.png",
    "/mock/images/postermen.png",
    "/mock/images/printlogo.png",
    "/mock/images/quickscan.png",
  ];

  const randomIndex = Math.floor(Math.random() * mockImages.length);
  return mockImages[randomIndex];
});

const showMenu = ref(false);
function toggleMenu() {
  showMenu.value = !showMenu.value;
}
</script>
