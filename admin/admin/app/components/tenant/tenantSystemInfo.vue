<template>
  <div>
    <fieldset class="flex flex-col gap-4 p-4 rounded-md border bg-white">
      <legend class="px-3 pb-1 mt-1 text-sm font-bold uppercase">Tenant Information</legend>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label
            for="fqdn"
            class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase"
          >
            URL Prefix:
          </label>
          <UIInputText
            v-model="tenant.fqdn"
            prefix="https://"
            affix=".prindustry.com"
            name="fqdn"
            placeholder=""
            required
          />
          <ErrorMessage name="fqdn" as="span" class="text-xs text-red-500" />
        </div>
        <div>
          <label
            for="email"
            class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase"
          >
            Email:
          </label>
          <UIInputText
            v-model="tenant.email"
            name="email"
            type="email"
            placeholder=""
            required
            autocomplete="off"
          />
          <ErrorMessage name="email" as="span" class="text-xs text-red-500" />
        </div>
        <div v-if="!isEditing">
          <label
            for="password"
            class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase"
          >
            Password:
          </label>
          <UIInputText
            v-model="tenant.password"
            name="password"
            type="password"
            placeholder=""
            required
            autocomplete="off"
          />
          <ErrorMessage name="password" as="span" class="text-xs text-red-500" />
        </div>
        <div v-if="!isEditing">
          <label
            for="confirm-password"
            class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase"
          >
            Confirm Password:
          </label>
          <UIInputText
            v-model="tenant.password_confirmation"
            name="confirm-password"
            type="password"
            placeholder=""
            required
            autocomplete="off"
          />
          <ErrorMessage name="password" as="span" class="text-xs text-red-500" />
        </div>
        <div>
          <label
            for="language"
            class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase"
          >
            Language:
          </label>
          <UISelector v-model="tenant.manager_language" name="language" :options="languages" />
        </div>
      </div>
    </fieldset>
  </div>
</template>

<script setup>
const props = defineProps({
  tenant: Object,
  isEditing: {
    type: Boolean,
    default: false,
  },
});

const languages = ref([
  {
    label: "English ( en )",
    value: "en",
  },
  {
    label: "French ( fr )",
    value: "fr",
  },
  {
    label: "Netherlands ( nl )",
    value: "nl",
  },
  {
    label: "German ( de )",
    value: "de",
  },
]);
</script>

<style scoped></style>
