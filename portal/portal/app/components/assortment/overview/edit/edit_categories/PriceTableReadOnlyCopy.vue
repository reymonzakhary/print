<template>
  <div>
    <div v-if="tables" class="columns is-multiline">
      <div class="column">
        <div v-for="(table, printingMethod, i) in tables" :key="printingMethod">
          <p class="capitalize">
            <font-awesome-icon
              v-if="printingMethod === 'offset'"
              :icon="['fal', 'toilet-paper-blank']"
            />
            <font-awesome-icon v-if="printingMethod === 'digital'" :icon="['fal', 'print']" />
            {{ printingMethod }}
          </p>

          <div v-if="i == 0" class="is-6 columns">
            <div
              v-for="(setting, key) in view_settings"
              :key="setting.id"
              class="column has-background-light has-margin-left-10 has-margin-right-10"
            >
              <div class="field is-horizontal">
                <p class="has-text-weight-bold">{{ setting.name }}</p>
                <span class="control has-margin-left-10">
                  <label :for="key">No</label>
                  <input
                    :id="key"
                    v-model="setting.value"
                    type="checkbox"
                    :name="key"
                    class="switch is-thin is-rounded is-info"
                    @change="updateColSpan()"
                  />
                  <label :for="key">{{ $t("yes") }}</label>
                </span>
              </div>
            </div>
          </div>

          <table
            class="table is-bordered is-hoverable is-narrow is-full-width has-margin-bottom-25"
          >
            <thead>
              <tr class="has-background-dark">
                <th class="has-text-white">Run</th>

                <th class="has-text-white" :colspan="colspan">
                  <span class="icon is-small">
                    <font-awesome-icon class :icon="['fal', 'rabbit']" />
                  </span>
                  {{ $t("normal") }}
                </th>

                <th class="has-text-white" :colspan="colspan">
                  <span class="icon is-small">
                    <font-awesome-icon class :icon="['fal', 'rabbit-fast']" />
                  </span>
                  {{ $t("express") }}
                </th>
                <th class="has-text-white" :colspan="colspan">
                  <span class="icon is-small">
                    <font-awesome-icon class :icon="['fal', 'dragon']" />
                  </span>
                  {{ $t("next day") }}
                </th>
              </tr>
              <tr class="has-background-light">
                <template v-for="i in 3">
                  <th v-if="i === 1" :key="i" />
                  <!-- NORMAL -->
                  <!-- Gross price -->
                  <th v-if="view_settings.gross_price.value" :key="i">
                    {{ $t("gross price") }}
                  </th>

                  <!-- Buying price -->
                  <th v-if="view_settings.buying_price.value" :key="i">
                    {{ $t("buying price") }}
                    <small class="tag is-success is-light">
                      {{ $t("gross price") }} - {{ $t("discount") }}
                    </small>
                  </th>

                  <!-- Selling price -->
                  <th v-if="view_settings.selling_price.value" :key="i">
                    {{ $t("selling price") }}
                    <small class="tag is-info is-light">
                      {{ $t("gross price") }} + {{ $t("margin") }}
                    </small>
                  </th>

                  <!-- Profist -->
                  <th v-if="view_settings.profit.value" :key="i">
                    {{ $t("profit") }}
                  </th>
                </template>
              </tr>
            </thead>

            <tbody>
              <tr v-for="(price, run) in table" :key="run">
                <!-- print for price.normal, price.express, price.tomorrow -->
                <template v-for="(delivery_value, delivery_type, i) in price">
                  <th v-if="delivery_type === 'normal'" :key="i">
                    {{ run }}
                  </th>

                  <!-- Gross price -->
                  <td v-if="view_settings.gross_price.value" :key="i">
                    {{ currencyFormatter(delivery_value.gross_price) }}
                  </td>

                  <!-- Buying price -->
                  <td v-if="view_settings.buying_price.value" :key="i">
                    <span v-if="delivery_value.buying_price">
                      {{ currencyFormatter(delivery_value.buying_price) }}
                      <span
                        v-if="delivery_value.discount.type === 'percentage'"
                        class="tag is-success is-rounded has-margin-left-5 is-light"
                      >
                        {{ delivery_value.discount.value }}%
                      </span>
                      <span
                        v-if="delivery_value.discount.type === 'fixed'"
                        class="tag is-success is-rounded has-margin-left-5 is-light"
                      >
                        {{ getCurrencySymbol() }} {{ delivery_value.discount.value }}
                      </span>
                    </span>
                    <span v-else>
                      {{ currencyFormatter(delivery_value.gross_price) }}
                    </span>
                  </td>

                  <!-- Selling price -->
                  <td v-if="view_settings.selling_price.value" :key="i">
                    <span v-if="delivery_value.selling_price">
                      {{ currencyFormatter(delivery_value.selling_price) }}

                      <span
                        v-if="price.normal.margin.type === 'percentage'"
                        class="tag is-info is-rounded has-margin-left-5 is-light"
                      >
                        {{ price.normal.margin.value }}%
                      </span>
                      <span
                        v-if="price.normal.margin.type === 'fixed'"
                        class="tag is-info is-rounded has-margin-left-5 is-light"
                      >
                        {{ getCurrencySymbol() }} {{ margin.value }}
                      </span>
                    </span>
                    <span v-else>
                      {{ currencyFormatter(delivery_value.gross_price) }}
                    </span>
                  </td>

                  <!-- Profit -->
                  <td v-if="view_settings.profit.value" :key="i" class="has-background-light">
                    <span v-if="delivery_value.profit" class="has-text-success">
                      {{ currencyFormatter(delivery_value.profit) }}
                    </span>
                    <span v-else-if="delivery_value.buying_price" class="has-text-warning">
                      {{
                        currencyFormatter(delivery_value.gross_price - delivery_value.buying_price)
                      }}
                    </span>
                    <span v-else-if="delivery_value.selling_price" class="has-text-warning">
                      {{
                        currencyFormatter(delivery_value.selling_price - delivery_value.gross_price)
                      }}
                    </span>
                    <span v-else class="has-text-danger"> {{ getCurrencySymbol() }} 0,00 </span>
                  </td>
                </template>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: ["prices", "discounts"],
  setup() {
    const authStore = useAuthStore();
    const { getCurrencySymbol } = useMoney();
    return {
      authStore,
      getCurrencySymbol,
    };
  },
  data() {
    return {
      // view settings
      view_settings: {
        gross_price: {
          name: "Gross price",
          value: false,
        },
        buying_price: {
          name: "Buying price",
          value: false,
        },
        selling_price: {
          name: "Selling price",
          value: true,
        },
        profit: {
          name: "Profit",
          value: true,
        },
      },
      colspan: 2,

      // currency settings
      iso: "nl-NL",
      currency: this.authStore.settings.data.find((setting) => setting.key === "currency").value,
    };
  },
  computed: {
    // collect price tables when passed from prop
    tables() {
      if (this.prices) {
        return this.prices;
      }
      return null;
    },
  },
  methods: {
    currencyFormatter(number) {
      if (number) {
        return new Intl.NumberFormat(this.iso, {
          style: "currency",
          currency: this.currency,
        }).format(number / 100);
      }

      return "";
    },
    updateColSpan() {
      let i = 0;
      for (const key in this.view_settings) {
        if (this.view_settings.hasOwnProperty(key)) {
          const element = this.view_settings[key];
          if (element.value === true) {
            i++;
          }
        }
      }
      this.colspan = i;
    },
  },
};
</script>

<style>
.end-of-table {
  border: 1px solid black !important;
}
</style>
