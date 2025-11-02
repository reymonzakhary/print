<template>
  <div>
    <UICard
      :key="handshake.sender_name"
      class="max-h-full divide-y overflow-y-auto px-4 shadow-gray-300 dark:shadow-gray-950"
      :rounded-full="true"
    >
      <div class="relative bg-white py-4 dark:bg-gray-700">
        <div class="absolute -right-4 -top-0 bg-gray-50 px-2 text-xs text-gray-500">
          {{ $t("contract") }} {{ handshake.contract.contract_nr }}
        </div>
        <header class="mt-2 flex items-center justify-between">
          <h3 class="flex w-full items-center justify-between gap-2 text-sm">
            <div class="flex flex-col">
              <span class="flex text-xs uppercase text-gray-500 dark:text-gray-300">
                {{ $t("reseller") }}
                <section
                  v-if="
                    handshake.contract?.custom_fields?.discount?.general?.slots.length > 0 &&
                    handshake.contract?.custom_fields?.discount?.general?.slots?.reduce(
                      (max, slot) => (slot.value > max.value ? slot : max),
                    ).type === 'percentage'
                  "
                  class="ml-4 flex -skew-x-12 items-center space-x-2 rounded bg-green-50 text-green-500"
                >
                  <span class="skew-x-12 px-4 font-bold">
                    -
                    {{
                      handshake.contract?.custom_fields?.discount?.general?.slots?.reduce(
                        (max, slot) => (slot.value > max.value ? slot : max),
                      ).value
                    }}
                    <span class="font-mono">%</span>
                  </span>
                </section>
                <section
                  v-if="
                    handshake.contract?.custom_fields?.discount?.general?.slots.length > 0 &&
                    handshake.contract?.custom_fields?.discount?.general?.slots?.reduce(
                      (max, slot) => (slot.value > max.value ? slot : max),
                    ).type === 'fixed'
                  "
                  class="ml-4 flex -skew-x-12 items-center space-x-2 rounded bg-green-50 text-green-500"
                >
                  <span class="skew-x-12 px-4 font-bold">
                    -
                    {{
                      formatCurrency(
                        handshake.contract?.custom_fields?.discount?.general?.slots?.reduce(
                          (max, slot) => (slot.value > max.value ? slot : max),
                        ).value / 1000 || 0,
                      )
                    }}
                  </span>
                </section>
              </span>
              <span class="font-bold">{{ handshake.sender_name }}</span>
            </div>
            <font-awesome-icon
              v-if="handshake.contract.st.name === 'pending'"
              :icon="['fal', 'hand-holding-hand']"
            />
            <font-awesome-icon
              v-if="handshake.contract.st.name === 'accepted'"
              :icon="['fal', 'handshake']"
            />
            <font-awesome-icon
              v-if="handshake.contract.st.name === 'declined'"
              :icon="['fal', 'handshake-slash']"
            />
            <div class="flex flex-col">
              <span class="text-xs uppercase text-gray-500 dark:text-gray-300">
                {{ $t("supplier") }}
              </span>
              <span class="font-bold"> {{ handshake.contract.receiver_name }}</span>
            </div>
            <div />
          </h3>
        </header>

        <!-- more messages from the same sender -->
        <p v-if="handshake.items?.length > 1" class="text-sm">
          {{ handshake.items?.length }} {{ "messages" }}
          <UIButton class="mt-4" :to="`/messages?selected=${handshake.id}`" @click.stop.prevent="">
            {{ $t("open conversation") }}
            <font-awesome-icon :icon="['fal', 'external-link']" class="ml-2" />
          </UIButton>
        </p>
      </div>

      <div
        v-if="handshake.whoami !== 'sender'"
        class="sticky bottom-0 flex w-full justify-between bg-white pb-4 dark:bg-gray-700"
      >
        <UIButton
          v-if="handshake.contract.st.name === 'accepted'"
          class="mt-4"
          variant="theme"
          @click.stop.prevent.self="handleConfigContract(handshake)"
        >
          <font-awesome-icon :icon="['fal', 'gear']" class="mr-2" />
          {{ $t("Contract configuration") }}
        </UIButton>
        <UIButton
          v-if="
            handshake.contract.st.name === 'pending' ||
            handshake.contract.st.name === 'accepted' ||
            handshake.contract.st.name === 'suspended'
          "
          class="mt-4"
          variant="inverted-danger"
          @click="handleReply(handshake, 'rejected')"
        >
          {{ $t("Decline") }}
        </UIButton>
        <UIButton
          v-if="
            handshake.contract.st.name === 'pending' ||
            handshake.contract.st.name === 'rejected' ||
            handshake.contract.st.name === 'suspended'
          "
          class="mt-4"
          variant="success"
          @click="handleReply(handshake, 'accepted')"
        >
          {{ $t("Accept") }}
        </UIButton>

        <UIButton
          v-if="handshake.contract.st.name === 'accepted'"
          class="mt-4"
          variant="inverted-warning"
          @click="handleReply(handshake, 'suspended')"
        >
          {{ $t("Suspend") }}
        </UIButton>
      </div>
    </UICard>
  </div>
</template>

<script setup>
const props = defineProps({
  handshake: {
    type: Object,
    required: true,
  },
  me: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(["reply", "config-contract"]);

const expandMessages = ref(null);
const expandItem = ref(null);
const expandCard = ref(null);

const { formatCurrency } = useMoney();

const handleConfigContract = (handshake) => {
  emit("config-contract", handshake);
};
const handleReply = (handshake, status) => {
  emit("reply", { handshake, status });
};
</script>
