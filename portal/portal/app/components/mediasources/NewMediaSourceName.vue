<template>
  <div>
    <AddMediaSourceHeader
      :step="1"
      :to_page="'/manage/media-sources'"
      class="sticky top-0 bg-gray-100 dark:bg-gray-800"
    />

    <div
      class="flex flex-wrap w-full p-4 m-auto bg-white rounded shadow-md shadow-gray-200 dark:shadow-gray-900 sm:w-1/2 lg:w-1/4"
    >
      <label class="w-full text-sm font-bold tracking-wide uppercase">
        {{ $t("mediasource name") }}
      </label>
      <input
        v-model="name"
        type="text"
        class="input"
        :placeholder="$t('new name')"
      />
      <button
        class="px-2 py-1 mt-4 ml-auto rounded bg-theme-500 text-themecontrast-500"
        @click="create()"
      >
        {{ $t("next") }}
      </button>
    </div>
  </div>
</template>

<script>
import AddMediaSourceHeader from "~/components/mediasources/AddMediaSourceHeader";
import { mapMutations } from "vuex";

export default {
  components: {
    AddMediaSourceHeader,
  },
  setup() {
    const api = useAPI();
    return { api };
  },
  data() {
    return {
      name: "",
    };
  },
  methods: {
    ...mapMutations({
      setSelectedMS: "mediasource/setSelectedMS",
      setMSComponent: "mediasource/setMSComponent",
    }),
    create() {
      // this.setMSComponent("NewMediaSourceFilesAndFolders");
      thisapiaxios
        .post("media-sources", {
          name: this.name,
          ctx_id: 1,
        })
        .then((response) => {
          this.setSelectedMS(response);
          this.setMSComponent("NewMediaSourceFilesAndFolders");
        })
        .catch((error) => this.handleError(error));
    },
  },
};
</script>
