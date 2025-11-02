<template>
  <UICard rounded-full>
    <div class="grid grid-cols-4 items-center gap-4 px-4 py-2">
      <div
        v-tooltip="{ content: serviceDetails.name ?? '', disabled: (serviceDetails.name?.length ?? 0) < 20 }"
        class="flex-1 truncate"
      >
        <h1>
          <font-awesome-icon :icon="['fal', 'bell-concierge']" class="mr-2" />
          {{ serviceDetails.name }}
        </h1>
      </div>
      <p
        v-if="serviceDetails.description"
        v-tooltip="{
          content: serviceDetails.description,
          disabled: serviceDetails.description.length < 20,
        }"
        class="flex-1 truncate"
      >
        {{ serviceDetails.description }}
      </p>
      <p v-else class="flex-1 text-gray-500">{{ $t("no description") }}</p>

      <section class="col-span-2 flex">
        <div class="flex-1 truncate">
          <UIInputText
            v-if="editing"
            v-model="serviceDetails.qty"
            name="qty"
            s
            type="number"
            class="inline-block"
            :affix="['fas', 'xmark']"
          />
          <small v-else class="mr-2 font-normal">
            {{ serviceDetails.qty ?? 0 }}
            <strong>x</strong>
          </small>
        </div>
        <div class="flex-1">
          <template v-if="!editing">
            <span class="text-xs font-bold uppercase tracking-wide">
              {{ $t("price") }}
            </span>
            <small class="ml-2 font-normal">{{ serviceDetails.display_price }}</small>
          </template>
          <UICurrencyInput v-if="editing" v-model="serviceDetails.price">
            <template #affix>
              <span class="text-xs font-bold uppercase tracking-wide">
                {{ $t("price") }}
              </span>
            </template>
          </UICurrencyInput>
        </div>
        <div class="flex-1 truncate text-right">
          <UIInputText
            v-if="editing"
            v-model="serviceDetails.vat"
            name="vat"
            type="number"
            class="inline-block"
            :affix="['fas', 'percent']"
          />
          <small v-else class="mr-2 font-normal">
            {{ Math.floor(serviceDetails.vat) }} <strong>%</strong> {{ $t("VAT") }}
          </small>
        </div>
        <template v-if="isEditable">
          <div v-if="editing" class="flex-1 text-right">
            <UIButton
              :icon="['fas', 'check']"
              variant="success"
              class="mr-1"
              s
              @click="handleServiceSave"
            />
            <UIButton
              v-if="mayDelete"
              :icon="['fas', 'trash']"
              variant="danger"
              class="mr-1"
              @click="handleServiceRemove"
            />
            <UIButton :icon="['fas', 'xmark']" variant="neutral-light" @click="editing = false" />
          </div>
          <div v-else class="flex-1 text-right">
            <UIButton
              v-if="mayEdit"
              :icon="['fas', 'pencil']"
              variant="link"
              @click="mayEdit && (editing = true)"
            />
          </div>
        </template>
      </section>
    </div>
  </UICard>
</template>

<script setup>
const props = defineProps({
  service: {
    type: Object,
    required: true,
  },
  mayEdit: {
    type: Boolean,
    default: true,
  },
  mayDelete: {
    type: Boolean,
    default: true,
  },
  quotation: {
    type: Object,
    default: () => {},
  },
});

const { statusMap } = useOrderStatus();

const emit = defineEmits(["onRemove", "onUpdate"]);

const editing = ref(false);
const serviceDetails = ref({});

watch(
  () => props.service,
  (newVal) => (serviceDetails.value = { ...newVal }),
  { deep: true, immediate: true },
);

const { isEditable } = storeToRefs(useSalesStore());

watch(
  () => isEditable.value,
  (newVal) => {
    if (!newVal) {
      editing.value = false;
    }
  },
  { deep: true, immediate: true },
);

watch(
  () => props.quotation.status.code,
  (newVal) => {
    if (newVal === statusMap.NEW) {
      editing.value = false;
    }
  },
  { deep: true, immediate: true },
);

async function handleServiceSave() {
  const updatedService = {
    id: serviceDetails.value.id,
    price: Number(serviceDetails.value.price),
    vat: Number(serviceDetails.value.vat),
    qty: Number(serviceDetails.value.qty),
  };
  emit("onUpdate", updatedService);
  editing.value = false;
}

const handleServiceRemove = () => emit("onRemove", serviceDetails.value.id);
</script>
