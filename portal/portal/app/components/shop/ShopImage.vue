<template>
  <figure class="flex h-full flex-col justify-center text-center">
    <transition name="fade" mode="out-in" tag="div">
      <font-awesome-icon
        v-if="!src && noSrc"
        :icon="['fal', 'hexagon-image']"
        class="text-gray-200"
        style="font-size: 50px"
      />
      <img
        v-else-if="src && src.length > 0"
        :src="src"
        :alt="file.filename"
        class="mx-auto h-full w-full rounded object-contain"
      />
      <span v-else-if="noSrc" />
    </transition>
  </figure>
</template>

<script>
export default {
  name: "Thumbnail",
  props: {
    disk: {
      type: String,
      required: true,
      default: "assets",
    },
    file: {
      type: Object,
      required: true,
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
      noSrc: false,
    };
  },
  watch: {
    "file.timestamp": "loadImage",
  },
  mounted() {
    if (window.IntersectionObserver) {
      const observer = new IntersectionObserver(
        (entries, obs) => {
          entries.forEach((entry) => {
            if (entry.isIntersecting) {
              this.loadImage();
              obs.unobserve(this.$el);
            }
          });
        },
        {
          root: null,
          threshold: "0.5",
        },
      );
      // add observer for template
      observer.observe(this.$el);
    } else {
      this.loadImage();
    }
  },
  methods: {
    /**
     * Load image
     */
    async loadImage() {
      if (this.file.path && this.file.path !== "undefined") {
        await this.api
          .get(`/media-manager/file-manager/preview?disk=${this.disk}&path=${this.file.path}`, {
            responseType: "arrayBuffer",
          })
          .then((response) => {
            const mimeType = this.getMimeTypeFromArrayBuffer(response);
            const imgBase64 = this.arrayBufferToBase64(response);
            this.src = `data:${mimeType};base64,${imgBase64}`;
          });
      } else {
        this.noSrc = true;
      }
    },
  },
};
</script>
