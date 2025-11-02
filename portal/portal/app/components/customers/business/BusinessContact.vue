<template>
  <UICardHeader>
    <template #left>
      <UICardHeaderTitle
        :icon="['fal', 'user']"
        :title="$t('Contact Information')"
      />
    </template>
    <template v-if="showEdit" #right>
      <UIButton
        :icon="['fal', 'pencil']"
        :disabled="isLoading"
        @click="handleEditContact"
      />
    </template>
  </UICardHeader>
  <UICard>
    <div class="p-2 flex flex-col gap-4 overflow-hidden">
      <div class="grid grid-cols-2">
        <span class="capitalize">{{ $t("Full name") }}</span>
        <span v-tooltip.bottom="contact.name" class="font-semibold">
          {{ contact.name }}
        </span>
      </div>
      <div class="grid grid-cols-2">
        <span class="capitalize">{{ $t("email") }}</span>
        <span v-tooltip.bottom="contact.email" class="font-semibold">{{
          contact.email
        }}</span>
      </div>
      <div class="grid grid-cols-2">
        <span class="capitalize">{{ $t("Phonenumber") }}</span>
        <span v-tooltip.bottom="contact.phone" class="font-semibold">{{
          contact.phone
        }}</span>
      </div>
    </div>
  </UICard>
</template>

<script setup>
const { contact } = defineProps({
  contact: {
    type: Object,
    required: true,
    validator: (value) => {
      const requiredList = ["name", "email", "phone"];
      return requiredList.every((key) => key in value);
    },
  },
  showEdit: {
    type: Boolean,
    default: true,
  },
});

const isLoading = ref(false);

async function handleEditContact() {
  // console.log("Edit Contact");
}
</script>

<style lang="scss" scoped>
.list-item {
  @apply grid grid-cols-2;
}
</style>
