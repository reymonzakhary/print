<template>
  <confirmation-modal no-scroll classes="w-11/12 sm:w-1/2" @on-close="closeModal">
    <template #modal-header>
      <font-awesome-icon :icon="['fal', 'bell-concierge']" class="mr-2" />
      {{ $t("would you like to edit this email?") }}
    </template>

    <template #modal-body>
      <article class="grid grid-cols-[1fr_,_minmax(150px_,_auto)] gap-4">
        <main>
          <div>
            <p class="mb-2 text-sm font-bold uppercase tracking-wide">{{ $t("language") }}</p>
            <div>
              <UIVSelect
                :model-value="emailDetails.language"
                :options="languageOptions"
                :placeholder="$t('select language')"
                :reduce="(option) => option.value"
                @update:model-value="emailDetails.language = $event"
              />
            </div>
          </div>
          <hr class="my-4" />
          <p class="mb-2 text-sm font-bold uppercase tracking-wide">{{ $t("contents") }}</p>
          <div>
            <UIInputText
              v-model="emailDetails.subject"
              name="subject"
              label="Subject"
              placeholder="Subject"
              class="mb-2"
              :disabled="isLoading"
            />
            <UITextArea
              v-model="emailDetails.body"
              name="body"
              label="Body"
              placeholder="Body"
              class="mb-2"
              :disabled="isLoading"
            />
          </div>
        </main>
        <aside class="flex h-0 min-h-full flex-col overflow-y-auto">
          <p class="mb-2 text-sm font-bold uppercase tracking-wide">{{ $t("tags") }}</p>
          <ul class="flex-1 space-y-1 overflow-y-auto border border-gray-200 p-2 font-bold">
            <li
              v-for="tag in _tags"
              :key="tag.name"
              v-tooltip="tag.name"
              class="flex cursor-pointer items-center justify-between rounded p-1 font-normal text-theme-500 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700"
              @click="copyTag(tag.name)"
            >
              <span>{{ tag.description.replace(/\[\[%*|]]/g, "").replace(/\./g, " ") }}</span>
            </li>
          </ul>
        </aside>
      </article>
    </template>
    <template #confirm-button>
      <UIButton variant="theme" class="!text-sm" :disabled="isLoading" @click="handleSend">
        {{ $t("send email to customer") }}
        <font-awesome-icon v-if="isLoading" :icon="['fal', 'spinner']" class="ml-2" spin />
      </UIButton>
    </template>
  </confirmation-modal>
</template>

<script setup>
const props = defineProps({
  salesId: {
    type: [Number, String],
    required: true,
  },
});
const emit = defineEmits(["onClose", "on-send"]);

const { availableLocales, locale } = useI18n();
const { t: $t } = useI18n();
const quotationRepository = useQuotationRepository();
const { addToast } = useToastStore();

const isLoading = ref(true);

const emailDetails = ref({
  language: `${locale.value}`,
  subject: null,
  body: null,
});

const _tags = ref([]);

onMounted(async () => {
  try {
    const { tags } = await quotationRepository.getEmailTemplate(props.salesId);
    _tags.value = Object.values(tags).map((tag) => ({
      name: tag,
      description: tag,
    }));
  } catch (err) {
    console.error(err);
  } finally {
    isLoading.value = false;
  }
});

const copyTag = (tag) => {
  const textarea = document.createElement("textarea");
  //Settings its value to the thing you want to copy
  textarea.value = tag;
  //Appending the textarea to body
  document.body.appendChild(textarea);
  //Selecting its content
  textarea.focus();
  textarea.select();
  //Copying the selected content to clipboard
  document.execCommand("copy");
  //Removing the textarea
  document.body.removeChild(textarea);

  addToast({
    type: "success",
    message: "copied!",
  });
};

const languageOptions = computed(() => {
  return availableLocales.map((locale) => {
    return {
      value: locale,
      label: new Intl.DisplayNames([locale], { type: "language" }).of(locale),
    };
  });
});

watch(
  () => emailDetails.value.language,
  (newLanguage) => {
    if (newLanguage.toLowerCase() === "nl") {
      emailDetails.value.subject = "Offerte voor mr/mvr [[%customer.full_name]]";
      emailDetails.value.body = `Beste [[%customer.full_name]],

Bedankt dat we u deze offerte met ID [[%quotation.id]] mogen sturen. Laat ons alstublieft weten met de onderstaande knoppen of u deze offerte accepteert of afwijst.

Bedankt,`;
    } else {
      emailDetails.value.subject = "Quotation for mr/mrs [[%customer.full_name]]";
      emailDetails.value.body = `Dear [[%customer.full_name]],

Thank you for allowing us to send you this quotation with ID [[%quotation.id]]. Please let us know with the buttons below if you accept or reject this quotation.

Thank you,`;
    }
  },
  { immediate: true },
);

function handleSend() {
  emit("on-send", emailDetails.value);
}

function closeModal() {
  emit("onClose");
}
</script>
