<template>
  <confirmation-modal v-if="$store.state.fm.modal.showModal" @on-close="closeModal">
    <template #modal-header>
      {{ $store.state.fm.modal.modalName }}
    </template>

    <template #modal-body>
      <div class="flex justify-between">
        <div class="font-bold">{{ $t("disk") }}:</div>
        <div class="" @click="copyToClipboard(selectedDisk)">
          {{ selectedDisk }}
        </div>
      </div>
      <div class="flex justify-between">
        <div class="font-bold">{{ $t("name") }}:</div>
        <div class="" @click="copyToClipboard(selectedItem.basename)">
          {{ selectedItem.basename }}
        </div>
      </div>
      <div class="flex justify-between">
        <div class="font-bold">{{ $t("path") }}:</div>
        <div class="" @click="copyToClipboard(selectedItem.path)">
          {{ selectedItem.path }}
        </div>
      </div>

      <template v-if="selectedItem.type === 'file'">
        <div class="flex justify-between">
          <div class="font-bold">{{ $t("size") }}:</div>
          <div
            class=""
            @click="copyToClipboard(bytesToHuman(selectedItem.size))"
          >
            {{ bytesToHuman(selectedItem.size) }}
          </div>
        </div>

        <div class="flex justify-between">
          <div class="font-bold">Url:</div>
          <span v-if="url" @click="copyToClipboard(url)">{{ url }}</span>
          <span v-else>
            <button
              type="button"
              class="px-2 py-1 rounded text-themecontrast-400 bg-theme-400"
              @click="getUrl"
            >
              <i class="fas fa-sm fa-link"></i> {{ $t("get url") }}
            </button>
          </span>
        </div>
      </template>

      <template v-if="selectedItem.hasOwnProperty('timestamp')">
        <div class="flex justify-between">
          <div class="font-bold">Updated:</div>
          <div
            class=""
            @click="copyToClipboard(timestampToDate(selectedItem.timestamp))"
          >
            {{ timestampToDate(selectedItem.timestamp) }}
          </div>
        </div>
      </template>

      <template v-if="selectedItem.hasOwnProperty('acl')">
        <div class="flex justify-between">
          <div class="font-bold">Access:</div>
          <div class="">{{ "access_" + selectedItem.acl }}</div>
        </div>
      </template>
    </template>
  </confirmation-modal>
</template>

<script>
import helper from "~/components/filemanager/mixins/filemanagerHelper";

export default {
  name: "Properties",
  mixins: [helper],
  setup() {
    const eventStore = useEventStore();
    const { handleError, handleSuccess } = useMessageHandler();
    return { eventStore, handleError, handleSuccess };
  },
  data() {
    return {
      url: null,
    };
  },
  computed: {
    selectedDisk() {
      return this.$store.getters["fm/content/selectedDisk"];
    },
    selectedItem() {
      return this.$store.getters["fm/content/selectedList"][0];
    },
  },
  beforeUnmount() {
    this.eventStore.off("addNotification");
  },
  methods: {
    getUrl() {
      this.$store
        .dispatch("fm/filemanager/url", {
          disk: this.selectedDisk,
          path: this.selectedItem.path,
        })
        .then((response) => {
          if (response.result.status === "success") {
            this.url = response.url;
          }
        })
        .catch((error) => {
          this.handleError(error);
        });
    },
    copyToClipboard(text) {
      // create input
      const copyInputHelper = document.createElement("input");
      copyInputHelper.className = "copyInputHelper";
      document.body.appendChild(copyInputHelper);
      // add text
      copyInputHelper.value = text;
      copyInputHelper.select();
      // copy text to clipboard
      document.execCommand("copy");
      // clear
      document.body.removeChild(copyInputHelper);

      // Notification
      this.eventStore.emit("addNotification", {
        status: "success",
        message: "Copied to clipboard!",
      });
    },
    closeModal() {
      this.$store.commit("fm/modal/clearModal");
    },
  },
};
</script>
