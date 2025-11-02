<template>
  <div>
    <SkeletonLine v-if="loading" :class="cn('aspect-square rounded-md', sizeClasses)" />
    <div
      v-else
      :class="
        cn(
          'relative aspect-square flex-shrink-0 overflow-hidden rounded-md bg-gray-100 p-1 dark:bg-gray-900 dark:from-theme-700',
          sizeClasses,
        )
      "
    >
      <NuxtImg
        v-if="image"
        :src="image"
        :alt="title"
        class="object-cover object-center"
        loading="lazy"
        format="webp"
      />
      <svg
        v-else
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 512 512"
        style="fill: var(--theme-500)"
        class="size-full p-2"
      >
        <path
          d="M0 96C0 60.7 28.7 32 64 32l384 0c35.3 0 64 28.7 64 64l0 320c0 35.3-28.7 64-64 64L64 480c-35.3 0-64-28.7-64-64L0 96zM323.8 202.5c-4.5-6.6-11.9-10.5-19.8-10.5s-15.4 3.9-19.8 10.5l-87 127.6L170.7 297c-4.6-5.7-11.5-9-18.7-9s-14.2 3.3-18.7 9l-64 80c-5.8 7.2-6.9 17.1-2.9 25.4s12.4 13.6 21.6 13.6l96 0 32 0 208 0c8.9 0 17.1-4.9 21.2-12.8s3.6-17.4-1.4-24.7l-120-176zM112 192a48 48 0 1 0 0-96 48 48 0 1 0 0 96z"
        />
      </svg>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  image: {
    type: String,
    required: false,
    default: undefined,
  },
  title: {
    type: String,
    required: false,
    default: "Loading...",
  },
  loading: {
    type: Boolean,
    required: true,
  },
  size: {
    type: String,
    required: false,
    default: "md",
    validator(value) {
      return ["md", "lg", "full"].includes(value);
    },
  },
});

const { cn } = useUtilities();

const sizeClasses = computed(() => {
  if (props.size === "full") return "!w-full rounded-2xl";
  return props.size === "md" ? "!size-14" : "!size-16";
});
</script>
