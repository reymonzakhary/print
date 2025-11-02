<template>
  <ConfirmationModal @on-close="closeModal">
    <template #modal-header>
      <span class="capitalize">{{ $t("remove") }} </span>
      {{
        assortment_flag === "print_product"
          ? $display_name(selected_category.display_name)
          : active_custom_category.name
      }}
    </template>

    <template #modal-body>
      <!-- <pre>{{ active_custom_category }}</pre> -->
      <section class="flex flex-wrap max-w-lg">
        <div class="max-h-screen p-2" style="min-width: 400px">
          {{ $t("will remove") }}
          <b>{{
            assortment_flag === "print_product"
              ? $display_name(selected_category.display_name)
              : active_custom_category.name
          }}</b
          >. {{ $t("are you sure") }}

          <p class="mt-4 text-gray-500">
            {{ $t("created at") }}
            <span class="font-mono">
              {{
                moment(selected_category.created_at).format("DD-MM-YYYY HH:MM")
              }}
            </span>
          </p>
        </div>
      </section>
    </template>

    <template #confirm-button>
      <button
        class="px-5 py-1 mr-2 text-sm text-white transition-colors bg-red-500 rounded-full hover:bg-red-700"
        @click="deleteCategory()"
      >
        {{ $t("remove") }}
      </button>
    </template>
  </ConfirmationModal>
</template>

<script>
import { mapState, mapMutations, mapActions } from "vuex";
import moment from "moment";

export default {
  name: "CategoryRemoveItem",
  emits: ["on-category-delete"],
  setup() {
    const api = useAPI();
    const { handleError, handleSuccess } = useMessageHandler();
    return { api, handleError, handleSuccess };
  },
  data() {
    return {
      moment: moment,
    };
  },
  computed: {
    ...mapState({
      selected_category: (state) => state.product_wizard.selected_category,
      active_custom_category: (state) => state.product.active_custom_category,
      assortment_flag: (state) => state.product.assortment_flag,
    }),
  },
  methods: {
    ...mapMutations({
      set_boops: "product/set_boops",
      set_component: "product/set_component",
    }),
    ...mapActions({
      get_categories: "product/get_categories",
      get_custom_categories: "product/get_custom_categories",
    }),

    deleteCategory() {
      if (this.assortment_flag === "print_product") {
        this.api
          .delete(`categories/${this.selected_category.slug}`)
          .then((response) => {
            this.$emit("on-category-delete", this.selected_category);
            this.handleSuccess(response);
            this.set_component("");
          })
          .catch((error) => this.handleError(error));
      } else {
        this.api
          .delete(`custom/categories/${this.active_custom_category.id}`)
          .then((response) => {
            this.$emit("on-category-delete", this.active_custom_category);
            this.handleSuccess(response);
            this.set_component("");
          })
          .catch((error) => this.handleError(error));
      }
    },

    closeModal() {
      this.set_component("");
    },
  },
}; //End Export
</script>
