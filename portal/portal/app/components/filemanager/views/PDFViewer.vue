<template>
  <div
    class="fixed top-0 bottom-0 left-0 flex items-center justify-center w-full h-full bg-black modal-content fm-modal-audio-player"
    style="z-index: 9999"
  >
    <div class="modal-header">
      <button
        class="fixed flex items-center m-4 text-4xl text-white aspect-1/1 rounded-full top-4 bg-black right-4 z-[9999]"
        @click="url ? $emit('onClose') : $store.commit('fm/modal/clearModal')"
      >
        <font-awesome-icon :icon="['fad', 'circle-xmark']" />
      </button>
    </div>
    <div class="w-full h-full" style="max-width: 1440px">
      <iframe v-if="show" :src="PDFLink" allowfullscreen height="100%" width="100%"></iframe>
      <div
        v-else
        class="flex flex-col items-center justify-center w-full h-full my-auto text-white place-self-center"
      >
        <div class="flex">
          <font-awesome-icon
            :icon="['fal', 'triangle-exclamation']"
            class="mr-2 text-lg text-amber-500"
          />
          {{
            //prettier-ignore
            $t("your browser is set to download pdf's instead of displaying them. You can change this in your browser settings or download the file below.")
          }}
        </div>
        <a :href="PDFLink" class="p-2 mt-2 border rounded text-theme-500 border-theme-500"
          >{{ $t("download") }} {{ $store.state.fm.content.selected.files[0] }}</a
        >
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    url: {
      type: [String, null],
      default: null,
    },
  },
  emits: ["onClose"],
  data() {
    return {
      show: false,
    };
  },
  computed: {
    PDFLink() {
      if (this.url) return this.url;
      const link = this.$store.state.fm.filemanager.PDFtoShow;
      return link;
    },
  },
  beforeUnmount() {
    this.$store.commit("fm/filemanager/setPDF", null);
  },
  mounted() {
    if (window && window.navigator.pdfViewerEnabled) {
      this.show = true;
    }
  },
};
</script>
