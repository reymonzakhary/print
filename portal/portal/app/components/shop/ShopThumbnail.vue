<template>
  <figure class="fm-thumbnail">
    <transition name="fade" mode="out-in">
      <font-awesome-icon v-if="!src" :icon="['fal', 'hexagon-image']" class="mr-1 text-gray-300" />
      <img v-else :src="src" :alt="file.filename" class="w-5 rounded" />
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
    };
  },
  computed: {
    /**
     * Authorization required
     * @return {*}
     */
    // auth() {
    //    return this.$store.getters['fm/settings/authHeader'];
    // },
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
      await this.api
        .get(`/media-manager/file-manager/thumbnails?disk=${this.disk}&path=${this.file.path}`, {
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
