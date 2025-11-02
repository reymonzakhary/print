<template>
  <figure
    class="flex overflow-hidden justify-center items-center h-24 text-center rounded-t bg-checkered"
  >
    <transition name="fade" mode="out-in">
      <font-awesome-icon
        v-if="!src"
        key="file-icon"
        :icon="['fal', 'file-image']"
        class="text-theme-500"
        style="font-size: 50px"
      />
      <img
        v-else
        key="file-image"
        :src="src"
        :alt="file.filename"
        class="object-contain mx-auto h-full"
      />
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
      observer: null,
    };
  },
  watch: {
    "file.timestamp": "loadImage",
  },
  mounted() {
    if (window.IntersectionObserver) {
      this.observer = new IntersectionObserver(
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
          threshold: 0.5,
        },
      );
      // add observer for template
      this.observer.observe(this.$el);
    } else {
      this.loadImage();
    }
  },
  beforeUnmount() {
    if (this.observer) {
      this.observer.unobserve(this.$el);
      this.observer.disconnect();
      this.observer = null;
    }
  },
  methods: {
    /**
     * Load image
     */
    async loadImage() {
      await this.api
        .get(
          `/media-manager/file-manager/preview?disk=${this.disk}&path=${this.file.path}&thumbsize=180`,
          { responseType: "arrayBuffer" },
        )
        .then((response) => {
          const mimeType = this.getMimeTypeFromArrayBuffer(response);
          const imgBase64 = this.arrayBufferToBase64(response);
          this.src = `data:${mimeType};base64,${imgBase64}`;
        });
    },
  },
};
</script>
