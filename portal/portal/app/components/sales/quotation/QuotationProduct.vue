<template>
  <article>
    <UICard
      class="flex h-full flex-col gap-3 !py-0"
      :shadow-color="getStatusShadow(props.item.status.code)"
      rounded-full
    >
      <SalesStatusIndicator
        :status="props.item.status.code"
        class="sticky top-0 z-10 rounded-b-none py-2"
        @update:status="handleUpdateStatus"
      />
      <h1
        v-tooltip="props.item.product.category.name"
        class="sticky top-8 z-10 -mt-3 truncate border-b bg-white px-4 py-3 font-bold capitalize dark:bg-gray-700"
      >
        <span class="text-sm text-gray-400">#{{ props.item.id }}</span>
        {{ props.item.product.category.name }}
      </h1>
      <!-- <hr class="dark:border-gray-800" /> -->
      <section class="px-4">
        <p class="mb-2 truncate text-xs font-bold uppercase tracking-wide">
          <font-awesome-icon :icon="['fal', 'parachute-box']" class="mr-1" />
          {{ $t("producer") }}
        </p>
        <UIVSelect
          v-if="isEditable"
          v-model="productDetails.supplier"
          :options="[]"
          :label="$t('producer')"
          :placeholder="$t('select producer')"
          disabled
        />
        <div v-else>
          <p class="text-sm font-bold">{{ productDetails.supplier }}</p>
        </div>
      </section>
      <hr class="dark:border-gray-800" />
      <section class="text-sm">
        <details :open="isEditable || isExternal">
          <summary
            class="-mb-3 -mt-4 bg-white px-4 pb-3 pt-4 transition hover:shadow dark:bg-gray-700 dark:hover:text-theme-400"
          >
            <div>
              <font-awesome-icon :icon="['fal', 'box-full']" class="mr-2" />
              <span>{{ $t("product specs") }}</span>
            </div>
          </summary>
        </details>
        <div data-details>
          <div
            v-for="(dividerGroup, key) in productDetails.options"
            :key="key"
            class="mt-2 px-4"
            :class="{
              'border-y border-gray-200 bg-gray-50 py-4 dark:border-gray-800 dark:bg-gray-600':
                key !== '' && key !== 'undefined' && key !== null,
            }"
          >
            <h2
              v-if="key !== '' && key !== 'undefined' && key !== null"
              class="mb-1 text-xs font-bold uppercase tracking-wide text-gray-800"
            >
              {{ key }}
            </h2>
            <ul :class="{ 'pb-2': key === '' && key !== 'undefined' && key !== null }">
              <li
                v-for="option in dividerGroup"
                :key="option.option_id"
                class="grid grid-cols-2 gap-2 break-words border-b-gray-200 dark:border-gray-800 [&:last-child]:pt-2 [&:not(:last-child)]:border-b [&:not(:last-child)]:py-2"
                :class="{ 'grid-cols-[1fr_,_2fr]': isExternal }"
              >
                <span>{{ $display_name(option.key_display_name) ?? option.key }}</span>
                <template v-if="isExternal && isEditable">
                  <UITextArea
                    v-model="option.value"
                    type="text"
                    :name="option.key"
                    :disabled="isEditDisabled || option.dynamic"
                    @blur="handleInstantSave('options')"
                  />
                </template>
                <template v-else>
                  <span class="font-bold">{{
                    $display_name(option.value_display_name) ?? option.value
                  }}</span>
                </template>
                <div v-if="option.value_dynamic" class="col-span-2 flex gap-3 text-gray-700">
                  <template
                    v-if="
                      option.value_dynamic_type === 'format' &&
                      option.value_height &&
                      option.value_width
                    "
                  >
                    <span
                      >{{ $t("height") }} ( {{ option.value_unit }} ) :
                      {{ option.value_height }}</span
                    >
                    <span
                      >{{ $t("width") }} ( {{ option.value_unit }} ) :
                      {{ option.value_width }}</span
                    >
                  </template>
                  <template v-if="option.value_dynamic_type === 'sides' && option.value_sides">
                    <span>{{ $t("sides") }} : {{ option.value_sides }}</span>
                  </template>
                  <template v-if="option.value_dynamic_type === 'pages' && option.value_pages">
                    <span>{{ $t("pages") }} : {{ option.value_pages }}</span>
                  </template>
                </div>
              </li>
            </ul>
          </div>
        </div>
      </section>
      <hr class="mt-auto dark:border-gray-800" />
      <section class="text-sm">
        <ul class="px-4">
          <li
            class="grid grid-cols-2 items-center break-words border-b border-b-gray-200 pb-2 dark:border-gray-800"
            :class="{ 'grid-cols-[1fr_,_2fr]': isExternal }"
          >
            <span class="truncate">{{ $t("quantity") }}</span>
            <div v-if="isEditable">
              <VDropdown v-model:shown="qtyNotCalculatedShow" :triggers="[]" placement="right">
                <UIInputText
                  v-model="productDetails.quantity"
                  type="number"
                  name="quantity"
                  min="1"
                  :value="props.item.qty"
                  placeholder="0"
                  :affix="
                    notCalculatedShown
                      ? ['fal', 'triangle-exclamation']
                      : ['fal', 'arrow-up-wide-short']
                  "
                  :disabled="isEditDisabled"
                  @blur="handleInstantSave('quantity')"
                />
                <template #popper>
                  <div class="max-w-96">
                    <div class="p-4 pb-2">
                      <UIButton
                        variant="neutral-light"
                        :icon="['fad', 'xmark']"
                        class="float-right mb-2 ml-2 !h-5"
                        @click="qtyNotCalculatedShow = false"
                      />
                      {{
                        //prettier-ignore
                        $t("This item has a fixed price and therefore the price will not be calculated by Prindustry. Please take extra care reviewing this item's price.")
                      }}
                    </div>
                    <div
                      class="flex w-full items-center justify-between rounded-lg bg-gray-100 px-4 py-2"
                    >
                      <label for="disableForever">{{ $t("Disable this message forever") }}</label>
                      <UISwitch
                        name="disableForever"
                        :value="disableForever"
                        @input="disableForever = $event"
                      />
                    </div>
                  </div>
                </template>
              </VDropdown>
            </div>
            <div v-else class="flex items-center font-bold">
              <span class="flex-1">{{ props.item.qty }}</span>
              <span>
                <font-awesome-icon :icon="['fal', 'arrow-up-wide-short']" />
              </span>
            </div>
          </li>
          <li
            class="grid grid-cols-2 items-center break-words pt-2"
            :class="{ 'grid-cols-[1fr_,_2fr]': isExternal }"
          >
            <span class="truncate">{{ $t("production days") }}</span>
            <div v-if="isEditable">
              <VDropdown v-model:shown="dlvNotCalculatedShow" :triggers="[]" placement="right">
                <UIInputText
                  v-model="productDetails.productionDays"
                  type="number"
                  name="productionDays"
                  min="0"
                  placeholder="0"
                  :affix="
                    notCalculatedShown
                      ? ['fal', 'triangle-exclamation']
                      : [
                          'fal',
                          productDetails.productionDays > 4
                            ? 'turtle'
                            : productDetails.productionDays < 3
                              ? 'rabbit-running'
                              : 'sheep',
                        ]
                  "
                  :disabled="isEditDisabled"
                  @blur="handleInstantSave('productionDays')"
                />
                <template #popper>
                  <div class="max-w-96">
                    <div class="p-4 pb-2">
                      <UIButton
                        variant="neutral-light"
                        :icon="['fad', 'xmark']"
                        class="float-right mb-2 ml-2 !h-5"
                        @click="dlvNotCalculatedShow = false"
                      />
                      {{
                        //prettier-ignore
                        $t("This item has a fixed price and therefore the price will not be calculated by Prindustry. Please take extra care reviewing this item's price.")
                      }}
                    </div>
                    <div
                      class="flex w-full items-center justify-between rounded-lg bg-gray-100 px-4 py-2"
                    >
                      <label for="disableForever">{{ $t("Disable this message forever") }}</label>
                      <UISwitch
                        name="disableForever"
                        :value="disableForever"
                        @input="disableForever = $event"
                      />
                    </div>
                  </div>
                </template>
              </VDropdown>
            </div>
            <div v-else class="flex items-center font-bold">
              <span class="flex-1">
                {{ productDetails.productionDays }}
              </span>
              <font-awesome-icon
                :icon="[
                  'fal',
                  productDetails.productionDays > 4
                    ? 'turtle'
                    : productDetails.productionDays < 3
                      ? 'rabbit-running'
                      : 'sheep',
                ]"
              />
            </div>
          </li>
        </ul>
      </section>
      <hr class="dark:border-gray-800" />
      <section class="px-4">
        <SalesReferenceInput
          v-model="productDetails.reference"
          class="mb-4"
          :disabled="
            isEditDisabled ||
            !hasAllPermissions(['quotations-items-reference-update', 'quotations-items-update'])
          "
          @blur="handleInstantSave('reference')"
        />
        <SalesNoteInput
          v-model="productDetails.note"
          :disabled="
            isEditDisabled ||
            !hasAllPermissions(['quotations-items-note-update', 'quotations-items-update'])
          "
          @blur="handleInstantSave('note')"
        />
      </section>
      <hr class="dark:border-gray-800" />
      <section class="px-4">
        <OrderFiles
          v-if="!loading && permissions.includes('quotations-items-media-list')"
          class="my-2"
          type="items"
          order-type="quotation"
          :object="productDetails"
          :order_id="Number(salesId)"
          :index="Math.random() * 100"
          :editable="isEditable"
          :ext_connection="isExternal"
          :prop-driven="true"
          :disabled="isEditDisabled || !permissions.includes('quotations-items-media-update')"
          :center-text="true"
          @on-file-uploaded="handleFileUploaded"
          @on-remove-file="handleRemoveFile"
        />
      </section>
      <hr class="dark:border-gray-800" />
      <section class="px-4">
        <template v-if="isEditable">
          <h2 class="mb-1 flex items-end justify-between text-sm font-bold uppercase tracking-wide">
            {{ $t("price") }}
            <small>{{
              props.item.product.calculation_type === "full_calculation"
                ? $t("full calculation")
                : props.item.product.calculation_type === "semi_calculation"
                  ? $t("semi calculation")
                  : $t("fixed price")
            }}</small>
          </h2>
          <UICurrencyInput
            :model-value="
              productDetails.calculated ? item.product.price.selling_price_ex : productDetails.price
            "
            class="rounded border border-gray-200 bg-gray-200 text-sm transition dark:border-gray-500 dark:bg-gray-500"
            :input-class="{
              'py-1': true,
              'border border-red-400': props.modelValue <= 0,
            }"
            :disabled="productDetails.calculated || isEditDisabled"
            @update:model-value="productDetails.price = $event"
            @blur="handleInstantSave('price')"
          >
            <template
              v-if="
                props.item.product.calculation_type === 'full_calculation' ||
                props.item.product.calculation_type === 'semi_calculation'
              "
              #affix
            >
              <UISwitch
                v-tooltip="
                  $t('When this switch is enabled, the price will be calculated by the system.')
                "
                :value="productDetails.calculated"
                :name="`calculated-${props.item.id}`"
                :disabled="isEditDisabled"
                @input="productDetails.calculated = $event"
              />
            </template>
          </UICurrencyInput>
        </template>
        <template v-else>
          <div class="flex">
            <h2 class="flex-1 text-sm font-bold uppercase tracking-wide">
              {{ $t("price") }}
              <small
                v-if="permissions.includes('quotations-update')"
                class="normal-case text-gray-600"
              >
                (
                {{
                  props.item.product.calculation_type === "full_calculation"
                    ? $t("full calculation")
                    : props.item.product.calculation_type === "semi_calculation"
                      ? $t("semi calculation")
                      : $t("fixed price")
                }}
                )
              </small>
            </h2>

            <VDropdown offset="4" placement="top">
              <!-- This will be the popover target (for the events and position) -->
              <button
                class="-mr-2 ml-4 rounded-full px-2 text-sm text-themecontrast-400 hover:bg-theme-100"
              >
                <span class="font-mono text-base font-bold text-theme-500">
                  {{ props.item.product.price.display_p }}
                </span>
              </button>
              <!-- This will be the content of the popover -->
              <template #popper>
                <ol
                  class="min-w-60 rounded-md p-4 text-sm shadow-md shadow-gray-200 dark:border-gray-900 dark:bg-gray-700 dark:text-theme-50 dark:shadow-black"
                >
                  <li class="flex justify-between py-1">
                    {{ $t("price per piece") }}:
                    <b class="ml-4 font-mono">{{ props.item.product.price.display_ppp }}</b>
                  </li>
                  <li class="flex justify-between border-b py-1 dark:border-gray-900">
                    {{ $t("quantity") }}:
                    <b class="ml-4 font-mono">x {{ props.item.product.price.qty }}</b>
                  </li>
                  <li class="flex justify-between py-1">
                    {{ $t("subtotal") }}:
                    <b class="ml-4 font-mono">{{
                      props.item.product.price.display_selling_price_ex
                    }}</b>
                  </li>
                  <li class="flex justify-between border-b py-1 dark:border-gray-900">
                    {{ $t("vat") }} {{ props.item.product.price.vat }}% :
                    <b class="ml-4 font-mono">{{ props.item.product.price.display_vat_p }}</b>
                  </li>
                  <li v-if="false" class="flex justify-between py-1">
                    {{ $t("margins") }}:
                    <b class="ml-4 font-mono">{{ props.item.product.price.margins }}</b>
                  </li>
                  <li v-if="false" class="flex justify-between border-b py-1 dark:border-gray-900">
                    {{ $t("discounts") }}:
                    <b class="ml-4 font-mono">{{ props.item.product.price.discount }}</b>
                  </li>
                  <li class="mt-4 flex justify-between py-1 font-bold">
                    {{ $t("total") }}:
                    <b class="ml-4 font-mono">{{
                      props.item.product.price.display_selling_price_inc
                    }}</b>
                  </li>
                  <li class="flex justify-between py-1 font-bold">
                    {{ $t("profit") }}:
                    <b class="ml-4 font-mono text-green-500">{{
                      props.item.product.price.display_profit
                    }}</b>
                  </li>
                </ol>
              </template>
            </VDropdown>
          </div>
        </template>
      </section>
      <section class="px-4">
        <template v-if="isEditable">
          <h2 class="mb-1 text-sm font-bold uppercase tracking-wide">
            {{ $t("VAT") }} <small>({{ $t("in %") }})</small>
          </h2>
          <UIInputText
            :model-value="productDetails.vatPercentage"
            name="vatPercentage"
            type="number"
            class="w-full text-sm"
            :disabled="isEditDisabled"
            @update:model-value="productDetails.vatPercentage = $event"
            @blur="handleInstantSave('vatPercentage')"
          />
        </template>
        <template v-else>
          <div class="flex">
            <h2 class="flex-1 text-xs font-bold uppercase tracking-wide">{{ $t("VAT") }}</h2>
            <span class="font-mono text-sm font-bold"> {{ productDetails.vatPercentage }}% </span>
          </div>
        </template>
      </section>
      <section class="mb-2 px-4">
        <template v-if="isEditable">
          <h2 class="mb-1 text-sm font-bold uppercase tracking-wide">{{ $t("shipping") }}</h2>
          <UICurrencyInput
            v-model="productDetails.shippingCost"
            input-class="py-1 text-sm"
            :disabled="isEditDisabled"
            @blur="handleInstantSave('shippingCost')"
          />
        </template>
        <template v-else>
          <div class="flex">
            <h2 class="flex-1 text-xs font-bold uppercase tracking-wide">{{ $t("shipping") }}</h2>
            <span class="font-mono text-sm font-bold">
              {{ money.getCurrencySymbol() }}
              {{ (Number(productDetails.shippingCost) / 100).toFixed(2).replace(".", ",") }}
            </span>
          </div>
        </template>
      </section>
      <section v-if="multipleAddresses" class="-mb-3 bg-theme-300 p-4 text-themecontrast-800">
        <SalesDeliverySelector
          :overview-class="{
            'bg-theme-400 text-themecontrast-900': isEditable,
            'bg-theme-300 text-themecontrast-800': !isEditable,
          }"
          button-class="hidden"
          :team="chosenTeam"
          :method="productDetails.deliveryMethod"
          :address="productDetails.deliveryAddress"
          :only-show="!permissions.includes('quotations-items-addresses-update')"
          :disabled="isEditDisabled"
          @update:team="chosenTeam = $event"
          @update:method="productDetails.deliveryMethod = $event"
          @update:address="productDetails.deliveryAddress = $event"
        />
      </section>
      <footer
        v-if="isEditable && !isExternal"
        class="grid grid-cols-3 rounded-b bg-theme-400 text-xs text-themecontrast-900 shadow-inner dark:bg-theme-800"
      >
        <button
          class="relative px-2 py-3 text-xs dark:text-theme-100"
          :class="{
            'transition-colors duration-75 hover:bg-theme-500 hover:text-themecontrast-700 dark:hover:bg-theme-700':
              permissions.includes('quotations-items-create'),
            'cursor-not-allowed opacity-50': !permissions.includes('quotations-items-create'),
          }"
          :disabled="
            saving ||
            productDetails.options.length === 0 ||
            !permissions.includes('quotations-items-create')
          "
          @click="emit('onDuplicateItem')"
        >
          <font-awesome-icon :icon="['fal', 'copy']" />
          {{ $t("duplicate") }}
        </button>
        <button
          class="relative px-2 py-3 text-xs dark:text-theme-100"
          :class="{
            'transition-colors duration-75 hover:bg-theme-500 hover:text-themecontrast-700 dark:hover:bg-theme-700':
              hasAllPermissions(['quotations-items-update', 'quotations-items-st-update']),
            'cursor-not-allowed opacity-50': !hasAllPermissions([
              'quotations-items-update',
              'quotations-items-st-update',
            ]),
          }"
          :disabled="
            saving || !hasAllPermissions(['quotations-items-update', 'quotations-items-st-update'])
          "
          @click="
            handleUpdateStatus(
              props.item.status.code === statusMap.CANCELED ? statusMap.NEW : statusMap.CANCELED,
            )
          "
        >
          <font-awesome-icon
            :icon="['fal', props.item.status.code === statusMap.CANCELED ? 'check' : 'xmark']"
          />
          {{ props.item.status.code === statusMap.CANCELED ? $t("uncancel") : $t("cancel") }}
        </button>
        <button
          class="relative px-2 py-3 text-xs text-red-300/50 transition-colors duration-75 dark:text-red-300"
          :class="{
            'cursor-not-allowed': isEditDisabled,
            '!text-red-400 hover:bg-theme-500 hover:text-themecontrast-700 dark:hover:bg-theme-700':
              permissions.includes('quotations-items-delete'),
          }"
          :disabled="saving || !permissions.includes('quotations-items-delete')"
          @click="emit('onRemoveItem')"
        >
          <font-awesome-icon :icon="['fal', 'trash']" />
          {{ $t("remove") }}
        </button>
      </footer>
    </UICard>
  </article>
</template>

<script setup>
import { isEqual } from "lodash";
import debounce from "debounce-promise";

const props = defineProps({
  item: {
    type: Object,
    required: true,
  },
  salesId: {
    type: [Number, String],
    required: true,
  },
});

const emit = defineEmits([
  "onDuplicateItem",
  "onRemoveItem",
  "on-item-updated",
  "on-new-address",
  "onCancelItem",
]);

const money = useMoney();
const config = useRuntimeConfig();
const { addToast } = useToastStore();
const { handleError } = useMessageHandler();
const { hasAllPermissions } = usePermissions();
const { permissions } = storeToRefs(useAuthStore());
const { getStatusColor, statusMap, getStatusShadow } = useOrderStatus();
const {
  isExternal,
  saving,
  multipleAddresses,
  isUsingTeamAddresses,
  isEditable: _isEditable,
  pickupAddresses,
} = storeToRefs(useSalesStore());

const quotationRepository = useQuotationRepository();
const { t: $t } = useI18n();

const isEditable = computed(
  () => _isEditable.value && permissions.value.includes("quotations-items-update"),
);

const productDetails = ref({
  id: null,
  supplier: null,
  quantity: null,
  productionDays: null,
  reference: null,
  note: null,
  calculated: false,
  price: null,
  shippingCost: 0,
  vatPercentage: 0,
  attachments: null,
  deliveryMethod: "delivery",
  deliveryAddress: null,
  options: [],
});
const watchedProductDetails = computed(() => JSON.parse(JSON.stringify(productDetails.value)));

const loading = ref(true);
const noDeliveryDate = ref(false);
const notCalculatedShown = ref(false);
const dlvNotCalculatedShow = ref(false);
const qtyNotCalculatedShow = ref(false);
const disableForever = ref(localStorage.getItem("disableForever") === "true");
watch(disableForever, (newValue) => {
  localStorage.setItem("disableForever", newValue);
});
const chosenTeam = ref(null);

const isEditDisabled = computed(() => {
  return props.item.status.code === statusMap.CANCELED;
});

const setProductDetails = async () => {
  noDeliveryDate.value = props.item.delivery_date === null || props.item.type === "custom";
  productDetails.value.calculated =
    (props.item.product.calculation_type === "full_calculation" &&
      props.item.product.price.id !== null) ||
    (props.item.product.calculation_type === "semi_calculation" &&
      props.item.product.price.id !== null);
  productDetails.value.id = props.item.id;
  productDetails.value.supplier = props.item.product.external_name;
  productDetails.value.quantity = props.item.qty ?? 1;
  productDetails.value.productionDays = props.item.product.price?.dlv?.days ?? 0;
  productDetails.value.reference = props.item.reference;
  productDetails.value.note = props.item.note;
  productDetails.value.price = props.item.product.price?.selling_price_ex
    ? props.item.product.price?.selling_price_ex
    : 0;
  productDetails.value.vatPercentage = props.item.product.price?.vat ?? 0;
  productDetails.value.shippingCost = props.item.shipping_cost ?? 0;
  productDetails.value.attachments = props.item.attachments;
  productDetails.value.deliveryMethod = props.item.delivery_pickup ? "pickup" : "delivery";
  if (props.item.delivery_address) {
    chosenTeam.value = isUsingTeamAddresses ? props.item.delivery_address.team_id : null;
    productDetails.value.deliveryAddress = props.item.delivery_address;
  }
  productDetails.value.options = groupByDivider(
    props.item.product.items,
    props.item.product.divided,
  );

  await nextTick();
  loading.value = false;
};
const groupByDivider = (array = [], divided, options = {}) => {
  if (!Array.isArray(array)) {
    return {};
  }
  const { dividerKey = "key_divider", defaultDivider = "" } = options;

  const grouped = array.reduce((acc, item) => {
    const divider = divided ? item[dividerKey] : defaultDivider;

    if (!acc[divider]) {
      acc[divider] = [];
    }

    acc[divider].push(item);

    return acc;
  }, {});

  return grouped;
};

watch(
  () => props.item,
  () => {
    setProductDetails();
  },
  { immediate: true, deep: true },
);

function handleFileUploaded(response) {
  productDetails.value.attachments = [...productDetails.value.attachments, response];
}

function handleRemoveFile(id) {
  productDetails.value.attachments = productDetails.value.attachments.filter(
    (file) => file.id !== id,
  );
  addToast({
    type: "success",
    message: $t("file succesfully removed from product"),
  });
}

async function handleUpdateStatus(status) {
  try {
    saving.value = true;
    await quotationRepository.updateItem({
      quotationId: props.salesId,
      itemId: props.item.id,
      data: {
        st: status,
      },
    });
    addToast({
      type: "success",
      message: $t("the item status has been successfully updated."),
    });
    emit("on-item-updated");
  } catch (error) {
    handleError(error);
  } finally {
    saving.value = false;
  }
}

const { data: contexts } = await useLazyAPI("/contexts", { transform: ({ data }) => data });

watch(
  () => productDetails.value.deliveryMethod,
  async (newValue, oldValue) => {
    if (loading.value) return;
    if (newValue === oldValue) return;

    productDetails.value.deliveryAddress = null;

    //   if (newValue === "pickup") {
    //     const mgrContext = contexts.value?.find((item) => item.name === "mgr");
    //     if (!mgrContext) {
    //       console.error("Mgr context not found");
    //       return;
    //     }
    //     pickupAddresses.value = await quotationRepository.getPickupAddresses(mgrContext.id);
    //     return;
    //   }
  },
);

const showNotCalculated = (type) => {
  if (type === "dlv") dlvNotCalculatedShow.value = true;
  if (type === "qty") qtyNotCalculatedShow.value = true;
  notCalculatedShown.value = true;
};
const debounceShowNotCalculated = debounce(showNotCalculated, 1200);
watch(
  () => productDetails.value.quantity,
  (newValue, oldValue) => {
    if (loading.value || newValue === oldValue) return;
    if (productDetails.value.calculated) return;
    if (notCalculatedShown.value || disableForever.value) return;
    debounceShowNotCalculated("qty");
  },
);
watch(
  () => productDetails.value.productionDays,
  (newValue, oldValue) => {
    if (loading.value || newValue === oldValue) return;
    if (productDetails.value.calculated) return;
    if (notCalculatedShown.value || disableForever.value) return;
    debounceShowNotCalculated("dlv");
  },
);
watch(
  () => productDetails.value.calculated,
  (newValue) => {
    if (!newValue) {
      notCalculatedShown.value = true;
    }
    if (newValue) {
      // Trigger a recalculate
      addToast({
        type: "info",
        message: $t("We're recalculating the price for you."),
      });
      update({
        product: {
          price: { qty: productDetails.value.quantity, vat: productDetails.value.vatPercentage },
        },
      });
    }
  },
);

watch(
  () => multipleAddresses.value,
  (newValue, oldValue) => {
    if (loading.value || newValue === oldValue) return;
    if (!newValue) {
      productDetails.value.deliveryAddress = null;
    }
  },
);

/**
 * The business logic for saving the quotationDetails.
 * Please keep everything below this ONLY for the business
 * logic regarding saving.
 */
const update = async (data) => {
  try {
    saving.value = true;
    const updatedItem = await quotationRepository.updateItem({
      quotationId: props.salesId,
      itemId: props.item.id,
      data: {
        ...data,
      },
    });
    emit("on-item-updated", updatedItem);
  } catch (error) {
    handleError(error);
    await setProductDetails();
  } finally {
    saving.value = false;
  }
};
const { debounced: debouncedUpdate, execute: executeUpdate } = useDebounce(
  update,
  config.public.formSaveDebounceTime,
);
const handleInstantSave = () => executeUpdate();
const updateWithSaver = (data, useDebounce = false) =>
  useDebounce ? debouncedUpdate(data) : update(data);

watch(
  watchedProductDetails,
  (newValue, oldValue) => {
    if (loading.value) return;

    let updatedProperty = null;
    for (const key in newValue) {
      if (!isEqual(newValue[key], oldValue[key])) {
        updatedProperty = { key, value: newValue[key] };
        break;
      }
    }
    if (updatedProperty === null) return;

    if (updatedProperty.key === "quantity") {
      if (newValue.quantity <= 0) {
        return addToast({
          type: "error",
          message: $t("quantity cannot be negative or zero"),
        });
      }

      const updateObject = {
        product: {
          price: {
            qty: newValue.quantity,
            vat: newValue.vatPercentage,
          },
        },
      };

      if (!productDetails.value.calculated) {
        updateObject.product.price.gross_price = productDetails.value.price;
      }

      updateWithSaver(updateObject, true);
    }

    if (updatedProperty.key === "productionDays") {
      if (newValue.productionDays < 0) {
        return addToast({
          type: "error",
          message: $t("production days cannot be negative"),
        });
      }

      const updateObject = {
        product: {
          price: {
            dlv: {
              days: newValue.productionDays,
            },
            vat: newValue.vatPercentage,
          },
        },
      };

      if (!productDetails.value.calculated) {
        updateObject.product.price.gross_price = productDetails.value.price;
      }

      updateWithSaver(updateObject, true);
    }

    if (updatedProperty.key === "reference") {
      updateWithSaver(
        {
          reference: newValue.reference,
        },
        true,
      );
    }

    if (updatedProperty.key === "note") {
      updateWithSaver(
        {
          note: newValue.note,
        },
        true,
      );
    }

    if (updatedProperty.key === "price" && !productDetails.value.calculated) {
      updateWithSaver(
        {
          product: {
            price: {
              gross_price: newValue.price < 0 ? newValue.price * -1 : newValue.price,
            },
          },
        },
        true,
      );
    }

    if (updatedProperty.key === "vatPercentage") {
      const updateObject = {
        product: {
          price: {
            vat: newValue.vatPercentage < 0 ? newValue.vatPercentage * -1 : newValue.vatPercentage,
          },
        },
      };

      if (!productDetails.value.calculated) {
        updateObject.product.price.gross_price = productDetails.value.price;
      }

      updateWithSaver(updateObject, true);
    }

    if (updatedProperty.key === "shippingCost") {
      updateWithSaver(
        {
          shipping_cost:
            newValue.shippingCost < 0 ? newValue.shippingCost * -1 : newValue.shippingCost,
        },
        true,
      );
    }

    if (updatedProperty.key === "deliveryMethod") {
      updateWithSaver({
        delivery_pickup: newValue.deliveryMethod === "pickup",
      });
    }

    if (updatedProperty.key === "deliveryAddress") {
      if (!newValue.deliveryAddress) return;
      quotationRepository.addAddressToItem({
        quotationId: props.salesId,
        itemId: props.item.id,
        data: {
          address: newValue.deliveryAddress.id,
        },
      });
    }

    /**
     * FOR EXTERNAL FLOW
     * Because in the repository we group the options by the divider,
     * We have to do some extra work to update the correct option.
     * Feel free to refactor this to something that makes more sense.
     */
    if (updatedProperty.key === "options") {
      updateWithSaver(
        {
          product: {
            product: productDetails.value.options,
          },
        },
        true,
      );
    }
  },
  { deep: true },
);
</script>

<style lang="scss" scoped>
.v-popper--shown {
  div[data-popper-shown] {
    @apply z-[51] drop-shadow-2xl;
  }

  &:before {
    content: "";
    @apply absolute left-0 top-0 z-50 h-full w-full bg-gray-900/20;
  }
}
</style>
