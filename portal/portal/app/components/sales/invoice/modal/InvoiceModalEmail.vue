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
            <p class="mb-2 text-sm font-bold tracking-wide uppercase">{{ $t("language") }}</p>
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
          <p class="mb-2 text-sm font-bold tracking-wide uppercase">{{ $t("contents") }}</p>
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
            <div class="flex items-center py-2 pl-2 pr-16 border border-gray-300 rounded-md w-fit">
              <img :src="pdfSVG" alt="pdf" class="!h-8" />
              <p class="ml-2 text-sm">
                <strong>{{ $t("Invoice PDF") }}</strong> <br />
                <em class="text-xs">{{ $t("invoice") }}.pdf</em>
              </p>
            </div>
          </div>
        </main>
        <aside class="flex flex-col h-0 min-h-full overflow-y-auto">
          <p class="mb-2 text-sm font-bold tracking-wide uppercase">{{ $t("tags") }}</p>
          <ul class="flex-1 p-2 space-y-1 overflow-y-auto font-bold border border-gray-200">
            <li v-for="tag in _tags" :key="tag.name" class="select-all">
              <span>{{ tag.name }}</span>
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
import pdfSVG from "~/assets/images/pdf-svgrepo-com.svg";

const props = defineProps({
  invoiceNumber: {
    type: Number,
    required: true,
  },
});

const emit = defineEmits(["onClose", "on-send"]);

const { handleError } = useMessageHandler();
const { availableLocales, locale } = useI18n();

const invoiceRepository = useInvoiceRepository();

const isLoading = ref(true);

const emailDetails = ref({
  language: `${locale.value}`,
  subject: null,
  body: null,
});

const _tags = ref([]);

onMounted(async () => {
  try {
    const { tags } = await invoiceRepository.getEmailTemplate(props.invoiceNumber);
    _tags.value = Object.values(tags).map((tag) => ({ name: tag, description: tag }));
  } catch (err) {
    handleError(err);
  } finally {
    isLoading.value = false;
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
  () => emailDetails.value.language,
  (newLanguage) => {
    if (newLanguage.toLowerCase() === "nl") {
      emailDetails.value.subject = "Factuur voor mr/mvr [[%customer.full_name]]";
      emailDetails.value.body = `Beste [[%customer.full_name]],

Je factuur met nummer [[%invoice.id]] is beschikbaar en eenvoudig te bekijken via bijgelegen PDF.

Bedankt,`;
    } else {
      emailDetails.value.subject = "Invoice for mr/mrs [[%customer.full_name]]";
      emailDetails.value.body = `Dear [[%customer.full_name]],

Your invoice with number [[%invoice.id]] is available and easily accessible via the attached PDF.

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
