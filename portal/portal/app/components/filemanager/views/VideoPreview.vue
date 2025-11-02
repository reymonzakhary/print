<template>
  <div>
    <div class="m-auto video_preview">
      <div v-if="loading" class="w-full h-40">
        <svg
          id="L4"
          version="1.1"
          xmlns="http://www.w3.org/2000/svg"
          xmlns:xlink="http://www.w3.org/1999/xlink"
          x="0px"
          y="0px"
          viewBox="0 0 100 100"
          enable-background="new 0 0 0 0"
          xml:space="preserve"
        >
          <circle fill="#fff" stroke="none" cx="36" cy="30" r="3">
            <animate
              attributeName="opacity"
              dur="1s"
              values="0;1;0"
              repeatCount="indefinite"
              begin="0.1"
            />
          </circle>
          <circle fill="#fff" stroke="none" cx="46" cy="30" r="3">
            <animate
              attributeName="opacity"
              dur="1s"
              values="0;1;0"
              repeatCount="indefinite"
              begin="0.2"
            />
          </circle>
          <circle fill="#fff" stroke="none" cx="56" cy="30" r="3">
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

      <video v-if="!loading && src" ref="fmVideo" controls>
        <source :src="src" :type="`video/${videoFile.extension}`" />
      </video>
    </div>
  </div>
</template>

<script>
export default {
  name: "ViedoPlayer",
  setup() {
    const api = useAPI();
    return { api };
  },
  data() {
    return {
      src: null,
      loading: true,
    };
  },
  computed: {
    selectedDisk() {
      return this.$store.getters["fm/content/selectedDisk"];
    },

    videoFile() {
      return this.$store.getters["fm/content/selectedList"][0];
    },
  },
  mounted() {
    this.setSrc();
  },
  methods: {
    setSrc() {
      this.loading = true;
      this.api
        .get(
          `media-manager/file-manager/preview?disk=${this.selectedDisk}&path=${this.videoFile.path}`,
          { responseType: "arrayBuffer" },
        )
        .then((res) => {
          const blob = new Blob([res]);
          const fileURL = URL.createObjectURL(blob);
          this.src = fileURL;
          this.loading = false;
          const vid = this.$refs.fmVideo;
          vid.play();
        });
    },
  },
};
</script>
<style>
.video_preview .plyr__controls {
  display: none;
}
.video_preview .plyr--video {
  min-width: 100px;
  min-height: 100px;
  max-width: 100%;
  max-height: 400px;
  margin: auto;
}
</style>
