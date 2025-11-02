<template>
  <div>
    <div
      class="flex w-full flex-col items-center justify-between space-y-4 md:flex-row md:space-y-0"
    >
      <div class="flex items-center">
        <img
          v-if="logo"
          ref="producerLogoImg"
          :src="logo"
          alt="Producer company logo"
          class="w-14 rounded object-contain"
          crossorigin="anonymous"
        />
        <h1 class="ml-4 text-center text-2xl font-bold">
          {{ name }}
        </h1>

        <!-- discount at this producer -->
        <section
          class="ml-4 flex -skew-x-12 items-center space-x-2 rounded bg-green-50 text-green-500"
        >
          <!-- <font-awesome-icon :icon="['fal', 'badge-percent']" /> -->
          <!-- <span class="text-sm font-bold">
            {{ $t("Discount") }}
          </span> -->
          <span class="skew-x-12 px-4 text-base font-bold">
            -
            {{ discount }}
            <span class="font-mono">%</span>
          </span>
        </section>
      </div>

      <div class="relative flex items-center rounded-full bg-white/50 p-2">
        <div
          class="flex h-14 w-14 items-center justify-center overflow-hidden rounded-full border-2 bg-white p-2"
          :class="{
            'outline-3 border-green-500 outline outline-green-50': handshake === 'accepted',
            'outline-3 border-blue-500 outline outline-blue-50': handshake === 'pending',
            'outline-3 border-amber-500 outline outline-amber-50': handshake === 'suspended',
            'outline-3 border-red-500 outline outline-red-50': handshake === 'rejected',
            'border-black': handshake === 'false',
          }"
        >
          <img
            v-if="logo"
            ref="producerLogoImg"
            :src="logo"
            alt="Producer company logo"
            class="object-contain"
            crossorigin="anonymous"
          />
        </div>

        <font-awesome-icon
          :icon="['fal', 'horizontal-rule']"
          class="mx-2 text-lg"
          :class="{
            'text-green-500': handshake === 'accepted',
            'text-blue-500': handshake === 'pending',
            'text-amber-500': handshake === 'suspended',
            'text-red-500': handshake === 'rejected',
            'text-black': handshake === 'false',
          }"
        />
        <font-awesome-icon
          v-if="handshake === 'false' || handshake === 'rejected'"
          :icon="['fal', 'handshake-slash']"
          class="mx-2 text-2xl text-red-500"
        />
        <font-awesome-icon
          v-else-if="handshake === 'pending'"
          :icon="['fal', 'hand-holding-hand']"
          class="mx-2 text-2xl text-blue-500"
        />
        <font-awesome-icon
          v-else-if="handshake === 'suspended'"
          :icon="['fal', 'hand-holding-hand']"
          class="mx-2 text-2xl text-amber-500"
        />
        <font-awesome-icon
          v-else
          :icon="['fal', 'handshake']"
          class="mx-2 text-2xl text-green-500"
        />
        <font-awesome-icon
          :icon="['fal', 'horizontal-rule']"
          class="mx-2 text-lg"
          :class="{
            'text-green-500': handshake === 'accepted',
            'text-blue-500': handshake === 'pending',
            'text-amber-500': handshake === 'suspended',
            'text-red-500': handshake === 'rejected',
            'text-black': handshake === 'false',
          }"
        />
        <div
          class="flex h-14 w-14 items-center justify-center overflow-hidden rounded-full border-2 bg-white p-2"
          :class="{
            'outline-3 border-green-500 outline outline-green-50': handshake === 'accepted',
            'outline-3 border-blue-500 outline outline-blue-50': handshake === 'pending',
            'outline-3 border-amber-500 outline outline-amber-50': handshake === 'suspended',
            'outline-3 border-red-500 outline outline-red-50': handshake === 'rejected',
            'border-black': handshake === 'false',
          }"
        >
          <img
            v-if="tenantLogo"
            ref="tenantLogoImg"
            :src="tenantLogo"
            alt="Producer logo"
            class="object-contain"
            crossorigin="anonymous"
          />
        </div>
      </div>

      <div
        v-if="handshake === 'accepted'"
        class="-skew-x-12 rounded bg-green-50 px-4 py-1 text-lg font-bold text-green-500"
      >
        <div class="skew-x-12">
          {{ $t("Hand shaked") }}
          <font-awesome-icon :icon="['fal', 'handshake']" class="ml-4" />
        </div>
      </div>
      <div
        v-else-if="handshake === 'pending'"
        class="-skew-x-12 rounded bg-blue-50 px-4 py-1 text-lg text-blue-500"
      >
        <div class="skew-x-12">
          {{ $t("Handshake pending") }}
          <font-awesome-icon :icon="['fal', 'hand-holding-hand']" class="ml-4" />
        </div>
      </div>
      <div
        v-else-if="handshake === 'suspended'"
        class="-skew-x-12 rounded bg-amber-50 px-4 py-1 text-lg text-amber-500"
      >
        <div class="skew-x-12">
          {{ $t("Handshake suspended by producer") }}
          <font-awesome-icon :icon="['fal', 'hand-holding-hand']" class="ml-4" />
        </div>
      </div>
      <div
        v-else-if="handshake === 'rejected'"
        class="-skew-x-12 rounded bg-red-50 px-4 py-1 text-lg text-red-500"
      >
        <div class="skew-x-12">
          {{ $t("Handshake rejected by producer") }}
          <font-awesome-icon :icon="['fal', 'handshake-slash']" class="ml-4" />
        </div>
      </div>
      <UIButton
        v-else
        class="rounded-md border bg-white px-4 !text-lg hover:!bg-white hover:shadow-md"
        variant="neutral-light"
        :disabled="loading"
        @click="emit('requestHandshake')"
      >
        {{ $t("Request handshake") }}
        <font-awesome-icon :icon="['fal', 'hand-holding-hand']" class="ml-4" />
      </UIButton>
    </div>
  </div>
</template>

<script setup>
const emit = defineEmits(["requestHandshake"]);
defineProps({
  loading: {
    type: Boolean,
    default: false,
  },
  name: {
    type: String,
    default: "",
  },
  logo: {
    type: String,
    default: "",
  },
  tenantLogo: {
    type: String,
    default: "",
  },
  handshake: {
    type: [String],
    default: "false",
  },
  discount: {
    type: Number,
    default: 0,
  },
});
</script>
