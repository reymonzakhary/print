<template>
  <div class="p-4">
    <nuxt-link to="/" class="text-theme-500">
      <font-awesome-icon :icon="['fal', 'chevron-left']" />
      {{ $t("back") }}
    </nuxt-link>

    <section class="flex w-full mb-2 bg-gray-100 dark:bg-gray-800">
      <div class="top-0 w-full p-4 md:px-0 lg:w-1/2">
        <UserProfile
          :is-loading="isLoading"
          :profile="user && user.profile"
          :user-id="user && user.id"
          :show-edit="true"
          :has-view-permission="true"
          @profile-updated="fetchMember(user.id)"
        />

        <UserInfo
          class="mt-4"
          :user="user"
          :is-loading="isLoading"
          @user-updated="fetchMember(user.id)"
        />
      </div>

      <div class="w-full p-4 lg:w-1/2">
        <div>
          <AddressList
            :user="user && user"
            :addresses="user && user.addresses"
            :is-loading="isLoading"
            @address-update="fetchMember(user.id)"
            @address-create="fetchMember(user.id)"
            @address-delete="fetchMember(user.id)"
          />
        </div>
      </div>
    </section>
  </div>
</template>

<script>
import { mapState } from "vuex";
export default {
  provide: {
    endpoint: "users",
  },
  setup() {
    const api = useAPI();
    const { handleError, handleSuccess } = useMessageHandler();
    return { api, handleError, handleSuccess };
  },
  data() {
    return {
      user: null,
      isLoading: true,
    };
  },
  head() {
    return {
      title: `${this.$t("my account")} | Prindustry Manager`,
    };
  },
  computed: {
    me() {
      return this.$store.state.settings.me;
    },
  },
  async created() {
    await this.fetchMember(this.me.id);
  },
  methods: {
    fetchMember(id) {
      this.isLoading = true;
      return this.api
        .get(`users/${id}?include_profile=true`)
        .then((response) => {
          this.user = response.data;
          this.isLoading = false;
        })
        .catch((err) => {
          this.handleError(err);
        });
    },
  },
};
</script>
