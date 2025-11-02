<template>
  <div class="flex flex-col gap-4 px-1">
    <div
      class="sticky top-0 grid grid-cols-2 items-end border-b bg-gray-100 pb-4 dark:border-black dark:bg-gray-800"
    >
      <span class="text-sm font-bold uppercase tracking-wide">{{ $t("receipt") }}</span>
      <div class="text-right">
        <UIButton
          :icon="['fal', 'circle-info']"
          variant="neutral-light"
          icon-placement="right"
          @click="priceInfo = !priceInfo"
        >
          <span class="hidden sm:inline">{{ $t("check price info") }}</span>
        </UIButton>
      </div>
    </div>

    <section v-if="props.items.length">
      <h2 class="mb-1 text-xs font-bold uppercase tracking-wide">{{ $t("products") }}</h2>
      <ul :class="{ 'space-y-2': priceInfo }">
        <transition-group name="list">
          <li
            v-for="item in props.items"
            :key="item.id"
            class="grid grid-cols-2 items-end"
            :class="{
              'rounded border bg-white px-4 py-2 shadow-md dark:border-black dark:bg-gray-700':
                priceInfo,
            }"
          >
            <SalesProductPreview :item="item" class="-ml-2" button-class="text-left" />
            <span class="text-right font-mono text-sm">
              {{ item.product?.price?.display_selling_price_ex ?? "NaN" }}
            </span>

            <div v-if="!priceInfo" class="ps-3 text-left font-mono text-[10px]">
              - {{ $t("Shipping Cost") }}
            </div>
            <span v-if="!priceInfo" class="text-right font-mono text-[11px]">{{
              item.display_shipping_cost
            }}</span>

            <!-- price details for reseller/supplier -->
            <div v-show="priceInfo" class="col-span-2 mt-2 grid grid-cols-2 items-end gap-y-1">
              <span class="text-xs">
                {{ $t("price per piece") }}
              </span>
              <span class="text-right font-mono text-sm">
                {{ item.product?.price?.display_ppp ?? "NaN" }}
              </span>
              <div class="text-left font-mono text-[10px]">
                {{ $t("Shipping Cost") }}
              </div>
              <span class="text-right font-mono text-[11px]">{{ item.display_shipping_cost }}</span>
              <span class="text-xs">
                {{ $t("quantity") }}
              </span>
              <span class="border-b text-right font-mono text-sm dark:border-gray-900">
                {{ item.product?.price?.qty ?? "NaN" }} x
              </span>
              <span class="text-xs font-bold">
                {{ $t("subtotal") }}
              </span>
              <span class="text-right font-mono text-sm font-bold">
                {{ item.product?.price?.display_selling_price ?? "NaN" }}
              </span>
              <template v-if="false">
                <span class="text-xs">
                  {{ $t("margin") }}
                </span>
                <span class="text-right font-mono text-sm">
                  {{ item.product?.price?.margins ?? "NaN" }}
                </span>

                <span class="text-xs">
                  {{ $t("discount") }}
                </span>
                <span class="text-right font-mono text-sm">
                  {{ item.product?.price?.discount ?? "NaN" }}
                </span>
              </template>
              <span class="text-xs">
                {{ $t("vat") }} <span class="text-gray-500"> {{ item.product?.price?.vat }}% </span>
              </span>
              <span class="border-b text-right font-mono text-sm dark:border-gray-900">
                {{ item.product?.price?.display_vat_total_p ?? "NaN" }}
              </span>
              <span class="text-sm font-bold">
                {{ $t("total") }}
              </span>
              <span class="text-right font-mono font-bold">
                {{ item.product?.price?.display_selling_price_total ?? "NaN" }}
              </span>
              <span class="text-xs font-bold">
                {{ $t("profit") }}
              </span>
              <span class="text-right font-mono text-sm font-bold text-green-500">
                {{ item.product?.price?.display_profit ?? "NaN" }}
              </span>
            </div>
          </li>
        </transition-group>
      </ul>
    </section>

    <section v-if="props.services.length">
      <h2 class="mb-1 text-xs font-bold uppercase tracking-wide">{{ $t("services") }}</h2>
      <ul>
        <li
          v-for="service in props.services"
          :key="service.id"
          class="grid grid-cols-2 items-center"
        >
          <div>
            <UIButton variant="outline" class="-ml-2 !justify-start">
              {{ service.qty }}x {{ service.name }}
            </UIButton>
          </div>
          <span class="text-right font-mono text-sm">
            {{ service.display_total_service_price }}
          </span>
        </li>
      </ul>
    </section>

    <hr v-if="props.items.length || props.services.length" class="dark:border-black" />

    <section class="grid grid-cols-2 items-end">
      <div>
        <span class="text-sm font-bold uppercase tracking-wide">{{ $t("shipping") }}</span>
      </div>
      <span class="text-right font-mono text-sm"> {{ props.shippingCosts }} </span>
    </section>

    <section v-if="props.items.length || props.services.length" class="grid grid-cols-2">
      <h3 class="mb-1 text-sm font-bold uppercase tracking-wide">{{ $t("subtotal") }}</h3>
      <span class="text-right font-mono text-sm"> {{ props.subtotal }} </span>
    </section>

    <hr v-if="props.items.length || props.services.length" class="dark:border-black" />

    <section v-if="vat.length">
      <h2 class="mb-1 text-xs font-bold uppercase tracking-wide">{{ $t("VAT") }}</h2>
      <ul>
        <li v-for="vatItem in vat" :key="vatItem.vat_percentage" class="grid grid-cols-2">
          <div>
            <UIButton variant="outline" class="-ml-2 mb-1 flex items-center capitalize">
              {{ vatItem.vat_percentage }}%
            </UIButton>
          </div>
          <span class="text-right font-mono text-sm"> {{ vatItem.total_vat_display }} </span>
        </li>
      </ul>
    </section>

    <section class="grid grid-cols-2 items-end">
      <div>
        <span class="text-sm font-bold uppercase tracking-wide">{{ $t("total vat") }}</span>
      </div>
      <span class="text-right font-mono text-sm"> {{ props.totalVat }} </span>
    </section>

    <hr v-if="vat.length" class="dark:border-black" />

    <section
      v-if="(props.items.length || props.services.length) && priceInfo && false"
      class="grid grid-cols-2 items-center"
    >
      <h3 class="mb-1 text-sm font-bold uppercase tracking-wide">{{ $t("profit") }}</h3>
      <span class="text-right font-mono text-sm"> {{ props.profit }} </span>
    </section>

    <section
      class="sticky bottom-0 grid grid-cols-2 bg-gray-100 dark:bg-gray-800"
      :class="{ 'border-t-2 pt-2 dark:border-black': priceInfo }"
    >
      <div>
        <h2 class="font-bold uppercase tracking-wide">{{ $t("total") }}</h2>
      </div>
      <span class="mt-[1px] text-right font-mono font-bold"> {{ props.total }} </span>
    </section>
  </div>
</template>

<script setup>
const props = defineProps({
  items: {
    type: Array,
    required: true,
  },
  services: {
    type: Array,
    required: true,
  },
  subtotal: {
    type: String,
    required: true,
  },
  shippingCosts: {
    type: String,
    required: true,
  },
  vat: {
    type: Array,
    required: true,
  },
  total: {
    type: String,
    required: true,
  },
  totalVat: {
    type: String,
    required: true,
  },
});

const priceInfo = ref(false);
</script>
