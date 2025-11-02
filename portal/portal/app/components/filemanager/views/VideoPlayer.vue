<template>
  <div
    class="fm-modal-video-player fixed bottom-0 left-0 top-0 z-50 flex h-full w-full items-end justify-center bg-black p-4"
  >
    <div class="modal-header">
      <button
        class="absolute right-0 top-0 m-4 flex items-center text-white"
        @click="$store.commit('fm/modal/clearModal')"
      >
        {{ $t("close") }}
        <font-awesome-icon :icon="['fad', 'circle-xmark']" class="ml-2" />
      </button>
    </div>
    <div class="modal-body m-auto">
      <div v-if="loading" class="h-40 w-full">
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
      <video
        v-if="src && !loading"
        ref="fmVideo"
        controls
        style="max-width: 800px; max-height: 50vh"
      >
        <source :src="src" :type="`video/${videoFile.extension}`" />
      </video>
    </div>
  </div>
</template>

<script>
import { mapGetters } from "vuex";
export default {
  name: "Player",
  props: {
    image: Object,
  },
  setup() {
    const api = useAPI();
    const { handleError } = useMessageHandler();
    const { t } = useI18n();
    return { api, handleError, t };
  },
  data() {
    return {
      src: "",
      player: {},
      loading: true,
    };
  },
  computed: {
    ...mapGetters({
      selectedList: "fm/content/selectedList",
    }),
    selectedDisk() {
      if (!this.$store.getters["fm/content/selectedDisk"]) {
        return this.image.disk;
      }
      return this.$store.getters["fm/content/selectedDisk"];
    },

    videoFile() {
      if (this.selectedList.length === 0) {
        return this.image;
      }
      return this.selectedList[0];
    },
  },
  watch: {
    selectedList() {
      this.setSource();
    },
  },
  mounted() {
    this.setSource();
  },
  unmounted() {
    if (this.src?.startsWith("blob:")) URL.revokeObjectURL(this.src);
  },
  methods: {
    async setSource() {
      this.loading = true;
      let attempts = 0;
      const maxAttempts = 3;

      const tryLoad = async () => {
        try {
          const res = await this.api.get(
            `/media-manager/file-manager/preview?disk=${this.selectedDisk}&path=${this.videoFile.path}`,
            { responseType: "arrayBuffer" },
          );
          const buf = res && res.data !== undefined ? res.data : res;
          const blob = new Blob([buf], { type: `video/${this.videoFile.extension}` });
          this.src = URL.createObjectURL(blob);
          this.loading = false;
        } catch (error) {
          if (!navigator.onLine) {
            // If offline, wait for online status
            await new Promise((resolve) => {
              window.addEventListener("online", resolve, { once: true });
            });
            return tryLoad();
          }

          if (attempts < maxAttempts) {
            attempts++;
            return tryLoad();
          }
          this.handleError(this.t("Your network is unstable, please try again later."));

          this.loading = false;
          return;
        }
      };

      await tryLoad();
    },
  },
};
</script>

<style lang="scss">
.plyr__control--overlaid {
  @apply bg-theme-400 text-themecontrast-400;
}
.plyr--full-ui input[type="range"] {
  @apply text-theme-500;
}
.plyr--video {
  min-width: 300px;
  min-height: 300px;
  max-width: 600px;
  max-height: 90vh;
}
.plyr--video .plyr__control.plyr__tab-focus,
.plyr--video .plyr__control:hover,
.plyr--video .plyr__control[aria-expanded="true"] {
  @apply bg-theme-400 text-themecontrast-400;
}
</style>
