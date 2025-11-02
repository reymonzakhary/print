<template>
  <UICard v-if="loading" class="z-10 grid h-full place-items-center !bg-gray-100 !shadow-xl">
    <UILoader />
  </UICard>
  <UICard
    v-else
    class="invoice-preview select-none !bg-white"
    :style="{
      width: '793.7px',
      fontSize: `${props.fontSize}px`,
      fontFamily: props.fontFamily,
      backgroundImage: `url(${props.backgroundImage})`,
      backgroundPosition: 'top left',
      backgroundSize: 'cover',
    }"
  >
    <!-- Header Indicator -->
    <div
      v-if="props.showGuides"
      class="absolute left-0 w-full border-t border-dashed border-theme-500"
      :style="{ top: 150 + props.addressOffset + 'px' }"
    >
      <span class="absolute -top-6 left-4 text-base text-theme-500">{{ $t("contentLimit") }}</span>
    </div>
    <!-- Header -->
    <!-- Somehow the text-align property doesn't work for images on my browser. So I had to use flexbox. The PDF does NOT use flexbox but text-align. -->
    <div
      class="logo-container flex"
      :class="{
        'justify-center': props.logoPosition === 'center',
        'justify-end': props.logoPosition === 'right',
      }"
    >
      <div>
        <img
          v-if="props.logo"
          :src="props.logo"
          alt="Company logo"
          class="logo"
          :style="{ width: `${props.logoWidth}px` }"
        />
      </div>
    </div>
    <div
      class="header"
      :style="{
        textAlign: props.addressDirection === 'rtl' ? 'right' : 'left',
        paddingTop: `${props.addressOffset}px`,
      }"
    >
      <div>{{ $t("companyName") }}</div>
      <div>{{ $t("contactName") }}</div>
      <div>{{ $t("streetAddress") }}</div>
      <div>{{ $t("postalCodeCity") }}</div>
    </div>
    <!-- Details -->
    <div>
      <table class="invoice-details" style="float: left; width: 60%">
        <tbody>
          <tr>
            <td style="width: 40%">{{ $t("invoiceNumber") }}:</td>
            <td style="width: 60%">2025-0002</td>
          </tr>
          <tr>
            <td style="width: 40%">{{ $t("invoiceDate") }}:</td>
            <td style="width: 60%">2025-01-23 15:12:13</td>
          </tr>
          <tr>
            <td style="width: 40%">{{ $t("dueDate") }}:</td>
            <td style="width: 60%">2025-02-07 15:12:13</td>
          </tr>
        </tbody>
      </table>
      <div style="float: right; width: 40%">
        <h1 class="invoice-title">{{ $t("invoice") }}</h1>
      </div>
      <div style="clear: both" />
    </div>
    <!-- Table -->
    <div
      class="items-container"
      :style="{ height: 418 + 25 * (16 - fontSize) - props.addressOffset + 'px' }"
    >
      <table class="items">
        <thead>
          <tr>
            <th style="width: 42%">{{ $t("productService") }}</th>
            <th style="width: 12%; text-align: right">{{ $t("vat") }}</th>
            <th style="width: 17%; text-align: right">{{ $t("unitPrice") }}</th>
            <th style="width: 10%; text-align: right">{{ $t("quantity") }}</th>
            <th style="width: 19%; text-align: right">{{ $t("subtotal") }}</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>
              {{ $t("productName") }} <br />
              <span style="font-size: 0.75em">{{ $t("productDescription") }}</span>
            </td>
            <td>21<span>%</span></td>
            <td class="currency-cell">
              <span class="currency-symbol">{{ getCurrencySymbol() }}</span
              >10,00
            </td>
            <td style="text-align: right">10</td>
            <td class="currency-cell">
              <span class="currency-symbol">{{ getCurrencySymbol() }}</span
              >100,00
            </td>
          </tr>
          <tr class="border-b" style="border-color: #aaa; height: 2.5em">
            <td colspan="4">
              {{ $t("shippingCosts") }} <br />
              <span style="font-size: 0.75em">{{ $t("vatIncluded") }}</span>
            </td>
            <td class="currency-cell">
              <span class="currency-symbol">{{ getCurrencySymbol() }}</span
              >7,95
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Totals -->
    <table class="totals">
      <tbody>
        <tr>
          <td style="width: 81%; text-align: right; padding-right: 1em">
            {{ $t("totalExclVat") }}
          </td>
          <td class="currency-cell" style="width: 19%">
            <span class="currency-symbol">{{ getCurrencySymbol() }}</span
            >107,95
          </td>
        </tr>
        <tr>
          <td style="width: 81%; text-align: right; padding-right: 1em">{{ $t("vat21") }}</td>
          <td class="currency-cell" style="width: 19%">
            <span class="currency-symbol">{{ getCurrencySymbol() }}</span
            >22,67
          </td>
        </tr>
        <tr>
          <td style="width: 81%; text-align: right; padding-right: 1em">
            <strong>{{ $t("amountDue") }}</strong>
          </td>
          <td class="currency-cell" style="width: 19%">
            <strong
              ><span class="currency-symbol">{{ getCurrencySymbol() }}</span> 130,62</strong
            >
          </td>
        </tr>
      </tbody>
    </table>
    <p class="footer-info">{{ $t("paymentInstructions") }}</p>
    <div class="page-number" />

    <!-- Footer Indicator -->
    <div
      v-if="props.showGuides"
      class="absolute bottom-[150px] left-0 w-full border-t border-dashed border-theme-500"
    >
      <span class="absolute -top-6 left-4 text-base text-theme-500">{{ $t("contentLimit") }}</span>
    </div>
  </UICard>
</template>

<script setup>
const props = defineProps({
  loading: {
    type: Boolean,
    default: false,
  },
  fontSize: {
    type: Number,
    default: 16,
  },
  fontFamily: {
    type: String,
    default: "Helvetica",
  },
  backgroundImage: {
    type: String,
    default: "",
  },
  showGuides: {
    type: Boolean,
    default: true,
  },
  logo: {
    type: String,
    default: "",
  },
  logoWidth: {
    type: Number,
    default: 300,
  },
  logoPosition: {
    type: String,
    default: "left",
  },
  addressDirection: {
    type: String,
    default: "ltr",
  },
  addressOffset: {
    type: Number,
    default: 0,
  },
});

const { getCurrencySymbol } = useMoney();
</script>

<style scoped>
.invoice-preview {
  font-family: "Helvetica";
  background-image: v-bind("props.backgroundImage");
  background-size: contain;
  background-position: top left;
  background-repeat: no-repeat;
  color: #333;
  padding: 48px;
  position: relative;
}

.page-number {
  position: absolute;
  bottom: 12.7mm;
  right: 12.7mm;
  text-align: right;
}
.page-number:after {
  counter-increment: page;
  content: counter(page);
}
.page_break {
  page-break-before: always;
}

.logo-container {
  height: 70px;
  text-align: left;
  margin-bottom: 32px;
}
.header {
  margin-bottom: 2.5em;
}
.invoice-details {
  margin-bottom: 1em;
}
.invoice-details td {
  padding: 0.25em 0;
}
.invoice-title {
  text-transform: uppercase;
  font-size: 1.75em;
  font-weight: bold;
  text-align: left;
}
table {
  table-layout: fixed;
  width: 100%;
  border-collapse: collapse;
}
/* .items-container {
  height: 418px;
}
.items-container.extra {
  height: 518px;
}
*/
.items th {
  border-bottom: 1px solid #000;
  text-align: left;
  padding: 0.5em 1em;
}
.items th:first-child {
  padding-left: 0;
}
.items th:last-child {
  padding-right: 0;
}
.items td {
  vertical-align: top;
  padding: 0.5em 1em;
  height: 2.5em;
  overflow: hidden;
}
.items td:first-child {
  padding-left: 0;
}
.items td:last-child {
  padding-right: 0;
}
.items tr {
  border-bottom: 1px solid #aaa;
}
.items tr:last-child {
  border-bottom: none;
}
.currency-cell {
  position: relative;
  text-align: right;
}
.currency-symbol {
  position: absolute;
  left: 0.25em;
}
.totals {
  width: 100%;
  margin-left: auto;
}
.totals tr:first-child {
  border-top: 1px solid #000;
}
.totals tr:first-child td {
  padding-top: 0.5em;
}
.totals td {
  padding: 0.25em 0;
}
.footer-info {
  text-align: right;
  font-weight: bold;
  margin-top: 2em;
}
.footer {
  margin-top: 2.5em;
  font-size: 0.9em;
}
</style>
