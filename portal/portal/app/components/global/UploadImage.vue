<template>
  <div>
    <FileManagerSelectPanel
      v-if="showFileManagerSelectPanel === true"
      :disk="disk"
      :disk-selector="diskSelector"
      @updates-setting="updatesSetting($event)"
      @update-value="updateValue($event.path)"
    ></FileManagerSelectPanel>
    <!--  array display  -->
    <div v-if="Array.isArray(image.path)" class="flex">
      <div v-for="(src, index) in arraySrc" :key="index" class="mx-2">
        <div
          class="flex items-center w-full pr-2 my-1 transition-colors duration-300 rounded cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-900 group"
          @click="open"
        >
          <div>
            <div>
              <img v-if="src" :src="src" alt="" class="bg-gray-100 rounded" width="100" />
              <font-awesome-icon
                v-else
                :icon="['fal', 'image']"
                class="px-2 text-gray-500 bg-gray-200 rounded text-7xl dark:bg-gray-800"
              />
            </div>
            <button
              v-if="image && image.path"
              class="invisible px-2 py-1 mx-10 my-2 text-sm text-red-500 border border-red-500 rounded-full group-hover:visible"
              @click.stop="updateValue('', index)"
            >
              <font-awesome-icon :icon="['fal', 'trash']" />
              <!-- {{$t('remove')}} -->
            </button>
          </div>
        </div>
      </div>
      <a class="px-2 pt-5 text-sm italic text-gray-500 cursor-pointer" @click="open">
        <!-- {{ $t("click to select image") }} -->
        <font-awesome-icon
          :icon="['fas', 'upload']"
          class="px-2 text-gray-500 bg-gray-200 rounded dark:bg-gray-800"
          :class="`text-${size}`"
        />
      </a>
    </div>
    <!--  single image display -->
    <label
      v-else
      class="flex items-center w-full pr-2 my-1 transition-colors duration-300 rounded cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-900 group"
      @click="open"
    >
      <div>
        <img v-if="src" :src="src" alt="" class="bg-gray-100 rounded" width="100" />
        <template v-else>
          <font-awesome-icon
            :icon="['fal', 'image']"
            class="rounded bg-gray-200 px-2 text-7xl text-gray-500 dark:bg-gray-800"
          />
          <p class="px-2 text-sm italic text-gray-500">
            {{ $t("click to select image") }}
          </p>
        </template>
        <button
          v-if="image && image.path"
          class="invisible px-2 py-1 ml-auto text-sm text-red-500 border border-red-500 rounded-full group-hover:visible"
          @click.stop="updateValue('', index)"
        >
          <font-awesome-icon :icon="['fal', 'trash']" />
          <!-- {{$t('remove')}} -->
        </button>
      </div>
    </label>
  </div>
</template>

<script>
export default {
  props: {
    multiple: { type: Boolean },
    image: {
      type: [Array, String, Object],
      required: true,
    },
    value: {
      type: [String, Array],
      required: false,
    },
    disk: {
      required: false,
      type: String,
      default: "assets",
    },
    diskSelector: {
      required: false,
      default: true,
      type: Boolean,
    },
    size: {
      required: false,
      type: String,
      default: "7xl",
    },
  },
  emits: ["imageChanged", "updateSettingLogo"],
  setup() {
    const api = useAPI();
    const { addToast } = useToastStore();
    return { api, addToast };
  },
  data() {
    return {
      img: null,
      imageName: null,
      showFileManagerSelectPanel: false,
      src: "",
      arraySrc: [],
    };
  },
  watch: {
    value: {
      immediate: true,
      handler(val, oldVal) {
        if (val != oldVal) {
          this.getImage();
        }
      },
    },
    image: {
      immediate: true,
      handler(val, oldVal) {
        this.getImage();
      },
    },
    arraySrc: {
      deep: true,
      handler(val, oldVal) {
        return val;
      },
    },
  },
  mounted() {
    if (this.image) {
      this.getImage();
    }
  },
  methods: {
    updateValue(value, index) {
      if (typeof index === "number") {
        this.arraySrc.splice(index, 1);
      }
      this.$emit("imageChanged", { value: value, index: index });
      this.addToast({
        message: this.$t("You made changes, be sure to save them"),
        type: "info",
      });
    },
    updatesSetting(value) {
      this.$emit("updateSettingLogo", value);
    },
    open() {
      this.showFileManagerSelectPanel = true;
    },
    async getImage() {
      if (Array.isArray(this.image.path)) {
        this.image.path.map((image) => {
          this.api
            .get(
              `/media-manager/file-manager/thumbnails?disk=${this.disk ?? "tenancy"}&path=${image}`,
              { responseType: "arraybuffer" },
            )
            .then((response) => {
              const mimeType = response.headers["content-type"].toLowerCase();
              const imgBase64 = Buffer.from(response.data, "binary").toString("base64");
              if (!this.arraySrc.includes(`data:${mimeType};base64,${imgBase64}`)) {
                this.arraySrc.push(`data:${mimeType};base64,${imgBase64}`);
              }
            })
            .catch(() => {
              this.arraySrc = [];
            });
        });
      } else {
        if (this.image && this.image.path) {
          await this.api
            .get(
              `/media-manager/file-manager/thumbnails?disk=${this.disk ?? "tenancy"}&path=${this.image.path}`,
              { responseType: "arraybuffer" },
            )
            .then((response) => {
              const mimeType = response.headers["content-type"].toLowerCase();
              const imgBase64 = Buffer.from(response.data, "binary").toString("base64");
              this.src = `data:${mimeType};base64,${imgBase64}`;
            })
            .catch(() => {
              this.src = "";
            });
        } else {
          this.src = "";
        }
      }
    },
  },
};
</script>
