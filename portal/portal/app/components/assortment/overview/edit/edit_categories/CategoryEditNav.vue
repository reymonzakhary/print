<template>
  <main>
    <UICardHeader rounded-full class="max-h-max md:max-h-[42px]">
      <template #center>
        <div class="flex w-full flex-col md:w-auto md:flex-row">
          <UICardHeaderTab
            :label="$t('Info')"
            :icon="['fal', 'memo-circle-info']"
            :active="active_detail === 'info'"
            @click="
              () => {
                setActive('info');
              }
            "
          />

          <UICardHeaderTab
            v-if="!selected_category.ref_id"
            :label="$t('Configuration')"
            :icon="['fal', 'gears']"
            :active="active_detail === 'config'"
            @click="
              () => {
                setActive('config');
              }
            "
          />

          <UICardHeaderTabSeperator v-if="!selected_category.ref_id" class="mx-2" />

          <UICardHeaderTab
            v-if="!selected_category.ref_id"
            :label="$t('Boxes & Options')"
            :icon="['fal', 'box-full']"
            :active="active_detail === 'boops'"
            @click="
              () => {
                setActive('boops');
              }
            "
          />

          <UICardHeaderTab
            v-if="!selected_category.ref_id"
            :label="$t('Excludes')"
            :icon="['fal', 'clipboard-list-check']"
            :active="active_detail === 'excludes'"
            @click="
              () => {
                setActive('excludes');
              }
            "
          />

          <UICardHeaderTabSeperator v-if="!selected_category.ref_id" class="mx-2" />

          <UICardHeaderTab
            v-if="!selected_category.ref_id"
            :label="$t('Calculation')"
            :icon="['fal', 'calculator']"
            :active="active_detail === 'calculation'"
            @click="
              () => {
                setActive('calculation');
              }
            "
          />

          <UICardHeaderTab
            v-if="!selected_category.ref_id"
            :label="$t('Price ranges')"
            :icon="['fal', 'ranking-star']"
            :active="active_detail === 'buffering'"
            @click="
              () => {
                setActive('buffering');
              }
            "
          />

          <UICardHeaderTab
            :label="$t('Margins')"
            :icon="['fal', 'hand-holding-dollar']"
            :active="active_detail === 'margins'"
            @click="
              () => {
                setActive('margins');
              }
            "
          />
        </div>
      </template>
    </UICardHeader>
  </main>
</template>

<script>
import { mapState, mapMutations } from "vuex";
export default {
  props: {
    changesMade: {
      type: Boolean,
      required: true,
    },
  },
  emits: ["continue", "continueNavigation"],
  setup() {
    const { permissions } = storeToRefs(useAuthStore());
    const { confirm } = useConfirmation();
    return {
      permissions,
      confirm,
    };
  },
  computed: {
    ...mapState({
      active_detail: (state) => state.product_wizard.active_detail,
      selected_category: (state) => state.product_wizard.selected_category,
    }),
  },
  watch: {
    active_detail(v) {
      setTimeout(() => {
        this.$parent.loading = "";
      }, 200);

      return v;
    },
    loading(v) {
      return v;
    },
    changesMade(v) {
      return v;
    },
  },
  methods: {
    ...mapMutations({
      set_active_detail: "product_wizard/set_active_detail",
    }),
    async setActive(detail) {
      if (this.changesMade) {
        this.confirm({
          title: this.$t("unsaved changes"),
          message: this.$t("you have unsaved changes. are you sure you want to leave?"),
          confirmOptions: {
            label: this.$t("leave"),
            variant: "warning",
          },
          cancelOptions: {
            label: this.$t("stay"),
            variant: "secondary",
          },
        })
          .then(() => {
            this.$store.commit("settings/setPreventNavigation", true); // Reset the flag if user confirms
            this.$emit("continueNavigation");
            this.set_active_detail(detail);
          })
          .catch(() => {
            // do nothing
          });
      } else {
        this.$store.commit("settings/setPreventNavigation", false); // Reset the flag if user confirms
        this.set_active_detail(detail);
      }
    },
    // close dertails and navigate to products overview
    deactivateDetails() {
      this.set_active_detail("");
      this.$router.push("/assortment");
    },
  },
};
</script>

<style></style>
