<template>
  <confirmation-modal
    no-scroll
    classes="w-11/12 sm:w-1/2 lg:w-1/3 2xl:w-1/4"
    @on-close="closeModal"
  >
    <template #modal-header>
      <font-awesome-icon :icon="['fal', 'bell-concierge']" class="mr-2" />
      {{ $t("add service") }}
    </template>

    <template #modal-body>
      <div>
        <details open>
          <summary>
            <span class="text-sm font-bold uppercase tracking-wide">
              {{ $t("create new service") }}
            </span>
          </summary>
        </details>
        <div data-details>
          <fieldset class="mt-4">
            <div class="grid grid-cols-[1fr_,_1fr] gap-4">
              <div>
                <label
                  for="service-name"
                  class="mb-1 block text-xs font-bold uppercase tracking-wide text-black"
                >
                  {{ $t("name") }}
                </label>
                <UIInputText
                  v-model="newService.name"
                  name="service-name"
                  placeholder="name"
                  type="text"
                />
              </div>
              <div>
                <label
                  for="service-price"
                  class="mb-1 block text-xs font-bold uppercase tracking-wide text-black"
                >
                  {{ $t("quantity") }}
                </label>
                <UIInputText
                  v-model="newService.qty"
                  type="number"
                  name="service-qty"
                  placeholder="1"
                  min="1"
                />
              </div>
              <div>
                <label
                  for="service-price"
                  class="mb-1 block text-xs font-bold uppercase tracking-wide text-black"
                >
                  {{ $t("price") }}
                </label>
                <UICurrencyInput v-model="newService.price" input-class="w-full !py-1" />
              </div>
              <div>
                <label
                  for="service-price"
                  class="mb-1 block text-xs font-bold uppercase tracking-wide text-black"
                >
                  {{ $t("vat") }} <small>({{ $t("in %") }})</small>
                </label>
                <UIInputText
                  v-model="newService.vat"
                  type="number"
                  name="service-vat"
                  placeholder="vat"
                />
              </div>
              <div class="col-span-2">
                <label
                  for="service-description"
                  class="mb-1 block text-xs font-bold uppercase tracking-wide text-black"
                >
                  {{ $t("description") }}
                </label>
                <UITextArea
                  v-model="newService.description"
                  name="service-description"
                  type="text"
                />
              </div>
            </div>
          </fieldset>
        </div>
        <hr class="my-4" />
        <details v-tooltip="$t('coming soon')" :open="false" class="mb-2" disabled>
          <summary>
            <span class="text-sm font-bold uppercase tracking-wide">
              {{ $t("add existing service") }}
            </span>
          </summary>
        </details>
      </div>
    </template>
    <template #confirm-button>
      <UIButton
        variant="theme"
        class="!text-sm"
        :disabled="props.disabled"
        @click="handleCreateAndAddService"
      >
        {{ $t("Add service") }}
      </UIButton>
    </template>
  </confirmation-modal>
</template>

<script setup>
const props = defineProps({
  disabled: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["onClose", "on-add-service"]);
const { settings } = storeToRefs(useAuthStore());

const newService = ref({
  name: "",
  vat: settings.value.data.find((setting) => setting.key === "vat").value,
  price: 0,
  description: "",
  qty: 1,
});

function handleCreateAndAddService() {
  emit("on-add-service", newService.value);
}

function closeModal() {
  emit("onClose");
}
</script>
