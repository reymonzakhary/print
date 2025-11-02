<template>
  <article
    :class="
      cn(
        'group relative aspect-square h-full cursor-pointer overflow-hidden rounded-md border border-theme-200 bg-gray-50 text-base shadow-md shadow-black/10 transition-all hover:scale-[1.025] hover:shadow-md dark:border-theme-900 dark:bg-gray-750 dark:shadow-gray-900',
        $attrs.class,
      )
    "
    @click="navigateTo(`/marketplace/product-finder/${category.slug}`)"
  >
    <!-- Self-producing icon -->
    <div
      v-if="category.ownProduction"
      v-tooltip="$t('You produce this yourself!')"
      class="absolute left-0 top-0 z-10 flex h-8 items-center gap-1 rounded-br-md rounded-tl-md bg-prindustry/75 p-2 text-white"
    >
      <font-awesome-icon :icon="['fas', 'print']" class="h-full" />
      <span
        class="w-0 overflow-hidden whitespace-nowrap text-xs font-medium transition-all group-hover:w-full"
      >
        {{ $t("own production") }}
      </span>
    </div>

    <!-- Image -->
    <div class="relative h-[calc(100%_-_2rem)] overflow-hidden p-4">
      <svg
        class="absolute -right-[15%] bottom-2 z-0 h-full opacity-50 dark:text-theme-800"
        xmlns="http://www.w3.org/2000/svg"
        viewBox="2.5434417724609375 3 852.45654296875 793.2177124023438"
      >
        <defs>
          <linearGradient
            id="prinshapeGradient"
            gradientTransform="rotate(35 0.5 0.5)"
            gradientUnits="objectBoundingBox"
          >
            <stop offset="0%" style="stop-color: var(--theme-100)" />
            <stop offset="100%" style="stop-color: var(--theme-400)" />
          </linearGradient>
        </defs>
        <path
          id="prinshape"
          fill="url(#prinshapeGradient)"
          d="M240 3h414s58 5 87 60l84 117s30 39 30 75v452s-14 89-121 89H405s-24 6-74-45L26 420s-50-62-4-129L156 57s23-47 84-54Z"
        />
      </svg>
      <NuxtImg
        v-if="category.media?.[0] || category.image"
        :alt="category.name + 'image'"
        :src="category.media?.[0] || category.image"
        class="pointer-events-none relative top-1/2 z-10 mx-auto ml-4 w-9/12 -translate-y-1/2 rounded-md object-cover"
      />
      <UIPrindustryBox
        v-else
        line="text-theme-400"
        class="z-20 h-36 w-36 rotate-[0.25rad] transform opacity-25"
      />
    </div>
    <!-- Name -->
    <!-- 1.25rem padding + 1.5rem lineheight -->
    <div
      class="absolute bottom-0 flex h-[2.5rem] w-full items-center justify-between bg-white px-4 py-2 transition-all group-hover:h-fit dark:bg-gray-700"
    >
      <dl>
        <div>
          <dt class="sr-only">{{ $t("Category") }}</dt>
          <dd class="line-clamp-1 group-hover:line-clamp-none">
            {{ $display_name(category.display_name) }}
          </dd>
        </div>
      </dl>
      <!-- Handshake producers indicator -->
<!--      <div v-if="category.handshakes > 0" class="flex">-->
<!--        <div class="ml-2 mt-1 flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">-->
<!--          <font-awesome-icon :icon="['fas', 'handshake']" class="text-theme-400" />-->
<!--          <span>{{ category.handshakes }}</span>-->
<!--        </div>-->
<!--        &lt;!&ndash; Order button &ndash;&gt;-->
<!--        <button-->
<!--          v-tooltip="$t('Order directly')"-->
<!--          class="ml-2 rounded-full bg-theme-500 p-1.5 text-xs text-white hover:bg-theme-600 focus:outline-none focus:ring-2 focus:ring-theme-400"-->
<!--          @click.stop="$emit('order', category)"-->
<!--        >-->
<!--          <font-awesome-icon :icon="['fas', 'shopping-cart']" />-->
<!--        </button>-->
<!--      </div>-->
    </div>
  </article>
</template>

<script setup>
defineProps({
  category: {
    type: Object,
    required: true,
  },
});

defineEmits(["order"]);

const { cn } = useUtilities();
</script>
