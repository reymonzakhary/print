<template>
  <div>
    <ConfirmationModal no-scroll classes="w-11/12 sm:w-1/2" @on-close="closeModal">
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
            {{ status }}
          </p>
        </div>
      </template>
      <template #modal-body>
        <article class="gap-4">
          <main class="">
            We would like to information from you about our decision to work with you
            <!-- <div>
              <p class="my-2 text-sm font-bold uppercase tracking-wide">language</p>
              <div>
                <UIVSelect
                  :model-value="requestDetails.language"
                  :options="languageOptions"
                  :placeholder="$t('select language')"
                  :reduce="(option) => option.value"
                  @update:model-value="requestDetails.language = $event"
                />
              </div>
            </div> -->
            <hr class="my-4" />
            <p class="mb-2 text-sm font-bold uppercase tracking-wide">make it personal</p>
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
        <div v-if="status === 'accepted'">
          <fieldset class="flex flex-col gap-4 p-4 rounded-md border bg-white">
            <legend class="px-3 pb-1 mt-1 text-sm font-bold uppercase">Contract Information</legend>
            <p class="italic text-gray-600">
              Select the price tiers and the percentages that should be paid on them
            </p>

            <div
              v-for="(run, index) in requestDetails.contract_data.runs"
              :key="run.id"
              class="flex items-end gap-3"
            >
              <div class="px-1 w-4/12">
                <label
                  :for="`run-from-${run.id}`"
                  class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase"
                >
                  From:
                </label>
                <span class="flex items-center">
                  <span class="bg-gray-100 rounded-l p-1 border border-r-0">€</span>
                  <UIInputText
                    v-model="run.from"
                    class="rounded-none rounded-r"
                    :name="`run-from-${run.id}`"
                    placeholder=""
                    required
                    type="number"
                    autocomplete="off"
                  />
                </span>
              </div>
              <div class="px-1 w-4/12">
                <label
                  :for="`run-to-${run.id}`"
                  class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase"
                >
                  To:
                </label>
                <span class="flex items-center">
                  <span class="bg-gray-100 rounded-l p-1 border border-r-0">€</span>
                  <UIInputText
                    v-model="run.to"
                    class="rounded-none rounded-r"
                    :name="`run-to-${run.id}`"
                    placeholder=""
                    required
                    type="number"
                    autocomplete="off"
                  />
                </span>
              </div>
              <div class="px-1 w-4/12">
                <label
                  :for="`run-percenage-${run.id}`"
                  class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase"
                >
                  Percentage:
                </label>
                <span class="flex items-center">
                  <UIInputText
                    v-model="run.percentage"
                    class="rounded-none rounded-l"
                    :name="`run-percenage-${run.id}`"
                    placeholder=""
                    required
                    type="number"
                    autocomplete="off"
                  />
                  <span class="bg-gray-100 rounded-r p-1 border border-l-0">%</span>
                </span>
              </div>
              <div
                v-if="
                  requestDetails.contract_data.runs.length > 1 &&
                  index !== requestDetails.contract_data.runs.length - 1
                "
                class="text-red-500 mb-2"
                @click="removeRun(index)"
              >
                <font-awesome-icon :icon="['fal', 'trash']" />
              </div>
              <div class="flex items-end">
                <div
                  v-if="index === requestDetails.contract_data.runs.length - 1"
                  class="p-2 bg-theme-500 cursor-pointer text-white rounded text-sm"
                  @click.stop="addRun"
                >
                  <font-awesome-icon :icon="['fal', 'plus']" />
                </div>
              </div>
            </div>
          </fieldset>
          <fieldset class="flex flex-col gap-4 p-4 mt-3 rounded-md border bg-white">
            <legend class="px-3 pb-1 mt-1 text-sm font-bold uppercase">Sales Information</legend>
            <div>
              <label
                for="payment-terms"
                class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase"
              >
                <FontAwesomeIcon :icon="['fal', 'calendar']" class="mr-2" />
                Payment Terms:
              </label>
              <UISelector
                v-model="requestDetails.contract_data.payment_terms"
                name="payment-terms"
                :options="options"
              />
            </div>
            <div class="">
              <label class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase">
                Can request quotation
              </label>
              <div class="flex relative items-center">
                No
                <div
                  class="relative mx-2 w-10 h-4 rounded-full transition duration-200 ease-linear cursor-pointer"
                  :class="[requestDetails.can_request_quotation ? 'bg-theme-400' : 'bg-gray-300']"
                >
                  <label
                    for="is-supplier"
                    class="absolute left-0 mb-2 w-4 h-4 bg-white rounded-full border-2 transition duration-100 ease-linear transform cursor-pointer"
                    :class="[
                      requestDetails.can_request_quotation
                        ? 'translate-x-6 border-theme-500'
                        : 'translate-x-0 border-gray-300',
                    ]"
                  />
                  <input
                    id="is-supplier"
                    v-model="requestDetails.can_request_quotation"
                    type="checkbox"
                    name="toggle"
                    class="w-full h-full appearance-none active:outline-none focus:outline-none"
                    @click="
                      requestDetails.can_request_quotation = !requestDetails.can_request_quotation
                    "
                  />
                </div>
                Yes
              </div>
            </div>
          </fieldset>
        </div>
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
          {{ status === "rejected" ? "decline" : status === "suspended" ? "suspend" : "accept" }}
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
    </ConfirmationModal>
  </div>
</template>

<script setup>
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";

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

const STATUS_CODES = {
  ACCEPTED: 320,
  REJECTED: 319,
  SUSPENDED: 327,
};

const requestDetails = ref({
  id: props.handshake.id,
  language: `NL`,
  title: "",
  body: null,
  st:
    props.status === "accepted"
      ? STATUS_CODES.ACCEPTED
      : props.status === "rejected"
        ? STATUS_CODES.REJECTED
        : STATUS_CODES.SUSPENDED,
  contract_data: {
    payment_terms: props.handshake.contract.custom_fields.payment_terms ?? "",
    runs: props.handshake.contract.custom_fields.runs ?? [
      {
        id: 1,
        from: null,
        to: null,
        percentage: null,
      },
    ],
    exchange_rate: {},
  },
  can_request_quotation: props.handshake.can_request_quotation ?? false,
});
const options = ref([
  {
    label: "15 days",
    value: 15,
  },
  {
    label: "30 days",
    value: 30,
  },
  {
    label: "60 days",
    value: 60,
  },
]);
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

const addRun = () => {
  requestDetails.value.contract_data.runs.push({
    id: requestDetails.value.contract_data.runs.at(-1).id + 1,
    from: null,
    to: null,
    percenage: null,
  });
};

const removeRun = (index) => {
  requestDetails.value.contract_data.runs.splice(index, 1);
};
</script>
