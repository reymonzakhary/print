<template>
  <div class="p-4">
    <transition name="fade">
      <HandshakeRequestModal
        v-if="showHandshakeModal"
        :producer="producer"
        :is-loading="sendingMessage"
        @on-send="handleHandshake($event)"
        @on-close="showHandshakeModal = false"
      />
    </transition>

    <sub-page-header
      class="sticky top-0 z-20 bg-gray-100 dark:bg-gray-800"
      :icon="['fal', 'parachute-box']"
      :title="$t('Producer details')"
      @on-close="navigateTo(`/marketplace/producer-finder`)"
      @on-back="navigateTo(`/marketplace/producer-finder`)"
    >
      <template #default>
        <div>
          <p class="text-lg">
            <font-awesome-icon :icon="['fal', 'parachute-box']" />
            <font-awesome-icon :icon="['fal', 'tag']" />
            {{ $t("Producer details") }} -
            <b>
              {{ producer.producerInfo?.company_name }}
            </b>
          </p>
        </div>
      </template>
    </sub-page-header>

    <UICard
      class="container mt-12 !rounded-md text-black shadow-none transition-colors"
      :style="'background-color:' + dominantColor + ';' + 'shadow-color: ' + dominantColor"
    >
      <div class="flex h-full w-full flex-col items-center justify-center rounded-md p-4">
        <ProducerHeader
          :loading="loadingProducer"
          :name="producer.producerInfo?.company_name"
          :logo="producer.logo"
          :tenant-logo="tenantLogo"
          :handshake="producer.handshake"
          :discount="
            producer.contract?.custom_fields?.discount?.general?.slots?.reduce((max, slot) =>
              slot.value > max.value ? slot : max,
            ).value // get the highest discount
          "
          class="mb-4 w-full"
          @request-handshake="showHandshakeModal = true"
        />

        <div class="my-20 flex w-full">
          <div class="relative mr-10 min-w-fit origin-bottom-left md:-ml-8 md:mr-20 md:-rotate-6">
            <img
              v-if="producer.producerInfo?.page_media?.length > 0"
              ref="producerFeatureImg"
              :src="producer.producerInfo?.page_media[0].url"
              alt="Producer feature img"
              class="h-32 w-32 rounded-full border-2 border-white object-cover shadow-lg md:h-64 md:w-64 md:rounded-3xl"
              crossorigin="anonymous"
              @load="getDominantColor"
            />
          </div>

          <UIListSkeleton
            v-if="loadingProducer"
            :key="'producer-skeleton1'"
            skeleton-line-height="10"
            skeleton-line-amount="1"
            class="relative mr-10 h-64 w-64 min-w-fit origin-bottom-left md:-ml-8 md:mr-20 md:-rotate-6"
          />

          <section class="w-full md:w-auto">
            <UIListSkeleton
              v-if="loadingProducer"
              :key="'producer-skeleton1'"
              skeleton-line-height="6"
              skeleton-line-amount="1"
              class="w-full"
            />
            <UIListSkeleton
              v-if="loadingProducer"
              :key="'producer-skeleton1'"
              skeleton-line-height="2"
              skeleton-line-amount="1"
              class="w-full"
            />

            <h2 class="mb-2 hidden font-mono text-4xl font-bold md:flex">
              {{ producer.producerInfo?.page_title }}
            </h2>

            <p>{{ producer.producerInfo?.page_description }}</p>

            <div class="mt-12 grid grid-cols-2 grid-rows-2 gap-12">
              <div class="flex flex-col">
                <span class="text-xs uppercase tracking-wide">
                  {{ $t("Shared categories") }}
                </span>

                <span class="text-lg font-bold">
                  <font-awesome-icon :icon="['fal', 'radar']" class="mr-2" />
                  {{ producer.sharedCategories }}
                </span>
              </div>
              <!-- TODO: <div class="flex flex-col">
                <span class="text-xs tracking-wide uppercase">
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
                  {{ new Date(producer.activeSince).getFullYear() }}
                </span>
              </div>
              <div class="flex flex-col">
                <span class="text-xs uppercase tracking-wide">
                  {{ $t("Location") }}
                </span>

                <span class="text-lg font-bold">
                  <font-awesome-icon :icon="['fal', 'location-dot']" class="mr-2" />
                  {{ producer.location }}
                </span>
              </div>
            </div>
          </section>
        </div>

        <div class="flex w-full">
          <UIListSkeleton
            v-if="loadingCategories"
            :key="'skeleton1'"
            class="h-full w-full"
            :skeleton-line-height="24"
            :skeleton-line-amount="1"
          />

          <section v-else class="grid w-full grid-cols-3 gap-4 md:grid-cols-4 lg:grid-cols-6">
            <div
              v-for="category in sharedCategories"
              :key="category"
              class="flex h-36 cursor-pointer flex-col justify-between rounded bg-white p-4 shadow transition-shadow hover:shadow-xl dark:bg-gray-700 dark:text-white dark:shadow-gray-800"
              @click="navigateTo(`/marketplace/product-finder/${category.slug}`)"
            >
              <template
                v-if="
                  producer.contract?.custom_fields?.contract?.categories?.find((c) => c.id === category.id)
                "
              >
                <span class="self-start text-lg font-bold">
                  {{ $display_name(category.display_name) }}
                </span>

                <section class="mt-auto flex justify-between">
                  <div
                    class="w-auto -skew-x-12 space-x-2 self-start rounded bg-green-100 text-green-500"
                  >
                    <span
                      v-if="bestSlot(category.id)?.type == 'percentage'"
                      class="skew-x-12 px-4 text-base font-bold"
                    >
                      -
                      {{ bestSlot(category.id)?.value || 0 }}
                      <span class="font-mono">%</span>
                    </span>
                    <span
                      v-if="bestSlot(category.id)?.type == 'fixed'"
                      class="skew-x-12 px-4 text-base font-bold"
                    >
                      -
                      {{ formatMinor(bestSlot(category.id)?.value || 0) }}
                    </span>
                  </div>
                  <font-awesome-icon :icon="['fal', 'arrow-right']" class="self-end" />
                </section>
              </template>
              <template v-else>
                <span class="">
                  <b>{{ $display_name(category.display_name) }}</b> <br />
                  <p class="italic text-gray-500">
                    {{ $t("this category is not shared with you") }}
                  </p>
                </span>
              </template>
            </div>
          </section>
        </div>
      </div>
    </UICard>

    <!-- <UICard class="container mt-4 !rounded-md border-2 border-none"> -->
    <!-- TODO: <div class="p-4 pt-8">
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
          <div
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
          </div>
        </div>
      </div> -->

    <!-- <div v-if="producer.handshake === true" class="w-full p-20 text-center">
        <div
          class="mx-auto w-full -skew-x-12 rounded bg-green-50 px-4 py-1 text-lg font-bold text-green-500 shadow-md shadow-green-200 sm:w-1/2 md:w-1/3"
        >
          <div class="skew-x-12">
            {{ $t("Hand shaked") }}
            <font-awesome-icon :icon="['fal', 'handshake']" class="ml-4" />
          </div>
        </div>
      </div>

      <div v-else class="w-full p-20 text-center">
        <h3 class="mb-10 text-xl font-bold">
          {{ $t("Convinced?") }}
        </h3>

        <UIButton
          class="self-center border !px-8 py-4 !text-2xl font-bold !text-black shadow-md hover:shadow-lg"
          :style="`background-color: ${dominantColor};  ${textStyles};`"
          @click="showHandshakeModal = true"
        >
          {{ $t("Request handshake") }}
          <font-awesome-icon :icon="['fal', 'hand-holding-hand']" class="ml-4" />
        </UIButton>
      </div>
    </UICard> -->
  </div>
</template>

<script setup>
// imports
import { extractColors } from "extract-colors";

const route = useRoute();
const api = useAPI();
const { fetchProducerDetails, fetchSharedCategories } = useMarketplaceRepository();
const { sendMessage } = useMessagesRepository();
const { handleError, handleSuccess } = useMessageHandler();
const { formatCurrency } = useMoney();

// Data refs
// styling refs
const dominantColor = ref("null");
const colorStyles = ref("null");
const textStyles = ref("null");

// producer refs
const producer = ref({});
const tenantLogo = ref(null);
const loadingProducer = ref(false);

// handshake refs
const showHandshakeModal = ref(false);
const sendingMessage = ref(false);

// categories refs
const loadingCategories = ref(false);
const sharedCategories = ref([]);

// Lifecycle hook
onMounted(async () => {
  loadingProducer.value = true;
  loadingCategories.value = true;
  try {
    // Fetch data in parallel
    const [info, producerDetails, categories] = await Promise.all([
      api.get("/info"),
      fetchProducerDetails(route.query.id),
      fetchSharedCategories(route.query.id),
    ]);

    // Update refs with results
    tenantLogo.value = info.logo;
    producer.value = producerDetails;
    sharedCategories.value = categories;
  } catch (error) {
    handleError(error);
  } finally {
    loadingProducer.value = false;
    loadingCategories.value = false;
  }
});

// methods
/**
 * This function sends a handshake request to the producer.
 *
 * @param {Object} message - The message to be sent.
 */
const handleHandshake = async (message) => {
  // set the sending message to true
  sendingMessage.value = true;

  const payload = {
    ...message,
    recipient_email: producer.value.producerInfo.email,
    recipient_hostname: producer.value.id,
  };

  try {
    // send the handshake request
    const response = await sendMessage(payload);
    handleSuccess(response);
    showHandshakeModal.value = false;
  } catch (error) {
    handleError(error);
  } finally {
    fetchProducerDetails(route.query.id)
      .then((producerDetails) => {
        producer.value = producerDetails;
      })
      .catch((error) => {
        handleError(error);
      });
    sendingMessage.value = false;
  }
};

/**
 * This function reduces the intensity of a color.
 *
 * @param {string} hex - The hex color to be reduced.
 * @returns {string} The reduced hex color.
 */
const reduceColorIntensity = (hex) => {
  const rgb = parseInt(hex.slice(1), 16);
  const r = Math.min(255, Math.floor(((rgb >> 16) & 0xff) + ((255 - (rgb >> 16)) & 0xff) * 0.7));
  const g = Math.min(255, Math.floor(((rgb >> 8) & 0xff) + ((255 - (rgb >> 8)) & 0xff) * 0.7));
  const b = Math.min(255, Math.floor((rgb & 0xff) + (255 - (rgb & 0xff)) * 0.7));
  return `#${((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1)}`;
};

/**
 * This function gets the dominant color of the producer logo. Uses reduced intensity
 */
const getDominantColor = async () => {
  // get the image
  const img = producer.value.producerInfo.page_media[0].url;

  // if the image is not loaded yet, wait for it
  if (!img) {
    setTimeout(getDominantColor, 100);
    return;
  }

  // create a new image object from the URL
  const imgObj = new Image();
  imgObj.src = img;

  // set the crossOrigin attribute to Anonymous
  imgObj.crossOrigin = "Anonymous";

  // extract the colors from the image
  const colors = await extractColors(imgObj.src); // method from package extract-colors

  // if there are at least two colors,
  if (colors.length >= 2) {
    // ignore white color
    if (colors[0].hex === "#ffffff") {
      // set the dominant color to the second color
      dominantColor.value = reduceColorIntensity(colors[1].hex);
    } else {
      // set the dominant color to the first color, which is not white
      dominantColor.value = reduceColorIntensity(colors[0].hex);
    }
    // dominantColor.value = reduceColorIntensity(colors[0].hex);
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

    // set the text color based on the brightness of the dominant color
    const textColor = isColorDark(dominantColor.value) ? "color: white;" : "color: black;";

    // set the color styles and text styles
    colorStyles.value += ";" + textColor;
    textStyles.value = textColor;
  } else if (colors.length === 1) {
    // if there is only one color, set the dominant color to the first color
    dominantColor.value = reduceColorIntensity(colors[0].hex);
    const textColor = isColorDark(dominantColor.value) ? "color: white;" : "color: black;";

    // set the color styles and text styles
    colorStyles.value = colors[0].hex + ";" + textColor;
    textStyles.value = textColor;
  }
};

/**
 * Determines the best slot for a given category ID based on the value of the slots.
 *
 * The function processes a nested data structure to locate the category specified by the
 * `categoryId`, and further identifies the slot within that category that has the highest
 * value. If the category or its slots are unavailable, the function returns `null`.
 *
 * @param {string | number} categoryId - The unique identifier of the category to search for.
 * @returns {Object | null} The slot object with the highest value in the specified category, or `null` if
 *                          no valid slots or category are found.
 */
const bestSlot = (categoryId) => {
  const cats = producer.value?.contract?.custom_fields?.contract?.discount?.categories;
  if (!Array.isArray(cats)) return null;
  const cat = cats.find((c) => c.id === categoryId);
  const slots = cat?.slots;
  if (!Array.isArray(slots) || slots.length === 0) return null;
  let best = slots[0];
  for (let i = 1; i < slots.length; i++) {
    const s = slots[i];
    if ((s?.value ?? -Infinity) > (best?.value ?? -Infinity)) best = s;
  }
  return best ?? null;
};

// Centralize minor-unit formatting (see verification note below)
const MINOR_UNIT_DIVISOR = 1000;
const formatMinor = (minor) => formatCurrency((minor ?? 0) / MINOR_UNIT_DIVISOR);
</script>

<style scoped>
/* Your styles here */
</style>
