<template>
  <div class="relative flex h-full gap-4 p-4 overflow-hidden">
    <section class="w-full md:w-1/4">
      <div class="bg-white rounded shadow-md shadow-gray-200 dark:shadow-gray-900 dark:bg-gray-700">
        <h2 class="px-4 pt-4 pb-2 text-sm font-bold tracking-wide uppercase">
          <font-awesome-icon :icon="['fal', 'diagram-nested']" />
          {{ $t("blueprints") }}
        </h2>

        <div
          class="flex items-center justify-between w-full px-4 py-2 text-base bg-gray-50 dark:bg-gray-900"
        >
          <font-awesome-icon :icon="['fal', 'diagram-nested']" class="mr-1" />
          <font-awesome-icon :icon="['fal', 'plus']" class="mr-2" />

          <input
            v-model="blueprintName"
            type="text"
            class="w-full px-2 py-1 transition-all duration-100 bg-white border rounded-l shadow-inner hover:bg-gray-50 dark:bg-gray-700 dark:border-gray-900 focus:outline-none focus:ring focus:border-theme-200"
            :placeholder="$t('new blueprint')"
          />
          <button
            class="px-2 py-1 rounded-r text-themecontrast-500 bg-theme-500"
            @click="create_blueprint(blueprintName), (blueprintName = '')"
          >
            {{ $t("add") }}
          </button>
        </div>

        <ul
          v-if="blueprints"
          class="relative h-full p-4 overflow-y-auto divide-y dark:divide-black"
          style="max-height: calc(100vh - 12rem)"
        >
          <div class="sticky top-0 flex mb-2">
            <input
              v-model="filter"
              type="text"
              placeholder="Filter blueprints"
              class="w-full px-2 py-1 bg-white border rounded shadow-md border-theme-300 dark:border-gray-900 dark:bg-gray-800 focus:outline-none focus:shadow-outline focus:border-theme-300"
            />
            <font-awesome-icon
              :icon="['fal', 'filter']"
              class="absolute right-0 mt-2 mr-6 text-gray-400"
            />
          </div>

          <template v-if="filtered_blueprints">
            <transition-group name="slide">
              <li v-for="(blueprint, i) in filtered_blueprints" :key="'blueprint' + i">
                <div
                  class="flex items-center justify-between p-2 rounded cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-900 group"
                  :class="{
                    'bg-theme-400 hover:bg-theme-400 dark:hover:bg-theme-500 text-white':
                      selected_blueprint.id === blueprint.id,
                  }"
                  @click="selected_blueprint.id !== blueprint.id ? selectBlueprint(blueprint) : ''"
                >
                  <div class="flex items-center">
                    <span
                      class="w-12 mr-2 font-mono font-bold text-gray-400"
                      :class="{
                        'text-gray-200': selected_blueprint.id === blueprint.id,
                      }"
                    >
                      #<span class="ml-auto text-right">
                        {{ blueprint.id }}
                      </span>
                    </span>
                    {{ blueprint.name }}
                    <span
                      class="text-xs text-gray-400"
                      :class="{
                        'text-gray-200': selected_blueprint.id === blueprint.id,
                      }"
                    >
                      - {{ blueprint.ns }}
                    </span>
                  </div>
                  <span class="mr-2 text-sm italic text-gray-400">
                    {{ blueprint.updated_at }}
                  </span>
                </div>
              </li>
            </transition-group>
          </template>
        </ul>
      </div>
    </section>

    <BluePrintConfiguration
      v-if="Object.keys(selected_blueprint).length > 0"
      :key="selected_blueprint.id"
      class="w-full h-full"
    />
  </div>
</template>

<script>
import { mapState, mapMutations } from "vuex";
export default {
  setup() {
    const api = useAPI();
    const { handleError, handleSuccess } = useMessageHandler();
    return { api, handleError, handleSuccess };
  },
  data() {
    return {
      blueprintName: "",
      url: "",
      pageUrl: "",
      filter: "",
    };
  },
  computed: {
    ...mapState({
      blueprints: (state) => state.blueprint.blueprints,
      selected_blueprint: (state) => state.blueprint.selected_blueprint,
    }),
    filtered_blueprints() {
      if (this.filter.length > 0) {
        return this.blueprints.filter((bp) => {
          return Object.values(bp).some((val) => {
            if (val !== null) {
              return val.toString().toLowerCase().includes(this.filter.toLowerCase());
            }
          });
        });
      }
      return this.blueprints;
    },
  },
  watch: {
    selected_blueprint: {
      deep: true,
      handler(v) {
        return v;
      },
    },
  },
  mounted() {
    this.api.get(`/blueprints`).then((response) => {
      this.populate_blueprints(response.data);
    });
  },
  methods: {
    ...mapMutations({
      populate_import_data: "blueprint/populate_import_data",
      populate_blueprints: "blueprint/populate_blueprints",
      add_blueprint: "blueprint/add_blueprint",
      select_blueprint: "blueprint/select_blueprint",
    }),

    create_blueprint() {
      this.api
        .post("blueprints", {
          name: this.blueprintName,
          ns: "orders.system",
        })
        .then((response) => {
          this.add_blueprint(response.data);
          this.handleSuccess(response);
        })
        .catch((error) => {
          this.handleError(error);
        });
    },
    selectBlueprint(blueprint) {
      this.select_blueprint(blueprint);
    },
  },
};
</script>
