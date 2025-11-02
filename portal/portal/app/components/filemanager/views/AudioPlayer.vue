<template>
  <div
    class="fixed top-0 bottom-0 left-0 z-50 flex items-center justify-center w-full h-full bg-black modal-content fm-modal-audio-player"
  >
    <div class="modal-header">
      <button
        class="absolute top-0 right-0 flex items-center m-4 text-white"
        @click="$store.commit('fm/modal/clearModal')"
      >
        {{ $t("close") }}
        <font-awesome-icon :icon="['fad', 'circle-xmark']" class="ml-2" />
      </button>
    </div>
    <div class="w-full max-w-4xl text-center border rounded">
      <h5 class="py-4 font-bold text-white modal-title">
        {{ audioFiles[0].basename }}
      </h5>
      <audio ref="fmAudio" controls>
        <source
          :src="`media-manager/file-manager/preview?disk=${selectedDisk}&path=${audioFiles[0].path}#t=0.1`"
          :type="`audio/${audioFiles[0].extension}`"
        />
      </audio>

      <!-- <div
				class="hidden px-2 py-2 d-flex justify-content-between"
				v-bind:class="playingIndex === index ? 'bg-light' : ''"
				v-for="(item, index) in audioFiles"
				v-bind:key="index"
			>
				<div class="w-75 text-truncate">
					<span class="pr-2 text-muted">{{ index }}.</span>
					{{ item.basename }}
				</div>
				<template v-if="playingIndex === index">
					<div v-if="status === 'playing'">
						<i v-on:click="togglePlay()" class="fas fa-play active"></i>
					</div>
					<div v-else>
						<i v-on:click="togglePlay()" class="fas fa-pause"></i>
					</div>
				</template>
				<template v-else>
					<div>
						<i v-on:click="selectTrack(index)" class="fas fa-play"></i>
					</div>
				</template>
			</div> -->
    </div>
  </div>
</template>

<script>
// import Plyr from "plyr";
// import translate from './../../../mixins/translate';

export default {
  name: "Player",
  setup() {
    const api = useAPI();
    return { api };
  },
  data() {
    return {
      player: {},
      playingIndex: 0,
      status: "paused",
    };
  },
  computed: {
    /**
     * Selected disk
     * @returns {*}
     */
    selectedDisk() {
      return this.$store.state.fm.content.selectedDisk;
    },

    /**
     * Audio files list
     * @returns {*}
     */
    audioFiles() {
      return this.$store.getters["fm/content/selectedList"];
    },
  },
  mounted() {
    // initiate player
    // this.player = new Plyr(this.$refs.fmAudio, {
    // 	speed: {
    // 		selected: 1,
    // 		options: [0.5, 1, 1.5]
    // 	}
    // });
    // select first item in the list
    // this.setSource(this.playingIndex);
    // add event listeners
    // this.player.on("play", () => {
    // 	this.status = "playing";
    // });
    // this.player.on("pause", () => {
    // 	this.status = "paused";
    // });
    // this.player.on("ended", () => {
    // 	if (this.audioFiles.length > this.playingIndex + 1) {
    // 		// play next track
    // 		this.selectTrack(this.playingIndex + 1);
    // 	}
    // });
  },
  beforeUnmount() {
    // destroy player
    // this.player.destroy();
  },
  methods: {
    /**
     * Select another audio track
     * @param index
     */
    // selectTrack(index) {
    // 	if (this.player.playing) {
    // 		// stop playing
    // 		this.player.stop();
    // 	}
    // 	// load new source
    // 	this.setSource(index);
    // 	// start play
    // 	this.player.play();

    // 	this.playingIndex = index;
    // },

    /**
     * Set source to audio player
     * @param index
     */
    async setSource() {
      const res = await this.api.get(
        `media-manager/file-manager/preview?disk=${this.selectedDisk}&path=${this.audioFiles[0].path}`,
        { responseType: "arrayBuffer" },
      );
      const fileURL = window.URL.createObjectURL(
        new Blob([res.data], { type: res.headers }),
      );

      return fileURL;

      // return;
      // this.player.source = {
      // 	type: "audio",
      // 	title: this.audioFiles[index].basename,
      // 	sources: [
      // 		{
      // 			src: fileURL,
      // 			type: `audio/${this.audioFiles[index].extension}`
      // 		}
      // 	]
      // };
    },

    /**
     * Play/Pause
     */
    togglePlay() {
      this.player.togglePlay();
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

.plyr--audio .plyr__control.plyr__tab-focus,
.plyr--audio .plyr__control:hover,
.plyr--audio .plyr__control[aria-expanded="true"] {
  @apply bg-theme-400 text-themecontrast-400;
}
</style>
