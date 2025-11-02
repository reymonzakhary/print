<template>
  <section>
    <div v-if="!noHeader" class="flex justify-between mb-1">
      <p class="text-xs font-bold tracking-wide uppercase">
        {{ $t("team") }}
      </p>
    </div>
    <template v-if="isEditable">
      <UIVSelect
        :class="dropdownClass"
        :disabled="disabled || reducedTeams.length <= 1"
        :options="reducedTeams"
        :model-value="modelValue ? Number(modelValue) : null"
        :reduce="(option) => option.value"
        :placeholder="$t('select a team')"
        :icon="['fal', 'users']"
        @update:model-value="$emit('update:model-value', $event)"
      />
      <div
        v-if="reducedTeams.length === 0 && !disabled"
        class="p-2 pt-3 -mt-1 text-xs text-orange-500 bg-orange-100 dark:text-orange-400"
      >
        <p>
          <font-awesome-icon :icon="['fad', 'triangle-exclamation']" class="mr-1 text-orange-400" />
          {{ $t("in order to continue the chosen member must be member of at least one team.") }}
        </p>
      </div>
    </template>
    <template v-else>
      <p
        v-if="teams.find((team) => team.id === modelValue)?.name"
        class="font-bold text-gray-500 bg-gray-100 dark:bg-gray-800 dark:text-gray-400"
        :class="overviewClass"
      >
        <font-awesome-icon :icon="['fad', 'circle-info']" class="mr-1 text-gray-400" />
        {{ teams.find((team) => team.id === modelValue)?.name }}
      </p>
    </template>
  </section>
</template>

<script>
export default {
  provide: {
    endpoint: "members",
  },
  props: {
    modelValue: {
      type: [Number, String, null],
      required: true,
    },
    dropdownClass: {
      type: String,
      default: "",
    },
    noHeader: {
      type: Boolean,
      default: false,
    },
    disabled: {
      type: Boolean,
      default: false,
    },
    overviewClass: {
      type: [String, Object],
      default: "",
    },
  },
  emits: ["update:model-value"],
  setup() {
    const { isEditable, teams } = storeToRefs(useSalesStore());
    return { isEditable, teams };
  },
  data() {
    return {
      showTeamFormModal: false,
      reducedTeams: [],
    };
  },
  watch: {
    teams: {
      handler() {
        if (!this.teams) {
          return;
        }
        const reduced = this.teams.map((team) => ({
          value: team.id,
          label: team.name,
        }));

        if (reduced.length === 1) {
          this.$emit("update:model-value", reduced[0].value);
        }

        this.reducedTeams = reduced;
      },
      deep: true,
      immediate: true,
    },
  },
};
</script>
