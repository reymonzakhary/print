<template>
  <div class="p-4">
    <section class="w-1/2 mx-auto">
      <div
        class="p-4 bg-white rounded shadow-md shadow-gray-200 dark:shadow-gray-900 dark:bg-gray-700"
      >
        <h2 class="font-bold tracking-wide uppercase">
          {{ $t("page groups") }}
        </h2>
        <ul v-if="resource_groups" class="divide-y dark:divide-black">
          <li class="flex items-center justify-between p-2">
            <div class="flex items-center my-2">
              <font-awesome-icon
                :icon="['fal', 'window-restore']"
                class="mr-1"
              />
              <font-awesome-icon :icon="['fal', 'plus']" class="mr-2" />

              <input
                v-model="newResourceGroup"
                type="text"
                class="w-full px-2 py-1 text-sm transition-all duration-100 bg-white border rounded-l shadow-inner hover:bg-gray-50 dark:bg-gray-700 dark:border-gray-900 focus:outline-none focus:ring focus:border-theme-200"
                placeholder="new resource group"
              />
              <button
                class="px-2 py-1 text-sm rounded-r text-themecontrast-500 bg-theme-500"
                @click="
                  create_resource_group(newResourceGroup),
                    (newResourceGroup = '')
                "
              >
                add
              </button>
            </div>
          </li>

          <template v-if="resource_groups.length > 0">
            <li v-for="(group, i) in resource_groups">
              <div
                class="flex items-center justify-between p-2 rounded cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-900 group"
                @click="
                  !show.includes(group.id)
                    ? show.push(group.id)
                    : retract(group.id)
                "
              >
                <span>
                  <font-awesome-icon :icon="['fal', 'window-restore']" />
                  {{ group.name }}
                </span>
                <span>
                  <button
                    class="invisible px-2 text-red-500 group-hover:visible"
                    @click="showRemoveItem = group.id"
                  >
                    <font-awesome-icon :icon="['fal', 'trash-can']" />
                  </button>
                  <font-awesome-icon
                    v-if="group.resources.length > 0"
                    :icon="[
                      'fal',
                      show.includes(group.id) ? 'caret-up' : 'caret-down',
                    ]"
                  />
                </span>
              </div>

              <transition name="slide">
                <div v-show="show.length > 0 && show.includes(group.id)">
                  <div
                    v-for="(resource, idx) in group.resources"
                    :key="idx + '_' + resource.id"
                    class="flex py-2 pl-8 group hover:text-theme-500"
                  >
                    <span>
                      <font-awesome-icon :icon="['fal', 'window']" />
                      {{ resource.title }}
                    </span>
                    <span>
                      <button
                        v-tooltip="'remove resource from group'"
                        class="invisible px-2 text-red-500 rounded-full group-hover:visible hover:bg-red-100"
                        @click="
                          update_resource_group({
                            id: group.id,
                            method: 'detach',
                            type: 'resource',
                            resource_id: resource.id,
                          }),
                            get_resource_groups()
                        "
                      >
                        <font-awesome-icon :icon="['fal', 'link-slash']" />
                      </button>
                    </span>
                  </div>
                </div>
              </transition>

              <transition name="fade">
                <ResourceGroupsRemoveItem
                  v-if="showRemoveItem === group.id"
                  :item="group"
                  @on-close="showRemoveItem = false"
                />
              </transition>
            </li>
          </template>
        </ul>
      </div>
    </section>
  </div>
</template>

<script>
import { mapState, mapActions } from "vuex";
export default {
  data() {
    return {
      newResourceGroup: "",
      reorderResourceGroups: [],
      show: [],
      showRemoveItem: false,
    };
  },
  head() {
    return {
      title: `${this.$t("page groups")} | Prindustry Manager`,
    };
  },
  computed: {
    ...mapState({
      resource: (state) => state.resources.resource,
      resource_groups: (state) => state.resources.resource_groups,
    }),
  },
  watch: {
    resource_groups(newVal) {
      this.reorderResourceGroups = [...this.resource_groups];
    },
  },
  created() {
    this.get_resource_groups();
    if (this.resource_groups && this.resource_groups.length > 0) {
      this.show.push(this.resource_groups[0].id);
    }
  },
  methods: {
    ...mapActions({
      get_resource_groups: "resources/get_resource_groups",
      create_resource_group: "resources/create_resource_group",
      delete_resource_group: "resources/delete_resource_group",
      update_resource_group: "resources/update_resource_group",
    }),
    retract(id) {
      const index = this.show.indexOf(id);
      this.show.splice(index, 1);
    },
  },
};
</script>
