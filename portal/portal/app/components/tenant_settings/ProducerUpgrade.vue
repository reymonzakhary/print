<template>
  <section>
    <ProducerUpgradeRequestModal
      v-if="showRequestModal"
      @on-send="handleSendEmail"
      @on-close="showRequestModal = false"
    />

    <div class="relative isolate px-6 py-8 sm:py-12 lg:px-8">
      <div
        class="absolute inset-x-0 -top-3 -z-10 transform-gpu overflow-hidden px-36 blur-3xl"
        aria-hidden="true"
      >
        <div
          class="mx-auto aspect-[1155/678] w-[72.1875rem] bg-gradient-to-tr from-theme-300 to-pink-500 opacity-50"
          style="
            clip-path: polygon(
              74.1% 44.1%,
              100% 61.6%,
              97.5% 26.9%,
              85.5% 0.1%,
              80.7% 2%,
              72.5% 32.5%,
              60.2% 62.4%,
              52.4% 68.1%,
              47.5% 58.3%,
              45.2% 34.5%,
              27.5% 76.7%,
              0.1% 64.9%,
              17.9% 100%,
              27.6% 76.8%,
              76.1% 97.7%,
              74.1% 44.1%
            );
          "
        />
      </div>
      <div class="mx-auto max-w-4xl text-center">
        <h2 class="text-base/7 font-semibold text-theme-500">{{ $t("Tenant type") }}</h2>
        <p
          class="mt-2 text-balance text-5xl font-semibold tracking-tight text-gray-900 dark:text-gray-200 sm:text-6xl"
        >
          {{ $t("Reseller, Producer or both?") }}'
        </p>
      </div>
      <p
        class="mx-auto mt-6 max-w-2xl text-pretty text-center text-lg font-medium text-gray-600 sm:text-xl/8"
      >
        {{
          // prettier-ignore
          $t('If you want other companies to be able to sell your products, you can request Prindustry to become a producer on the platform.')
        }}
      </p>
      <div
        v-if="!messages.length"
        class="mx-auto mt-16 grid max-w-lg grid-cols-1 items-center gap-y-6 sm:mt-20 sm:gap-y-0 lg:max-w-4xl lg:grid-cols-2"
      >
        <div
          v-for="(tier, tierIdx) in tiers"
          :key="tier.id"
          :class="[
            tier.featured
              ? 'relative bg-gray-900 shadow-2xl dark:bg-black'
              : 'bg-white dark:bg-gray-700 dark:!text-gray-200 sm:mx-8 lg:mx-0',
            tier.featured
              ? ''
              : tierIdx === 0
                ? 'rounded-t-md sm:rounded-b-none lg:rounded-bl-md lg:rounded-tr-none'
                : 'sm:rounded-t-none lg:rounded-bl-none lg:rounded-tr-3xl',
            'rounded-md p-8 shadow-md sm:p-10',
          ]"
        >
          <h3
            :id="tier.id"
            :class="[
              tier.featured ? 'text-theme-400' : 'text-theme-600 dark:text-theme-400',
              'text-base/7 font-semibold',
            ]"
          >
            {{ tier.name }}
          </h3>
          <p class="mt-4 flex items-baseline gap-x-2">
            <span
              :class="[
                tier.featured ? 'text-white' : 'text-gray-900 dark:text-gray-100',
                'text-5xl font-semibold tracking-tight',
              ]"
              >{{ tier.priceMonthly }}</span
            >
            <span :class="[tier.featured ? 'text-gray-400' : 'text-gray-500', 'text-base']"
              >/{{ $t("sale") }}</span
            >
          </p>
          <p
            :class="[
              tier.featured ? 'text-gray-300' : 'text-gray-600 dark:text-gray-400',
              'mt-6 text-base/7',
            ]"
          >
            {{ tier.description }}
          </p>
          <ul
            role="list"
            :class="[
              tier.featured ? 'text-gray-300' : 'text-gray-600 dark:text-gray-300',
              'mt-8 space-y-3 text-sm/6 sm:mt-10',
            ]"
          >
            <li
              v-for="(feature, i) in tier.features"
              :key="feature"
              class="flex items-center gap-x-3"
              :class="{ 'text-gray-500': i < 7 && tier.featured }"
            >
              <font-awesome-icon
                :class="[tier.featured ? 'text-theme-400' : 'text-theme-500']"
                aria-hidden="true"
                :icon="['far', 'check']"
              />
              {{ feature }}
            </li>
            <li
              v-for="feature in tier.nonFeatures"
              :key="feature"
              class="flex items-center gap-x-3 opacity-50"
            >
              <font-awesome-icon
                :class="[tier.featured ? 'text-red-400' : 'text-red-500']"
                aria-hidden="true"
                :icon="['far', 'xmark']"
              />
              {{ feature }}
            </li>
          </ul>
          <a
            v-if="tier.featured"
            :href="tier.href"
            :aria-describedby="tier.id"
            :class="[
              tier.featured
                ? 'bg-gradient-to-r from-theme-400 to-pink-500 text-white shadow-sm hover:bg-theme-400 focus-visible:outline-theme-500'
                : 'text-gray-600 ring-1 ring-inset ring-gray-200 hover:ring-gray-300 focus-visible:outline-theme-600',
              'mt-8 block rounded-md px-3.5 py-2.5 text-center text-sm font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 sm:mt-10',
            ]"
            :disabled="tierIdx === 0"
            @click="showRequestModal = true"
          >
            {{ tier.featured ? "Get started today" : "already you" }}
          </a>
        </div>
      </div>

      <div
        v-else
        class="mx-auto mt-16 grid max-w-lg grid-cols-1 items-center gap-y-6 sm:mt-20 sm:gap-y-0 lg:max-w-4xl"
      >
        <div
          class="flex items-center justify-between rounded bg-gradient-to-tr from-amber-50 via-orange-200 to-red-200 p-4 text-amber-900 shadow-md shadow-orange-300 ring-1 ring-amber-100"
        >
          <div>
            {{ $t("Request to upgrade to") }}
            <span class="ml-1 text-sm font-bold uppercase">{{ $t("Producer") }}</span>
          </div>
          <p class="ml-4 italic">Prindustry is currently reviewing your request.</p>
          <span
            class="ml-auto animate-pulse rounded bg-amber-500 p-2 text-sm font-bold uppercase tracking-wide text-white"
          >
            {{ $t("pending") }}...
          </span>
        </div>

        <div v-for="(message, index) in messages" :key="`message_${index}`" class="mt-8">
          <article v-for="(item, i) in message.items" :key="`item_${i}`">
            <div v-if="i === message.items.length - 1" class="mt-12 rounded-md bg-white/60 p-4">
              <p class="mb-4 font-bold uppercase tracking-wide text-gray-600">
                {{ $t("Your request info") }}
              </p>
              <p class="text-lg text-gray-600 first-letter:uppercase">{{ item.subject }}</p>
              <p class="my-4 text-lg text-gray-600">{{ item.title }}</p>
              <p class="text-gray-600">
                {{ item.body }}
              </p>
            </div>
          </article>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
const emit = defineEmits(["sendEmail"]);
const showRequestModal = ref(false);
const { t } = useI18n();
const tiers = [
  {
    name: t("Reseller"),
    id: "tier-reseller",
    href: "#",
    priceMonthly: "0%",
    description: t("This is already you!"),
    features: [
      t("Sell products from other producers"),
      t("Set your own prices and margins"),
      t("Compare products accross the market"),
      t("Get paid for every sale"),
      t("Comfortably build your own products using our boxes and options model"),
      //prettier-ignore
      t("Easily manage prices on your whole assortment based on material, machine and additional costs"),
      t("Sell your products on your own shop"),
    ],
    nonFeatures: [t("Sell your products in the Marketplace")],
    featured: false,
  },
  {
    name: t("Producer"),
    id: "tier-producer",
    href: "#",
    priceMonthly: "5%",
    description: t("Power up your business: Sell your unique products on the marketplace!"),
    features: [
      t("Sell products from other producers"),
      t("Set your own prices and margins"),
      t("Compare products accross the market"),
      t("Get paid for every sale"),
      t("Comfortably build your own products using our boxes and options model"),
      // prettier-ignore
      t("Easily manage prices on your whole assortment based on material, machine and additional costs"),
      t("Sell your products on your own shop"),
      t("Sell your products in the Marketplace"),
    ],
    featured: true,
  },
];

const { fetchMessages } = useMessagesRepository();
const { handleError } = useMessageHandler();
// const { theUser: me } = storeToRefs(useAuthStore);

// State variables
const messages = ref([]);
const isLoading = ref(true);

// Lifecycle hooks
onMounted(() => {
  fetchMessages("sender")
    .then((response) => {
      messages.value = groupMessagesBySender(response);
    })
    .catch((error) => {
      handleError(error);
    })
    .finally(() => {
      isLoading.value = false;
    });
});

// Methods
const groupMessagesBySender = (messages) => {
  // Filter messages to only include those with type 'producer'
  const producerMessages = messages.filter((message) => message.type === "producer");

  return producerMessages.reduce((acc, message) => {
    const existing = acc.find((h) => h.sender_name === message.sender_name);
    if (existing) {
      if (!existing.items) {
        existing.items = [{ ...existing }];
      }
      existing.items.push({ ...message });
    } else {
      acc.push({ ...message });
    }

    return acc;
  }, []);
};

const handleSendEmail = (e) => {
  // Send email
  emit("sendEmail", e);

  showRequestModal.value = false;
};
</script>
