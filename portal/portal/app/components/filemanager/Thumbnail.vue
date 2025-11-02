<template>
  <figure class="fm-thumbnail flex w-5 items-center justify-center">
    <transition name="fade" mode="out-in">
      <font-awesome-icon
        v-if="!src"
        key="file-icon"
        :icon="['fal', 'file-image']"
        class="text-xs text-theme-500"
        fixed-width
      />
      <img
        v-else
        key="file-image"
        :src="src"
        :alt="file.filename"
        class="m-auto aspect-square rounded object-cover"
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
    size: {
      required: false,
      type: Number,
      default: 20,
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
  async mounted() {
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
          threshold: "0.5",
        },
      );
      // add observer for template
      this.observer.observe(this.$el);
    } else {
      await this.loadImage();
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
    isFilePath(path) {
      const regex = /[^/]+\.[^/]+$/;
      return regex.test(path);
    },
    /**
     * Load image
     */
    async loadImage() {
      const path = this.isFilePath(this.file.path)
        ? this.file.path
        : `${this.file.path}/${this.file.name}`;
      const normalizedPath = path.replace(/\/+/g, "/");

      try {
        const response = await this.api.get(
          `/media-manager/file-manager/thumbnails?disk=${this.disk}&path=${normalizedPath}&size=${this.size}&t=${Date.now()}`,
          { responseType: "arrayBuffer" },
        );

        const mimeType = this.getMimeTypeFromArrayBuffer(response);
        const blob = new Blob([response], { type: mimeType });

        // Generate unique blob URL each time
        if (this.src) URL.revokeObjectURL(this.src);
        this.src = URL.createObjectURL(blob);
      } catch (err) {
        console.error(err);
      }
    },
  },
};
</script>
