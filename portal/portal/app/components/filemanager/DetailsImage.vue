<template>
  <figure class="w-full text-center overflow-hidden details-image h-[250px] bg-checkered rounded-t">
    <!-- <transition-group name=""> -->
    <div
      v-if="imageLoading"
      key="loading_svg_container"
      class="flex justify-center w-full h-full bg-black rounded-t text-theme-200"
    >
      <svg
        id="L4"
        key="loading_svg_1"
        version="1.1"
        xmlns="http://www.w3.org/2000/svg"
        xmlns:xlink="http://www.w3.org/1999/xlink"
        x="0px"
        y="0px"
        viewBox="0 0 100 100"
        enable-background="new 0 0 0 0"
        xml:space="preserve"
        class="m-auto"
      >
        <circle fill="#fff" stroke="none" cx="40" cy="40" r="3">
          <animate
            attributeName="opacity"
            dur="1s"
            values="0;1;0"
            repeatCount="indefinite"
            begin="0.1"
          />
        </circle>
        <circle fill="#fff" stroke="none" cx="50" cy="40" r="3">
          <animate
            attributeName="opacity"
            dur="1s"
            values="0;1;0"
            repeatCount="indefinite"
            begin="0.2"
          />
        </circle>
        <circle fill="#fff" stroke="none" cx="60" cy="40" r="3">
          <animate
            attributeName="opacity"
            dur="1s"
            values="0;1;0"
            repeatCount="indefinite"
            begin="0.3"
          />
        </circle>
      </svg>
    </div>
    <img
      v-if="src"
      key="loading_svg_image"
      :src="src"
      :alt="file.name"
      class="object-contain m-auto h-full"
    />
    <!-- </transition-group> -->
  </figure>
</template>

<script>
import helper from "~/components/filemanager/mixins/filemanagerHelper";

export default {
  mixins: [helper],
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
      type: Number,
      required: false,
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
      imageLoading: false,
    };
  },
  computed: {
    imageExtensions() {
      return this.$store.state.fm.settings.imageExtensions;
    },
  },
  watch: {
    file() {
      this.loadImage();
    },
    src(v) {
      return v;
    },
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
    async loadImage() {
      this.imageLoading = true;
      await this.api
        .get(
          `/media-manager/file-manager/preview?disk=${this.disk}&path=${
            this.file.path
          }&thumbsize=${this.size ? this.size : ""}`,
          { responseType: "arrayBuffer" },
        )
        .then((response) => {
          const mimeType = this.getMimeTypeFromArrayBuffer(response);
          const imgBase64 = this.arrayBufferToBase64(response);
          this.src = `data:${mimeType};base64,${imgBase64}`;

          this.imageLoading = false;
        });
    },
    thisImage(extension) {
      // extension not found
      if (!extension) return false;
      return this.imageExtensions.includes(extension.toLowerCase());
    },
  },
};
</script>
