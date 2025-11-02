<template>
  <div
    v-if="permissions.includes('campaigns-list')"
    class="flex flex-col items-center"
  >
    <font-awesome-icon
      :icon="['fad', 'fire']"
      class="mb-4 text-red-500 fa-5x"
    />
    <div class="flex flex-col">
      <div class="flex justify-between w-full pb-1 mb-1 border-b">
        <span class="px-2 mr-4 text-red-500 bg-red-100 rounded-full">
          {{ exportsLength }}
        </span>
        {{ $t("exports") }}
      </div>
      <div v-if="exportsLength" class="flex justify-between w-full">
        <span class="px-2 mr-4 text-red-500 bg-red-100 rounded-full">
          {{ exportOutingsLength }}
        </span>
        {{ $t("files generated") }}
      </div>
    </div>
  </div>
</template>

<script>
export default {
  setup() {
    const API = useAPI();
    const { permissions } = storeToRefs(useAuthStore());
    return { permissions, API };
  },
  data() {
    return {
      campaigns: [],
    };
  },
  computed: {
    exportsLength() {
      const exportCount = this.campaigns.reduce(
        (count, current) => count + current.exports.length,
        0,
      );
      return exportCount;
    },
    exportOutingsLength() {
      let exportsCount = 0;
      this.campaigns.reduce((count, current) => {
        for (let i = 0; i < current.exports.length; i++) {
          if (current.exports[i].path) {
            const outings = Object.keys(current.exports[i].path).length;
            exportsCount += count + outings;
          }
        }
      }, 0);
      return exportsCount;
    },
  },
  watch: {
    campaigns: {
      handler(v) {
        return v;
      },
      deep: true,
    },
    exportsLength(v) {
      return v;
    },
  },
  mounted() {
    this.getCampaigns();
  },
  methods: {
    getCampaigns() {
      this.API.get("modules/campaigns")
        .then((response) => {
          this.campaigns = response.data;
        })
        .catch((error) => {
          this.handleError(error);
        });
    },
  },
};
</script>

<style></style>
