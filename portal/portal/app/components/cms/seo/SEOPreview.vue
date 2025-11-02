<template>
  <article>
    <SEOSectionTitle :title="variant" :icon="icon" />

    <div v-if="variant === 'google'" class="border bg-white p-4 dark:bg-gray-900">
      <h4 class="capitalize text-theme-500">
        {{ title === "null" ? "" : title }}
      </h4>
      <p class="mt-1 text-sm text-green-500">
        {{ url === "null" ? "" : url }}
      </p>
      <span class="text-sm text-gray-500">
        {{ description === "null" ? "" : description }}
      </span>
    </div>
    <div v-if="variant === 'facebook'" class="overflow-hidden rounded border shadow-lg">
      <div
        class="flex h-64 items-center justify-center overflow-hidden bg-gray-100 dark:bg-gray-900"
      >
        <img v-if="image && !loading" :src="src" :alt="title" />
        <font-awesome-icon v-else-if="!image && !loading" :icon="['fal', 'image']" />
        <font-awesome-icon v-else :icon="['fal', 'spinner-third']" spin />
      </div>
      <div class="w-full bg-gray-200 p-4 dark:bg-gray-900">
        <p class="text-sm uppercase text-gray-400">
          {{ url === "null" ? "" : url }}
        </p>
        <h4 class="font-bold capitalize text-gray-800">
          {{ title === "null" ? "" : title }}
        </h4>
        <span class="text-sm text-gray-500">
          {{ description === "null" ? "" : description }}
        </span>
      </div>
    </div>
    <div
      v-if="variant === 'x'"
      class="overflow-hidden rounded border shadow-md shadow-gray-200 dark:shadow-gray-900"
    >
      <div
        class="flex h-64 items-center justify-center overflow-hidden bg-gray-100 dark:bg-gray-900"
      >
        <img v-if="image && !loading" :src="src" :alt="title" />
        <font-awesome-icon v-else-if="!image && !loading" :icon="['fal', 'image']" />
        <font-awesome-icon v-else :icon="['fal', 'spinner-third']" spin />
      </div>
      <div class="w-full p-4">
        <h4 class="font-bold capitalize text-gray-800 dark:text-gray-100">
          {{ title === "null" ? "" : title }}
        </h4>
        <span class="text-sm text-gray-500 dark:text-gray-100">
          {{ description === "null" ? "" : description }}
        </span>
        <p class="text-sm text-gray-400 dark:text-gray-100">
          {{ url === "null" ? "" : url }}
        </p>
      </div>
    </div>
  </article>
</template>

<script>
export default {
  name: "SEOPreview",
  props: {
    title: {
      type: String,
      default: "",
    },
    description: {
      type: String,
      default: "",
    },
    url: {
      type: String,
      default: "",
    },
    image: {
      type: String,
      default: "",
    },
    variant: {
      type: String,
      default: "google",
      validator: function (value) {
        return ["google", "facebook", "x"].indexOf(value) !== -1;
      },
    },
  },
  setup() {
    const { getMimeTypeFromArrayBuffer, arrayBufferToBase64 } = useUtilities();
    const api = useAPI();
    return { getMimeTypeFromArrayBuffer, arrayBufferToBase64, api };
  },
  data() {
    return {
      src: "",
      loading: true,
    };
  },
  computed: {
    icon() {
      switch (this.variant) {
        case "google":
          return ["fab", "google"];
        case "facebook":
          return ["fab", "facebook"];
        case "x":
          return ["fab", "x-twitter"];
      }
      return ["fal", "question"];
    },
  },
  mounted() {
    if (this.image) {
      this.getImage();
    }
  },
  methods: {
    async getImage() {
      await this.api
        .get(`/media-manager/file-manager/preview?disk=assets&path=${this.image}`, {
          responseType: "arrayBuffer",
        })
        .then((response) => {
          const mimeType = getMimeTypeFromArrayBuffer(response);
          const imgBase64 = arrayBufferToBase64(response);
          this.src = `data:${mimeType};base64,${imgBase64}`;
          this.loading = false;
        });
    },
  },
};
</script>
