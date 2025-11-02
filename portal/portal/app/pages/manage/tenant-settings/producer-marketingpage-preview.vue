<template>
  <div class="">
    <sub-page-header
      class="sticky top-8 z-20 mt-4 rounded border border-amber-500 bg-amber-100 p-4 text-amber-500 dark:bg-amber-800"
      :icon="['fal', 'parachute-box']"
      :title="$t('Producer details')"
      @on-close="navigateTo(`/manage/tenant-settings/producer-information`)"
      @on-back="navigateTo(`/manage/tenant-settings/producer-information`)"
    >
      <template #default>
        <div>
          <p class="text-lg">
            <font-awesome-icon :icon="['fal', 'magnifying-glass-waveform']" />
            {{ $t("You are viewing a static version your producer marketing page") }}
          </p>
        </div>
      </template>
    </sub-page-header>

    <UICard
      v-if="producer"
      class="container mt-12 !rounded-md p-4 text-black shadow-none"
      :style="'background-color:' + dominantColor + ';' + 'shadow-color: ' + dominantColor"
    >
      <div class="flex h-full w-full flex-col items-center justify-center rounded-md p-4">
        <ProducerHeader
          :name="producer.company_name"
          :logo="producer.logo"
          :tenant-logo="tenantLogo"
          class="mb-4 w-full"
        />

        <div class="my-20 flex w-full">
          <div class="relative mr-10 min-w-fit origin-bottom-left md:-ml-8 md:mr-20 md:-rotate-6">
            <img
              v-if="producer.page_media"
              ref="producerLogo"
              :src="producer.page_media[0].url"
              alt="Producer logo"
              class="h-32 w-32 rounded-full border-2 border-white object-cover shadow-lg md:h-64 md:w-64 md:rounded-3xl"
              crossorigin="anonymous"
              @load="getDominantColor"
            />
            <!-- <h2 class="flex font-mono text-4xl font-bold md:hidden">
              {{ producer.page_title }}
            </h2> -->
          </div>

          <section class="wfull md-wauto">
            <h2 class="hidden font-mono text-4xl font-bold md:flex">
              {{ producer.page_title }}
            </h2>
            <p>{{ producer.page_description }}</p>
            <div class="mt-12 grid grid-cols-2 grid-rows-2 gap-12">
              <div class="flex flex-col">
                <span class="text-xs uppercase tracking-wide">
                  {{ $t("Shared categories") }}
                </span>

                <span class="text-lg font-bold">
                  <font-awesome-icon :icon="['fal', 'radar']" class="mr-2" />
                  {{ producer.shared_categories?.length }}
                </span>
              </div>
              <!-- <div class="flex flex-col">
                <span class="text-xs uppercase tracking-wide">
                  {{ $t("Rating") }}
                </span>
                <UIRatingStars
                  variant="dark"
                  :rating="producer.rating"
                  :reviews="producer.reviewAmount"
                  class="text-theme-400"
                />
              </div> -->

              <div class="flex flex-col">
                <span class="text-xs uppercase tracking-wide">
                  {{ $t("Active since") }}
                </span>
                <span class="text-lg font-bold">
                  <font-awesome-icon :icon="['fal', 'calendar-days']" class="mr-2" />
                  {{ new Date(producer.created_at).getFullYear() }}
                </span>
              </div>

              <div class="flex flex-col">
                <span class="text-xs uppercase tracking-wide">
                  {{ $t("Location") }}
                </span>

                <span v-if="me.addresses" class="text-lg font-bold">
                  <font-awesome-icon :icon="['fal', 'location-dot']" class="mr-2" />
                  {{ me.addresses[0].city }}, {{ me.addresses[0].country.name }}
                </span>
              </div>

              <div class="flex flex-col">
                <span class="text-xs uppercase tracking-wide">
                  {{ $t("Contact") }}
                </span>

                <span class="text-lg text-theme-400">
                  <font-awesome-icon :icon="['fal', 'envelope']" class="mr-2" />
                  <a :href="`mailto:${producer.email}`" class="hover:underline">
                    {{ producer.email }}
                  </a>
                </span>
              </div>
            </div>
          </section>
        </div>

        <div class="flex w-full">
          <section class="grid w-full grid-cols-6 gap-4">
            <div
              v-for="category in producer.shared_categories"
              :key="category"
              class="flex h-36 flex-col justify-between rounded bg-white p-4 shadow dark:bg-gray-700 dark:text-white dark:shadow-gray-800"
            >
              <span class="self-start text-lg font-bold">
                {{ $display_name(category.display_name) }}
              </span>

              <section class="mt-auto flex justify-between">
                <div
                  class="w-auto -skew-x-12 space-x-2 self-start rounded bg-green-100 text-green-500"
                >
                  <span class="skew-x-12 px-4 text-base font-bold">
                    - 0
                    <span class="font-mono">%</span>
                  </span>
                </div>
                <font-awesome-icon :icon="['fal', 'arrow-right']" class="self-end" />
              </section>
            </div>
          </section>
        </div>
      </div>
    </UICard>

    <UICard class="container mt-4 !rounded-md border-2 border-none">
      <div class="p-4 pt-8">
        <h2 class="ml-8 text-2xl font-bold">
          {{ $t("Reviews") }}
        </h2>
        <div class="my-4 grid grid-cols-4 divide-x divide-dashed">
          <div class="flex flex-col px-8">
            <span class="flex w-full justify-between">
              <UIFiltersStars />
              <p class="mt-4">
                {{ new Date().toLocaleDateString() }}
              </p>
            </span>
            <p class="mt-4">
              <UITextArea :placeholder="$t('Write a review')" />
            </p>
            <p v-if="me?.profile" class="mt-4 text-sm font-bold">
              {{ me.profile.first_name }} {{ me.profile.last_name }}
            </p>
            <p v-else-if="me?.mail" class="mt-4 text-sm font-bold">
              {{ me.mail }}
            </p>
          </div>
          <!-- <div
            v-for="(review, i) in producer.reviews"
            :key="`review_${i}_${review.rating}`"
            class="flex flex-col px-8"
          >
            <span class="flex w-full justify-between">
              <UIRatingStars :rating="review.rating" variant="dark" />
              <p class="mt-4">
                {{ review.date }}
              </p>
            </span>
            <p class="mt-4">
              {{ review.review }}
            </p>
            <p class="mt-4 text-sm font-bold">
              {{ review.reviewer }}
            </p>
          </div> -->
        </div>
      </div>

      <div class="w-full p-20 text-center">
        <h3 class="mb-10 text-xl font-bold">
          {{ $t("Convinced?") }}
        </h3>

        <UIButton
          class="self-center border !px-8 py-4 !text-2xl font-bold !text-black shadow-md hover:shadow-lg"
          :style="`background-color: ${dominantColor};  ${textStyles};`"
          @click="navigateTo(`/marketplace/producer-finder`)"
        >
          {{ $t("Request handshake") }}
          <font-awesome-icon :icon="['fal', 'hand-holding-hand']" class="ml-4" />
        </UIButton>
      </div>
    </UICard>
  </div>
</template>

<script setup>
// imports
import { ref, onMounted } from "vue";

import { extractColors } from "extract-colors";
// import ProducerInformationSidebar from "~/components/tenant_settings/ProducerInformationSidebar.vue";
// const route = useRoute();
const api = useAPI();
// const { fetchProducerDetails } = useMarketplaceRepository();
// const { handleError, handleSuccess } = useMessageHandler();

const props = defineProps({
  tenant: {
    type: Object,
    required: true,
  },
});
// Define your reactive state
const producer = ref(props.tenant);

const me = ref("null");

const dominantColor = ref("null");
const colorStyles = ref("null");
const textStyles = ref("null");
const producerLogo = ref(null);
const tenantLogo = ref(null);

// Function to reduce color intensity
const reduceColorIntensity = (hex) => {
  const rgb = parseInt(hex.slice(1), 16);
  const r = Math.min(255, Math.floor(((rgb >> 16) & 0xff) + ((255 - (rgb >> 16)) & 0xff) * 0.7));
  const g = Math.min(255, Math.floor(((rgb >> 8) & 0xff) + ((255 - (rgb >> 8)) & 0xff) * 0.7));
  const b = Math.min(255, Math.floor((rgb & 0xff) + (255 - (rgb & 0xff)) * 0.7));
  return `#${((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1)}`;
};

// Modify getDominantColor to use reduced intensity
const getDominantColor = async () => {
  const img = producerLogo.value;
  if (!img) {
    setTimeout(getDominantColor, 100);
    return;
  }
  img.crossOrigin = "Anonymous";

  const colors = await extractColors(img.src);

  if (colors.length >= 2) {
    dominantColor.value = reduceColorIntensity(colors[0].hex);
    // dominantColor.value = colors[0].hex;
    colorStyles.value = `background: linear-gradient(to right, ${reduceColorIntensity(
      colors[0].hex,
    )}, ${reduceColorIntensity(colors[1].hex)})`;

    /**
     * This function takes an array of numbers and returns the sum of all the elements.
     *
     * @param {number[]} numbers - An array of numbers to be summed.
     * @returns {number} The sum of all the numbers in the array.
     */
    const isColorDark = (hex) => {
      const rgb = parseInt(hex.slice(1), 16);
      const brightness =
        ((rgb >> 16) & 0xff) * 0.299 + ((rgb >> 8) & 0xff) * 0.587 + (rgb & 0xff) * 0.114;
      return brightness < 128;
    };

    const textColor = isColorDark(dominantColor.value) ? "color: white;" : "color: black;";
    colorStyles.value += ";" + textColor;
    textStyles.value = textColor;
  } else if (colors.length === 1) {
    dominantColor.value = reduceColorIntensity(colors[0].hex);
    const textColor = isColorDark(dominantColor.value) ? "color: white;" : "color: black;";
    colorStyles.value = colors[0].hex + ";" + textColor;
    textStyles.value = textColor;
  }
};

// Lifecycle hook
onMounted(async () => {
  try {
    const info = await api.get("/info");
    tenantLogo.value = info.logo;
  } catch (error) {
    console.error("Error fetching info:", error);
  }

  try {
    const response = await api.get("/account/me");
    me.value = response.data;
  } catch (error) {
    handleError(error);
  }

  // fetchProducerDetails(route.params.id)
  //   .then((response) => {
  //     producer.value = response;
  //   })
  //   .catch((error) => {
  //     handleError(error);
  //   });
});
</script>

<style scoped>
/* Your styles here */
</style>
