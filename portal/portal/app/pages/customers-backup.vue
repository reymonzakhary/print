<template>
  <UsersPage endpoint="members" />
</template>

<script>
export default {
  head() {
    return {
      title: `${this.$t("Customers")} | Prindustry Manager`,
    };
  },
}
</script>
