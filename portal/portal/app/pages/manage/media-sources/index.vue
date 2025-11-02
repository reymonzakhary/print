<template>
  <div class="flex items-start space-x-4 p-4">
    <div
      class="w-1/3 rounded bg-white shadow-md shadow-gray-200 dark:bg-gray-700 dark:shadow-gray-900"
    >
      <div class="p-4 pb-2 text-sm font-bold uppercase tracking-wide">
        <font-awesome-icon :icon="['fal', 'photo-film-music']" class="mr-2" />
        {{ $t("mediasources") }}
      </div>
      <div class="bg-gray-50 px-4 py-2">
        <div class="flex">
          <input
            v-model="name"
            type="text"
            class="input rounded-r-none px-2 py-1"
            :placeholder="$t('new mediasource name')"
          />
          <button
            class="rounded-r bg-theme-400 px-4 py-1 text-themecontrast-400"
            @click="createMS()"
          >
            {{ $t("create") }}
          </button>
        </div>
      </div>

      <div class="p-2">
        <div
          v-for="(ms, i) in mediaSources"
          :key="`ms_${ms.id}_${i}`"
          class="group flex cursor-pointer items-center justify-between rounded px-2 py-1 hover:bg-gray-100"
          :class="{
            'bg-theme-100 text-theme-500': ms.id === selectedMediaSource.id,
          }"
          @click="setSelectedMS(ms)"
        >
          {{ ms.name }}
          <button
            class="invisible mx-2 rounded-full px-2 py-1 text-red-500 transition hover:bg-red-100 group-hover:visible"
            @click="deleteMS(ms.id)"
          >
            <font-awesome-icon :icon="['fal', 'trash-can']" />
          </button>
        </div>
      </div>
    </div>

    <div
      v-if="Object.keys(selectedMediaSource).length > 0 && mediaSources.length > 0"
      class="w-2/3 rounded bg-white p-4 shadow-md shadow-gray-200"
    >
      <div class="mb-2 text-sm font-bold uppercase tracking-wide">
        {{ selectedMediaSource.name }}
      </div>

      <div class="flex w-full">
        <div
          class="flex w-full items-center justify-between rounded-l border border-r-0 bg-gray-50 p-2 text-sm"
        >
          <div class="mx-2 w-full">
            <button
              v-if="Object.keys(disk).length === 0"
              class="w-full rounded border border-theme-400 px-2 py-1 text-theme-500 hover:bg-theme-100"
              @click="showDisks = true"
            >
              <font-awesome-icon :icon="['fal', 'hard-drive']" />
              {{ $t("select disk") }}
            </button>
            <div v-else class="flex w-full items-center">
              <div class="font-bold text-gray-500">
                <font-awesome-icon :icon="['fal', 'hard-drive']" class="mr-2" />
                {{ disk }}
              </div>
              <button
                class="mx-2 rounded-full px-2 py-1 text-theme-500 hover:bg-theme-100"
                @click="showDisks = true"
              >
                <font-awesome-icon :icon="['fal', 'pencil']" />
              </button>
            </div>
          </div>

          <div class="mx-2 flex w-full min-w-max items-center">
            <button
              v-if="!path"
              :class="{
                'border-gray-300 text-gray-300 hover:bg-white': Object.keys(disk).length === 0,
              }"
              class="w-full rounded border border-theme-400 px-2 py-1 text-theme-500 hover:bg-theme-100"
              @click="Object.keys(disk).length > 0 ? (showFiles = true) : ''"
            >
              <font-awesome-icon :icon="['fal', 'road']" />
              {{ $t("select path") }}
            </button>
            <div v-else class="flex w-full items-center">
              <div class="font-bold text-gray-500">
                <font-awesome-icon :icon="['fal', 'road']" class="mr-2" />
                /{{ path }}
              </div>
              <button
                class="mx-2 w-full min-w-max rounded-full px-2 py-1 text-theme-500 hover:bg-theme-100"
                @click="showFiles = true"
              >
                <font-awesome-icon :icon="['fal', 'pencil']" />
              </button>
            </div>
            <select
              v-if="path && !filetypes.includes(path.split('.').pop())"
              id=""
              v-model="inheritance"
              name=""
              class="input px-2 py-1"
              :class="{ 'rounded-r-none': inheritance === '/*.' }"
            >
              <option value="">
                {{ $t("select inheritance mode") }}
              </option>
              <option value="*">{{ $t("all") }}</option>
              <option value="/*">{{ $t("all subfolders") }}</option>
              <option value="/*.">{{ $t("all of type") }}</option>
            </select>
            <select
              v-if="inheritance === '/*.'"
              id=""
              v-model="filetype"
              name=""
              class="input rounded-l-none px-2 py-1"
            >
              <option>{{ $t("select filetype") }}</option>
              <option v-for="type in filetypes" :key="type" :value="type">
                {{ type }}
              </option>
            </select>
          </div>

          <select id="access" v-model="access" name="access" class="input px-2 py-1">
            <option>{{ $t("select access type") }}</option>
            <option value="0">{{ $t("deny") }}</option>
            <option value="1">{{ $t("read") }}</option>
            <option value="2">{{ $t("read/write") }}</option>
          </select>
        </div>
        <button
          class="min-w-max rounded-r bg-theme-400 px-4 py-1 text-themecontrast-400 hover:bg-theme-500"
          @click="createRule"
        >
          {{ $t("add rule") }}
        </button>
      </div>

      <div
        v-for="(rule, i) in selectedMediaSource.rules"
        :key="`rule_${i}`"
        class="my-1 flex items-center justify-between italic text-gray-500"
      >
        <div class="flex-1">
          <font-awesome-icon :icon="['fal', 'hard-drive']" />
          {{ rule.disk }}
        </div>
        <div class="flex-1">
          <font-awesome-icon :icon="['fal', 'road']" />
          {{ rule.path }}
        </div>
        <div class="flex-1">
          <font-awesome-icon :icon="['fal', Number(rule.access) === 0 ? 'lock' : 'key']" />
          {{
            Number(rule.access) === 0
              ? $t("deny")
              : Number(rule.access) === 1
                ? $t("read")
                : $t("read/write")
          }}
        </div>
        <button
          class="mx-2 rounded-full px-2 py-1 text-red-500 hover:bg-red-100"
          :disabled="deleteLoading"
          @click="deleteRule(rule.id)"
        >
          <font-awesome-icon :icon="['fal', 'trash']" />
        </button>
      </div>
    </div>

    <ConfirmationModal v-if="showDisks">
      <template #modal-header>{{ selectedMediaSource.name }}</template>
      <template #modal-body>
        <MediaSourceDiskList class="w-full" @disk-selected="disk = $event" />
      </template>
      <template #cancel-button>
        <button
          class="mr-4 rounded-full bg-gray-200 px-2 py-1 text-sm hover:bg-gray-300"
          @click="((showDisks = false), (disk = {}))"
        >
          {{ $t("close") }}
        </button>
      </template>
      <template #confirm-button>
        <button
          class="rounded-full bg-theme-500 px-2 py-1 text-sm text-themecontrast-500 hover:bg-theme-600"
          @click="showDisks = false"
        >
          {{ $t("choose disk") }}
        </button>
      </template>
    </ConfirmationModal>

    <MediaSourceFilesSelectPanel
      v-if="showFiles"
      :disk="disk"
      @media-source-path="((path = $event), (showFiles = false))"
    />

    <MediaSourceUserSelectPanel
      v-if="showUsers"
      :disk="disk"
      @media-source-user="((userId = $event.id), (user = $event), (showUsers = false))"
    />
  </div>
</template>

<script>
import { mapState, mapMutations, mapActions } from "vuex";
import MediaSourceDiskList from "~/components/mediasources/MediaSourceDiskList.vue";
import MediaSourceFilesSelectPanel from "~/components/mediasources/MediaSourceFilesSelectPanel.vue";
import MediaSourceUserSelectPanel from "~/components/mediasources/MediaSourceUserSelectPanel.vue";

export default {
  components: {
    MediaSourceDiskList,
    MediaSourceFilesSelectPanel,
    MediaSourceUserSelectPanel,
  },
  setup() {
    const api = useAPI();
    const { handleError, handleSuccess } = useMessageHandler();
    return { api, handleError, handleSuccess };
  },
  data() {
    return {
      name: "",
      showDisks: false,
      disk: {},
      deleteLoading: false,
      showFiles: false,
      path: "",
      inheritance: "",
      filetype: "",
      filetypes: [
        "gif",
        "png",
        "jpeg",
        "jpg",
        "bmp",
        "psd",
        "svg",
        "ico",
        "ai",
        "tif",
        "tiff",
        "json",
        "log",
        "ini",
        "xml",
        "md",
        "env",
        "php",
        "css",
        "cpp",
        "class",
        "h",
        "java",
        "sh",
        "swift",
        "htm",
        "html",
        "cda",
        "mid",
        "mp3",
        "mpa",
        "ogg",
        "wav",
        "wma",
        "avi",
        "mpeg",
        "mpg",
        "flv",
        "mp4",
        "mkv",
        "mov",
        "ts",
        "3gppo",

        "arj",
        "deb",
        "pkg",
        "rar",
        "rpm",
        "7ze",
        "tare-archive",

        "rtf",
        "doc",
        "docx",
        "odt",

        "xlr",
        "xls",
        "xlsx",

        "ppt",
        "pptx",
        "pptm",
        "xps",
        "potx",
      ],
      showUsers: false,
      user: {},
      userId: null,
      access: null,
    };
  },
  computed: {
    ...mapState({
      mediaSources: (state) => state.mediasource.mediaSources,
      selectedMediaSource: (state) => state.mediasource.selectedMediaSource,
      users: (state) => state.users.users,
    }),
  },
  watch: {
    mediaSources: {
      deep: true,
      handler(v) {
        return v;
      },
    },
    selectedMediaSource(v) {
      return v;
    },
  },
  created() {
    this.getMSs();
    this.get_users();
    this.disk = {};
    this.path = "";
  },
  methods: {
    ...mapMutations({
      setMediaSources: "mediasource/setMediaSources",
      deleteMediaSource: "mediasource/deleteMediaSource",
      setMSComponent: "mediasource/setMSComponent",
      setSelectedMS: "mediasource/setSelectedMS",
      setMediaSourceRules: "mediasource/setMediaSourceRules",
      addMediaSourceRules: "mediasource/addMediaSourceRules",
      removeMediaSourceRule: "mediasource/removeMediaSourceRule",
    }),
    ...mapActions({
      get_users: "users/get_users",
    }),
    getMSs() {
      this.api.get("media-sources").then((response) => {
        this.setMediaSources(response.data);
      });
    },
    createMS() {
      this.api
        .post("media-sources", {
          name: this.name,
          ctx_id: 1,
        })
        .then((response) => {
          this.handleSuccess(response);
          this.setSelectedMS(response);
          this.getMSs();
        })
        .catch((error) => this.handleError(error));
    },
    deleteMS(id) {
      this.api
        .delete(`media-sources/${id}`)
        .then((response) => {
          this.deleteMediaSource(id);
          this.handleSuccess(response);
        })
        .catch((error) => this.handleError(error));
    },
    createRule() {
      this.api
        .post(`media-sources/${this.selectedMediaSource.id}/rules`, {
          // user_id: this.userId,
          disk: this.disk,
          path: `${this.path}${this.inheritance}${this.filetype}`,
          access: this.access,
          media_source_id: this.selectedMediaSource.id,
        })
        .then((response) => {
          this.addMediaSourceRules(response.data);
          this.handleSuccess(response);
        })
        .catch((error) => this.handleError(error));
    },
    deleteRule(id) {
      this.deleteLoading = true;
      this.api
        .delete(`media-sources/${this.selectedMediaSource.id}/rules/${id}`)
        .then((response) => {
          this.removeMediaSourceRule(id);
          this.handleSuccess(response);
        })
        .catch((error) => this.handleError(error))
        .finally(() => {
          this.deleteLoading = false;
        });
    },
    close() {
      this.showUsers = false;
      this.user = {};
      this.userId = null;
    },
    closeModal() {
      this.showDisks = false;
      this.disk = {};
    },
  },
};
</script>
