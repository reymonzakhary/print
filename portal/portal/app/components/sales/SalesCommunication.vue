<template>
  <article class="p-3 text-sm text-gray-700 bg-gray-200 dark:bg-gray-900 dark:text-gray-400">
    <h1 class="mb-2 text-xs font-bold tracking-wide uppercase">
      {{ $t("emails that have been sent") }} :
    </h1>
    <section>
      <ul v-if="reversedMails.length" class="space-y-2">
        <li v-for="email in reversedMails" :key="email.sent_at" class="truncate">
          {{ email.subject }}
        </li>
      </ul>
      <div v-else class="p-2">
        {{ $t("no sent emails") }}
      </div>
    </section>
  </article>
</template>

<script setup>
const props = defineProps({
  mails: {
    type: Array,
    default: () => [],
  },
});

const reversedMails = computed(() => {
  if (Array.isArray(props.mails)) {
    return [...props.mails].reverse();
  } else {
    return [];
  }
});
</script>
