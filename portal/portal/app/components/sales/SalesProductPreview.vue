<template>
  <VDropdown offset="4" :placement="placement">
    <div class="cursor-pointer">
      <UIButton variant="link" :class="buttonClass">
        {{ props.item.product?.category?.name ?? "-" }}
      </UIButton>
    </div>
    <template #popper>
      <div
        class="p-4 overflow-auto text-white bg-gray-900 rounded shadow"
        style="max-height: calc(100vh - 4rem)"
      >
        <b class="flex justify-between w-64 border-b border-black">
          <span class="text-sm text-gray-100"> #{{ props.item.id }} </span>
          {{ props.item.product?.category?.name ?? "-" }}
        </b>

        <span
          v-for="(dividerGroup, i) in props.item.product.product"
          :key="'option_' + i"
          class="w-64 text-sm border-b border-black"
        >
          <h2
            v-if="dividerGroup.divider !== '_'"
            class="mb-1 text-xs font-bold tracking-wide text-white uppercase"
          >
            {{ dividerGroup.divider }}
          </h2>
          <ul>
            <li v-for="boop in dividerGroup.items" :key="boop" class="flex justify-between">
              <span>{{ boop.display_key }}</span>
              <b>{{ boop.display_value }}</b>
            </li>
          </ul>
        </span>

        <li
          v-for="prop in props.item.product.properties"
          :key="'prop_' + prop.key"
          class="flex flex-wrap justify-between w-full py-2"
        >
          <b
            :class="
              typeof prop.value === 'object'
                ? 'text-xs sticky -top-4 bg-gray-900 w-full z-0 py-1'
                : 'w-1/2'
            "
            class="truncate"
          >
            {{ prop.display_key }}
          </b>

          <span v-if="typeof prop.value === 'object'" class="w-full divide-y divide-dashed">
            <div
              v-for="(value, key) in prop.value"
              :key="'value_' + key"
              class="flex flex-wrap w-full my-1"
            >
              <span class="w-1/2 pr-2 lowercase truncate"> {{ key }}: </span>
              <span v-tooltip="value" class="w-1/2 truncate max-w-prose">
                {{ value }}
              </span>
            </div>
          </span>
          <span v-else class="w-1/2 text-right truncate"> {{ prop.value }}</span>
        </li>
      </div>
    </template>
  </VDropdown>
</template>

<script setup>
const props = defineProps({
  item: {
    type: Object,
    required: true,
  },
  placement: {
    type: String,
    default: "left",
  },
  buttonClass: {
    type: String,
    default: "",
  },
});
</script>
