<template>
  <form class="mx-2 mt-8 flex items-center md:mt-0">
    <div class="relative mx-1 w-full">
      <label
        for="name"
        class="absolute left-0 top-0 -mt-4 flex text-xs font-bold uppercase tracking-wide"
      >
        {{ $t("name") }}
        <VTooltip>
          <font-awesome-icon
            :icon="['far', 'circle-info']"
            class="ml-2 text-xs text-prindustry-500"
          />
          <template #popper>
            <div class="max-w-sm">
              {{
                // prettier-ignore
                $t("This name is used internally as a unique identifier for the product. It links the product to the Prindustry database and should be unique within your organization. Choose a name that clearly distinguishes this product from others.")
              }}
            </div>
          </template>
        </VTooltip>
      </label>
      <input
        type="text"
        class="input"
        name="name"
        :value="nameValue"
        @change="$emit('update-name', $event.target.value)"
      />
    </div>
    <font-awesome-icon :icon="['fas', 'angle-right']" class="mx-2" />
    <div class="relative mx-1 w-full">
      <label
        for="display_name"
        class="absolute left-0 top-0 -mt-4 text-xs font-bold uppercase tracking-wide text-theme-500"
      >
        {{ $t("display name") }}
        <font-awesome-icon
          v-tooltip="$t('The name shown to users and customers')"
          :icon="['far', 'circle-info']"
          class="ml-2 text-xs text-theme-500"
        />
      </label>
      <input
        type="text"
        class="input border-theme-500 text-theme-500 focus:border-theme-500 focus:ring-theme-100"
        name="display_name"
        :value="displayValue"
        @input="$emit('update-display-name', $event.target.value)"
      />
    </div>
    <button
      v-if="showButton"
      type="button"
      class="my-4 whitespace-nowrap rounded bg-theme-400 px-4 py-2 text-themecontrast-400"
      @click="$emit('submit')"
    >
      {{ buttonText }}
    </button>
  </form>
</template>

<script setup>
const props = defineProps({
  displayValue: {
    type: String,
    default: "",
  },
  nameValue: {
    type: String,
    default: "",
  },
  buttonText: {
    type: String,
    default: "Add",
  },
  showButton: {
    type: Boolean,
    default: true,
  },
});

const emit = defineEmits(["update-name", "update-display-name", "submit"]);

const { t: $t } = useI18n();
</script>
