<template>
  <div>
    <confirmation-modal no-scroll classes="w-11/12 sm:w-1/2" @on-close="closeModal">
      <template #modal-header>
        <div class="flex items-center space-x-2">
          <font-awesome-icon :icon="['fal', 'bell-concierge']" />
          <h2 class="font-bold">{{ handshake.subject }}</h2>
          <p
            class="font-bold italic"
            :class="{
              'text-red-500': status === 'rejected',
              'text-green-500': status === 'accepted',
              'text-amber-500': status === 'suspended',
            }"
          >
            {{ $t(status) }}
          </p>
        </div>
      </template>
      <template #modal-body>
        <article class="gap-4">
          <main class="">
            {{ $t("We would like to information from you about our decision to work with you") }}
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
          </main>
        </article>
      </template>
      <template #confirm-button>
        <UIButton
          class="px-4 !text-sm"
          :variant="
            status === 'rejected' ? 'danger' : status === 'suspended' ? 'warning' : 'success'
          "
          :disabled="isLoading"
          @click="handleSend(status)"
        >
          {{
            status === "rejected"
              ? $t("decline")
              : status === "suspended"
                ? $t("suspend")
                : $t("accept")
          }}
          <font-awesome-icon
            :icon="[
              'fal',
              isLoading
                ? 'spinner-third'
                : status === 'rejected'
                  ? 'xmark'
                  : status === 'suspended'
                    ? 'minus'
                    : 'check',
            ]"
            class="ml-2"
            :spin="isLoading"
          />
        </UIButton>
      </template>
    </confirmation-modal>
  </div>
</template>

<script setup>
const props = defineProps({
  handshake: {
    type: Object,
    required: true,
  },
  status: {
    type: String,
    required: true,
  },
  isLoading: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["onClose", "on-send"]);

const { availableLocales, locale } = useI18n();

const STATUS_CODES = {
  ACCEPTED: 320,
  REJECTED: 319,
  SUSPENDED: 327,
};

const requestDetails = ref({
  id: props.handshake.id,
  language: `${locale.value}`,
  title: "",
  body: null,
  st:
    props.status === "accepted"
      ? STATUS_CODES.ACCEPTED
      : props.status === "rejected"
        ? STATUS_CODES.REJECTED
        : STATUS_CODES.SUSPENDED,
  contract: {},
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
      requestDetails.value.title = `Hi ${props.handshake?.sender_name}!`;
      requestDetails.value.body = `Wij hebben besloten...`;
    } else {
      requestDetails.value.title = `Hi ${props.handshake?.sender_name}!`;
      requestDetails.value.body = `We decided to...`;
    }
  },
  { immediate: true },
);

function handleSend(status) {
  requestDetails.value.st =
    status === "accepted"
      ? STATUS_CODES.ACCEPTED
      : status === "rejected"
        ? STATUS_CODES.REJECTED
        : STATUS_CODES.SUSPENDED;
  emit("on-send", requestDetails.value);
}

function closeModal() {
  emit("onClose");
}
</script>
