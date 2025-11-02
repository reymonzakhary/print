<template>
  <transition name="fade" mode="out-in">
    <object class="w-full">
      <embed
        :src="$store.state.fm.filemanager.PDFtoShow"
        :alt="file.filename"
        class="object-fill w-full min-w-[4rem]"
        type="application/pdf"
      />
    </object>
  </transition>
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
		const api = useAPI();
		return { api };
	},
  data() {
    return {
      src: "",
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
      await this.api
        .get(
          `media-manager/file-manager/download?disk=${this.disk}&path=${this.file.path} `,
          { responseType: "arrayBuffer" },
        )
        .then((res) => {
          const fileURL = window.URL.createObjectURL(
            new Blob([res], { type: "application/pdf" }),
          );

          this.$store.commit("fm/filemanager/setPDF", fileURL);
        });
    },
  },
};
</script>
