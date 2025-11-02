<template>
  <div>
    <section class="mb-8">
      <UISectionTitle is="h2">{{ $t("mode") }}</UISectionTitle>
      <div class="mb-2 flex w-1/3">
        <template v-if="loading">
          <SkeletonLine class="h-8 w-96" />
        </template>
        <template v-else>
          <button
            class="w-full rounded-l bg-gray-200 px-2 py-1 text-sm text-black transition-colors duration-150 hover:bg-gray-300 dark:bg-gray-600 dark:text-white"
            :class="{
              'border-theme-600 bg-theme-400 text-themecontrast-400 shadow-inner hover:bg-theme-500 dark:border-theme-600 dark:bg-theme-400 dark:hover:bg-theme-500':
                mode === 'runs',
            }"
            @click="mode = 'runs'"
          >
            {{ $t("Runs") }}
          </button>
          <button
            class="w-full rounded-r bg-gray-200 px-2 py-1 text-sm text-black transition-colors duration-150 hover:bg-gray-300 dark:bg-gray-600 dark:text-white"
            :class="{
              'border-theme-600 bg-theme-400 text-themecontrast-400 shadow-inner hover:bg-theme-500 dark:border-theme-600 dark:bg-theme-400 dark:hover:bg-theme-500':
                mode === 'price',
            }"
            @click="mode = 'price'"
          >
            {{ $t("Price") }}
          </button>
        </template>
      </div>
    </section>
    <section>
      <UISectionTitle is="h2">{{ $t("slots") }}</UISectionTitle>
      <div v-if="loading" class="flex flex-wrap gap-4">
        <SkeletonLine key="skel1" class="aspect-square h-56" />
        <SkeletonLine key="skel3" class="aspect-square h-56" />
        <SkeletonLine key="skel2" class="aspect-square h-56" />
      </div>
      <TransitionGroup v-else name="list" tag="div" class="flex flex-wrap gap-4">
        <DiscountTile
          v-for="(discount, index) in discounts"
          :key="discount.id"
          :discount="discount"
          :is-deletable="index === discounts.length - 1"
          :is-infinitable="index === discounts.length - 1"
          :is-from-editable="index === 0"
          @update:discount="handleDiscountUpdate($event, discount.id)"
          @on-remove-discount="handleDiscountDelete(discount.id)"
        />
        <DiscountSlot
          v-if="!discounts.length || Number(discounts[discounts.length - 1].to) !== -1"
          key="DiscountSlot"
          @add-discount="handleDiscountAdd"
        />
      </TransitionGroup>
    </section>
    <footer class="mt-4 flex items-end justify-end px-24">
      <UIButton variant="success" class="!px-4 !text-base" @click="handleSaveDiscounts">
        {{ $t("Save Discounts") }}
      </UIButton>
    </footer>
  </div>
</template>

<script setup>
const props = defineProps({
  category: {
    type: String,
    required: false,
    default: "general",
  },
});

const { t: $t } = useI18n();
const discountRepository = useDiscountRepository();
const { addToast } = useToastStore();
const { handleError } = useMessageHandler();

const mode = ref(null);
const loading = ref(true);
const discounts = ref(null);
const isChanged = ref(false);
watch(
  () => discounts.value,
  () => {
    if (loading.value || isChanged.value) return;
    addToast({
      type: "info",
      message: $t("You made changes, be sure to save them"),
    });
    isChanged.value = true;
  },
  { deep: true },
);

watch(
  mode,
  (value, oldValue) => {
    if (value === oldValue || (loading.value && mode.value !== null)) return;
    handleFetchDiscounts();
  },
  { immediate: true },
);

async function handleFetchDiscounts() {
  try {
    loading.value = true;
    const [response, activeMode] = await discountRepository.getDiscounts(
      mode.value,
      props.category,
    );
    discounts.value = response;
    if (!mode.value) mode.value = activeMode;
    await nextTick();
    loading.value = false;
  } catch (err) {
    handleError(err);
  }
}

function handleDiscountAdd() {
  if (discounts.value.length >= 1) {
    discounts.value.push({
      id: discounts.value.length + 1,
      from: discounts.value[discounts.value.length - 1].to + 1,
      to: discounts.value[discounts.value.length - 1].to + 2,
      type: "percentage",
      value: discounts.value[discounts.value.length - 1].value,
    });
    return;
  }
  discounts.value.push({
    id: Math.random().toString(36).substring(2, 9),
    from: 0,
    to: 1,
    type: "percentage",
    value: 0,
  });
}

function handleDiscountUpdate(updatedDiscount, id) {
  const updatedDiscountIndex = discounts.value.findIndex((discount) => discount.id === id);
  const discount = {
    id,
    ...updatedDiscount,
  };

  discounts.value[updatedDiscountIndex] = discount;
  const nextDiscountIndex = updatedDiscountIndex + 1;
  if (nextDiscountIndex >= discounts.value.length) return;

  const nextDiscount = discounts.value[nextDiscountIndex];

  const theNextDiscount = {
    ...nextDiscount,
    from: updatedDiscount.to + 1,
  };

  discounts.value[nextDiscountIndex] = theNextDiscount;
}

function handleDiscountDelete(id) {
  discounts.value = discounts.value.filter((discount) => discount.id !== id);
}

async function handleSaveDiscounts() {
  try {
    await discountRepository.saveDiscounts(discounts.value, mode.value, props.category);
    addToast({
      type: "success",
      message: $t("Discounts saved successfully"),
    });
  } catch (error) {
    handleError(error);
  }
}
</script>
