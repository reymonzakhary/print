<template>
  <confirmation-modal @on-close="closeModal">
    <template #modal-header>
      {{ $t("update") }}
      {{ user.profile ? `${user.profile.first_name} ${user.profile.last_name}` : user.name }}'s
      {{ $t("teams") }}
    </template>

    <template #modal-body>
      <!-- filter -->
      <div class="sticky top-10 z-10 flex pb-10">
        <input
          ref="filter"
          v-model="filter"
          type="text"
          class="w-full rounded border border-theme-300 bg-white px-2 py-1 text-black focus:outline-none focus:ring dark:border-gray-900 dark:bg-gray-700"
          placeholder="filter"
        />
        <font-awesome-icon
          class="absolute right-0 mr-4 mt-2 text-gray-600"
          :icon="['fal', 'filter']"
        />
      </div>

      <UILoader v-if="isLoading" />
      <ul v-else class="divide-y">
        <li
          v-for="team in filtered_teams"
          :key="`team_${team.id}`"
          class="group flex w-full flex-wrap items-center justify-between py-4 pl-2 hover:bg-gray-100 dark:hover:bg-gray-900"
        >
          <div class="flex w-full items-center justify-between font-bold">
            <ValueSwitch
              :name="team.name"
              :set-checked="!!selectedTeams.find((selected) => selected.id === team.id)"
              classes="justify-between w-full"
              @checked-value="updateTeams(team.id, 'toggleTeam')"
            />
          </div>

          <div
            v-if="selectedTeams.find((selected) => selected.id === team.id)"
            class="flex w-full items-center justify-between py-2"
          >
            {{ $t("team admin") }}
            <ValueSwitch
              :name="`${team.name}_admin`"
              :display-name="false"
              :set-checked="!!selectedTeams.find((selected) => selected.id === team.id).admin"
              classes="justify-between font-normal"
              @checked-value="updateTeams(team.id, 'toggleAdmin')"
            />
          </div>

          <div
            v-if="selectedTeams.find((selected) => selected.id === team.id)"
            class="flex w-full items-center justify-between"
          >
            {{ $t("team authorizer") }}
            <ValueSwitch
              :name="`${team.name}_authorizer`"
              :display-name="false"
              :set-checked="!!selectedTeams.find((selected) => selected.id === team.id).authorizer"
              classes="justify-between font-normal"
              @checked-value="updateTeams(team.id, 'toggleAuthorizer')"
            />
          </div>
        </li>
      </ul>
    </template>

    <template #confirm-button>
      <button
        class="mr-2 rounded-full bg-green-500 px-4 py-1 text-sm text-white transition-colors hover:bg-green-400"
        aria-label="Close modal"
        @click.once="updateUser"
      >
        {{ $t("Update teams") }}
      </button>
    </template>
  </confirmation-modal>
</template>

<script>
export default {
  name: "EditTeamsModal",
  inject: ["endpoint"],
  props: {
    user: {
      type: [Object, null],
      required: true,
    },
  },
  emits: ["teams-updated", "close-modal"],
  setup() {
    const api = useAPI();
    const { addToast } = useToastStore();
    const { handleError } = useMessageHandler();
    return { addToast, api, handleError };
  },
  data() {
    return {
      filter: "",
      teams: [],
      isLoading: false,
      selectedTeams: [],
    };
  },
  computed: {
    filtered_teams() {
      return this.teams.filter((team) => {
        return team.name.toLowerCase().includes(this.filter.toLowerCase());
      });
    },
  },
  mounted() {
    this.fetchTeams();
  },
  methods: {
    async fetchTeams() {
      this.isLoading = true;
      this.api
        .get("teams?per_page=99999")
        .then((response) => {
          this.teams = response.data;
          this.populateSelectedTeams();
        })
        .catch((err) => {
          this.handleError(err);
        })
        .finally(() => {
          this.isLoading = false;
        });
    },
    populateSelectedTeams() {
      if (this.user.teams && this.user.teams.length > 0) this.selectedTeams = [];
      this.selectedTeams = this.user.teams.map((team) => {
        return {
          id: team.id,
          admin: team.admin,
          authorizer: team.authorizer,
        };
      });
    },
    updateTeams(teamId, action) {
      const teamIndex = this.selectedTeams.findIndex((selected) => selected.id === teamId);

      switch (action) {
        case "toggleTeam":
          if (teamIndex === -1) {
            this.selectedTeams.push({
              id: teamId,
              admin: false,
              authorizer: false,
            });
          } else {
            this.selectedTeams.splice(teamIndex, 1);
          }
          break;
        case "toggleAdmin":
          this.selectedTeams[teamIndex].admin = !this.selectedTeams[teamIndex].admin;
          break;
        case "toggleAuthorizer":
          this.selectedTeams[teamIndex].authorizer = !this.selectedTeams[teamIndex].authorizer;
          break;
        default:
          this.handleError("Invalid action");
      }
    },
    updateUser() {
      this.api
        .put(`${this.endpoint}/${this.user.id}`, {
          teams: this.selectedTeams,
          type: "individual",
        })
        .then(() => {
          this.addToast({
            message: this.$t(`successfully updated teams`),
            type: "success",
          });
          this.$emit("teams-updated", this.selectedTeams);
          this.closeModal();
        })
        .catch((err) => {
          this.handleError(err);
        });
    },
    closeModal() {
      this.$emit("close-modal");
    },
  },
};
</script>
