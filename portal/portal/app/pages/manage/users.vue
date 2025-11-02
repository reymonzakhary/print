<template>
  <UsersPage :endpoint="endpoint" />
</template>

<script>
export default {
  data() {
    return {
      users: [],
      isFetchingusers: false,
      endpoint: "users",
    };
  },
  head() {
    return {
      title: `${this.$t("users")} | Prindustry Manager`,
    };
  },
};
</script>
