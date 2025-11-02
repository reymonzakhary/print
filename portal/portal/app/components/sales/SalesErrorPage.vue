<template>
  <div class="-mt-10 grid h-full place-items-center text-center">
    <div>
      <div class="my-4 -ml-10 flex justify-center">
        <font-awesome-icon :icon="['fal', 'exclamation-circle']" class="fa-3x m-4 text-gray-300" />
        <font-awesome-icon
          :icon="['fal', 'triangle-exclamation']"
          class="fa-5x my-4 text-gray-400"
        />
        <font-awesome-icon :icon="['fal', 'exclamation-circle']" class="fa-2x my-4 text-gray-300" />
      </div>
      <h1 class="-mt-4 text-xl font-bold text-gray-400">
        {{ $t("Whoops. We've encountered some issues") }}
      </h1>
      <code class="my-3 inline-flex max-w-lg items-center space-x-4 p-2 pl-6 text-center text-sm">
        <span class="flex gap-4">
          <span class="flex-1">
            <span>
              {{ error.message }}
            </span>
          </span>
        </span>
      </code>
      <div class="flex gap-4">
        <SalesActionButton variant="neutral" @click="emit('reset-error')">
          {{
            $t("Go back to {salesType}", {
              salesType: props.salesType === "quotation" ? $t("quotations") : $t("orders"),
            })
          }}
        </SalesActionButton>
        <SalesActionButton variant="neutral" @click="emit('clear-error')">
          {{ $t("Try again") }}
        </SalesActionButton>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  error: {
    type: Object,
    required: true,
  },
  salesType: {
    type: String,
    required: true,
    validator: (value) => ["quotation", "order"].includes(value),
  },
});

const emit = defineEmits(["reset-error", "clear-error"]);
</script>
