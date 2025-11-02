<template>
  <confirmation-modal no-scroll classes="w-11/12 sm:w-1/2" @on-close="closeModal">
    <template #modal-header>
      <font-awesome-icon :icon="['fal', 'bell-concierge']" class="mr-2" />
      {{ $t("We would like some information from you") }}
    </template>

    <template #modal-body>
      <article class="gap-4">
        <main class="">
          {{ $t("We would like some information from you to help us review your request") }}
          <div>
            <p class="my-2 text-sm font-bold uppercase tracking-wide">{{ $t("language") }}</p>
            <div>
              <UIVSelect
                :model-value="requestDetails.language"
                :options="languageOptions"
                :placeholder="$t('select language')"
                :reduce="(option) => option.value"
                @update:model-value="requestDetails.language = $event"
              />
            </div>
          </div>
          <hr class="my-4" />
          <p class="mb-2 text-sm font-bold uppercase tracking-wide">{{ $t("title") }}</p>
          <div>
            <UIInputText
              v-model="requestDetails.title"
              name="subject"
              label="Subject"
              placeholder="Subject"
              class="mb-2"
              :disabled="isLoading"
            />
          </div>
          <hr class="my-4" />
          <p class="mb-2 text-sm font-bold uppercase tracking-wide">{{ $t("message") }}</p>
          <div>
            <UITextArea
              v-model="requestDetails.body"
              name="body"
              label="Body"
              placeholder="Body"
              class="mb-2"
              :disabled="isLoading"
            />
          </div>
        </main>
      </article>
    </template>
    <template #confirm-button>
      <button
        variant="theme"
        class="rounded-full bg-gradient-to-r from-theme-400 to-pink-500 px-2 py-1 text-sm tracking-wide text-white transition-all hover:bg-gradient-to-l"
        :disabled="isLoading"
        @click="handleSend"
      >
        {{ $t("request producer status") }}
        <font-awesome-icon v-if="isLoading" :icon="['fal', 'spinner']" class="ml-2" spin />
      </button>
    </template>
  </confirmation-modal>
</template>

<script setup>
const emit = defineEmits(["onClose", "on-send"]);

const { availableLocales, locale } = useI18n();

const isLoading = ref(false);

const requestDetails = ref({
  language: `${locale.value}`,
  title: "",
  subject: "producer request",
  body: null,
  to: "cec",
  type: "producer",
});

const languageOptions = computed(() => {
  return availableLocales.map((locale) => {
    return {
      value: locale,
      label: new Intl.DisplayNames([locale], { type: "language" }).of(locale),
    };
  });
});

watch(
  () => requestDetails.value.language,
  (newLanguage) => {
    if (newLanguage.toLowerCase() === "nl") {
      requestDetails.value.title = `Hi Prindustry,`;
      requestDetails.value.body = `Wij zouden graag producent worden op jullie platform om ons aanbod te tonen in de marktplaats. Kunnen jullie mij helpen met het aanmaken van een producentenaccount?
      
Wij bieden de volgende product soorten aan:
- [product 1]
- [product 2]
- [product 3]

Bedankt!`;
    } else {
      requestDetails.value.title = `Hi Prindustry,`;
      requestDetails.value.body = `We would like to become a producer on your platform to showcase our offerings in the marketplace. Can you help me create a producer account?
      
We offer the following product types:
- [product 1]
- [product 2]
- [product 3]

Thank you!`;
    }
  },
  { immediate: true },
);

function handleSend() {
  emit("on-send", requestDetails.value);
}

function closeModal() {
  emit("onClose");
}
</script>
