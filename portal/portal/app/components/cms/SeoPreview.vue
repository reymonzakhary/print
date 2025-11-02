<template>
  <section class="overflow-hidden">
    <section
      class="fixed left-0 top-0 z-50 h-screen w-screen bg-black opacity-25"
      @click="$parent.viewSeo = false"
    />
    <transition name="slide-fade">
      <section
        v-if="slideSeo"
        class="fixed right-0 top-0 z-50 h-screen max-h-full w-full rounded bg-white px-2 py-3 dark:bg-gray-700 lg:w-2/4 xl:w-2/6"
      >
        <div class="mb-3 flex items-center justify-between py-2">
          <p class="text-sm font-bold capitalize">
            {{ $t("preview") }}
          </p>
          <button class="capitalize hover:text-gray-400" @click="$parent.viewSeo = false">
            <font-awesome-icon :icon="['fad', 'circle-xmark']" />
          </button>
        </div>

        <div class="h-full overflow-y-auto px-4 pb-6">
          <aside>
            <SEOSectionTitle title="Google" :icon="['fab', 'google']" />
            <div class="border bg-white p-4">
              <h4 class="capitalize text-theme-500">
                {{ data.long_title }}
              </h4>
              <p class="mt-1 text-sm text-green-500">{{ url }}{{ data.slug }}</p>
              <span class="text-sm text-gray-500">
                {{ data.description }}
              </span>
            </div>
          </aside>

          <!-- facebook preview -->
          <aside class="mt-8">
            <SEOSectionTitle title="Facebook" :icon="['fab', 'facebook']" />
            <h3 class="mb-1 py-1 text-xs font-bold capitalize tracking-wide text-gray-500">
              <font-awesome-icon :icon="['fab', 'facebook']" />
              {{ $t("facebook") }}
            </h3>
            <div class="overflow-hidden rounded border shadow-lg">
              <div class="flex h-64 items-center justify-center overflow-hidden bg-gray-100">
                <img v-if="src" :src="src" :alt="data.title" />
                <font-awesome-icon v-else :icon="['fal', 'image']" />
              </div>
              <div class="w-full bg-gray-200 p-4">
                <p class="text-sm uppercase text-gray-400">{{ url }}{{ data.slug }}</p>
                <h4 class="font-bold capitalize text-gray-800">
                  {{ data.long_title }}
                </h4>
                <span class="text-sm text-gray-500">
                  {{ data.description }}
                </span>
              </div>
            </div>
          </aside>

          <!-- x preview -->
          <aside class="mt-8">
            <SEOSectionTitle title="X" :icon="['fab', 'x-twitter']" />
            <div
              class="overflow-hidden rounded border shadow-md shadow-gray-200 dark:shadow-gray-900"
            >
              <div class="flex h-64 items-center justify-center overflow-hidden bg-gray-100">
                <img v-if="src" :src="src" :alt="data.title" />
                <font-awesome-icon v-else :icon="['fal', 'image']" />
              </div>
              <div class="w-full p-4">
                <h4 class="font-bold capitalize text-gray-800">
                  {{ data.long_title }}
                </h4>
                <span class="text-sm text-gray-500">
                  {{ data.description }}
                </span>
                <p class="text-sm text-gray-400">{{ url }}{{ data.slug }}</p>
              </div>
            </div>
          </aside>
        </div>
      </section>
    </transition>
  </section>
</template>

<script>
export default {
  props: {
    data: Object,
    url: String,
  },
  setup() {
    const { getMimeTypeFromArrayBuffer, arrayBufferToBase64 } = useUtilities();
    const api = useAPI();
    return { getMimeTypeFromArrayBuffer, arrayBufferToBase64, api };
  },
  data() {
    return {
      slideSeo: false,
      src: "",
    };
  },
  mounted() {
    this.slideSeo = true;
    if (this.data.image) {
      this.getImage();
    }
  },
  methods: {
    async getImage() {
      await this.api
        .get(`/media-manager/file-manager/preview?disk=assets&path=${this.data.image.path}`, {
          responseType: "arrayBuffer",
        })
        .then((response) => {
          const mimeType = getMimeTypeFromArrayBuffer(response);
          const imgBase64 = arrayBufferToBase64(response);
          this.src = `data:${mimeType};base64,${imgBase64}`;
        });
    },
  },
};
</script>

<style>
.slide-fade-enter-active {
  transition: all 0.3s ease-out;
}

.slide-fade-leave-active {
  transition: all 0.1s cubic-bezier(1, 0.5, 0.8, 1);
}

.slide-fade-enter,
.slide-fade-leave-to {
  transform: translateX(300px);
  opacity: 0;
}
</style>
