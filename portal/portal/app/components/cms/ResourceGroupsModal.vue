<template>
  <SlideInModal
    :icon="['fas', 'sitemap']"
    title="Resource Groups"
    :show="show"
    @on-close="$emit('onClose')"
    @on-backdrop-click="$emit('onClose')"
  >
    <ul v-if="isFetching">
      <li
        v-for="i in 3"
        :key="i"
        class="h-10 px-4 py-2 my-1 bg-gray-200 dark:bg-gray-900 loading"
      ></li>
    </ul>
    <ul v-else>
      <li
        v-for="resourceGroup in resourceGroups"
        :key="resourceGroup.id"
        class="flex items-center justify-between max-w-2xl gap-4 px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-800"
        @click="() => handleResourceGroupToggle(resourceGroup.id)"
      >
        <span>{{ resourceGroup.name }}</span>
        <div>
          <UISwitch
            :value="resourceGroup.enabled"
            :disabled="isDoingServerStuff"
            @input="() => handleResourceGroupToggle(resourceGroup.id)"
          />
        </div>
      </li>
    </ul>
  </SlideInModal>
</template>

<script>
export default {
  name: "ResourceGroupsModal",
  props: {
    show: {
      type: Boolean,
      required: true,
    },
    resource: {
      type: Number,
      required: true,
    },
  },
  emits: ["onClose"],
  setup() {
    const api = useAPI();
    const { handleError, handleSuccess } = useMessageHandler();
    return { api, handleError, handleSuccess };
  },
  data() {
    return {
      isDoingServerStuff: false,
      isFetching: true,
      resourceGroups: [
        {
          id: 1,
          name: "Group 1",
          enabled: true,
        },
      ],
    };
  },
  async created() {
    await this.fetchResourceGroups();
  },
  methods: {
    handleResourceGroupToggle(id) {
      if (this.isDoingServerStuff) return;
      const resourceGroup = this.resourceGroups.find(
        (resourceGroup) => resourceGroup.id === id,
      );
      resourceGroup.enabled = !resourceGroup.enabled;
      this.toggleResourceAttachment(
        id,
        resourceGroup.enabled ? "attach" : "detach",
      );
    },
    async toggleResourceAttachment(groupId, mode) {
      if (mode !== "detach" && mode !== "attach") {
        console.error(
          'You have provided an invalid mode. Please provide either "detach" or "attach" you silly goose',
        );
        return this.handleError(error);
      }

      try {
        this.isDoingServerStuff = true;
        await this.api.put(
          `/modules/cms/resources/groups/${groupId}?${mode}=resource`,
          { resource_id: this.resource },
        );
        this.isDoingServerStuff = false;
      } catch (error) {
        console.error(error);
        this.handleError(error);
      }
    },
    async fetchResourceGroups() {
      try {
        this.isFetching = true;
        const { data } = await this.api.get(`modules/cms/resources/groups`);
        this.resourceGroups = this.normalizeResourceGroups(data);
        this.isFetching = false;
      } catch (error) {
        console.error(error);
        this.handleError(error);
      }
    },
    normalizeResourceGroups(resourceGroups) {
      return resourceGroups.map((resourceGroup) => {
        return {
          id: resourceGroup.id,
          name: resourceGroup.name,
          enabled: resourceGroup.resources.some(
            (resource) => resource.id === this.resource,
          ),
        };
      });
    },
  },
};
</script>
