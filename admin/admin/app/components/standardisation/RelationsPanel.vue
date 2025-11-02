<template>
  <div>
    <SidePanel>
      <template #side-panel-header>
        <div class="flex items-center p-4">
          <img
            id="prindustry-logo"
            src="images/Prindustry-box.png"
            alt="Prindustry Logo"
            class="h-6 mr-1"
          />

          <p class="font-bold tracking-wide uppercase">
            <span class="text-gray-500">{{ type }}</span> {{ item.name }}
          </p>
        </div>
      </template>
      <template #side-panel-content>
        <div class="relative mt-4">
          <transition name="slide">
            <div
              v-if="responseMessage !== ''"
              :class="`bg-${responseStatusColor}-500 text-${responseStatusColor}-800 rounded p-2 m-4`"
            >
              {{ responseMessage }}
            </div>
          </transition>

          <!-- ITEM'S RELATIONS -->
          <div class="p-4 mt-4 mb-2 text-sm font-bold tracking-wide uppercase">
            <font-awesome-icon :icon="['fal', 'folder-tree']" class="mr-1" />
            <span class="text-gray-500">Relations </span>
          </div>

          <div
            class="sticky top-0 flex items-center p-4 text-sm font-bold tracking-wide uppercase bg-white border-b-2 dark:border-gray-900 blur dark:bg-gray-800"
          >
            <span class="flex-1"> Category </span>

            <font-awesome-icon :icon="['fal', 'caret-right']" class="flex-1" />

            <span class="flex-1">Box</span>

            <font-awesome-icon :icon="['fal', 'caret-right']" class="flex-1" />

            <span class="flex-1"> Option </span>
          </div>

          <ul v-if="relations.length > 0" class="text-sm divide-y dark:divide-gray-900">
            <li
              v-for="(relation, i) in relations"
              :key="i"
              class="px-4 py-1 transition-colors hover:bg-gray-100 dark:hover:bg-gray-900"
            >
              <!-- {{relation}} -->
              <div class="flex items-center">
                <span v-if="relation.category" class="flex-1">
                  {{ relation.category.name }}
                </span>

                <font-awesome-icon :icon="['fal', 'caret-right']" class="flex-1 text-blue-500" />

                <span class="flex-1" :class="{ italic: type === 'box' }">
                  {{ type === "box" ? item.name : relation.box.name }}
                </span>

                <font-awesome-icon :icon="['fal', 'caret-right']" class="flex-1 text-blue-500" />

                <span class="flex-1" :class="{ italic: type === 'option' }">
                  {{ type === "box" ? relation.option.name : item.name }}
                </span>
              </div>
            </li>
          </ul>
        </div>
      </template>
    </SidePanel>
  </div>
</template>

<script>
import SidePanel from "../global/SidePanel.vue";

export default {
  components: { SidePanel },
  props: {
    item: Object,
    type: String,
  },
  data() {
    return {
      selected: this.item,
      relations: [{ name: "bla" }],

      responseMessage: "",
      responseStatusColor: "",
    };
  },
  mounted() {
    this.getRelations();
  },
  methods: {
    getRelations() {
      let prefix;
      this.type === "box" ? (prefix = "boxes") : (prefix = "options");
      axios
        .get(`${prefix}/${this.item.slug}/relations?per_page=99999999`)
        .then((response) => {
          console.log(response);
          this.relations = response.data.data;
        })
        .catch((err) => {
          this.set_notification({
            text: err.response.data.message,
            status: "red",
          });
        });
    },
    close() {
      this.$parent.closeModal();
    },
  },
};
</script>

<style></style>
