<template>
  <article>
    <div class="text-center">
      <h1
        :class="[
          'font-bold text-gray-400',
          size === 'sm' ? 'mx-auto max-w-48 text-sm' : size === 'md' ? 'text-lg' : 'text-xl',
        ]"
      >
        {{ props.message }}
      </h1>
      <div
        :class="[
          'flex justify-center',
          size === 'sm' ? '-ml-6 mt-2' : size === 'md' ? 'my-3 -ml-8' : 'my-4 -ml-10',
        ]"
      >
        <font-awesome-icon
          :icon="['fal', 'ban']"
          :class="[
            'text-gray-300',
            size === 'sm' ? 'fa-2x m-2' : size === 'md' ? 'fa-3x m-3' : 'fa-3x m-4',
          ]"
        />
        <font-awesome-icon
          :icon="['fad', 'lock']"
          :class="[
            'my-4 text-gray-400',
            size === 'sm' ? 'fa-3x mt-2' : size === 'md' ? 'fa-3x my-3' : 'fa-5x my-4',
          ]"
        />
        <font-awesome-icon
          :icon="['fal', 'triangle-exclamation']"
          :class="[
            'text-gray-300',
            size === 'sm' ? 'fa-2x mt-2' : size === 'md' ? 'fa-1x my-3' : 'fa-2x my-4',
          ]"
        />
      </div>
    </div>
  </article>
</template>

<script setup>
const props = defineProps({
  message: {
    type: String,
    required: true,
  },
  size: {
    type: String,
    default: "lg",
    validator(value) {
      return ["sm", "md", "lg"].includes(value);
    },
  },
});
</script>
