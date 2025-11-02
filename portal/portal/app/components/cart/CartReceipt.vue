<template>
  <section>
    <header class="flex w-full items-center justify-between">
      <p class="font-bold capitalize">{{ $t("receipt") }}</p>
    </header>

    <div class="mt-4 w-full">
      <p class="flex text-sm font-bold capitalize tracking-wide">
        {{ $t("price") }}
      </p>

      <div class="block">
        <template v-for="lineitem in cart_products" :key="'product-' + lineitem.id">
          <!-- {{ lineitem.variation.category.name }} -->
          <p class="flex items-center justify-between">
            <VDropdown offset="4" placement="left">
              <div class="flex flex-1 cursor-pointer items-center">
                <div
                  class="flex w-40 flex-1 items-center justify-start truncate pr-2 text-theme-500 hover:underline"
                >
                  <div class="w-10 text-sm text-gray-400">{{ lineitem.qty }}</div>
                  {{ lineitem.name ?? lineitem.variation?.category?.name }}
                </div>
              </div>
            </VDropdown>
            <span class="flex-1 whitespace-nowrap">
              {{ lineitem.display_subtotal }}
            </span>
          </p>
        </template>
        <hr class="my-2 dark:border-black" />

        <p v-if="cart.subtotal" class="mt-4 flex justify-between font-bold capitalize">
          {{ $t("subtotal") }}
          <span class="">
            {{ cart.subtotal }}
          </span>
        </p>
        <p v-if="cart.vat" class="mt-4 flex justify-between font-bold capitalize">
          {{ $t("vat") }}
          <span class="">
            {{ cart.vat }}
          </span>
        </p>
        <hr class="my-2 dark:border-black" />
      </div>

      <p v-if="cart.total" class="mt-4 flex justify-between text-xl font-bold sm:mt-0 xl:mt-4">
        {{ $t("total") }}
        <span>
          {{ cart.total }}
        </span>
      </p>
    </div>
  </section>
</template>

<script>
import { mapState } from "vuex";
export default {
  setup() {
    const authStore = useAuthStore();
    return { authStore };
  },
  data() {
    return {
      orderToOffer: false,
      vat: 21,

      // currency settings
      iso: "nl-NL",
      // currency: this.authStore.settings.data.find((setting) => setting.key === "currency").value,
      currency: this.authStore.currencySettings,
    };
  },
  computed: {
    ...mapState({
      cart: (state) => state.cart.cart,
      cart_products: (state) => state.cart.cart_products,
    }),
  },
  methods: {
    currencyFormatter(number) {
      if (number) {
        return new Intl.NumberFormat(this.iso, {
          style: "currency",
          currency: this.currency,
        }).format(number);
      }

      return "";
    },
  },
};
</script>
