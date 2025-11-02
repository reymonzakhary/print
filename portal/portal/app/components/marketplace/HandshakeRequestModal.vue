<template>
  <confirmation-modal no-scroll classes="w-11/12 sm:w-1/2" @on-close="closeModal">
    <template #modal-header>
      <div class="flex items-center space-x-2">
        <font-awesome-icon :icon="['fal', 'bell-concierge']" />
        <h2 class="font-bold">{{ requestDetails.subject }}</h2>
        <p v-if="producer?.external && producer?.config" class="italic">
          {{ $t("API Connection") }}
        </p>
        <p v-else class="italic">{{ $t("Make it personal!") }}</p>
      </div>
    </template>

    <template #modal-body>
      <article class="gap-4">
        <section class="">
          {{ $t("Please fill in your API info") }}
          <template v-if="producer?.external && producer?.config">
            <div v-for="(key, index) in Object.keys(producer.config.auth)" :key="index">
              <UIInputText
                v-model="requestDetails[key]"
                :prefix="key"
                :name="key"
                :label="key"
                :placeholder="key"
                required
                class="mb-2 !whitespace-normal"
                :disabled="isLoading"
              />
            </div>
          </template>
          <template v-else>
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
            <p class="mb-2 text-sm font-bold uppercase tracking-wide">
              {{ $t("make it personal") }}
            </p>
            <div>
              <UIInputText
                v-model="requestDetails.title"
                name="subject"
                label="Subject"
                placeholder="Subject"
                class="mb-2 !whitespace-normal"
                :disabled="isLoading"
              />
              <UITextArea
                v-model="requestDetails.body"
                name="subject"
                label="Subject"
                placeholder="Subject"
                class="mb-2 !whitespace-normal"
                :disabled="isLoading"
              />
            </div>
          </template>
        </section>
      </article>
    </template>
    <template #confirm-button>
      <UIButton class="px-4 !text-sm" variant="theme" :disabled="isLoading" @click="handleSend">
        {{ $t("request handshake") }}
        <font-awesome-icon
          :icon="['fal', isLoading ? 'spinner-third' : 'hand-holding-hand']"
          class="ml-2"
          :spin="isLoading"
        />
      </UIButton>
    </template>
  </confirmation-modal>
</template>

<script setup>
const props = defineProps({
  producer: {
    type: Object,
    required: true,
  },
  isLoading: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["on-close", "on-send"]);
const { handleError } = useMessageHandler();
const { t: $t } = useI18n();

const { availableLocales, locale } = useI18n();

const requestDetails = ref({
  language: `${locale.value}`,
  subject: "",
  title: "",
  body: null,
});

onMounted(() => {
  if (props.producer?.external && props.producer?.config) {
    Object.keys(props.producer.config.auth).forEach((key) => {
      if (!requestDetails.value[key]) {
        requestDetails.value[key] = "";
      }
    });
  }
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
      requestDetails.value.subject = `Handshake aanvraag`;
      requestDetails.value.title = `Hi ${props.producer?.name}!`;
      requestDetails.value.body = `Wij zouden graag zaken met jullie doen!`;
    } else {
      requestDetails.value.subject = `Handshake request`;
      requestDetails.value.title = `Hi ${props.producer?.name}!`;
      requestDetails.value.body = `We would like to do business with you!`;
    }
  },
  { immediate: true },
);

function handleSend() {
  if (!requestDetails.value.title?.trim() || !requestDetails.value.body?.trim()) {
    handleError($t("Please fill in all required fields"));
    return;
  }
  emit("on-send", requestDetails.value);
}

function closeModal() {
  emit("on-close");
}
</script>
