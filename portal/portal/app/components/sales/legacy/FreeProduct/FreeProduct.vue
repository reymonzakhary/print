<template>
  <div
    class="flex h-full w-full flex-col rounded border-black bg-white shadow-md shadow-green-200 dark:bg-gray-900 dark:shadow-green-900 sm:border md:border-0 print:shadow-none"
  >
    <div
      class="w-full cursor-pointer border-green-300 bg-green-100 py-1 text-center text-green-500 dark:bg-green-500 dark:text-green-100"
    >
      <span>{{ $t("Open Product") }}</span>
    </div>
    <div class="p-4">
      <CategorySelector v-model="category" />
    </div>
    <section class="-mt-[.1rem] px-4 pb-3">
      <p class="mb-2 truncate text-xs font-bold uppercase tracking-wide">
        <font-awesome-icon :icon="['fal', 'parachute-box']" class="mr-1" />
        {{ $t("producer") }}
      </p>
      <UIVSelect
        v-tooltip="$t('producer is automatically set to your prindustry account')"
        :model-value="producer"
        :options="[]"
        :label="$t('producer')"
        disabled
      />
    </section>
    <section
      class="flex cursor-pointer flex-col items-stretch border-b border-t px-4 pt-2 text-sm dark:border-gray-900"
    >
      <span
        class="mb-2 flex w-full items-center justify-between text-xs font-bold uppercase tracking-wide text-theme-500"
      >
        <span>
          <font-awesome-icon :icon="['fal', 'box-full']" />
          {{ $t("product specs") }}
        </span>
        <UIButton :icon="['fal', 'plus']" variant="default" @click="handleAddBoop">
          {{ $t("Add Spec") }}
        </UIButton>
      </span>
      <hr class="-mx-4 mb-3" />
      <div
        v-for="boop in boops"
        :key="boop.id"
        class="mb-2 grid grid-cols-[calc(50%_-_16px)_,_calc(50%_-_16px)_,_32px] items-center gap-2"
      >
        <BoxSelector v-model="boop.box" />
        <OptionsSelector
          v-model="boop.option"
          v-tooltip="!category?.id ? $t('Please select a category first') : null"
          :category-id="category.id"
          :disabled="!category?.id"
          use-api-search
        />
        <UIButton
          class="!h-6 !w-6 !text-xs"
          variant="danger"
          :icon="['fal', 'minus']"
          icon-class="ml-[1px]"
          @click="handleDeleteBoop(boop.id)"
        />
      </div>
    </section>
    <section
      class="flex cursor-pointer flex-col items-stretch border-b px-4 pt-2 text-sm transition-all duration-200 ease-linear dark:border-gray-900"
    >
      <div class="mb-2 grid grid-cols-2 items-center gap-2">
        <span>{{ $t("quantity") }}</span>
        <UIInputText
          v-model="quantity"
          type="number"
          placeholder="1"
          name="quantity"
          :rules="yup.number().integer().min(1)"
          :affix="['fal', 'arrow-up-wide-short']"
        />
      </div>
    </section>
    <section
      class="mb-2 flex cursor-pointer flex-col items-stretch border-b px-4 pt-2 text-sm transition-all duration-200 ease-linear dark:border-gray-900"
    >
      <div class="mb-2 grid grid-cols-2 items-center gap-2">
        <span>{{ $t("production days") }}</span>
        <UIInputText
          v-model="productionDays"
          type="number"
          placeholder="0"
          name="productionDays"
          :affix="[
            'fal',
            productionDays > 4 ? 'turtle' : productionDays < 3 ? 'rabbit-running' : 'sheep',
          ]"
        />
      </div>
    </section>

    <Form class="mt-auto">
      <section class="mb-3 px-4">
        <SalesReferenceInput v-model="reference" />
      </section>

      <section class="border-b px-4 pb-3">
        <SalesNoteInput v-model="notes" />
      </section>
    </Form>
    <section class="border-b p-[33px] px-6">
      <p class="text-center text-sm italic text-orange-500">
        <font-awesome-icon :icon="['fad', 'triangle-exclamation']" class="mr-1" />
        {{ $t("First save the product in order to upload a file") }}
      </p>
    </section>
    <section class="mt-3 px-4">
      <h2 class="mb-1 text-sm font-bold uppercase tracking-wide">
        {{ $t("price") }}
        <small>open_product</small>
      </h2>
      <UICurrencyInput
        v-model="price"
        input-class="input w-full p-1 px-2 text-sm"
        :class="{
          'border border-red-400': price <= 0,
        }"
        @blur="price = price < 0 ? price * -1 : price"
      />
    </section>
    <section class="my-3 px-4">
      <h2 class="mb-1 text-sm font-bold uppercase tracking-wide">
        {{ $t("VAT") }} <small>({{ $t("in %") }})</small>
      </h2>
      <UIInputText
        v-model="vatPercentage"
        name="vatPercentage"
        type="number"
        class="w-full text-sm"
        @blur="vatPercentage = vatPercentage < 0 ? vatPercentage * -1 : vatPercentage"
      />
    </section>
    <section class="mb-5 px-4">
      <h2 class="mb-1 text-sm font-bold uppercase tracking-wide">{{ $t("shipping") }}</h2>
      <UICurrencyInput
        v-model="shippingCost"
        input-class="w-full p-1 px-2 text-sm"
        @blur="shippingCost = shippingCost < 0 ? shippingCost * -1 : shippingCost"
      />
    </section>
    <section
      v-if="deliveryType === 'multiple'"
      class="flex items-center justify-between bg-theme-300 px-4 dark:bg-theme-700"
    >
      <p class="my-2 text-xs font-bold uppercase tracking-wide text-white">
        {{ $t("To add the delivery address, please save the product first.") }}
      </p>
    </section>
    <footer class="grid grid-cols-2 rounded-b text-xs text-themecontrast-900 shadow-inner">
      <button
        class="relative bg-green-500 px-2 py-3 text-xs text-green-100 transition-colors duration-75 hover:bg-green-500 hover:text-green-700 dark:bg-green-800 dark:text-green-100 dark:hover:bg-green-700"
        @click="handleAddProduct"
      >
        <font-awesome-icon :icon="['fal', 'copy']" />
        {{ $t("Save") }}
      </button>
      <button
        class="hover:bg-gred-500 relative bg-red-500 px-2 py-3 text-xs text-red-100 transition-colors duration-75 hover:text-red-700 dark:bg-red-800 dark:text-red-300 dark:hover:bg-red-700"
        @click="handleClose"
      >
        <font-awesome-icon :icon="['fal', 'trash']" />
        {{ $t("Cancel") }}
      </button>
    </footer>
  </div>
</template>

<script setup>
import { useStore } from "vuex";
import * as yup from "yup";
import SalesNoteInput from "~/components/sales/SalesNoteInput.vue";
import SalesReferenceInput from "~/components/sales/SalesReferenceInput.vue";

const { addToast } = useToastStore();
const { t: $t } = useI18n();

const emits = defineEmits(["onSaveProduct", "onClose"]);

const store = useStore();
const order = computed(() => store.state.orders.active_order_data);
const deliveryType = computed(() => order.value.delivery_type);

const category = ref({
  label: "",
  value: "",
});

const producer = ref(window.location.hostname);

const boops = ref([
  {
    box: {
      label: "",
      value: "",
    },
    option: {
      label: "",
      value: "",
    },
  },
]);

const quantity = ref(0);
const productionDays = ref(0);
const reference = ref("");
const notes = ref("");
const price = ref(0);
const vatPercentage = ref(0);
const shippingCost = ref(0);

function handleAddBoop() {
  const newBoop = {
    id: boops.value.length + 1,
    box: {
      label: "",
      value: "",
    },
    option: {
      label: "",
      value: "",
    },
  };
  boops.value = [...boops.value, newBoop];
}

function handleDeleteBoop(id) {
  boops.value = boops.value.filter((boop) => boop.id !== id);
}

async function handleAddProduct() {
  const newProduct = {
    type: "print",
    calculation_type: "open_calculation",
    product: boops.value.map((boop) => ({
      value_display_name: boop.option.label,
      key_display_name: boop.box.label,
      key: boop.box.slug,
      source_key: boop.box?.source_slug,
      key_id: boop.box.value,
      source_value: boop.option?.source_slug,
      value: boop.option.slug,
      value_id: boop.option.id || boop.option.linked,
      divider: null,
      dynamic: false,
      _: [],
    })),
    items: boops.value.map((boop) => ({
      value_display_name: boop.option.label,
      key_display_name: boop.box.label,
      key: boop.box.slug,
      source_key: boop.box?.source_slug,
      key_id: boop.box.value,
      source_value: boop.option?.source_slug,
      value: boop.option.slug,
      value_id: boop.option.id || boop.option.linked,
      divider: null,
      dynamic: false,
      _: [],
    })),
    category: { name: category.value.label },
    divided: true,
    quantity: quantity.value,
    calculation: [],
    price: {
      gross_price: price.value,
      pm: "",
      qty: quantity.value,
      dlv: {
        days: productionDays.value,
      },
      vat: vatPercentage.value,
    },
    shipping_cost: shippingCost.value,
    reference: reference.value,
    note: notes.value ?? null,
  };

  if (quantity.value === 0) {
    return addToast({
      message: $t("quantity must be greater than 0"),
      type: "error",
    });
  }
  emits("onSaveProduct", newProduct);
}

function handleClose() {
  emits("onClose");
}
</script>
