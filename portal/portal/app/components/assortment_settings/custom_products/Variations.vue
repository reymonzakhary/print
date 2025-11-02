<template>
  <div
    class="m-4 mx-auto h-full w-full rounded bg-white p-2 shadow-md shadow-gray-200 dark:bg-gray-700 dark:shadow-gray-900/50 md:w-1/2"
  >
    <transition name="fade">
      <SidePanel v-if="add_variation" @on-close="close()">
        <template #side-panel-header>
          <h2 class="sticky top-0 p-4 font-bold uppercase tracking-wide text-theme-900">
            <font-awesome-icon :icon="['fal', edit ? 'pencil' : 'plus']" class="mr-1" />
            {{ (edit ? $t("update") : $t("add")) + " " + type }}
            <span class="text-theme-500">
              {{
                edit && type === "box" && active_box
                  ? active_box.name
                  : edit && type === "option" && active_option
                    ? active_option.name
                    : ""
              }}
            </span>
          </h2>
        </template>

        <template #side-panel-content>
          <VariationGroupForm
            v-if="type === 'box'"
            :edit="edit"
            :box="active_box"
            :boxes="boxes"
            @on-new-box="(box) => boxes.push(box)"
            @on-update-box="getBoxes()"
          ></VariationGroupForm>
          <VariationOptionForm
            v-if="type === 'option'"
            :edit="edit"
            :option="active_option"
            :boxes="boxes"
          ></VariationOptionForm>
        </template>
      </SidePanel>
    </transition>

    <header
      class="flex items-center justify-between rounded-t px-2 py-1 font-bold uppercase tracking-wide dark:border-gray-900"
    >
      {{ $t("Variations") }}

      <span>
        <button
          v-if="permissions.includes('custom-assortments-boxes-create')"
          class="mr-4 text-theme-500"
          @click="(set_add_variation(true), set_type('box'))"
        >
          <font-awesome-icon :icon="['fal', 'plus']" />
          <font-awesome-icon :icon="['fal', 'crate-apple']" />
          {{ $t("box") }}
        </button>
        <button
          v-if="permissions.includes('custom-assortments-options-create')"
          class="text-theme-500"
          @click="(set_add_variation(true), set_type('option'))"
        >
          <font-awesome-icon :icon="['fal', 'plus']" />
          <font-awesome-icon :icon="['fal', 'apple-whole']" />
          {{ $t("option") }}
        </button>
      </span>
    </header>

    <VariationBoxList
      v-if="permissions.includes('custom-assortments-products-variations-access')"
      :boxes="boxes"
      :i="1"
      @on-delete-box="deleteBox"
      @on-delete-option="deleteOption"
    />
  </div>
</template>

<script>
import { mapState, mapMutations } from "vuex";

export default {
  name: "Variations",
  setup() {
    const api = useAPI();
    const { permissions } = storeToRefs(useAuthStore());
    const eventStore = useEventStore();
    const { handleError, handleSuccess } = useMessageHandler();
    return { permissions, api, handleError, handleSuccess, eventStore };
  },
  data() {
    return {
      variations_filter: "",
      boxes: [],
    };
  },
  computed: {
    ...mapState({
      add_variation: (state) => state.assortmentsettings.add_variation,
      type: (state) => state.assortmentsettings.type,
      edit: (state) => state.assortmentsettings.edit,
      active_box: (state) => state.assortmentsettings.active_box,
      active_option: (state) => state.assortmentsettings.active_option,
    }),
  },
  created() {
    this.getBoxes();
    this.eventStore.on("update-boxes", () => {
      this.getBoxes();
    });
  },
  unmounted() {
    this.eventStore.off("update-boxes");
  },
  methods: {
    ...mapMutations({
      set_active_box: "assortmentsettings/set_active_box",
      set_active_option: "assortmentsettings/set_active_option",
      set_add_variation: "assortmentsettings/set_add_variation",
      set_type: "assortmentsettings/set_type",
      set_edit: "assortmentsettings/set_edit",
    }),

    getBoxes() {
      this.api
        .get("custom/boxes")
        .then((response) => {
          this.boxes = response.data;
        })
        .catch((error) => {
          this.handleError(error);
        });
    },

    deleteBox(id) {
      this.api
        .delete(`custom/boxes/${id}`)
        .then((response) => {
          this.handleSuccess(response);
          this.getBoxes();
        })
        .catch((error) => this.handleError(error));
    },
    deleteOption(id) {
      this.api
        .delete(`custom/options/${id}`)
        .then((response) => {
          this.handleSuccess(response);
          this.getBoxes();
        })
        .catch((error) => this.handleError(error));
    },
    close() {
      this.set_add_variation(false);
    },
  },
};
</script>
