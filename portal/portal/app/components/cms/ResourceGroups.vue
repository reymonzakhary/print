<template>
  <div>
    <SidePanel>
      <template #side-panel-header>
        <h2 class="p-4 font-bold tracking-wide uppercase">
          <font-awesome-icon
            :icon="['fal', 'window-restore']"
            class="mr-2 text-sm"
          />
          <span class="">{{ $t("resource groups") }}</span>
        </h2>
      </template>

      <template #side-panel-content>
        <div class="p-4">
          <ul class="divide-y divide-gray-200 dark:divide-black">
            <li
              v-for="group in resource_groups"
              :key="'group_' + group.id"
              class="flex items-center justify-between p-2 group hover:bg-gray-100 dark:hover:bg-gray-900"
            >
              <div class="flex items-center justify-between w-full">
                {{ group.name }}
                <div
                  class="relative w-10 h-4 mx-2 transition duration-200 ease-linear rounded-full cursor-pointer"
                  :class="[
                    group.resources.length > 0 &&
                    group.resources.find((x) => x.id === editableResource.id)
                      ? 'bg-green-500'
                      : 'bg-gray-300',
                  ]"
                  @click="
                    group.resources.length > 0 &&
                    group.resources.find((x) => x.id === editableResource.id)
                      ? update_resource_group({
                          id: group.id,
                          method: 'detach',
                          type: 'resource',
                          resource_id: editableResource.id,
                        })
                      : update_resource_group({
                          id: group.id,
                          method: 'attach',
                          type: 'resource',
                          resource_id: editableResource.id,
                        }),
                      updateLocal()
                  "
                >
                  <label
                    class="absolute left-0 w-4 h-4 mb-2 transition duration-100 ease-linear transform bg-white border-2 rounded-full cursor-pointer"
                    :class="[
                      group.resources.length > 0 &&
                      group.resources.find((x) => x.id === editableResource.id)
                        ? 'translate-x-6 border-green-500'
                        : 'translate-x-0 border-gray-300',
                    ]"
                  >
                    <input
                      type="checkbox"
                      hidden
                      class="w-full h-full appearance-none active:outline-none focus:outline-none"
                    />
                  </label>
                </div>
              </div>
            </li>
          </ul>
        </div>
      </template>
    </SidePanel>
  </div>
</template>

<script>
import { mapState, mapActions } from "vuex";
export default {
  name: "ResourceGroups",
  props: {
    editableResource: Object,
  },
  emits: ["onClose"],
  computed: {
    ...mapState({
      resource: (state) => state.resources.resource,
      resource_groups: (state) => state.resources.resource_groups,
    }),
  },
  created() {
    this.get_resource_groups();
  },
  methods: {
    ...mapActions({
      get_resource_groups: "resources/get_resource_groups",
      create_resource_group: "resources/create_resource_group",
      delete_resource_group: "resources/delete_resource_group",
      update_resource_group: "resources/update_resource_group",
    }),
    updateLocal() {
      setTimeout(() => {
        this.get_resource_groups();
      }, 50);
    },
    close() {
      this.$parent.showResourceGroups = false;
      this.$emit("onClose");
    },
  },
};
</script>
